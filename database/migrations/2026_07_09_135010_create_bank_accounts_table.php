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
            $table->string('name');                         // e.g. "Main bKash Account"
            $table->string('account_number')->nullable();
            $table->string('bank_name')->nullable();        // Bank/MFS provider name
            $table->string('branch')->nullable();
            $table->enum('type', ['cash', 'bkash', 'nagad', 'bank', 'other'])->default('cash');
            $table->string('holder_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
