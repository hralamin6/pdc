<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [

            ['name' => 'Self Sufficiency', 'slug' => 'self-sufficiency'],
            ['name' => 'Rural Lifestyle', 'slug' => 'rural-lifestyle'],
            ['name' => 'Homesteading', 'slug' => 'homesteading'],
            ['name' => 'Off Grid Living', 'slug' => 'off-grid-living'],
            ['name' => 'Natural Living', 'slug' => 'natural-living'],
            ['name' => 'Sustainable Living', 'slug' => 'sustainable-living'],

            ['name' => 'Regenerative Agriculture', 'slug' => 'regenerative-agriculture'],
            ['name' => 'Soil & Land Health', 'slug' => 'soil-land-health'],
            ['name' => 'Local Crops & Seeds', 'slug' => 'local-crops-seeds'],

            ['name' => 'Animal Based Nutrition', 'slug' => 'animal-based-nutrition'],
            ['name' => 'Animal Farming', 'slug' => 'animal-farming'],

            ['name' => 'Fermentation & Probiotics', 'slug' => 'fermentation-probiotics'],
            ['name' => 'Traditional Food Wisdom', 'slug' => 'traditional-food-wisdom'],
            ['name' => 'Clean & Ethical Food', 'slug' => 'clean-ethical-food'],
            ['name' => 'Food Preservation', 'slug' => 'food-preservation'],

            ['name' => 'Herbal & Natural Remedies', 'slug' => 'herbal-natural-remedies'],

            ['name' => 'Eco Housing & Green Building', 'slug' => 'eco-housing-green-building'],
            ['name' => 'Renewable Energy', 'slug' => 'renewable-energy'],
            ['name' => 'Water & Resource Management', 'slug' => 'water-resource-management'],

            ['name' => 'Survival & Preparedness', 'slug' => 'survival-preparedness'],
            ['name' => 'Off Grid Skills', 'slug' => 'off-grid-skills'],

            ['name' => 'Conscious Consumption', 'slug' => 'conscious-consumption'],
            ['name' => 'Anti Consumerism', 'slug' => 'anti-consumerism'],

            ['name' => 'Discipline & Mindset', 'slug' => 'discipline-mindset'],
            ['name' => 'Community & Local Economy', 'slug' => 'community-local-economy'],

        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => $category['slug']], // unique check
                ['name' => $category['name']]
            );
        }
    }
}
