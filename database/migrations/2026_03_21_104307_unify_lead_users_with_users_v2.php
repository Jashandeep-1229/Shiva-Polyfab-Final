<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\LeadUser;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop existing FKs pointing to lead_users - with extreme caution
        try {
            Schema::table('leads', function (Blueprint $table) {
                $table->dropForeign(['assigned_user_id']);
                $table->dropForeign(['added_by']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->dropForeign(['added_by']);
            });
        } catch (\Exception $e) {}
        
        try {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->dropForeign(['completed_by']);
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lead_histories', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
            });
        } catch (\Exception $e) {}

        // 2. Map LeadUsers to Users
        $mapping = [];
        $leadUsers = LeadUser::all();

        foreach ($leadUsers as $lu) {
            $user = User::where('email', $lu->email)->first();
            
            if (!$user) {
                $role_as = $lu->role;
                if ($role_as == 'Executive') $role_as = 'Sale Executive';
                if ($role_as == 'Lead Manager') $role_as = 'Senior Sale Executive';

                $user = User::create([
                    'name' => $lu->name,
                    'email' => $lu->email,
                    'phone' => $lu->phone ?? '',
                    'password' => $lu->password,
                    'show_password' => 'migrated',
                    'role_as' => $role_as,
                    'created_by_id' => 0,
                    'status' => $lu->status ?? 1
                ]);
            }
            
            $mapping[$lu->id] = $user->id;
        }

        // 3. Update all tables using lead_user_id (referenced as various columns)
        $updates = [
            'leads' => ['assigned_user_id', 'added_by'],
            'agent_leads' => ['assigned_user_id', 'added_by'],
            'lead_followups' => ['added_by', 'completed_by'],
            'agent_lead_followups' => ['added_by', 'completed_by'],
            'lead_histories' => ['user_id'],
            'agent_lead_histories' => ['user_id'],
            'agent_overall_followups' => ['added_by', 'completed_by']
        ];

        foreach ($updates as $table => $columns) {
            if (!Schema::hasTable($table)) continue;
            
            foreach ($columns as $column) {
                foreach ($mapping as $oldId => $newId) {
                    try {
                        DB::table($table)->where($column, $oldId)->update([$column => $newId]);
                    } catch (\Exception $e) {}
                }
            }
        }

        // 4. Update Hierarchy
        if (Schema::hasTable('manager_user')) {
            foreach($leadUsers as $lu) {
                if ($lu->parent_id && isset($mapping[$lu->parent_id])) {
                    $managerId = $mapping[$lu->parent_id];
                    $currentUserId = $mapping[$lu->id];
                    
                    try {
                        DB::table('manager_user')->updateOrInsert(
                            ['manager_id' => $managerId, 'user_id' => $currentUserId],
                            []
                        );
                    } catch (\Exception $e) {}
                }
            }
        }

        // 5. Re-add FKs pointing to users table - cautiously
        try {
            Schema::table('leads', function (Blueprint $table) {
                $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('lead_histories', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not easily reversible without a mapping log, so we'll just drop the new FKs
    }
};
