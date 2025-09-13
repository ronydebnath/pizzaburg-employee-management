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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('device_fingerprint')->unique();
            $table->string('device_name')->nullable();
            $table->string('device_type')->default('unknown'); // mobile, tablet, desktop, unknown
            $table->string('os_name')->nullable();
            $table->string('os_version')->nullable();
            $table->string('browser_name')->nullable();
            $table->string('browser_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('is_trusted')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'is_trusted']);
            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
