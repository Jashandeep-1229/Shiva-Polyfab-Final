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
        Schema::create('manage_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('from')->nullable();
            $table->string('from_id')->nullable();
            $table->string('stock_name')->nullable();
            $table->string('stock_id')->nullable();
            $table->string('date')->nullable();
            $table->string('unit')->nullable();
            $table->string('quantity')->nullable();
            $table->string('average')->nullable();
            $table->string('in_out')->nullable();
            $table->text('remarks')->nullable();
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
        Schema::dropIfExists('manage_stocks');
    }
};
