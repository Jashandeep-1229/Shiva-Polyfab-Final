<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$fks = [
    'lead_users' => ['lead_users_parent_id_foreign'],
    'leads' => ['leads_assigned_user_id_foreign', 'leads_added_by_foreign'],
    'lead_followups' => ['lead_followups_added_by_foreign', 'lead_followups_completed_by_foreign'],
    'lead_histories' => ['lead_histories_user_id_foreign'], // Try again just in case
];

foreach ($fks as $table => $constraints) {
    foreach ($constraints as $fk) {
        try {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk}`");
            echo "Dropped $fk from $table\n";
        } catch (\Exception $e) {
            echo "Failed or already dropped $fk\n";
        }
    }
}
echo "Done.\n";
