<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->integer('question_number');
            $table->enum('type', ['mcq', 'short_answer', 'essay'])->default('mcq');
            $table->text('question_text');
            $table->json('options')->nullable(); // ['A' => 'option text', 'B' => '...']
            $table->string('correct_answer')->nullable(); // 'A', 'B', etc for MCQ
            $table->text('model_answer')->nullable(); // for essays/short answers
            $table->integer('marks')->default(1);
            $table->text('explanation')->nullable(); // explanation for answer
            $table->integer('order')->default(0);
            $table->timestamps();
            
            $table->unique(['exam_id', 'question_number']);
            $table->index(['exam_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
