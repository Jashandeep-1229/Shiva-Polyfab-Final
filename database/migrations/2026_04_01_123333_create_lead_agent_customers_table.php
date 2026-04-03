<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lead_agent_customers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('role')->nullable(); // Agent, Customer
            $table->string('type')->nullable(); // A, B, C
            $table->unsignedBigInteger('sale_executive_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('gst')->nullable();
            $table->text('address')->nullable();
            $table->string('pincode')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('status')->default(1);
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->unsignedBigInteger('agent_lead_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_agent_customers');
    }
};
