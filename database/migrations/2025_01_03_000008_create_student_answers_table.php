<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exam_question_id')->constrained('exam_questions')->cascadeOnDelete();
            $table->longText('answer_text')->nullable();
            $table->string('selected_option')->nullable(); // 'A', 'B', etc
            $table->timestamp('answered_at');
            $table->integer('time_spent_seconds')->default(0);
            $table->boolean('is_final')->default(false); // whether this is the final answer
            $table->timestamps();
            
            $table->index(['exam_session_id', 'exam_question_id']);
            $table->unique(['exam_session_id', 'exam_question_id']); // one final answer per question
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_answers');
    }
};
