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
        Schema::table('job_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('hold_reason_id')->nullable()->after('is_hold');
            $table->text('hold_notes')->nullable()->after('hold_reason_id');
            $table->timestamp('held_at')->nullable()->after('hold_notes');
            $table->unsignedBigInteger('held_by_id')->nullable()->after('held_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropColumn(['hold_reason_id', 'hold_notes', 'held_at', 'held_by_id']);
        });
    }
};
