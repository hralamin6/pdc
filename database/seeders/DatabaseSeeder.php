<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles & permissions first
        $this->call([DivisionSeeder::class]);
        $this->call([DistrictSeeder::class]);
        $this->call([UpazilaSeeder::class]);
        $this->call([UnionSeeder::class]);

        $this->call([PermissionSeeder::class]);
        $this->call([SettingSeeder::class]);

        // Seed demo users with specific roles
        $superadmin = User::updateOrCreate([
            'email' => 'superadmin@mail.com'], [
                'name' => 'সুপার এডমিন',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        $admin = User::updateOrCreate([
            'email' => 'admin@mail.com'], [
                'name' => 'এডমিন',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        $accountant = User::updateOrCreate([
            'email' => 'accountant@mail.com'], [
                'name' => 'হিসাবরক্ষক',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        $mentor = User::updateOrCreate([
            'email' => 'mentor@mail.com'], [
                'name' => 'হালাকাহ মেন্টর',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        $user = User::updateOrCreate([
            'email' => 'user@mail.com'], [
                'name' => 'সাধারণ ব্যবহারকারী',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        // Assign roles
        if ($superadmin && ! $superadmin->hasRole('super-admin')) {
            $superadmin->assignRole('super-admin');
        }
        if ($admin && ! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        if ($accountant && ! $accountant->hasRole('accountant')) {
            $accountant->assignRole('accountant');
        }
        if ($mentor && ! $mentor->hasRole('mentor')) {
            $mentor->assignRole('mentor');
        }
        if ($user && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }

        // Seed details and the 20 department users
        $this->call([UserDetailSeeder::class]);
        $this->call([DailyReportTemplateSeeder::class]);
        $this->call([DailyReportSeeder::class]);
        $this->call([BlogSeeder::class]);
        $this->call([HalaqahSeeder::class]);
        $this->call([BookSeeder::class]);
        $this->call([P2PLibrarySeeder::class]);
        $this->call([PageSeeder::class]);
        $this->call([QuizSeeder::class]);
        $this->call([SettingSeeder::class]);
        $this->call([ChatSeeder::class]);
        $this->call([FinanceSeeder::class]);
        $this->call([MiscellaneousSeeder::class]);

    }
}
