<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('to_account_id')->constrained('bank_accounts')->restrictOnDelete();
            $table->foreignId('transferred_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('fee_expense_id')->nullable()->constrained('expenses')->nullOnDelete(); // auto-created fee expense
            $table->decimal('amount', 10, 2);              // amount sent from source
            $table->decimal('fee', 10, 2)->default(0);     // transfer fee charged
            $table->date('transfer_date');
            $table->string('reference_id')->nullable();    // bKash/Nagad TxID
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
