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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone');
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('regarding')->nullable();
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->unsignedBigInteger('status_id')->nullable();
            $table->unsignedBigInteger('added_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('source_id')->references('id')->on('lead_sources')->onDelete('set null');
            $table->foreign('assigned_user_id')->references('id')->on('lead_users')->onDelete('set null');
            $table->foreign('status_id')->references('id')->on('lead_statuses')->onDelete('set null');
            $table->foreign('added_by')->references('id')->on('lead_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leads');
    }
};
