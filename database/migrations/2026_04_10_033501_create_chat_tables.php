<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('phone_no');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->text('message')->nullable();
            $table->string('message_type')->default('text'); // text, image, video, document
            $table->string('media_id')->nullable(); // WhatsApp Media ID
            $table->string('media_path')->nullable(); // Local storage path
            $table->string('wa_message_id')->nullable(); // WhatsApp Message ID
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'failed', 'received'])->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::table('agent_customers', function (Blueprint $table) {
            $table->timestamp('last_message_at')->nullable();
            $table->integer('unseen_count')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
        Schema::table('agent_customers', function (Blueprint $table) {
            $table->dropColumn(['last_message_at', 'unseen_count']);
        });
    }
};
