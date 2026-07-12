<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            // User can be null for anonymous donations (if the system supports totally guest donations)
            // But if anonymous means "hidden from other members but known to system", we still need user_id.
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // For halaqah-specific donations
            $table->foreignId('halaqah_id')->nullable()->constrained('halaqahs')->nullOnDelete();

            // For campaign-specific donations
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->nullOnDelete();

            $table->enum('type', ['halaqah', 'recurring', 'general', 'campaign'])->default('general');

            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');

            $table->text('note')->nullable(); // Message from donor

            // Who recorded this donation? (if collected manually by treasurer)
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('payment_method', ['cash', 'bkash', 'nagad', 'bank', 'other'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();

            // status: pending (needs treasurer approval if user submits), confirmed, rejected, pending_payment
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'pending_payment'])->default('pending');

            $table->boolean('is_anonymous')->default(false); // Hide name from public/members

            $table->timestamp('donated_at')->nullable(); // Actual time of donation (can be retroactive)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
