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
            $table->date('billing_date')->nullable();
            $table->decimal('billing_weight', 15, 3)->nullable();
            $table->decimal('billing_rate', 15, 2)->nullable();
            $table->decimal('billing_extra', 15, 2)->default(0);
            $table->decimal('billing_gst_percent', 5, 2)->default(0);
            $table->decimal('billing_total_price', 15, 2)->nullable();
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
            $table->dropColumn(['billing_date', 'billing_weight', 'billing_rate', 'billing_extra', 'billing_gst_percent', 'billing_total_price']);
        });
    }
};
