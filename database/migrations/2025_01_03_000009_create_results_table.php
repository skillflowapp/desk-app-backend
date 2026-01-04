<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_marks', 8, 2)->default(0);
            $table->decimal('obtained_marks', 8, 2)->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->boolean('is_passed')->default(false);
            $table->enum('status', ['pending', 'graded', 'published', 'disputed'])->default('pending');
            $table->json('question_scores')->nullable(); // { question_id: { obtained: 5, total: 10 } }
            $table->json('ai_feedback')->nullable(); // AI generated feedback
            $table->text('teacher_remarks')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            
            $table->index(['exam_id', 'student_id']);
            $table->index(['exam_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
