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
            $table->boolean('can_next_process')->default(0)->after('can_edit');
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
            $table->dropColumn('can_next_process');
        });
    }
};
