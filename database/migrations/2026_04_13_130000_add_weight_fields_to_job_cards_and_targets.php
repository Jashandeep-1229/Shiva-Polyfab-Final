<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update job_cards table
        Schema::table('job_cards', function (Blueprint $table) {
            $table->string('bag_type')->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('guzzete', 10, 2)->nullable();
            $table->decimal('gsm', 10, 2)->nullable();
            $table->decimal('estimate_weight_pcs', 10, 4)->nullable();
            $table->decimal('total_weight_kg', 12, 4)->nullable();
        });

        // Create executive_target_records table
        Schema::create('executive_target_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_card_id');
            $table->unsignedBigInteger('executive_id');
            $table->date('date');
            $table->string('bag_type')->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('guzzete', 10, 2)->nullable();
            $table->decimal('gsm', 10, 2)->nullable();
            $table->decimal('per_pcs_weight', 10, 4)->nullable();
            $table->bigInteger('total_pcs')->nullable();
            $table->decimal('total_weight', 12, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('job_card_id')->references('id')->on('job_cards')->onDelete('cascade');
            $table->foreign('executive_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('executive_target_records');
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropColumn(['bag_type', 'width', 'length', 'guzzete', 'gsm', 'estimate_weight_pcs', 'total_weight_kg']);
        });
    }
};
