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
        Schema::create('daily_report_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('type', ['boolean', 'number', 'text', 'mixed'])->default('mixed'); // mixed = checkbox + number/text
            $table->string('category')->nullable(); // e.g., 'Ibadah', 'Study', 'Personal'
            $table->boolean('is_system_default')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_report_templates');
    }
};
