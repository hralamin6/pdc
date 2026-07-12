<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon', 50)->default('o-book-open');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('publications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['ebook', 'physical', 'both'])->default('ebook');
            $table->foreignId('book_category_id')->nullable()->constrained('book_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('authors')->nullOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained('publications')->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('external_link')->nullable();
            $table->unsignedInteger('pages_count')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->string('publication_year', 4)->nullable();
            $table->timestamps();
        });

        Schema::create('book_user_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->enum('reading_status', ['want_to_read', 'reading', 'completed'])->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('review')->nullable();
            $table->unsignedInteger('pages_read')->default(0);
            $table->unique(['user_id', 'book_id']);
            $table->timestamps();
        });

        Schema::create('library_hubs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('library_hub_id')->nullable()->constrained('library_hubs')->nullOnDelete();
            $table->enum('status', ['available', 'borrowed', 'lost'])->default('available');
            $table->boolean('is_borrowable')->default(true);
            $table->string('condition')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('borrow_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrower_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('book_copy_id')->constrained('book_copies')->cascadeOnDelete();
            $table->unsignedInteger('requested_days');
            $table->enum('status', ['pending', 'accepted', 'rejected', 'given', 'active', 'returned'])->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('borrow_requests');
        Schema::dropIfExists('book_copies');
        Schema::dropIfExists('library_hubs');
        Schema::dropIfExists('book_user_interactions');
        Schema::dropIfExists('books');
        Schema::dropIfExists('publications');
        Schema::dropIfExists('book_categories');
        Schema::dropIfExists('authors');
        Schema::enableForeignKeyConstraints();
    }
};
