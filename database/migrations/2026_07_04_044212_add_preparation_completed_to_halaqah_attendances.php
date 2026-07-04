<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('halaqah_attendances', function (Blueprint $table) {
            $table->boolean('preparation_completed')->default(false)->after('attended');
        });
    }

    public function down(): void
    {
        Schema::table('halaqah_attendances', function (Blueprint $table) {
            $table->dropColumn('preparation_completed');
        });
    }
};
