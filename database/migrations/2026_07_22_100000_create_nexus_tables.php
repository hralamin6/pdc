<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nexus_workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 10)->default('🤖');
            $table->string('color', 20)->default('#6366f1');
            $table->string('default_provider')->default('mistral');
            $table->string('default_model')->nullable();
            $table->text('system_prompt')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('user_id');
        });

        Schema::create('nexus_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nexus_chat_id');
            $table->enum('role', ['user', 'assistant', 'system', 'tool']);
            $table->longText('content')->nullable();
            $table->text('thinking')->nullable();
            $table->string('tool_name')->nullable();
            $table->json('tool_input')->nullable();
            $table->text('tool_output')->nullable();
            $table->enum('tool_status', ['pending', 'running', 'success', 'error'])->nullable();
            $table->unsignedInteger('tokens_in')->default(0);
            $table->unsignedInteger('tokens_out')->default(0);
            $table->string('model_used')->nullable();
            $table->boolean('is_error')->default(false);
            $table->foreignId('parent_message_id')->nullable()->constrained('nexus_messages')->nullOnDelete();
            $table->json('metadata')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->timestamps();
        });

        Schema::create('nexus_chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nexus_workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('provider')->default('mistral');
            $table->string('model')->nullable();
            $table->text('system_prompt')->nullable();
            $table->unsignedInteger('total_tokens_in')->default(0);
            $table->unsignedInteger('total_tokens_out')->default(0);
            $table->unsignedInteger('message_count')->default(0);
            $table->timestamp('last_message_at')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->unsignedBigInteger('branch_of_message_id')->nullable();
            $table->timestamps();
            $table->index(['nexus_workspace_id', 'last_message_at']);
            $table->index(['user_id', 'is_archived']);
        });

        // Add FKs after both tables exist
        Schema::table('nexus_messages', function (Blueprint $table) {
            $table->foreign('nexus_chat_id')->references('id')->on('nexus_chats')->cascadeOnDelete();
        });
        Schema::table('nexus_chats', function (Blueprint $table) {
            $table->foreign('branch_of_message_id')->references('id')->on('nexus_messages')->nullOnDelete();
        });

        Schema::create('nexus_memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value');
            $table->string('category')->default('general');
            $table->foreignId('source_chat_id')->nullable()->constrained('nexus_chats')->nullOnDelete();
            $table->json('embedding')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'key']);
            $table->index('user_id');
        });

        Schema::create('nexus_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('nexus_workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->string('file_path');
            $table->unsignedInteger('file_size')->default(0);
            $table->enum('status', ['pending', 'indexing', 'indexed', 'failed'])->default('pending');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'nexus_workspace_id']);
        });

        Schema::create('nexus_document_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nexus_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->mediumText('content');
            $table->json('embedding');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('nexus_document_id');
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('nexus_document_chunks');
        Schema::dropIfExists('nexus_documents');
        Schema::dropIfExists('nexus_memories');
        Schema::dropIfExists('nexus_chats');
        Schema::dropIfExists('nexus_messages');
        Schema::dropIfExists('nexus_workspaces');
        Schema::enableForeignKeyConstraints();
    }
};
