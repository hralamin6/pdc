<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('halaqah_series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mentor_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft');
            $table->string('banner_path')->nullable();
            $table->string('target_audience_level')->nullable();
            $table->timestamps();
        });

        Schema::create('halaqahs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->nullable()->constrained('halaqah_series')->nullOnDelete();
            $table->string('title');
            $table->string('topic');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            $table->enum('gender_restriction', ['none', 'brothers_only', 'sisters_only'])->default('none');
            $table->unsignedInteger('max_capacity')->nullable();
            $table->boolean('is_registration_open')->default(true);
            $table->string('qr_token')->unique()->nullable();
            $table->foreignId('speaker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('location');
            $table->string('meeting_link')->nullable();
            $table->string('target_audience')->nullable();
            $table->string('materials_path')->nullable();
            $table->json('resources')->nullable();
            $table->timestamps();
        });

        Schema::create('halaqah_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('halaqah_id')->constrained('halaqahs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('rsvp');
            $table->string('status_new')->default('rsvp');
            $table->boolean('attended')->default(false);
            $table->boolean('preparation_completed')->default(false);
            $table->string('check_in_method')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
            
            $table->unique(['halaqah_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('halaqah_attendances');
        Schema::dropIfExists('halaqahs');
        Schema::dropIfExists('halaqah_series');
        Schema::enableForeignKeyConstraints();
    }
};
