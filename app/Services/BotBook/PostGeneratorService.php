<?php

namespace App\Services\BotBook;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use App\Services\AI\AiServiceFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;

class PostGeneratorService
{
    /**
     * Post content types with their characteristics
     */
    private array $contentTypes = [

        'rural_lifestyle' => [
            'name_bn' => 'গ্রামীণ জীবনধারা',
            'name_en' => 'Rural Lifestyle',
            'sub_categories' => ['Village Living', 'Slow Life', 'Community Culture'],
        ],

        'self_sufficiency' => [
            'name_bn' => 'স্বয়ংসম্পূর্ণতা',
            'name_en' => 'Self Sufficiency',
            'sub_categories' => ['Food Independence', 'Energy Independence', 'DIY Skills'],
        ],

        'self_reliant_family' => [
            'name_bn' => 'আত্মনির্ভরশীল পরিবার',
            'name_en' => 'Self Reliant Family',
            'sub_categories' => ['Family Farming', 'Home Skills', 'Family Economy'],
        ],

        'homesteading_basics' => [
            'name_bn' => 'হোমস্টেডিং বেসিকস',
            'name_en' => 'Homesteading Basics',
            'sub_categories' => ['Land Setup', 'Animal Care', 'Food Preservation'],
        ],

        'off_grid_living' => [
            'name_bn' => 'অফ-গ্রিড জীবনযাপন',
            'name_en' => 'Off Grid Living',
            'sub_categories' => ['Solar Power', 'Water Systems', 'Remote Living'],
        ],

        'urban_vs_rural' => [
            'name_bn' => 'শহর বনাম গ্রাম',
            'name_en' => 'Urban vs Rural',
            'sub_categories' => ['Cost of Living', 'Lifestyle Health', 'Freedom Comparison'],
        ],

        'natural_living' => [
            'name_bn' => 'প্রাকৃতিক জীবন',
            'name_en' => 'Natural Living',
            'sub_categories' => ['Nature Connection', 'Sunlight Health', 'Barefoot Living'],
        ],

        'sustainable_living' => [
            'name_bn' => 'টেকসই বসবাস',
            'name_en' => 'Sustainable Living',
            'sub_categories' => ['Low Waste', 'Eco Practices', 'Green Habits'],
        ],

        'minimal_consumption' => [
            'name_bn' => 'কম ভোগে স্বাধীনতা',
            'name_en' => 'Freedom Through Minimal Consumption',
            'sub_categories' => ['Minimal Needs', 'Simple Living', 'Debt Free Life'],
        ],

        'grow_your_food' => [
            'name_bn' => 'নিজের খাবার নিজে উৎপাদন',
            'name_en' => 'Grow Your Own Food',
            'sub_categories' => ['Home Gardening', 'Seasonal Crops', 'Seed Saving'],
        ],

        // Captain Green Mindset

        'captain_green_mindset' => [
            'name_bn' => 'ক্যাপ্টেন গ্রিন মাইন্ডসেট',
            'name_en' => 'Captain Green Mindset',
            'sub_categories' => ['Mission Living', 'Discipline Code', 'Purpose Focus'],
        ],

        'personal_discipline' => [
            'name_bn' => 'ব্যক্তিগত শৃঙ্খলা',
            'name_en' => 'Personal Discipline',
            'sub_categories' => ['Habit Building', 'Self Control', 'Consistency'],
        ],

        'daily_routine_design' => [
            'name_bn' => 'দৈনিক রুটিন ডিজাইন',
            'name_en' => 'Daily Routine Design',
            'sub_categories' => ['Morning Routine', 'Night Routine', 'Time Blocking'],
        ],

        'leadership_development' => [
            'name_bn' => 'নেতৃত্বগুণ উন্নয়ন',
            'name_en' => 'Leadership Development',
            'sub_categories' => ['Decision Making', 'Team Guidance', 'Responsibility'],
        ],

        'mental_resilience' => [
            'name_bn' => 'মানসিক দৃঢ়তা',
            'name_en' => 'Mental Resilience',
            'sub_categories' => ['Stress Control', 'Focus Training', 'Hardship Endurance'],
        ],

        // Animal Based Nutrition

        'animal_based_nutrition' => [
            'name_bn' => 'প্রাণীজ পুষ্টি',
            'name_en' => 'Animal Based Nutrition',
            'sub_categories' => ['Meat Diet', 'Fat Nutrition', 'Protein Health'],
        ],

        'animal_protein' => [
            'name_bn' => 'প্রাণীজ প্রোটিন',
            'name_en' => 'Animal Protein',
            'sub_categories' => ['Beef Protein', 'Egg Protein', 'Fish Protein'],
        ],

        'healthy_fats' => [
            'name_bn' => 'স্বাস্থ্যকর চর্বি',
            'name_en' => 'Healthy Fats',
            'sub_categories' => ['Saturated Fat', 'Animal Fat', 'Cooking Fat'],
        ],

        'red_meat_nutrition' => [
            'name_bn' => 'রেড মিট পুষ্টি',
            'name_en' => 'Red Meat Nutrition',
            'sub_categories' => ['Iron Source', 'B12 Source', 'Strength Food'],
        ],

        'bone_broth' => [
            'name_bn' => 'হাড়ের স্যুপ',
            'name_en' => 'Bone Broth',
            'sub_categories' => ['Collagen', 'Joint Health', 'Gut Repair'],
        ],

        // Plant Toxin Awareness

        'plant_defense_chemicals' => [
            'name_bn' => 'উদ্ভিদের প্রাকৃতিক প্রতিরক্ষা',
            'name_en' => 'Plant Defense Chemicals',
            'sub_categories' => ['Natural Toxins', 'Plant Survival', 'Food Sensitivity'],
        ],

        'lectin_awareness' => [
            'name_bn' => 'লেক্টিন সচেতনতা',
            'name_en' => 'Lectin Awareness',
            'sub_categories' => ['Beans Lectins', 'Soaking Methods', 'Digestive Impact'],
        ],

        'oxalate_effects' => [
            'name_bn' => 'অক্সালেট প্রভাব',
            'name_en' => 'Oxalate Effects',
            'sub_categories' => ['Kidney Stones', 'Leafy Greens', 'Mineral Binding'],
        ],

        'gluten_sensitivity' => [
            'name_bn' => 'গ্লুটেন সংবেদনশীলতা',
            'name_en' => 'Gluten Sensitivity',
            'sub_categories' => ['Wheat Issues', 'Gut Inflammation', 'Alternatives'],
        ],

        'gut_health' => [
            'name_bn' => 'গাট হেলথ',
            'name_en' => 'Gut Health',
            'sub_categories' => ['Microbiome', 'Digestion', 'Food Reactions'],
        ],

        // Fermentation

        'fermentation_basics' => [
            'name_bn' => 'ফারমেন্টেশন বেসিকস',
            'name_en' => 'Fermentation Basics',
            'sub_categories' => ['Starter Culture', 'Salt Ratio', 'Anaerobic Process'],
        ],

        'kefir_culture' => [
            'name_bn' => 'কেফির',
            'name_en' => 'Kefir Culture',
            'sub_categories' => ['Milk Kefir', 'Water Kefir', 'Probiotic Drink'],
        ],

        'pickle_science' => [
            'name_bn' => 'আচারের বিজ্ঞান',
            'name_en' => 'Pickle Science',
            'sub_categories' => ['Lacto Pickle', 'Oil Pickle', 'Shelf Life'],
        ],

        'sprouting_grains' => [
            'name_bn' => 'অঙ্কুরোদগম',
            'name_en' => 'Sprouting Grains',
            'sub_categories' => ['Enzyme Boost', 'Soaking', 'Digestibility'],
        ],

        'probiotic_foods' => [
            'name_bn' => 'প্রোবায়োটিক খাবার',
            'name_en' => 'Probiotic Foods',
            'sub_categories' => ['Fermented Dairy', 'Fermented Veg', 'Gut Flora'],
        ],

        // Regenerative Agro

        'regenerative_agriculture' => [
            'name_bn' => 'রিজেনারেটিভ কৃষি',
            'name_en' => 'Regenerative Agriculture',
            'sub_categories' => ['Soil Revival', 'Carbon Sequestration', 'Natural Farming'],
        ],

        'nitrogen_fixation' => [
            'name_bn' => 'নাইট্রোজেন ফিক্সেশন',
            'name_en' => 'Nitrogen Fixation',
            'sub_categories' => ['Legumes', 'Soil Fertility', 'Root Bacteria'],
        ],

        'soil_fertility' => [
            'name_bn' => 'মাটির উর্বরতা',
            'name_en' => 'Soil Fertility',
            'sub_categories' => ['Compost', 'Microbes', 'Organic Matter'],
        ],

        'local_crops' => [
            'name_bn' => 'লোকাল ফসল',
            'name_en' => 'Local Crops',
            'sub_categories' => ['Indigenous Seeds', 'Climate Crops', 'Food Security'],
        ],

        'specialty_grains' => [
            'name_bn' => 'বিশেষ শস্য',
            'name_en' => 'Specialty Grains',
            'sub_categories' => ['Ancient Grains', 'Millets', 'Rare Pulses'],
        ],

        // Ethical Products

        'clean_food' => [
            'name_bn' => 'ক্লিন ফুড',
            'name_en' => 'Clean Food',
            'sub_categories' => ['Chemical Free', 'Traceable Food', 'Whole Food'],
        ],

        'safe_food_supply' => [
            'name_bn' => 'নিরাপদ খাবার',
            'name_en' => 'Safe Food Supply',
            'sub_categories' => ['Testing', 'Storage', 'Handling'],
        ],

        'ethical_business' => [
            'name_bn' => 'নৈতিক ব্যবসা',
            'name_en' => 'Ethical Business',
            'sub_categories' => ['Fair Trade', 'Farmer Support', 'Transparency'],
        ],

        'mustard_oil_purity' => [
            'name_bn' => 'সরিষার তেল',
            'name_en' => 'Mustard Oil Purity',
            'sub_categories' => ['Cold Pressed', 'Adulteration Check', 'Omega Profile'],
        ],

        'farm_to_city' => [
            'name_bn' => 'ফার্ম টু সিটি',
            'name_en' => 'Farm to City Supply',
            'sub_categories' => ['Direct Sourcing', 'Logistics', 'Fresh Delivery'],
        ],

        // Prepping & Survival

        'survivalism' => [
            'name_bn' => 'সারভাইভালিজম',
            'name_en' => 'Survivalism',
            'sub_categories' => ['Bushcraft', 'Emergency Skills', 'Wilderness Survival'],
        ],

        'prepping_strategy' => [
            'name_bn' => 'প্রিপিং',
            'name_en' => 'Prepping Strategy',
            'sub_categories' => ['Stockpiling', 'Risk Planning', 'Backup Systems'],
        ],

        'crisis_preparedness' => [
            'name_bn' => 'সংকট প্রস্তুতি',
            'name_en' => 'Crisis Preparedness',
            'sub_categories' => ['Disaster Plan', 'Family Safety', 'Evacuation'],
        ],

        'food_storage' => [
            'name_bn' => 'খাবার মজুদ',
            'name_en' => 'Food Storage',
            'sub_categories' => ['Dry Storage', 'Canning', 'Fermented Storage'],
        ],

        'off_grid_skills' => [
            'name_bn' => 'অফ-গ্রিড দক্ষতা',
            'name_en' => 'Off Grid Skills',
            'sub_categories' => ['Fire Making', 'Water Filtering', 'Shelter Build'],
        ],

        // Eco Lifestyle

        'eco_lifestyle' => [
            'name_bn' => 'পরিবেশবান্ধব জীবনযাপন',
            'name_en' => 'Eco Lifestyle',
            'sub_categories' => ['Low Carbon', 'Eco Habits', 'Green Living'],
        ],

        'ceb_housing' => [
            'name_bn' => 'CEB ঘর নির্মাণ',
            'name_en' => 'CEB Housing',
            'sub_categories' => ['Compressed Earth Block', 'Thermal Comfort', 'Low Cost Build'],
        ],

        'eco_housing' => [
            'name_bn' => 'ইকো হাউসিং',
            'name_en' => 'Eco Housing',
            'sub_categories' => ['Natural Materials', 'Passive Cooling', 'Green Design'],
        ],

        'grounding_practice' => [
            'name_bn' => 'গ্রাউন্ডিং',
            'name_en' => 'Grounding Practice',
            'sub_categories' => ['Barefoot Walking', 'Earth Contact', 'Inflammation Relief'],
        ],

        'solar_homestead' => [
            'name_bn' => 'সোলার হোম',
            'name_en' => 'Solar Homestead',
            'sub_categories' => ['Panels', 'Battery Bank', 'Energy Backup'],
        ],

        // Anti-Consumerism

        'anti_consumerism' => [
            'name_bn' => 'ভোক্তাবাদ বিরোধীতা',
            'name_en' => 'Anti Consumerism',
            'sub_categories' => ['Mindful Buying', 'Need vs Want', 'Simple Economy'],
        ],

        'cheap_food_trap' => [
            'name_bn' => 'সস্তা খাবারের ফাঁদ',
            'name_en' => 'Cheap Food Trap',
            'sub_categories' => ['Ultra Processed', 'Hidden Chemicals', 'Health Cost'],
        ],

        'fake_development' => [
            'name_bn' => 'ফেইক উন্নয়ন',
            'name_en' => 'Fake Development',
            'sub_categories' => ['Urban Illusion', 'GDP Myth', 'Lifestyle Disease'],
        ],

        'real_development' => [
            'name_bn' => 'বাস্তব উন্নয়ন',
            'name_en' => 'Real Development',
            'sub_categories' => ['Food Security', 'Soil Health', 'Community Wealth'],
        ],

        'conscious_living' => [
            'name_bn' => 'সচেতন জীবনযাপন',
            'name_en' => 'Conscious Living',
            'sub_categories' => ['Intentional Life', 'Value Based Living', 'Awareness'],
        ],

    ];

