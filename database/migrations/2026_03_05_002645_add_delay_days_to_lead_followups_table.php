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
            $table->integer('delay_days')->default(0)->after('complete_date');
        });
    }

    public function down()
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropColumn('delay_days');
        });
    }
};
