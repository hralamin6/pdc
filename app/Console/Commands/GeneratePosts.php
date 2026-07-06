<?php

namespace App\Console\Commands;

use App\Services\BotBook\PostGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GeneratePosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botbook:generate-posts {count=5 : Number of posts to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI-powered fitness/health blog posts with images';

    /**
     * Execute the console command.
     */
    public function handle(PostGeneratorService $postService): int
    {
        $count = (int) $this->argument('count');

        $this->info("📝 Generating {$count} AI-powered blog posts...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        try {
            $posts = [];
            
            for ($i = 0; $i < $count; $i++) {
                try {
                    $generatedPosts = $postService->generatePosts(1);
                    if (!empty($generatedPosts)) {
                        $posts = array_merge($posts, $generatedPosts);
                    }
                    $progressBar->advance();
                } catch (\Exception $e) {
                    $this->error("\n❌ Post generation error: " . $e->getMessage());
                    $progressBar->advance();
                }
            }

            $progressBar->finish();
            $this->newLine(2);

            if (empty($posts)) {
                $this->warn('⚠️  No posts were created.');
                return Command::FAILURE;
            }

            $this->info("✅ Successfully generated " . count($posts) . " posts!");
            $this->newLine();

            // Display created posts
            $tableData = [];
            foreach ($posts as $post) {
                $tableData[] = [
                    $post->id,
                    Str::limit($post->title, 40),
                    $post->user?->name ?? 'N/A',
                    $post->category?->name ?? 'Uncategorized',
                    optional($post->published_at)->format('Y-m-d') ?? '✗',
                ];
            }

            $this->table(
                ['ID', 'Title', 'Author', 'Category', 'Published'],
                $tableData
            );

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Post generation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
