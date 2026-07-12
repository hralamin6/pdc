<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('quizzable');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('mode', ['async', 'live'])->default('async');
            $table->enum('status', ['draft', 'published', 'live', 'closed'])->default('draft');
            $table->unsignedSmallInteger('time_limit_minutes')->nullable();
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->dateTime('live_started_at')->nullable();
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('show_answers_after')->default(true);
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_mark_value', 3, 2)->default(0.25);
            $table->unsignedTinyInteger('pass_mark_percent')->nullable();
            $table->unsignedSmallInteger('points_on_pass')->default(10);
            $table->json('bonus_points_for_rank')->nullable();
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->boolean('ai_generated')->default(false);
            $table->timestamps();
        });

        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->enum('type', ['mcq', 'true_false', 'multi_select', 'short_text'])->default('mcq');
            $table->text('question_text');
            $table->text('ideal_answer')->nullable();
            $table->string('question_image')->nullable();
            $table->decimal('marks', 4, 2)->default(1.00);
            $table->text('ai_explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'timed_out'])->default('in_progress');
            $table->decimal('score_raw', 6, 2)->nullable();
            $table->decimal('score_percentage', 5, 2)->nullable();
            $table->unsignedSmallInteger('rank')->nullable();
            $table->unsignedInteger('time_taken_seconds')->nullable();
            $table->boolean('passed')->nullable();
            $table->unsignedSmallInteger('points_awarded')->default(0);
            $table->json('question_order')->nullable();
            $table->timestamps();

            $table->index(['quiz_id', 'user_id']);
        });

        Schema::create('quiz_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->text('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->json('selected_option_ids')->nullable();
            $table->text('text_answer')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('ai_grade', 3, 2)->nullable();
            $table->text('ai_grade_reason')->nullable();
            $table->decimal('admin_grade', 4, 2)->nullable();
            $table->decimal('marks_awarded', 4, 2)->nullable();
            $table->dateTime('answered_at');
            $table->timestamps();

            $table->unique(['attempt_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('quiz_answers');
        Schema::dropIfExists('quiz_options');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('quiz_questions');
        Schema::dropIfExists('quizzes');
        Schema::enableForeignKeyConstraints();
    }
};
