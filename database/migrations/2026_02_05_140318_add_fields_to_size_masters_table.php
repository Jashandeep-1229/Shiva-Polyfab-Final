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
        Schema::table('size_masters', function (Blueprint $table) {
            $table->unsignedBigInteger('fabric_id')->nullable()->after('name');
            $table->unsignedBigInteger('bopp_id')->nullable()->after('fabric_id');
            $table->string('order_send_for')->nullable()->after('bopp_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('size_masters', function (Blueprint $table) {
            $table->dropColumn(['fabric_id', 'bopp_id', 'order_send_for']);
        });
    }
};
