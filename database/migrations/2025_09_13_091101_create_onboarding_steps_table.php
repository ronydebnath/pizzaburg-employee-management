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
        Schema::create('onboarding_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_invite_id')->constrained()->onDelete('cascade');
            $table->string('step_name'); // personal_details, document_upload, contract_review, signature_upload, completed
            $table->string('status')->default('pending'); // pending, in_progress, completed, failed
            $table->json('step_data')->nullable(); // Store step-specific data
            $table->text('notes')->nullable(); // HR notes or employee notes
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarding_steps');
    }
};
