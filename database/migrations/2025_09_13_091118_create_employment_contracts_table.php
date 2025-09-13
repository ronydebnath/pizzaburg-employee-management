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
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarding_invite_id')->constrained()->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->string('template_key'); // References position.contract_template_key
            $table->string('status')->default('draft'); // draft, sent, signed, completed
            $table->json('contract_data')->nullable(); // Contract-specific data (salary, start date, etc.)
            $table->string('signature_file_path')->nullable(); // Path to uploaded signature image
            $table->string('signed_pdf_path')->nullable(); // Path to final signed PDF
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
