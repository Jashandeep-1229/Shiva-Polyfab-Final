<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('job_card_processes', function (Blueprint $table) {
            $table->decimal('blockage_time', 10, 2)->nullable()->after('blockage_reason_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_card_processes', function (Blueprint $table) {
            $table->dropColumn('blockage_time');
        });
    }
};
