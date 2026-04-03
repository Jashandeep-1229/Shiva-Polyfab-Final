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
        Schema::table('menu_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            // $table->string('role_name')->nullable()->change(); // Skip this to avoid DBAL 4/Laravel 9 conflict
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menu_permissions', function (Blueprint $table) {
            //
        });
    }
};
