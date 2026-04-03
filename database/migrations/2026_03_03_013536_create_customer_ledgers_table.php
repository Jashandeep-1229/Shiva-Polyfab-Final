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
        Schema::create('customer_ledgers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('job_card_id')->nullable();
            $table->unsignedBigInteger('packing_slip_id')->nullable();
            $table->unsignedBigInteger('payment_method_id')->nullable(); // For manual Cr entries
            $table->date('transaction_date');
            
            $table->decimal('amount', 20, 3)->default(0);
            $table->decimal('gst', 20, 3)->default(0);
            $table->decimal('total_amount', 20, 3)->default(0);
            
            $table->decimal('extra_charge_amount', 20, 3)->default(0);
            $table->decimal('extra_charge_gst', 20, 3)->default(0);
            $table->decimal('extra_total_amount', 20, 3)->default(0);
            
            $table->decimal('grand_total_amount', 20, 3)->default(0);
            $table->enum('dr_cr', ['Dr', 'Cr']); // Dr = Debit (Due), Cr = Credit (Received)
            
            $table->text('remarks')->nullable();
            $table->text('software_remarks')->nullable();
            $table->unsignedBigInteger('user_id');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_ledgers');
    }
};
