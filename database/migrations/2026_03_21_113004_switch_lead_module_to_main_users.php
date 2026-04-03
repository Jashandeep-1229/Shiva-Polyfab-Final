<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Prepare User Mapping based on Email
        $lead_users = DB::table('lead_users')->get();
        $mapping = [];

        foreach ($lead_users as $lu) {
            $existing = DB::table('users')->where('email', $lu->email)->first();
            if ($existing) {
                echo "Mapping old lead_user ID {$lu->id} to existing user ID {$existing->id} ({$lu->email})\n";
                $mapping[$lu->id] = $existing->id;
            } else {
                echo "Creating new user for {$lu->email} from lead_users...\n";
                $newId = DB::table('users')->insertGetId([
                    'name' => $lu->name,
                    'email' => $lu->email,
                    'phone' => $lu->phone ?? '',
                    'password' => $lu->password,
                    'show_password' => $lu->show_password,
                    'role_as' => $lu->role,
                    'status' => $lu->status,
                    'created_by_id' => 1,
                    'created_at' => $lu->created_at,
                    'updated_at' => $lu->updated_at
                ]);
                $mapping[$lu->id] = $newId;
            }
        }

        // 2. Clear FK constraints that point to lead_users
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                // Determine existing foreign keys to drop
                // We'll use try-catch or direct drop if we are sure of names
                try { $table->dropForeign('leads_assigned_user_id_foreign'); } catch (\Exception $e) {}
                try { $table->dropForeign('leads_added_by_foreign'); } catch (\Exception $e) {}
            });
        }

        if (Schema::hasTable('lead_followups')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                try { $table->dropForeign('lead_followups_added_by_foreign'); } catch (\Exception $e) {}
                try { $table->dropForeign('lead_followups_completed_by_foreign'); } catch (\Exception $e) {}
            });
        }

        // 3. Update data across all tables
        foreach ($mapping as $old => $new) {
            if (Schema::hasTable('leads')) {
                DB::table('leads')->where('assigned_user_id', $old)->update(['assigned_user_id' => $new]);
                DB::table('leads')->where('added_by', $old)->update(['added_by' => $new]);
            }
            if (Schema::hasTable('lead_followups')) {
                DB::table('lead_followups')->where('added_by', $old)->update(['added_by' => $new]);
                DB::table('lead_followups')->where('completed_by', $old)->update(['completed_by' => $new]);
            }
            if (Schema::hasTable('agent_leads')) {
                DB::table('agent_leads')->where('assigned_user_id', $old)->update(['assigned_user_id' => $new]);
                DB::table('agent_leads')->where('added_by', $old)->update(['added_by' => $new]);
            }
        }

        // 4. Re-add foreign keys pointing to the main 'users' table
        if (Schema::hasTable('leads')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (Schema::hasTable('lead_followups')) {
            Schema::table('lead_followups', function (Blueprint $table) {
                $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        echo "Lead module users merged into main users table successfully.\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No simple way to reverse this migration as data is merged.
    }
};
