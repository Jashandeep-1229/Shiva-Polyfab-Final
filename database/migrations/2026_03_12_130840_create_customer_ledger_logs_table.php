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
        Schema::create('customer_ledger_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_ledger_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('action');
            $table->longText('old_data')->nullable();
            $table->longText('new_data')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('customer_ledger_logs');
    }
};
