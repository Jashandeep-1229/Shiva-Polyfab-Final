<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agent_deals_ins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('lead_agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('firm_name')->nullable();
            $table->string('phone');
            $table->string('state');
            $table->string('city');
            $table->unsignedBigInteger('deals_in_id')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('agent_leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_no')->unique();
            $table->string('name_of_job');
            $table->unsignedBigInteger('agent_id');
            $table->text('requirement')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('status_id');
            $table->unsignedBigInteger('assigned_user_id');
            $table->unsignedBigInteger('added_by');
            $table->string('order_no')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('agent_lead_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_lead_id');
            $table->unsignedBigInteger('status_at_time_id');
            $table->string('type')->default('Call');
            $table->datetime('followup_date');
            $table->datetime('complete_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('delay_days')->default(0);
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamps();
        });

        Schema::create('agent_lead_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_lead_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type'); 
            $table->text('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_lead_histories');
        Schema::dropIfExists('agent_lead_followups');
        Schema::dropIfExists('agent_leads');
        Schema::dropIfExists('lead_agents');
        Schema::dropIfExists('agent_deals_ins');
    }
};
