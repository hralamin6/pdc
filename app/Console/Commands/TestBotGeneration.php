<?php

namespace App\Console\Commands;

use App\Services\BotBook\BotUserGeneratorService;
use Illuminate\Console\Command;

class TestBotGeneration extends Command
{
    protected $signature = 'botbook:test';
    protected $description = 'Test bot user generation with a single bot';

    public function handle(BotUserGeneratorService $botService): int
    {
        $this->info('🧪 Testing bot user generation...');
        $this->newLine();

        try {
            // Test with a single bot
            $bot = $botService->generateBotUser('dr_fitbot');

            $this->info('✅ Bot created successfully!');
            $this->newLine();

            // Display bot details
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $bot->id],
                    ['Name', $bot->name],
                    ['Email', $bot->email],
                    ['Phone', $bot->detail?->phone ?? 'N/A'],
                    ['Gender', $bot->detail?->gender ?? 'N/A'],
                    ['Date of Birth', $bot->detail?->date_of_birth ?? 'N/A'],
                    ['Address', $bot->detail?->address ?? 'N/A'],
                    ['Division', $bot->detail?->division?->name ?? 'N/A'],
                    ['District', $bot->detail?->district?->name ?? 'N/A'],
                    ['Upazila', $bot->detail?->upazila?->name ?? 'N/A'],
                    ['Union', $bot->detail?->union?->name ?? 'N/A'],
                    ['Postal Code', $bot->detail?->postal_code ?? 'N/A'],
                    ['Occupation', $bot->detail?->occupation ?? 'N/A'],
                ]
            );

            $this->newLine();
            $this->info('📝 Bio:');
            $this->line($bot->detail?->bio ?? 'N/A');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
