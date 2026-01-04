<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('exam_code_id')->constrained('exam_codes')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'cancelled', 'timed_out'])->default('in_progress');
            $table->string('device_fingerprint')->nullable(); // for device binding
            $table->string('ip_address');
            $table->json('meta_data')->nullable(); // browser info, OS, etc
            $table->boolean('flagged_for_review')->default(false);
            $table->text('flag_reason')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index(['exam_id', 'status']);
            $table->unique(['exam_code_id', 'student_id']); // one session per student per code
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_sessions');
    }
};
