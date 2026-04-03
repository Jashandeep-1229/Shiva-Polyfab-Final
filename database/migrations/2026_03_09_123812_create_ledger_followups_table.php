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
        Schema::create('ledger_followups', function (Blueprint $col) {
            $col->id();
            $col->unsignedBigInteger('customer_id');
            $col->unsignedBigInteger('user_id'); // Created by user
            $col->datetime('followup_date_time'); // Scheduled or event time
            $col->string('subject')->nullable();
            $col->text('remarks')->nullable();
            $col->enum('status', ['Continue', 'Closed'])->default('Continue');
            $col->unsignedBigInteger('parent_id')->nullable(); // To link nested history threads
            $col->timestamps();
            $col->softDeletes();
            
            $col->foreign('customer_id')->references('id')->on('agent_customers')->onDelete('cascade');
            $col->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $col->foreign('parent_id')->references('id')->on('ledger_followups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger_followups');
    }
};
