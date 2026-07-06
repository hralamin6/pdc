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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // Made nullable - set by backup job
            $table->string('disk')->nullable(); // Made nullable - set by backup job
            $table->string('path')->nullable(); // Made nullable - set by backup job
            $table->string('type'); // 'manual', 'scheduled', 'auto'
            $table->string('status')->default('pending'); // 'pending', 'running', 'completed', 'failed'
            $table->bigInteger('file_size')->nullable();
            $table->json('includes')->nullable(); // what was included in backup
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['status', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
