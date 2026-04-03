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
        Schema::table('packing_details', function (Blueprint $table) {
            $table->unsignedBigInteger('size_id')->nullable()->after('job_card_id');
            $table->unsignedBigInteger('color_id')->nullable()->after('size_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packing_details', function (Blueprint $table) {
            $table->dropColumn('size_id');
            $table->dropColumn('color_id');
        });
    }
};
