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
    Schema::create('guest_subscriptions', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->string('endpoint', 500)->unique();
      $table->string('public_key')->nullable();
      $table->string('auth_token')->nullable();
      $table->string('content_encoding')->nullable();
      $table->string('session_id')->nullable(); // Track guest session
      $table->string('device_id')->nullable(); // Browser fingerprint for tracking
      $table->timestamps();

      $table->index(['session_id', 'device_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('guest_subscriptions');

  }
};
