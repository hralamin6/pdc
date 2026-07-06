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
        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->longText('content');
            $table->integer('tokens')->default(0);
            $table->json('metadata')->nullable(); // For storing additional data like model, temperature, etc.
            $table->foreignId('parent_id')->nullable()->constrained('ai_messages')->nullOnDelete(); // For message regeneration
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            $table->index(['ai_conversation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_messages');
    }
};
