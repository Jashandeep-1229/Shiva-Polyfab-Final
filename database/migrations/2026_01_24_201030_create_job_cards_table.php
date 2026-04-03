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
        Schema::create('job_cards', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('job_type')->nullable();
            $table->string('name_of_job')->nullable();
            $table->string('bopp_id')->nullable();
            $table->string('fabric_id')->nullable();
            $table->string('no_of_pieces')->nullable();
            $table->string('loop_color')->nullable();
            $table->string('order_send_for')->nullable();
            $table->string('dispatch_date')->nullable();
            $table->string('job_card_date')->nullable();
            $table->string('job_card_type')->nullable();
            $table->string('cylinder_given_id')->nullable();
            $table->string('customer_agent_id')->nullable();
            $table->string('sale_executive_id')->nullable();
            $table->text('file_upload')->nullable();
            $table->text('remarks')->nullable();
            $table->string('complete_date')->nullable();
            $table->string('complete_by_id')->nullable();
            $table->string('cancel_date')->nullable();
            $table->string('cancel_by_id')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->string('late_reasons')->nullable();
            $table->string('software_remarks')->nullable();
            $table->string('job_card_process')->nullable();
            $table->string('status')->nullable();
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
        Schema::dropIfExists('job_cards');
    }
};
