<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // General Info (Bilingual & Realistic Bangla)
            'app.name' => 'পটুয়াখালী সেন্ট্রাল দাওয়াহ (PSTU Central Dawah)',
            'app.email' => 'dawah.pstu@gmail.com',
            'app.phone' => '+8801700123456',
            'app.address' => 'দুমকী, পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় (PSTU), পটুয়াখালী-৮৬০২',
            'app.details' => 'পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়ের কেন্দ্রীয় দাওয়াহ ফোরাম। শিক্ষার্থীদের দ্বীন প্রচার, সহীহ আকীদা ও সুন্নাহর সঠিক অনুশীলন, লাইব্রেরি বিস্তার এবং সমাজসেবামূলক উদ্যোগে উদ্বুদ্ধ করাই আমাদের মূল লক্ষ্য।',
            'app.placeholder' => 'https://placehold.co/800x600?text=PSTU+Central+Dawah',
            'app.image_url' => 'https://placehold.co/1200x630?text=PSTU+Central+Dawah+Banner',
            'app.env' => 'local',
            'app.debug' => 'true',
            'app.url' => 'http://localhost:8000',
            'app.locale' => 'bn',
            'app.timezone' => 'Asia/Dhaka',
            'queue.connection' => 'sync',

            // Branding URLs
            'branding.logo_url' => 'https://placehold.co/500x120?text=PSTU+Dawah+Logo',
            'branding.icon_url' => 'https://placehold.co/192x192?text=PSTU+Icon',

            // Mail Configurations (Sandbox-friendly defaults)
            'mail.mailer' => 'smtp',
            'mail.host' => 'sandbox.smtp.mailtrap.io',
            'mail.port' => '2525',
            'mail.username' => 'smtp_sandbox_user',
            'mail.password' => 'smtp_sandbox_pass',
            'mail.encryption' => 'tls',
            'mail.from.address' => 'no-reply@pstudawah.org',
            'mail.from.name' => 'PSTU Central Dawah',

            // AI Settings
            'ai.openrouter.api_key' => '',
            'ai.openrouter.base_url' => 'https://openrouter.ai/api/v1',
            'ai.gemini.api_key' => '',
            'ai.pollination.api_key' => '',
            'ai.openai.api_key' => '',
            'ai.openai.org' => '',
            'ai.openai.base_url' => 'https://api.openai.com/v1',

            // AI SDK Configuration defaults
            'ai_sdk.providers' => json_encode([
                'gemini' => [
                    'driver' => 'gemini',
                    'key' => env('GEMINI_API_KEY', ''),
                    'url' => '',
                    'is_enabled' => true,
                ],
                'groq' => [
                    'driver' => 'groq',
                    'key' => env('GROQ_API_KEY', ''),
                    'url' => '',
                    'is_enabled' => true,
                ],
                'mistral' => [
                    'driver' => 'mistral',
                    'key' => env('MISTRAL_API_KEY', ''),
                    'url' => '',
                    'is_enabled' => true,
                ],
                'openrouter' => [
                    'driver' => 'openrouter',
                    'key' => env('OPENROUTER_API_KEY', ''),
                    'url' => 'https://openrouter.ai/api/v1',
                    'is_enabled' => true,
                ],
                'pollinations' => [
                    'driver' => 'pollinations',
                    'key' => env('POLLINATIONS_API_KEY', ''),
                    'url' => 'https://gen.pollinations.ai/v1',
                    'is_enabled' => true,
                ],
            ]),

            'ai_sdk.defaults' => json_encode([
                'default' => 'mistral',
                'default_for_images' => 'pollinations',
                'default_for_audio' => 'openai',
                'default_for_transcription' => 'openai',
                'default_for_embeddings' => 'openai',
                'default_for_reranking' => 'cohere',
            ]),
        ];

        // Clear the cache to prevent stale settings
        Setting::clearCache();

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        $this->command->info('Application Settings seeded successfully in Bangla & English!');
    }
}
