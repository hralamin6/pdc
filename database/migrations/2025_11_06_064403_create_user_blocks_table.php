<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This table is not needed - user blocks are handled in conversation_user pivot
    }

    public function down(): void
    {
        //
    }
};
