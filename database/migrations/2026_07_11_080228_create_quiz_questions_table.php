<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->unsignedSmallInteger('order')->default(0);
            $table->enum('type', ['mcq', 'true_false', 'multi_select', 'short_text'])->default('mcq');
            $table->text('question_text');
            $table->text('ideal_answer')->nullable();
            $table->string('question_image')->nullable(); // Spatie media path
            $table->decimal('marks', 4, 2)->default(1.00);
            $table->text('ai_explanation')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
