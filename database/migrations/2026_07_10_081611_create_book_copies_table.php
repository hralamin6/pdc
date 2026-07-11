<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();

            // Ownership
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('library_hub_id')->nullable()->constrained('library_hubs')->nullOnDelete();

            $table->enum('status', ['available', 'borrowed', 'lost'])->default('available');
            $table->boolean('is_borrowable')->default(true);
            $table->string('condition')->nullable(); // e.g. "New", "Good", "Worn"

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_copies');
    }
};
