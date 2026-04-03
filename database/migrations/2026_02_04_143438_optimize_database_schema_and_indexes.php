<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tables = ['fabrics', 'bopps', 'inks', 'danas', 'loops'];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE $table MODIFY user_id BIGINT UNSIGNED NULL");
            DB::statement("ALTER TABLE $table MODIFY alert_min_stock DECIMAL(15,2) NULL");
            DB::statement("ALTER TABLE $table MODIFY alert_max_stock DECIMAL(15,2) NULL");
            DB::statement("ALTER TABLE $table MODIFY order_qty DECIMAL(15,2) NULL");
            
            Schema::table($table, function (Blueprint $tableGroup) use ($table) {
                $indexes = DB::select("SHOW INDEX FROM $table");
                $indexNames = array_column($indexes, 'Key_name');

                if (!in_array($table . '_user_id_index', $indexNames)) $tableGroup->index('user_id');
                if (!in_array($table . '_name_index', $indexNames)) $tableGroup->index('name');
                if (!in_array($table . '_status_index', $indexNames)) $tableGroup->index('status');
            });
        }

        // Job Cards
        DB::statement("ALTER TABLE job_cards MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY bopp_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY fabric_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY cylinder_given_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY customer_agent_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY sale_executive_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY complete_by_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY cancel_by_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_cards MODIFY no_of_pieces INT NULL");

        Schema::table('job_cards', function (Blueprint $tableGroup) {
            $indexes = DB::select("SHOW INDEX FROM job_cards");
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('job_cards_user_id_index', $indexNames)) $tableGroup->index('user_id');
            if (!in_array('job_cards_bopp_id_index', $indexNames)) $tableGroup->index('bopp_id');
            if (!in_array('job_cards_fabric_id_index', $indexNames)) $tableGroup->index('fabric_id');
            if (!in_array('job_cards_customer_agent_id_index', $indexNames)) $tableGroup->index('customer_agent_id');
            if (!in_array('job_cards_sale_executive_id_index', $indexNames)) $tableGroup->index('sale_executive_id');
            if (!in_array('job_cards_status_index', $indexNames)) $tableGroup->index('status');
            if (!in_array('job_cards_job_type_index', $indexNames)) $tableGroup->index('job_type');
            if (!in_array('job_cards_job_card_process_index', $indexNames)) $tableGroup->index('job_card_process');
        });

        // Job Card Processes
        DB::statement("ALTER TABLE job_card_processes MODIFY job_card_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_card_processes MODIFY user_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE job_card_processes MODIFY machine_id BIGINT UNSIGNED NULL");
        
        // Correcting spelling here
        DB::statement("ALTER TABLE job_card_processes MODIFY blockage_reason_id BIGINT UNSIGNED NULL"); 
        
        DB::statement("ALTER TABLE job_card_processes MODIFY estimate_production DECIMAL(15,2) NULL");
        DB::statement("ALTER TABLE job_card_processes MODIFY actual_order DECIMAL(15,2) NULL");
        DB::statement("ALTER TABLE job_card_processes MODIFY wastage DECIMAL(15,2) NULL");
        // Keeping working_hours as string due to dirty data like '10-1'
        DB::statement("ALTER TABLE job_card_processes MODIFY working_hours VARCHAR(255) NULL");

        Schema::table('job_card_processes', function (Blueprint $tableGroup) {
            $indexes = DB::select("SHOW INDEX FROM job_card_processes");
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('job_card_processes_job_card_id_index', $indexNames)) $tableGroup->index('job_card_id');
            if (!in_array('job_card_processes_user_id_index', $indexNames)) $tableGroup->index('user_id');
            if (!in_array('job_card_processes_machine_id_index', $indexNames)) $tableGroup->index('machine_id');
            if (!in_array('job_card_processes_status_index', $indexNames)) $tableGroup->index('status');
        });

        // Manage Stocks
        DB::statement("ALTER TABLE manage_stocks MODIFY from_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE manage_stocks MODIFY stock_id BIGINT UNSIGNED NULL");
        DB::statement("ALTER TABLE manage_stocks MODIFY quantity DECIMAL(15,2) NULL");
        DB::statement("ALTER TABLE manage_stocks MODIFY average DECIMAL(15,2) NULL");

        Schema::table('manage_stocks', function (Blueprint $tableGroup) {
            $indexes = DB::select("SHOW INDEX FROM manage_stocks");
            $indexNames = array_column($indexes, 'Key_name');

            if (!in_array('manage_stocks_stock_id_index', $indexNames)) $tableGroup->index('stock_id');
            if (!in_array('manage_stocks_stock_name_index', $indexNames)) $tableGroup->index('stock_name');
            if (!in_array('manage_stocks_in_out_index', $indexNames)) $tableGroup->index('in_out');
            if (!in_array('manage_stocks_status_index', $indexNames)) $tableGroup->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
