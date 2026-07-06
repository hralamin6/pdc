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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            // Personal Details
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('occupation')->nullable();
            
            // Address Details
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('union_id')->nullable()->constrained()->nullOnDelete();
            
            // Social Media Links
            $table->string('website')->nullable()->default('http://website.com');
            $table->string('facebook')->nullable()->default('http://facebook.com');
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('youtube')->nullable();
            $table->string('github')->nullable();
            
            // Additional Info
            $table->text('bio')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