    /**
     * Generate multiple posts
     */
    public function generatePosts(int $count = 5): array
    {
        $generatedPosts = [];

        for ($i = 0; $i < $count; $i++) {
            try {
                $post = $this->generateSinglePost();

                if ($post) {
                    $generatedPosts[] = $post;
 Http::post('https://n8n.aminhub.tech/webhook/laravel-post', [
        'title' => $post->title,
        'url' => route('web.post', $post->slug),
        'excerpt' => $post->excerpt,
        'image' => $post->getFirstMediaUrl('featured_image'),
    ]);
                    \Log::info('Post created', [
                        'id' => $post->id,
                        'title' => $post->title,
                        'author' => $post->user->name,
                    ]);
                }

                // Delay to avoid rate limiting
                sleep(3);
            } catch (\Exception $e) {
                \Log::error('Post generation failed', [
                    'iteration' => $i,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return $generatedPosts;
    }

    /**
     * Generate a single post
     */
    private function generateSinglePost(): ?Post
    {
        // 1. Select random content type
        $contentType = array_rand($this->contentTypes);
        $typeConfig = $this->contentTypes[$contentType];

        // 2. Find best bot user (least posts + personality match)
        $botUser = $this->selectBotUser();

        if (! $botUser) {
            \Log::warning('No suitable bot user found');

            return null;
        }

        // 3. Generate post content with AI using PostWriter agent
        $postData = $this->generatePostContent($typeConfig);

        // 4. Generate SEO meta fields

        // 5. Create post
        $post = Post::create([
            'user_id' => $botUser->id,
            'category_id' => $postData->structured['category_id'],
            'title' => $postData->structured['title'],
            'slug' => Str::slug($postData->structured['title']).'-'.Str::random(6),
            'excerpt' => $postData->structured['excerpt'],
            'content' => $postData->structured['content'],
            'is_featured' => false,
            'published_at' => now(),
            'views_count' => 0,
            'meta_title' => Str::limit($postData->structured['title'], 120) ?? null,
            'meta_description' => Str::limit($postData->structured['excerpt'], 220) ?? null,
            'meta_keywords' => null,
        ]);

        // 6. Generate and attach featured image
        try {
            $this->generateFeaturedImage($post, $postData);
        } catch (\Exception $e) {
            \Log::warning('Featured image generation failed', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $post;
    }

    /**
     * Select bot user with least posts and matching personality
     */
    private function selectBotUser(): ?User
    {
        // Get bot users with post counts
        $botUsers = User::whereHas('roles', function ($q) {
            $q->where('name', 'bot');
        })
            ->whereHas('detail') // Ensure they have details
            ->withCount('posts')
            ->get();

        if ($botUsers->isEmpty()) {
            return null;
        }

        // Filter by preferred personalities if possible
        // For now, just get the one with least posts
        return $botUsers->sortBy('posts_count')->first();
    }

    /**
     * Generate post content using PostWriter agent
     */
    private function generatePostContent(array $typeConfig): StructuredAgentResponse
    {
        $existingCategories = Category::active()->get(['id', 'name']);
        $categoriesList = $existingCategories->map(fn ($cat) => "{$cat->name} (ID: {$cat->id})")->implode(', ');

        $prompt = "
'{$typeConfig['name_bn']}' ({$typeConfig['name_en']}) ক্যাটাগরির উপর ভিত্তি করে একটি বিস্তারিত, তথ্যবহুল এবং আকর্ষণীয় বাংলা ব্লগ পোস্ট লিখুন।

আমার প্রজেক্টটি একটি সমন্বিত লাইফস্টাইল উদ্যোগ—যেখানে স্বয়ংসম্পূর্ণতা, প্রাকৃতিক জীবনযাপন, প্রাণীজ পুষ্টি, ঐতিহ্যবাহী খাদ্যজ্ঞান, টেকসই কৃষি, নৈতিক ব্যবসা এবং সচেতন জীবনদর্শন একসাথে কাজ করে। এখানে গ্রামীণ বাস্তবতা, খাদ্য স্বাধীনতা, মানসিক দৃঢ়তা এবং পরিবেশবান্ধব অবকাঠামোর মাধ্যমে একটি বিকল্প, স্বাস্থ্যকর ও দায়িত্বশীল জীবনধারা তুলে ধরা হয়।

নিচের সাব-ক্যাটাগরিগুলোর মধ্য থেকে যেকোনো একটি **র‌্যান্ডমভাবে নির্বাচন করে** সেই নির্দিষ্ট দৃষ্টিকোণ থেকে পোস্টটি লিখুন:
".implode(', ', $typeConfig['sub_categories']).'।

---

ক্যাটাগরি নির্বাচন নির্দেশনা:

আপনাকে নিচে প্রদত্ত বিদ্যমান ক্যাটাগরি তালিকা থেকে এই পোস্টের জন্য সবচেয়ে প্রাসঙ্গিক একটি ক্যাটাগরি নির্বাচন করতে হবে।

বিদ্যমান ক্যাটাগরি তালিকা:
'.$categoriesList.'

নিয়মাবলি:
- নতুন কোনো ক্যাটাগরি তৈরি করবেন না।
- উপরের তালিকা থেকেই নির্বাচন করবেন।
- category_id অবশ্যই সংখ্যাসূচক হবে।
- নির্বাচিত category_id কনটেন্টের বিষয়ের সাথে সরাসরি প্রাসঙ্গিক হতে হবে।

---

অতিরিক্ত নির্দেশনা:

- পোস্টের জন্য একটি আকর্ষণীয় শিরোনাম লিখুন।
- ২–৩ লাইনের সংক্ষিপ্ত সারসংক্ষেপ (excerpt) লিখুন।
- একটি বাস্তবধর্মী ও বিস্তারিত image generation prompt লিখুন in english (গ্রামীণ/প্রাকৃতিক/স্বয়ংসম্পূর্ণ জীবনধারা ভিত্তিক)।

---

শুধুমাত্র নিচের JSON ফরম্যাটে রেসপন্স রিটার্ন করুন (অন্য কোনো টেক্সট নয়):

{
  "title": "Post Title",
  "content": "Full Blog Content",
  "excerpt": "Short Summary",
  "image_prompt": "Descriptive image generation prompt relevant to the post",
  "category_id": number
}
';

        try {
            $response = \App\Ai\Agents\PostWriter::make()
                ->prompt($prompt);

            if (! $response) {
                \Log::warning('PostWriter returned empty response');
                throw new \RuntimeException('Failed to generate post content: AI returned empty response');
            }

            \Log::info('AI generated post content successfully using PostWriter', [
                'title' => $response->structured['title'] ?? 'N/A',
            ]);

            return $response;

        } catch (\Exception $e) {
            \Log::error('PostWriter generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e; // Rethrow exception for higher-level handling
        }
    }

    /**
     * Select appropriate category based on post content
     */
    /**
     * Generate SEO meta fields
     */

    /**
     * Generate and attach featured image
     */
    private function generateFeaturedImage(Post $post, StructuredAgentResponse $postData): void
    {
        $pollinationsService = AiServiceFactory::make('pollinations');

        $prompt = $postData->structured['image_prompt'].' no text in image, no woman in image';

        \Log::info('Generating featured image for post', [
            'post_id' => $post->id,
            'prompt' => $prompt,
        ]);

        // Generate image
        $imagePath = $pollinationsService->generateImage($prompt, [
            'width' => 1200,
            'height' => 630, // Standard OG image size
            'model' => 'flux',
        ]);

        // Add to media library
        $post->addMedia($imagePath)
            ->toMediaCollection('featured_image');

        // Clean up temp file
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        \Log::info('Featured image generated and attached', [
            'post_id' => $post->id,
        ]);
    }
}
