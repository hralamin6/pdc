<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable(); // For MCQ / multi_select
            $table->text('text_answer')->nullable(); // For short_text
            $table->boolean('is_correct')->nullable(); // null = pending grading
            $table->decimal('ai_grade', 3, 2)->nullable(); // 0.00–1.00
            $table->text('ai_grade_reason')->nullable();
            $table->decimal('admin_grade', 4, 2)->nullable(); // manual override
            $table->decimal('marks_awarded', 4, 2)->nullable(); // final marks
            $table->dateTime('answered_at');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']); // One answer per question per attempt
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
