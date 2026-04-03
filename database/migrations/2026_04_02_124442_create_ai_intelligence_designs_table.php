<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_intelligence_designs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('user_id')->nullable();
            $blueprint->string('customer_name')->nullable();
            $blueprint->string('contact_no')->nullable();
            
            // Raw text from user to extract data from
            $blueprint->text('requirements')->nullable(); 
            
            // Extracted Job Card fields (Size, Color, BOPP, etc.)
            $blueprint->json('ai_parsed_data')->nullable(); 
            
            // Paths to design mockup images
            $blueprint->json('design_mockups')->nullable();
            
            $blueprint->enum('status', ['Draft', 'Shared', 'Approved', 'Converted'])->default('Draft');
            $blueprint->timestamp('approval_date')->nullable();
            $blueprint->unsignedBigInteger('job_card_id')->nullable();
            
            $blueprint->softDeletes();
            $blueprint->timestamps();
            
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_intelligence_designs');
    }
};
