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
        Schema::create('common_roll_outs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('job_card_id');
            $blueprint->unsignedBigInteger('user_id');
            $blueprint->decimal('rolls_out', 10, 2);
            $blueprint->string('action_type'); // 'Next Process' or 'Discontinued'
            $blueprint->date('date');
            $blueprint->text('remarks')->nullable();
            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->foreign('job_card_id')->references('id')->on('job_cards')->onDelete('cascade');
            $blueprint->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('common_roll_outs');
    }
};
