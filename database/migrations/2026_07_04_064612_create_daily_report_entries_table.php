<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_report_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('daily_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_report_item_id')->constrained()->cascadeOnDelete();
            $table->boolean('boolean_value')->default(false);
            $table->integer('numeric_value')->nullable();
            $table->text('text_value')->nullable();
            $table->timestamps();
            
            $table->unique(['daily_report_id', 'user_report_item_id'], 'entry_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_report_entries');
    }
};
