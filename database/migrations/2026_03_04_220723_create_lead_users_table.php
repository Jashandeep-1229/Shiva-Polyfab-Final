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
        Schema::create('lead_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('role')->default('Executive'); // Admin, Manager, Team Leader, Executive
            $table->string('phone')->nullable();
            $table->tinyInteger('status')->default(1); // 1: Active, 0: Inactive
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('lead_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lead_users');
    }
};
