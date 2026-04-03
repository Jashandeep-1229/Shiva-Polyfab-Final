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
        Schema::create('agent_customers', function (Blueprint $table) {
            $table->id();
            $table->string('from')->nullable()->default('Manually');
            $table->string('user_id')->nullable();
            $table->string('code')->nullable();
            $table->string('name')->nullable();
            $table->string('phone_no')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_customers');
    }
};
