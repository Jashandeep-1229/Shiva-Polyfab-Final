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
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->string('gst')->nullable()->after('email');
            $table->text('address')->nullable()->after('gst');
            $table->string('pincode')->nullable()->after('address');
            $table->string('state')->nullable()->after('pincode');
            $table->string('city')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->dropColumn(['gst', 'address', 'pincode', 'state', 'city']);
        });
    }
};
