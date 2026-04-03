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
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->string('type')->default('Call')->after('lead_id'); // Call, Visit
            $table->unsignedBigInteger('status_at_time_id')->nullable()->after('type');
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->string('order_no')->nullable()->after('lead_no');
        });
    }

    public function down()
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn(['type', 'status_at_time_id']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('order_no');
        });
    }
};
