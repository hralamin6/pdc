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
            $table->foreignId('speaker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->string('location');
            $table->string('target_audience')->nullable();
            $table->string('materials_path')->nullable();
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
