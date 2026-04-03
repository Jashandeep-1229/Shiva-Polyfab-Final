<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $fks = [
            'leads' => ['leads_assigned_user_id_foreign', 'leads_added_by_foreign'],
            'lead_followups' => ['lead_followups_added_by_foreign', 'lead_followups_completed_by_foreign'],
            'lead_histories' => ['lead_histories_user_id_foreign'],
            'lead_users' => ['lead_users_parent_id_foreign'],
        ];

        foreach ($fks as $table => $constraints) {
            foreach ($constraints as $fk) {
                try {
                    DB::statement("ALTER TABLE `$table` DROP FOREIGN KEY `$fk`");
                } catch (\Exception $e) {
                    // Ignore if already dropped or nonexistent
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // One-way migration
    }
};
