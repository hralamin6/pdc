<?php

namespace Database\Seeders;

use App\Models\DailyReportTemplate;
use Illuminate\Database\Seeder;

class DailyReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Ibadah (Fixed)
            ['title' => 'Fajr Salah', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 10],
            ['title' => 'Dhuhr Salah', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 20],
            ['title' => 'Asr Salah', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 30],
            ['title' => 'Maghrib Salah', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 40],
            ['title' => 'Isha Salah', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 50],
            ['title' => 'Salah in Jamaat', 'type' => 'number', 'category' => 'Ibadah', 'sort_order' => 60], // Number of prayers in jamaat
            ['title' => 'Tahajjud / Qiyam al-Layl', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 70],
            ['title' => 'Fasting (Nafl/Fard)', 'type' => 'boolean', 'category' => 'Ibadah', 'sort_order' => 80],
            
            // Quran & Adhkar
            ['title' => 'Quran Recitation (Pages)', 'type' => 'number', 'category' => 'Quran & Adhkar', 'sort_order' => 90],
            ['title' => 'Morning/Evening Adhkar', 'type' => 'boolean', 'category' => 'Quran & Adhkar', 'sort_order' => 100],
            
            // Study & Action
            ['title' => 'Islamic Study / Reading (Minutes)', 'type' => 'number', 'category' => 'Study & Action', 'sort_order' => 110],
            ['title' => 'Da\'wah Activity', 'type' => 'mixed', 'category' => 'Study & Action', 'sort_order' => 120], // Mixed allows checkbox + notes
            ['title' => 'Sadaqah / Charity', 'type' => 'mixed', 'category' => 'Study & Action', 'sort_order' => 130],
            
            // Personal & Community
            ['title' => 'Physical Exercise', 'type' => 'boolean', 'category' => 'Personal & Community', 'sort_order' => 140],
            ['title' => 'Contacted a Brother/Sister', 'type' => 'mixed', 'category' => 'Personal & Community', 'sort_order' => 150],
            ['title' => 'Slept Early / Woke for Fajr', 'type' => 'boolean', 'category' => 'Personal & Community', 'sort_order' => 160],
            ['title' => 'Personal Reflection / Journal', 'type' => 'text', 'category' => 'Personal & Community', 'sort_order' => 170],
        ];

        foreach ($items as $item) {
            DailyReportTemplate::updateOrCreate(
                ['title' => $item['title']],
                array_merge($item, ['is_system_default' => true])
            );
        }
    }
}
