<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
            $table->string('meeting_link')->nullable(); // For hybrid/online
            $table->string('target_audience')->nullable();
            $table->string('materials_path')->nullable();
            $table->json('resources')->nullable(); // Array of links/files
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halaqahs');
    }
};
