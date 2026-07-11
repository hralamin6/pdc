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
            $table->nullableMorphs('quizzable'); // Halaqah | HalaqahSeries | null
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('mode', ['async', 'live'])->default('async');
            $table->enum('status', ['draft', 'published', 'live', 'closed'])->default('draft');

            // Timing
            $table->unsignedSmallInteger('time_limit_minutes')->nullable(); // null = no limit
            $table->dateTime('available_from')->nullable();
            $table->dateTime('available_until')->nullable();
            $table->dateTime('live_started_at')->nullable();

            // Behaviour
            $table->boolean('shuffle_questions')->default(true);
            $table->boolean('shuffle_options')->default(true);
            $table->boolean('show_answers_after')->default(true);

            // Scoring
            $table->boolean('negative_marking')->default(false);
            $table->decimal('negative_mark_value', 3, 2)->default(0.25);
            $table->unsignedTinyInteger('pass_mark_percent')->nullable();

            // Gamification
            $table->unsignedSmallInteger('points_on_pass')->default(10);
            $table->json('bonus_points_for_rank')->nullable(); // {"1": 50, "2": 30, "3": 10}

            // AI metadata
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->boolean('ai_generated')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
