<?php

namespace App\Console\Commands;

use App\Services\BotBook\CategoryGeneratorService;
use Illuminate\Console\Command;

class GenerateCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botbook:generate-categories {count=5 : Number of categories to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-powered fitness/health categories';

    /**
     * Execute the console command.
     */
    public function handle(CategoryGeneratorService $categoryService): int
    {
        $count = (int) $this->argument('count');

        $this->info("🏷️  Generating {$count} fitness/health categories...");
        $this->newLine();

        try {
            $categories = $categoryService->generateCategories($count);

            if (empty($categories)) {
                $this->warn('⚠️  No new categories were created (they may already exist).');
                return Command::SUCCESS;
            }

            $this->info("✅ Successfully generated " . count($categories) . " categories!");
            $this->newLine();

            // Display created categories
            $tableData = [];
            foreach ($categories as $category) {
                $tableData[] = [
                    $category->id,
                    $category->name,
                    $category->slug,
                    $category->is_active ? '✓' : '✗',
                ];
            }

            $this->table(
                ['ID', 'Name', 'Slug', 'Active'],
                $tableData
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Category generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
