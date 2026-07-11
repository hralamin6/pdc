<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE books MODIFY COLUMN type ENUM('ebook', 'physical', 'both') DEFAULT 'ebook'");
    }

    public function down(): void
    {
        // Reverting this might cause data loss if there are 'both' records, but here's the attempt.
        DB::statement("ALTER TABLE books MODIFY COLUMN type ENUM('ebook', 'physical') DEFAULT 'ebook'");
    }
};
