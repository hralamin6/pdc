<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');

            $table->enum('frequency', ['weekly', 'monthly', 'yearly'])->default('monthly');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable(); // Null means indefinitely

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_donated_at')->nullable(); // When the last successful donation for this pledge occurred
            $table->timestamp('next_due_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_pledges');
    }
};
