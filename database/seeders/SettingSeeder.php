<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'payment.bkash_no' => '01712345678',
            'payment.nagad_no' => '01812345678',
            'payment.bank_name' => 'Dutch Bangla Bank PLC',
            'payment.bank_account_no' => '123-456-7890123',
            'payment.bank_branch' => 'PSTU Branch',
            'payment.bank_holder' => 'PSTU Dawah Community',
        ];

        foreach ($settings as $key => $value) {
            \App\Models\Setting::set($key, $value);
        }
    }
}
