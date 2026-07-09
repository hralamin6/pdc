<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donations', function (Blueprint $table) {
            $table->string('transaction_id')->nullable()->after('payment_method');
            // Since modifying enums is tricky, we'll alter the column directly using DB statement for MySQL/MariaDB
        });

        // Update the enum to include 'pending_payment'
        DB::statement("ALTER TABLE donations MODIFY COLUMN status ENUM('pending', 'confirmed', 'rejected', 'pending_payment') DEFAULT 'pending'");

        Schema::table('donation_pledges', function (Blueprint $table) {
            $table->timestamp('next_due_at')->nullable()->after('last_donated_at');
        });
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE donations MODIFY COLUMN status ENUM('pending', 'confirmed', 'rejected') DEFAULT 'pending'");

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('transaction_id');
        });

        Schema::table('donation_pledges', function (Blueprint $table) {
            $table->dropColumn('next_due_at');
        });
    }
};
