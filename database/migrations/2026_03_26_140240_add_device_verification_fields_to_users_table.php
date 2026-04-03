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
        Schema::table('users', function (Blueprint $table) {
            $table->string('verified_device_id')->nullable();
            $table->text('last_device_info')->nullable();
            $table->string('current_otp')->nullable();
            $table->timestamp('otp_created_at')->nullable();
            $table->boolean('is_device_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['verified_device_id', 'last_device_info', 'current_otp', 'otp_created_at', 'is_device_verified']);
        });
    }
};
