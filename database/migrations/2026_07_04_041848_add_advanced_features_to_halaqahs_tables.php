<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('halaqah_series', function (Blueprint $table) {
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('draft')->after('description');
            $table->string('banner_path')->nullable()->after('status');
            $table->string('target_audience_level')->nullable()->after('banner_path'); // beginner, intermediate, advanced
        });

        Schema::table('halaqahs', function (Blueprint $table) {
            $table->text('description')->nullable()->after('topic');
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft')->after('description');
            $table->enum('gender_restriction', ['none', 'brothers_only', 'sisters_only'])->default('none')->after('status');
            $table->unsignedInteger('max_capacity')->nullable()->after('gender_restriction');
            $table->boolean('is_registration_open')->default(true)->after('max_capacity');
            $table->string('qr_token')->unique()->nullable()->after('is_registration_open');
            $table->string('meeting_link')->nullable()->after('location'); // For hybrid/online
            $table->json('resources')->nullable()->after('materials_path'); // Array of links/files
        });

        Schema::table('halaqah_attendances', function (Blueprint $table) {
            $table->string('check_in_method')->nullable()->after('attended'); // manual, qr_scan
            $table->timestamp('checked_in_at')->nullable()->after('check_in_method');
            // 'status' enum needs to be updated to include 'waitlist'
            // MySQL ENUM modification is tricky, so we'll just add a new column and drop old if needed, or change it to string
        });
        
        // Change enum to string to support 'waitlist' safely across different DBs
        Schema::table('halaqah_attendances', function (Blueprint $table) {
            $table->string('status_new')->default('rsvp')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('halaqah_series', function (Blueprint $table) {
            $table->dropColumn(['status', 'banner_path', 'target_audience_level']);
        });

        Schema::table('halaqahs', function (Blueprint $table) {
            $table->dropColumn(['description', 'status', 'gender_restriction', 'max_capacity', 'is_registration_open', 'qr_token', 'meeting_link', 'resources']);
        });

        Schema::table('halaqah_attendances', function (Blueprint $table) {
            $table->dropColumn(['check_in_method', 'checked_in_at', 'status_new']);
        });
    }
};
