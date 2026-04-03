<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lead_states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });

        Schema::create('lead_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('lead_states')->onDelete('cascade');
            $table->string('name');
            $table->boolean('status')->default(1);
            $table->timestamps();
            
            // Allow same city name in different states
            $table->unique(['state_id', 'name']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('lead_cities');
        Schema::dropIfExists('lead_states');
    }
};
