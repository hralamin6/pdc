<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();

            $table->enum('reading_status', ['want_to_read', 'reading', 'completed'])->nullable();
            $table->unsignedTinyInteger('rating')->nullable(); // 1 to 5
            $table->text('review')->nullable();
            $table->unsignedInteger('pages_read')->default(0);

            $table->unique(['user_id', 'book_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_user_interactions');
    }
};
