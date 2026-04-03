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
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->boolean('is_lead')->default(0)->after('status');
            $table->unsignedBigInteger('lead_id')->nullable()->after('is_lead');
            $table->unsignedBigInteger('agent_lead_id')->nullable()->after('lead_id');
            
            // Foreign keys if appropriate (optional depending on system)
            // $table->foreign('lead_id')->references('id')->on('leads')->onDelete('set null');
            // $table->foreign('agent_lead_id')->references('id')->on('agent_leads')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->dropColumn(['is_lead', 'lead_id', 'agent_lead_id']);
        });
    }
};
