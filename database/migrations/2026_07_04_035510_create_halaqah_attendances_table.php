<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('halaqah_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('halaqah_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('rsvp');
            $table->string('status_new')->default('rsvp');
            $table->boolean('attended')->default(false);
            $table->boolean('preparation_completed')->default(false);
            $table->string('check_in_method')->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->text('feedback')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();
            
            $table->unique(['halaqah_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('halaqah_attendances');
    }
};
