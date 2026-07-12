<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->enum('type', ['cash', 'bkash', 'nagad', 'bank', 'other'])->default('cash');
            $table->string('holder_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('color', 7)->default('#6366f1');
            $table->string('icon', 50)->default('o-tag');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained('expense_categories')->restrictOnDelete();
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'bkash', 'nagad', 'bank_transfer', 'other'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->date('expense_date');
            $table->nullableMorphs('linkable');
            $table->text('notes')->nullable();
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');
            $table->timestamps();
        });

        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('transferred_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('fee_expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->date('transfer_date');
            $table->string('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();
        });

        Schema::create('monthly_treasury_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('total_income', 12, 2)->default(0);
            $table->decimal('total_expense', 12, 2)->default(0);
            $table->decimal('total_transfer_fees', 12, 2)->default(0);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->unique(['year', 'month']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('monthly_treasury_reports');
        Schema::dropIfExists('fund_transfers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('bank_accounts');
        Schema::enableForeignKeyConstraints();
    }
};
