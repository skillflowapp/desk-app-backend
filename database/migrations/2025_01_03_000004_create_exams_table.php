<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pdf_upload_id')->nullable()->constrained('pdf_uploads')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('duration_minutes'); // exam duration in minutes
            $table->integer('total_marks')->default(100);
            $table->integer('passing_marks')->default(40);
            $table->json('ai_prompt')->nullable(); // AI generation instructions
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('show_answers_after')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            
            $table->index(['teacher_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
