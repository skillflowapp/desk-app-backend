<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->integer('max_attempts')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            $table->index(['code', 'is_active']);
            $table->index('exam_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_codes');
    }
};
