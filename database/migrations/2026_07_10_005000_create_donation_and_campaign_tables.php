<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('goal_amount', 12, 2)->nullable();
            $table->string('currency', 10)->default('BDT');
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('halaqah_id')->nullable()->constrained('halaqahs')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('donation_campaigns')->nullOnDelete();
            $table->enum('type', ['halaqah', 'recurring', 'general', 'campaign'])->default('general');
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->text('note')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('payment_method', ['cash', 'bkash', 'nagad', 'bank', 'other'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->enum('status', ['pending', 'confirmed', 'rejected', 'pending_payment'])->default('pending');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('donated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donation_pledges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('BDT');
            $table->enum('frequency', ['weekly', 'monthly', 'yearly'])->default('monthly');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_donated_at')->nullable();
            $table->timestamp('next_due_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donation_campaign_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('donation_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('donation_campaign_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('donation_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('question');
            $table->text('answer')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('donation_campaign_faqs');
        Schema::dropIfExists('donation_campaign_updates');
        Schema::dropIfExists('donation_pledges');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donation_campaigns');
        Schema::enableForeignKeyConstraints();
    }
};
