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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('email')->nullable()->after('name');
            $table->text('address')->nullable()->after('phone');
            $table->string('architect_builder')->nullable()->after('regarding');
            $table->string('sales_coordinator')->nullable()->after('architect_builder');
            $table->string('sales_person')->nullable()->after('sales_coordinator');
            $table->string('product')->nullable()->after('sales_person');
            $table->decimal('amount', 15, 2)->default(0)->after('product');
            $table->string('site_stage')->nullable()->after('amount');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['email', 'address', 'architect_builder', 'sales_coordinator', 'sales_person', 'product', 'amount', 'site_stage']);
        });
    }
};
