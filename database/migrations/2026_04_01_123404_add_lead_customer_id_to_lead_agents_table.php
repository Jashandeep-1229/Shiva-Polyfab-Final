<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lead_agents', function (Blueprint $table) {
            $table->unsignedBigInteger('lead_agent_customer_id')->nullable()->after('agent_customer_id');
        });
    }

    public function down()
    {
        Schema::table('lead_agents', function (Blueprint $table) {
            $table->dropColumn('lead_agent_customer_id');
        });
    }
};
