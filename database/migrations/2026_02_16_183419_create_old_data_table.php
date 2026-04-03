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
        Schema::create('old_data', function (Blueprint $table) {
            $table->id();
            $table->date('order_date')->nullable();
            $table->date('dispatch_date')->nullable();
            $table->string('name_of_job')->nullable();
            
            $table->unsignedBigInteger('bopp_id')->nullable();
            $table->unsignedBigInteger('fabric_id')->nullable();
            $table->unsignedBigInteger('loop_color_id')->nullable();
            
            $table->text('remarks')->nullable();
            $table->integer('pieces')->nullable();
            $table->string('send_for')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('bopp_id')->references('id')->on('bopps')->onDelete('set null');
            $table->foreign('fabric_id')->references('id')->on('fabrics')->onDelete('set null');
            $table->foreign('loop_color_id')->references('id')->on('loops')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('old_data');
    }
};
