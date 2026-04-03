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
        Schema::create('common_manage_stocks', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('from')->default('Manually');
            $table->integer('from_id')->default(0);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('color_id');
            $table->unsignedBigInteger('size_id');
            $table->double('quantity', 15, 3);
            $table->string('in_out'); // 'In' or 'Out'
            $table->text('remarks')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for faster lookups
            $table->index('color_id');
            $table->index('size_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_manage_stocks');
    }
};
