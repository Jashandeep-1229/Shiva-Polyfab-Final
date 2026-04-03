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
        // Drop existing and recreate for a clean revamp as requested
        Schema::dropIfExists('ledger_followup_histories');
        Schema::dropIfExists('ledger_followups');

        Schema::create('ledger_followups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Created by
            $table->unsignedBigInteger('customer_id');
            $table->string('subject');
            $table->datetime('start_date');
            $table->datetime('complete_date')->nullable();
            $table->unsignedBigInteger('completed_by')->nullable();
            $table->float('total_no_of_days')->nullable();
            $table->enum('status', ['Pending', 'Closed'])->default('Pending');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('agent_customers')->onDelete('cascade');
            $table->foreign('completed_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('ledger_followup_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('followup_id');
            $table->text('remarks')->nullable();
            $table->datetime('followup_date_time'); // Scheduled
            $table->integer('status')->default(1);
            $table->datetime('complete_date_time')->nullable();
            $table->unsignedBigInteger('complete_by')->nullable();
            $table->float('total_no_of_days')->nullable();
            $table->timestamps();

            $table->foreign('followup_id')->references('id')->on('ledger_followups')->onDelete('cascade');
            $table->foreign('complete_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ledger_followup_histories');
        Schema::dropIfExists('ledger_followups');
    }
};
