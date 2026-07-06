<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists
        if (Schema::hasTable('activities')) {
            // Table exists, update it to support UUIDs
            $this->updateExistingTable();
        } else {
            // Table doesn't exist, create it with UUID support
            $this->createTable();
        }
    }

    /**
     * Create the activities table with UUID support
     */
    protected function createTable(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('log_name')->nullable()->index();
            $table->text('description');

            // Support both integer and UUID subject IDs
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id'], 'subject');

            // Support both integer and UUID causer IDs
            $table->string('causer_type')->nullable();
            $table->string('causer_id')->nullable();
            $table->index(['causer_type', 'causer_id'], 'causer');

            $table->json('properties')->nullable();
            $table->string('event')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });
    }

    /**
     * Update existing table to support UUIDs
     */
    protected function updateExistingTable(): void
    {
        // Get the column type
        $subjectIdType = DB::select("SHOW COLUMNS FROM activities WHERE Field = 'subject_id'")[0]->Type ?? null;

        // Only alter if it's not already a string type
        if ($subjectIdType && strpos($subjectIdType, 'bigint') !== false) {
            Schema::table('activities', function (Blueprint $table) {
                // Drop existing indexes first
                $table->dropIndex('subject');
                $table->dropIndex('causer');

                // Change columns to support UUIDs (strings)
                $table->string('subject_id')->nullable()->change();
                $table->string('causer_id')->nullable()->change();

                // Recreate indexes
                $table->index(['subject_type', 'subject_id'], 'subject');
                $table->index(['causer_type', 'causer_id'], 'causer');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
