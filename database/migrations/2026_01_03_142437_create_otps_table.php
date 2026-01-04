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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('code', 6); // 6-digit OTP
            $table->enum('type', ['email_verification', 'password_reset']); // OTP purpose
            $table->dateTime('expires_at');
            $table->boolean('is_used')->default(false);
            $table->dateTime('used_at')->nullable();
            $table->integer('attempts')->default(0); // Track wrong attempts
            $table->timestamps();
            
            // Indexes for faster lookups
            $table->index(['user_id', 'type']);
            $table->index(['email', 'type']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
