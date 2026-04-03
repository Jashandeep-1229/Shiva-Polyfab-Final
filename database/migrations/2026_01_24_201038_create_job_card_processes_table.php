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
        Schema::create('job_card_processes', function (Blueprint $table) {
            $table->id();
            $table->string('job_card_id')->nullable();
            $table->string('process_name')->nullable();
            $table->string('process_start_date')->nullable();
            $table->string('process_end_date')->nullable();
            $table->string('total_time')->nullable();
            $table->string('process_remarks')->nullable();
            $table->string('user_id')->nullable();
            $table->string('status')->nullable();
            $table->string('date')->nullable();
            $table->string('estimate_production')->nullable();
            $table->string('actual_order')->nullable();
            $table->string('wastage')->nullable();
            $table->string('working_hours')->nullable();
            $table->string('machine_id')->nullable();
            $table->string('shift_time')->nullable();
            $table->string('bloackage_reaason_id')->nullable();
            $table->string('other_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_card_processes');
    }
};
