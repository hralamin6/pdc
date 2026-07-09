<?php

namespace App\Console\Commands;

use App\Models\Donation;
use App\Models\DonationPledge;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessRecurringDonations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donation:process-recurring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate pending payment records for due recurring pledges';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing recurring donations...');

        // Find pledges that are active, and either have no next_due_at, or next_due_at is past/today
        $duePledges = DonationPledge::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('next_due_at')
                    ->orWhere('next_due_at', '<=', now());
            })
            ->get();

        $count = 0;

        foreach ($duePledges as $pledge) {
            // Check if there is already a pending_payment for this cycle to avoid duplicates
            // We'll just check if there is ANY pending_payment for this pledge to keep it simple,
            // so we don't generate 5 pending payments if the user ignores it for 5 months.
            // They need to pay the current pending one first.
            $hasPending = Donation::where('user_id', $pledge->user_id)
                ->where('type', 'recurring')
                ->where('status', 'pending_payment')
                ->exists();

            if (! $hasPending) {
                // Generate a pending payment
                Donation::create([
                    'user_id' => $pledge->user_id,
                    'type' => 'recurring',
                    'amount' => $pledge->amount,
                    'currency' => $pledge->currency,
                    'status' => 'pending_payment',
                    'note' => 'Automated recurring pledge generation',
                ]);
                $count++;
            }

            // Update next due date for the pledge
            $nextDue = $pledge->next_due_at ? Carbon::parse($pledge->next_due_at) : now();

            if ($pledge->frequency === 'weekly') {
                $nextDue->addWeek();
            } elseif ($pledge->frequency === 'monthly') {
                $nextDue->addMonth();
            } elseif ($pledge->frequency === 'yearly') {
                $nextDue->addYear();
            }

            // If the calculated next due date is STILL in the past (e.g. missed many months),
            // just jump it forward to next month from today to reset the cycle properly.
            if ($nextDue->isPast()) {
                if ($pledge->frequency === 'weekly') {
                    $nextDue = now()->addWeek();
                } elseif ($pledge->frequency === 'monthly') {
                    $nextDue = now()->addMonth();
                } elseif ($pledge->frequency === 'yearly') {
                    $nextDue = now()->addYear();
                }
            }

            $pledge->update(['next_due_at' => $nextDue]);
        }

        $this->info("Processed $count due pledges and generated pending payments.");
    }
}
