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
            $table->decimal('cylinder_billing_weight', 15, 3)->nullable()->after('billing_total_price');
            $table->decimal('cylinder_billing_rate', 15, 2)->nullable()->after('cylinder_billing_weight');
            $table->decimal('cylinder_billing_gst_percent', 5, 2)->default(0)->after('cylinder_billing_rate');
            $table->decimal('cylinder_billing_total', 15, 2)->nullable()->after('cylinder_billing_gst_percent');
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
            $table->dropColumn(['cylinder_billing_weight', 'cylinder_billing_rate', 'cylinder_billing_gst_percent', 'cylinder_billing_total']);
        });
    }
};
