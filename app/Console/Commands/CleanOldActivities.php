<?php

namespace App\Console\Commands;

use App\Models\Activity;
use Illuminate\Console\Command;

class CleanOldActivities extends Command
{
    protected $signature = 'activities:clean {--days=90 : Days to keep activities}';
    protected $description = 'Clean up old activity logs';

    public function handle()
    {
        $days = $this->option('days');
        $date = now()->subDays($days);

        $count = Activity::where('created_at', '<', $date)->delete();

        $this->info("Deleted {$count} activities older than {$days} days.");

        return Command::SUCCESS;
    }
}

