<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('session_id', 64)->nullable()->index(); // group messages per session
            $table->text('user_message');
            $table->longText('ai_response');
            $table->string('model_used', 100)->default('gemini-2.5-pro');
            $table->unsignedInteger('response_time_ms')->nullable(); // how fast AI replied
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_histories');
    }
};
