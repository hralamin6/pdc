<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
        Schema::dropIfExists('borrow_requests');
    }
};
