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
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bill_id');
            $table->string('description');
            $table->string('hsn_code')->nullable();
            
            $table->decimal('qty', 15, 3)->default(0);
            $table->string('unit')->default('Kgs');
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('amount', 15, 2)->default(0);
            
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('gst_amount', 15, 2)->default(0);
            
            $table->decimal('total_amount', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bill_items');
    }
};
