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
        Schema::table('job_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_lead_id')->nullable()->after('lead_id');
        });
    }

    public function down()
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropColumn('agent_lead_id');
        });
    }
};
