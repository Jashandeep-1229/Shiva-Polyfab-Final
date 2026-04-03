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
        Schema::create('packing_slips', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('job_card_id')->nullable();
            $table->string('total_weight')->nullable();
            $table->string('pending_weight')->nullable();
            $table->string('dispatch_weight')->nullable();
            $table->string('total_bags')->nullable();
            $table->string('pending_bags')->nullable();
            $table->string('dispatch_bags')->nullable();
            $table->string('packing_date')->nullable();
            $table->string('dispatch_date')->nullable();
            $table->string('complete_date')->nullable();
            $table->string('remarks')->nullable();
            $table->string('status')->nullable();
            $table->string('dispatch_by')->nullable();
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
        Schema::dropIfExists('packing_slips');
    }
};
