<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Halaqah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::role(['super-admin', 'admin', 'accountant'])->first() ?? User::first();
        $users = User::limit(10)->get();

        if (!$adminUser || $users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // 1. Create Bank Accounts
        $accounts = [
            ['name' => 'Main Cash Box', 'type' => 'cash', 'account_number' => null],
            ['name' => 'Community bKash', 'type' => 'bkash', 'account_number' => '01700000000'],
            ['name' => 'Community Nagad', 'type' => 'nagad', 'account_number' => '01800000000'],
            ['name' => 'DBBL Bank', 'type' => 'bank', 'account_number' => '123456789'],
        ];

        foreach ($accounts as $acc) {
            BankAccount::firstOrCreate(['name' => $acc['name']], $acc);
        }

        $cashId = BankAccount::where('type', 'cash')->first()->id;
        $bkashId = BankAccount::where('type', 'bkash')->first()->id;

        // 2. Create Expense Categories
        $categories = [
            ['name' => 'Event Food & Snacks', 'color' => '#10b981', 'icon' => 'o-shopping-cart'],
            ['name' => 'Banners & Printing', 'color' => '#3b82f6', 'icon' => 'o-printer'],
            ['name' => 'Charity & Sadaqah', 'color' => '#f43f5e', 'icon' => 'o-heart'],
            ['name' => 'Transfer Fees', 'color' => '#f59e0b', 'icon' => 'o-arrows-right-left'],
            ['name' => 'Miscellaneous', 'color' => '#64748b', 'icon' => 'o-ellipsis-horizontal-circle'],
        ];

        foreach ($categories as $cat) {
            $cat['created_by'] = $adminUser->id;
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $foodCatId = ExpenseCategory::where('name', 'Event Food & Snacks')->first()->id;
        $printCatId = ExpenseCategory::where('name', 'Banners & Printing')->first()->id;
        $charityCatId = ExpenseCategory::where('name', 'Charity & Sadaqah')->first()->id;

        // Get campaigns and halaqahs if exist
        $campaigns = DonationCampaign::all();
        $halaqahs = Halaqah::all();

        // 3. Seed Donations
        for ($i = 0; $i < 20; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $method = ['cash', 'bkash', 'nagad'][rand(0, 2)];
            $accId = $method === 'cash' ? $cashId : $bkashId;
            
            Donation::create([
                'user_id' => $users->random()->id,
                'amount' => rand(100, 2000),
                'payment_method' => $method,
                'bank_account_id' => $accId,
                'transaction_id' => $method !== 'cash' ? 'TXN' . rand(100000, 999999) : null,
                'donated_at' => $date,
                'status' => 'confirmed',
                'collected_by' => $adminUser->id,
                'campaign_id' => $campaigns->isNotEmpty() && rand(0, 1) ? $campaigns->random()->id : null,
                'halaqah_id' => $halaqahs->isNotEmpty() && rand(0, 1) ? $halaqahs->random()->id : null,
            ]);
        }

        // 4. Seed Expenses
        for ($i = 0; $i < 15; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $method = ['cash', 'bkash'][rand(0, 1)];
            $accId = $method === 'cash' ? $cashId : $bkashId;
            
            $catId = [$foodCatId, $printCatId, $charityCatId][rand(0, 2)];
            $title = match ($catId) {
                $foodCatId => ['Iftar Items', 'Halaqah Snacks', 'Water Bottles'][rand(0, 2)],
                $printCatId => ['Welcome Banner', 'Dawah Flyers', 'Event Badges'][rand(0, 2)],
                $charityCatId => ['Medical Help', 'Poor Student Fund', 'Winter Clothes'][rand(0, 2)],
            };

            $expense = Expense::create([
                'title' => $title,
                'expense_category_id' => $catId,
                'recorded_by' => $adminUser->id,
                'bank_account_id' => $accId,
                'amount' => rand(200, 3000),
                'payment_method' => $method,
                'transaction_id' => $method !== 'cash' ? 'EXP' . rand(100000, 999999) : null,
                'expense_date' => $date,
                'status' => 'confirmed',
                'notes' => 'Seeded expense data',
            ]);

            // Link some expenses
            if (rand(0, 1)) {
                if ($campaigns->isNotEmpty() && rand(0, 1)) {
                    $expense->linkable_type = DonationCampaign::class;
                    $expense->linkable_id = $campaigns->random()->id;
                } elseif ($halaqahs->isNotEmpty()) {
                    $expense->linkable_type = Halaqah::class;
                    $expense->linkable_id = $halaqahs->random()->id;
                }
                $expense->save();
            }
        }

        $this->command->info('Finance data (Accounts, Categories, Donations, Expenses) seeded successfully!');
    }
}
