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
        Schema::create('kyc_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_invite_id')->constrained()->onDelete('cascade');
            $table->string('verification_id')->unique(); // External provider verification ID
            $table->string('provider')->default('internal'); // internal, sumsub, onfido, etc.
            $table->string('status')->default('pending'); // pending, processing, approved, rejected, failed
            $table->string('type')->default('selfie_liveness'); // selfie_liveness, document_verification, address_verification
            $table->json('verification_data')->nullable(); // Provider-specific verification data
            $table->json('result_data')->nullable(); // Verification results and scores
            $table->string('selfie_image_path')->nullable(); // Path to captured selfie
            $table->string('document_image_path')->nullable(); // Path to document image (if applicable)
            $table->text('rejection_reason')->nullable(); // Reason for rejection if applicable
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['onboarding_invite_id', 'status']);
            $table->index(['provider', 'status']);
            $table->index('verification_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kyc_verifications');
    }
};
