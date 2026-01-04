<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_queues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->string('entity_type'); // 'exam_session', 'student_answer', etc
            $table->unsignedBigInteger('entity_id');
            $table->enum('action', ['create', 'update', 'delete'])->default('create');
            $table->json('payload'); // the data to sync
            $table->enum('status', ['pending', 'synced', 'failed'])->default('pending');
            $table->integer('retry_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            
            $table->index(['student_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queues');
    }
};
