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
        Schema::create('agent_overall_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->datetime('followup_date');
            $table->datetime('complete_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('delay_days')->default(0);
            $table->unsignedBigInteger('added_by');
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->boolean('status')->default(0)->comment('0: Pending, 1: Completed');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('lead_agents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_overall_followups');
    }
};
