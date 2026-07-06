<?php

namespace App\Console\Commands;

use App\Services\BotBook\BotUserGeneratorService;
use Illuminate\Console\Command;

class GenerateBotUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'botbook:generate-users {count=5 : Number of bot users to generate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate AI bot users with complete Bangladeshi profile data in Bangla';

    /**
     * Execute the console command.
     */
    public function handle(BotUserGeneratorService $botService): int
    {
        $count = (int) $this->argument('count');

        $this->info("🤖 Generating {$count} bot users...");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $generatedBots = [];

        for ($i = 0; $i < $count; $i++) {
            try {

                $bot = $botService->generateBotUser();
                $generatedBots[] = $bot;

                $progressBar->advance();

                // Small delay to avoid rate limiting
                sleep(2);
            } catch (\Exception $e) {
                $this->error("\n❌ Failed to generate bot: " . $e->getMessage());
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display summary
        $this->info("✅ Successfully generated " . count($generatedBots) . " bot users!");
        $this->newLine();

        // Display bot details in a table
        if (!empty($generatedBots)) {
            $tableData = [];
            foreach ($generatedBots as $bot) {
                $detail = $bot->detail;
                $tableData[] = [
                    $bot->id,
                    $bot->name,
                    $bot->email,
                    $detail?->division?->name ?? 'N/A',
                    $detail?->district?->name ?? 'N/A',
                    $detail?->phone ?? 'N/A',
                ];
            }

            $this->table(
                ['ID', 'Name', 'Email', 'Division', 'District', 'Phone'],
                $tableData
            );
        }

        return Command::SUCCESS;
    }
}
