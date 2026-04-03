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
            $table->unsignedBigInteger('completed_by')->nullable()->after('added_by');
            $table->foreign('completed_by')->references('id')->on('lead_users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('lead_followups', function (Blueprint $table) {
            $table->dropForeign(['completed_by']);
            $table->dropColumn('completed_by');
        });
    }
};
