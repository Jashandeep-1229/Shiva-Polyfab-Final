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
        Schema::create('master_vendors', function (Blueprint $table) {
            $table->id();
            $table->string('master_type'); // Fabric, Bopp, Ink, Dana, Loop
            $table->unsignedBigInteger('master_id');
            $table->string('name');
            $table->string('phone_no');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint for master_type, master_id, and phone_no
            // As per user requirement: phone no unique according to master item
            $table->unique(['master_type', 'master_id', 'phone_no'], 'unique_master_vendor_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_vendors');
    }
};
