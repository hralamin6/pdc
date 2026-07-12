<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('ai_provider')->default('openrouter');
            $table->string('model')->nullable();
            $table->text('system_prompt')->nullable();
            $table->integer('total_tokens')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'last_message_at']);
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_conversation_id')->constrained('ai_conversations')->cascadeOnDelete();
            $table->enum('role', ['user', 'assistant', 'system']);
            $table->longText('content');
            $table->integer('tokens')->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('ai_messages')->nullOnDelete();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
            
            $table->index(['ai_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::enableForeignKeyConstraints();
    }
};
