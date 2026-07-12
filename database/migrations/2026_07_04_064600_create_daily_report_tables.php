<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['boolean', 'number', 'text', 'mixed'])->default('mixed');
            $table->string('category')->nullable();
            $table->boolean('is_system_default')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->enum('privacy_level', ['private', 'mentor_only', 'public'])->default('private');
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted'])->default('draft');
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
        });

        Schema::create('user_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('daily_report_template_id')->nullable()->constrained('daily_report_templates')->nullOnDelete();
            $table->string('custom_title')->nullable();
            $table->enum('type', ['boolean', 'number', 'text', 'mixed'])->default('mixed');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_report_date')->nullable();
            $table->integer('total_reports')->default(0);
            $table->json('badges')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained('daily_reports')->cascadeOnDelete();
            $table->foreignId('user_report_item_id')->constrained('user_report_items')->cascadeOnDelete();
            $table->boolean('boolean_value')->default(false);
            $table->integer('numeric_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            
            $table->unique(['daily_report_id', 'user_report_item_id'], 'entry_unique');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('daily_report_entries');
        Schema::dropIfExists('user_streaks');
        Schema::dropIfExists('user_report_items');
        Schema::dropIfExists('daily_reports');
        Schema::dropIfExists('daily_report_templates');
        Schema::enableForeignKeyConstraints();
    }
};
