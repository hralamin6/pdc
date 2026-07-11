<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'timed_out'])->default('in_progress');
            $table->decimal('score_raw', 6, 2)->nullable();
            $table->decimal('score_percentage', 5, 2)->nullable();
            $table->unsignedSmallInteger('rank')->nullable(); // Computed after all submissions
            $table->unsignedInteger('time_taken_seconds')->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->json('question_order')->nullable(); // Shuffled snapshot for this attempt
            $table->timestamps();

            // One attempt per user per quiz (enforced by model, not DB — to allow future config)
            $table->index(['quiz_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
