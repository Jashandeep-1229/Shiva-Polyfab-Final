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
        Schema::create('cylinder_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_card_id')->nullable();
            $table->string('cylinder_agent_id')->nullable();
            $table->string('name_of_job')->nullable();
            $table->string('check_in_by')->nullable();
            $table->string('check_in_date')->nullable();
            $table->string('check_out_by')->nullable();
            $table->string('check_out_date')->nullable();
            $table->string('total_no_of_days')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('cylinder_jobs');
    }
};
