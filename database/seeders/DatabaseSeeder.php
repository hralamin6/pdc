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
        $this->call([PostSeeder::class]);

        $this->call([PermissionSeeder::class]);
        
        $this->call([HalaqahSeeder::class]);
        $this->call([DailyReportTemplateSeeder::class]);


        // Seed demo users 
        $admin = User::updateOrCreate([
            'email' => 'admin@mail.com'], [
                'name' => 'admin',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        $user = User::updateOrCreate([
            'email' => 'user@mail.com'], [
                'name' => 'user',
                'email_verified_at' => now(),
                'password' => bcrypt('000000'),
            ]);

        // Assign roles
        if ($admin && ! $admin->hasRole('super-admin')) {
            $admin->assignRole('super-admin');
        }
        if ($user && ! $user->hasRole('user')) {
            $user->assignRole('user');
        }
    }
}
