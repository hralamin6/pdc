<?php

namespace App\Services\BotBook;

use App\Models\Category;
use App\Services\AI\AiServiceFactory;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;

class CategoryGeneratorService
{
    /**
     * Fitness/Health category themes
     */
    private array $categoryThemes = [
        'strength_training' => [
            'name' => 'Strength Training',
            'description' => 'Build muscle, increase strength, and improve overall fitness',
        ],
        'cardio_running' => [
            'name' => 'Cardio & Running',
            'description' => 'Improve cardiovascular health and endurance',
        ],
        'yoga_meditation' => [
            'name' => 'Yoga & Meditation',
            'description' => 'Mind-body wellness, flexibility, and stress relief',
        ],
        'nutrition_diet' => [
            'name' => 'Nutrition & Diet',
            'description' => 'Healthy eating, meal planning, and dietary guidance',
        ],
        'weight_loss' => [
            'name' => 'Weight Loss',
            'description' => 'Sustainable weight management and fat loss strategies',
        ],
        'supplements' => [
            'name' => 'Supplements',
            'description' => 'Vitamins, minerals, and performance supplements',
        ],
        'health_wellness' => [
            'name' => 'Health & Wellness',
            'description' => 'Overall health, disease prevention, and wellbeing',
        ],
        'motivation' => [
            'name' => 'Motivation',
            'description' => 'Inspiration, mindset, and fitness motivation',
        ],
        'home_workouts' => [
            'name' => 'Home Workouts',
            'description' => 'Effective exercises you can do at home',
        ],
        'sports_performance' => [
            'name' => 'Sports Performance',
            'description' => 'Athletic training and performance optimization',
        ],
    ];

    /**
     * Generate fitness/health categories using AI
     */
    public function generateCategories(int $count = 5): array
    {
        $generatedCategories = [];
        $aiService = AiServiceFactory::make('nvidia');

        for ($i = 0; $i < $count; $i++) {
            try {
                // Generate unique category name using AI
                $categoryData = $this->generateCategoryWithAI($aiService);

                // Check if category already exists
                if (Category::where('slug', $categoryData->structured['slug'])->exists()) {
                    \Log::info('Category already exists, skipping', [
                        'name' => $categoryData->structured['name'],
                    ]);

                    continue;
                }

                // Create category
                $category = Category::create([
                    'name' => $categoryData->structured['name'],
                    'slug' => $categoryData->structured['slug'],
                    'parent_id' => null,
                    'is_active' => true,
                ]);

                $generatedCategories[] = $category;

                \Log::info('Category created', [
                    'name' => $category->name,
                    'slug' => $category->slug,
                ]);

                // Small delay to avoid rate limiting
                sleep(2);
            } catch (\Exception $e) {
                \Log::error('Category generation failed', [
                    'iteration' => $i,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $generatedCategories;
    }

    /**
     * Generate category name and slug using AI
     */
    private function generateCategoryWithAI($aiService): StructuredAgentResponse
    {
        $response = \App\Ai\Agents\CategoryWriter::make()
            ->prompt('create a unique category, category name will be in bangla');

        if (! $response) {
            \Log::error('CategoryWriter returned empty response');
            throw new \RuntimeException('Failed to generate category: AI returned empty response');
        }

        \Log::info('AI generated category successfully using CategoryWriter', [
            'name' => $response->structured['name'] ?? 'N/A',
        ]);

        return $response;
    }

    /**
     * Generate enhanced description using AI
     */
    private function generateDescription($aiService, array $theme): string
    {
        $prompt = "Write a compelling 1-2 sentence description for a fitness/health category called '{$theme['name']}'. "
            ."Focus: {$theme['description']}. "
            .'Make it engaging, informative, and motivational. '
            .'Keep it under 150 characters. '
            .'Return ONLY the description text, no quotes or extra formatting.';

        $response = $aiService->chat([
            ['role' => 'user', 'content' => $prompt],
        ], [
            'temperature' => 0.7,
            'max_tokens' => 100,
        ]);

        // Extract content from response
        $description = is_array($response) ? ($response['content'] ?? '') : $response;

        // Clean up
        $description = html_entity_decode(strip_tags($description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = trim($description, " \n\r\t\v\0\"'");

        // Fallback if too short or empty
        if (strlen($description) < 20) {
            return $theme['description'];
        }

        return Str::limit($description, 150);
    }

    /**
     * Generate subcategories for existing categories
     */
    public function generateSubcategories(int $parentId, int $count = 3): array
    {
        $parent = Category::findOrFail($parentId);
        $aiService = AiServiceFactory::make('cerebras');

        $prompt = "Generate {$count} specific subcategory names for the fitness category '{$parent->name}'. "
            .'Return ONLY a JSON array of subcategory names. '
            .'Example format: ["Subcategory 1", "Subcategory 2", "Subcategory 3"]. '
            .'Make them specific, relevant, and practical.';

        try {
            $response = $aiService->chat([
                ['role' => 'user', 'content' => $prompt],
            ], [
                'temperature' => 0.8,
                'max_tokens' => 200,
            ]);

            $responseText = is_array($response) ? ($response['content'] ?? '') : $response;
            $responseText = html_entity_decode(strip_tags($responseText), ENT_QUOTES | ENT_HTML5, 'UTF-8');

            // Extract JSON
            if (preg_match('/\[.*\]/s', $responseText, $matches)) {
                $subcategoryNames = json_decode($matches[0], true);

                if (is_array($subcategoryNames)) {
                    $generatedSubcategories = [];

                    foreach ($subcategoryNames as $name) {
                        $slug = Str::slug($name);

                        // Skip if exists
                        if (Category::where('slug', $slug)->exists()) {
                            continue;
                        }

                        $subcategory = Category::create([
                            'name' => $name,
                            'slug' => $slug,
                            'parent_id' => $parentId,
                            'is_active' => true,
                        ]);

                        $generatedSubcategories[] = $subcategory;

                        \Log::info('Subcategory created', [
                            'name' => $subcategory->name,
                            'parent' => $parent->name,
                        ]);
                    }

                    return $generatedSubcategories;
                }
            }
        } catch (\Exception $e) {
            \Log::error('Subcategory generation failed', [
                'parent_id' => $parentId,
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Get available category themes
     */
    public function getAvailableThemes(): array
    {
        return array_keys($this->categoryThemes);
    }
}
