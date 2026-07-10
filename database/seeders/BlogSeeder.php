<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role(['super-admin', 'admin', 'mentor'])->get();
        if ($users->isEmpty()) {
            $users = User::limit(5)->get();
        }
        if ($users->isEmpty()) {
            $this->command->warn('No users found. Run UserSeeder first.');
            return;
        }

        // 1. Create Blog Categories
        $categories = [
            ['name' => 'Quranic Tafseer', 'slug' => Str::slug('Quranic Tafseer'), 'is_active' => true],
            ['name' => 'Hadith & Sunnah', 'slug' => Str::slug('Hadith & Sunnah'), 'is_active' => true],
            ['name' => 'Seerah of Prophet (PBUH)', 'slug' => Str::slug('Seerah of Prophet PBUH'), 'is_active' => true],
            ['name' => 'Fiqh & Rulings', 'slug' => Str::slug('Fiqh & Rulings'), 'is_active' => true],
            ['name' => 'Islamic Finance', 'slug' => Str::slug('Islamic Finance'), 'is_active' => true],
            ['name' => 'Daily Reflections', 'slug' => Str::slug('Daily Reflections'), 'is_active' => true],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        $allCategories = Category::all();

        // 2. Create Blog Posts
        $postsData = [
            [
                'title' => 'Understanding the Wisdom Behind Patience (Sabr)',
                'excerpt' => 'Patience is not just waiting; it is how we behave while we are waiting. Explore the Quranic perspective on Sabr.',
                'content' => 'Patience (Sabr) is one of the most emphasized virtues in Islam. Allah says in the Quran: "O you who have believed, seek help through patience and prayer. Indeed, Allah is with the patient." (Quran 2:153)...',
                'is_featured' => true,
            ],
            [
                'title' => 'The Etiquettes of Seeking Knowledge',
                'excerpt' => 'Knowledge without proper etiquette is dangerous. Learn how the early scholars approached learning.',
                'content' => 'Imam Malik advised his student: "Learn etiquette before you learn knowledge." This profound advice highlights that knowledge must be accompanied by humility, sincerity, and respect for teachers...',
                'is_featured' => false,
            ],
            [
                'title' => 'Why Islamic Finance is the Future',
                'excerpt' => 'Interest (Riba) destroys societies. Understand the ethical foundations of Islamic banking and finance.',
                'content' => 'Islamic finance is fundamentally based on risk-sharing and asset-backed transactions. Unlike conventional banking which relies on renting money (interest/riba), Islamic economics focuses on trade and real economic activity...',
                'is_featured' => true,
            ],
            [
                'title' => 'Lessons from the Battle of Badr',
                'excerpt' => 'A small group can overcome a large army with faith and trust in Allah. Key lessons from Badr.',
                'content' => 'The Battle of Badr was a turning point in Islamic history. Despite being heavily outnumbered, the Muslims were granted victory through their unwavering reliance on Allah (Tawakkul) and strategic planning...',
                'is_featured' => false,
            ],
            [
                'title' => 'How to Maximize Your Time for Ibadah',
                'excerpt' => 'Time management from an Islamic perspective. Stop wasting time and start earning rewards.',
                'content' => 'Prophet Muhammad (PBUH) said: "There are two blessings which many people lose: health and free time." To make the most of our day, we must prioritize Salah, block out time for Quran, and avoid mindless scrolling...',
                'is_featured' => false,
            ]
        ];

        foreach ($postsData as $idx => $pd) {
            $title = $pd['title'];
            $slug = Str::slug($title);
            
            $post = Post::firstOrNew(['slug' => $slug]);
            if (!$post->exists) {
                $post->fill([
                    'user_id' => $users->random()->id,
                    'category_id' => $allCategories->random()->id,
                    'title' => $title,
                    'excerpt' => $pd['excerpt'],
                    'content' => $pd['content'],
                    'is_featured' => $pd['is_featured'],
                    'published_at' => Carbon::now()->subDays(rand(1, 30)),
                    'views_count' => rand(10, 500),
                ]);
                $post->save();
            }
        }

        $this->command->info('Blog Categories and Posts seeded successfully!');
    }
}
