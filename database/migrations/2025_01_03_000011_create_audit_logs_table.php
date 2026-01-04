<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // 'login', 'exam_created', 'answer_submitted', 'result_published', etc
            $table->string('entity_type')->nullable(); // 'exam', 'result', 'user', etc
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('old_values')->nullable(); // for updates
            $table->json('new_values')->nullable(); // for updates
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->json('meta_data')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
