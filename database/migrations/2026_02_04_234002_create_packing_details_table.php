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
        Schema::create('packing_details', function (Blueprint $table) {
            $table->id();
            $table->string('packing_slip_id')->nullable();
            $table->string('weight')->nullable();
            $table->string('start_date')->nullable();
            $table->string('complete_date')->nullable();
            $table->string('complete_by')->nullable();
            $table->string('status')->nullable();
            $table->string('remarks')->nullable();
            $table->string('is_undo')->nullable();
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
        Schema::dropIfExists('packing_details');
    }
};
