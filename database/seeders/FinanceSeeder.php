<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\Donation;
use App\Models\DonationCampaign;
use App\Models\DonationCampaignFaq;
use App\Models\DonationCampaignUpdate;
use App\Models\DonationPledge;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Halaqah;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FinanceSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::role(['super-admin', 'admin', 'accountant'])->first() ?? User::first();
        
        // Fetch the first two normal users (with 'user' role)
        $normalUsers = User::role('user')->get();
        if ($normalUsers->count() < 2) {
            $this->command->warn('Not enough normal users found to seed pledges properly. Falling back to any users.');
            $user1 = User::first();
            $user2 = User::skip(1)->first() ?? $user1;
        } else {
            $user1 = $normalUsers->first();
            $user2 = $normalUsers->skip(1)->first();
        }

        $allUsers = User::all();

        if (!$adminUser || $allUsers->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // 1. Create Bank Accounts (Bangla & Realistic)
        $accounts = [
            [
                'name' => 'প্রধান ক্যাশ বক্স (নগদ টাকা)',
                'type' => 'cash',
                'account_number' => null
            ],
            [
                'name' => 'কমিউনিটি বিকাশ (পার্সোনাল)',
                'type' => 'bkash',
                'account_number' => '01711223344'
            ],
            [
                'name' => 'কমিউনিটি নগদ (মার্চেন্ট)',
                'type' => 'nagad',
                'account_number' => '01811223344'
            ],
            [
                'name' => 'ডাচ্-বাংলা ব্যাংক লিমিটেড (ডিবিবিএল)',
                'type' => 'bank',
                'account_number' => '123456789012'
            ],
        ];

        foreach ($accounts as $acc) {
            BankAccount::firstOrCreate(['name' => $acc['name']], $acc);
        }

        $cashAccount = BankAccount::where('type', 'cash')->first();
        $bkashAccount = BankAccount::where('type', 'bkash')->first();
        $nagadAccount = BankAccount::where('type', 'nagad')->first();
        $bankAccount = BankAccount::where('type', 'bank')->first();

        // 2. Create Expense Categories (Bangla & Realistic)
        $categories = [
            [
                'name' => 'হালাকাহ খাবার ও আপ্যায়ন',
                'color' => '#10b981',
                'icon' => 'o-shopping-cart'
            ],
            [
                'name' => 'দাওয়াহ বই ও লিফলেট প্রিন্টিং',
                'color' => '#3b82f6',
                'icon' => 'o-printer'
            ],
            [
                'name' => 'অসহায় শীতবস্ত্র ও জরুরি ত্রাণ',
                'color' => '#f43f5e',
                'icon' => 'o-heart'
            ],
            [
                'name' => 'ব্যাংক ও মোবাইল ট্রানজেকশন ফি',
                'color' => '#f59e0b',
                'icon' => 'o-arrows-right-left'
            ],
            [
                'name' => 'লাইব্রেরি বুক সেলফ ও আসবাবপত্র',
                'color' => '#64748b',
                'icon' => 'o-ellipsis-horizontal-circle'
            ],
        ];

        foreach ($categories as $cat) {
            $cat['created_by'] = $adminUser->id;
            $cat['slug'] = Str::slug($cat['name']);
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $foodCat = ExpenseCategory::where('slug', Str::slug('হালাকাহ খাবার ও আপ্যায়ন'))->first();
        $printCat = ExpenseCategory::where('slug', Str::slug('দাওয়াহ বই ও লিফলেট প্রিন্টিং'))->first();
        $reliefCat = ExpenseCategory::where('slug', Str::slug('অসহায় শীতবস্ত্র ও জরুরি ত্রাণ'))->first();
        $feeCat = ExpenseCategory::where('slug', Str::slug('ব্যাংক ও মোবাইল ট্রানজেকশন ফি'))->first();
        $furnCat = ExpenseCategory::where('slug', Str::slug('লাইব্রেরি বুক সেলফ ও আসবাবপত্র'))->first();

        // 3. Seed 5 Detailed Donation Campaigns (Bangla)
        $campaignsData = [
            [
                'title' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ ও দাওয়াহ লাইব্রেরি সম্প্রসারণ',
                'description' => 'বিশ্ববিদ্যালয়ের কেন্দ্রীয় মসজিদ সংলগ্ন দাওয়াহ লাইব্রেরির বুক সেলফ, ই-বুক রিডার এবং বসার জায়গা বাড়ানোর জন্য অর্থ সংগ্রহ। আমাদের লক্ষ্য শিক্ষার্থীদের জ্ঞান অর্জনের জন্য একটি সুন্দর পরিবেশ তৈরি করা।',
                'goal_amount' => 300000.00,
                'status' => 'active',
                'starts_at' => Carbon::now()->subDays(30),
                'ends_at' => Carbon::now()->addDays(60),
            ],
            [
                'title' => 'অসহায় শিক্ষার্থীদের জন্য ফ্রি শিক্ষা উপকরণ বিতরণ ও বৃত্তি তহবিল',
                'description' => 'দরিদ্র ও মেধাবী শিক্ষার্থীদের সেমিস্টার ফি পরিশোধ, বই ক্রয় এবং প্রয়োজনীয় শিক্ষা উপকরণ সরবরাহে নিয়মিত সহায়তা। আসুন আমরা তাদের শিক্ষা জীবনে আলোর পথ দেখাই।',
                'goal_amount' => 150000.00,
                'status' => 'active',
                'starts_at' => Carbon::now()->subDays(15),
                'ends_at' => Carbon::now()->addDays(90),
            ],
            [
                'title' => 'উপকূলীয় এলাকায় শীতবস্ত্র ও কম্বল বিতরণ কর্মসূচি',
                'description' => 'পটুয়াখালীর উপকূলবর্তী অঞ্চলের শীতার্ত ও দরিদ্র মানুষের জন্য শীতবস্ত্র ও মানসম্মত কম্বল ক্রয়ের তহবিল। আপনার সামান্য দান একজন বৃদ্ধ বা শিশুর তীব্র শীত নিবারণ করতে পারে।',
                'goal_amount' => 100000.00,
                'status' => 'completed',
                'starts_at' => Carbon::now()->subDays(90),
                'ends_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'রমজান ইফতার ও ফুড প্যাক বিতরণ ২০২৬',
                'description' => 'পবিত্র রমজান মাসে ক্যাম্পাসের দরিদ্র কর্মচারী, দিনমজুর এবং অসহায় মেস শিক্ষার্থীদের মাঝে ইফতার ও মাসব্যাপী খাদ্যসামগ্রী বিতরণ প্রকল্প। রোজাদারদের মুখে হাসি ফোটান।',
                'goal_amount' => 200000.00,
                'status' => 'active',
                'starts_at' => Carbon::now()->subDays(5),
                'ends_at' => Carbon::now()->addDays(40),
            ],
            [
                'title' => 'ক্যাম্পাস বনায়ন ও পরিবেশ পরিচ্ছন্নতা উদ্যোগ',
                'description' => 'বিশ্ববিদ্যালয় ক্যাম্পাসে ফলদ ও ঔষধি গাছ রোপণ এবং পরিচ্ছন্ন পরিবেশ নিশ্চিত করতে ডাস্টবিন স্থাপন ও সচেতনতা বৃদ্ধি কর্মসূচি। এটি একটি সাদকাহ জারিয়া প্রকল্প।',
                'goal_amount' => 50000.00,
                'status' => 'active',
                'starts_at' => Carbon::now()->subDays(2),
                'ends_at' => Carbon::now()->addDays(30),
            ],
        ];

        $campaigns = [];
        foreach ($campaignsData as $cData) {
            $cData['created_by'] = $adminUser->id;
            $cData['slug'] = Str::slug($cData['title']);
            $cData['currency'] = 'BDT';
            
            $campaign = DonationCampaign::firstOrCreate(['slug' => $cData['slug']], $cData);
            $campaigns[] = $campaign;

            // 4. Seed 3-4 FAQs for each campaign
            $faqs = [
                [
                    'question' => 'আমি কীভাবে এই ক্যাম্পেইনে অনুদান দিতে পারি?',
                    'answer' => 'আপনি আমাদের বিকাশ মার্চেন্ট নম্বর, নগদ অ্যাকাউন্ট অথবা সরাসরি ব্যাংক একাউন্টে ট্রান্সফার করতে পারেন। এছাড়াও সরাসরি প্রধান কোষাধ্যক্ষের কাছে ক্যাশ টাকা প্রদান করতে পারেন।',
                ],
                [
                    'question' => 'সংগৃহীত ফান্ডের হিসাব কীভাবে পাব?',
                    'answer' => 'আমাদের ওয়েবসাইটের লাইভ আপডেট সেকশনে প্রতিটি অনুদান এবং খরচের পুঙ্খানুপুঙ্খ বিবরণ সহ ক্যাশ রসিদ নিয়মিত প্রকাশ করা হবে।',
                ],
                [
                    'question' => 'দানকারী কি বেনামে দান করতে পারেন?',
                    'answer' => 'হ্যাঁ, দান করার সময় "বেনামে দান করুন" অপশনটি চালু করে দিলেই আপনার ব্যক্তিগত নাম-পরিচয় সাইটে গোপন রাখা হবে।',
                ],
                [
                    'question' => 'দান করার পর রসিদ বা কনফার্মেশন কীভাবে পাব?',
                    'answer' => 'মোবাইল ব্যাংকিং বা ব্যাংক ট্রান্সফারের ক্ষেত্রে আপনার ট্রানজেকশন আইডি প্রদান করার ২৪ ঘণ্টার মধ্যে আমাদের এডমিন প্যানেল ভেরিফাই করে নোটিফিকেশন পাঠিয়ে দেবে।',
                ],
            ];

            foreach (array_slice($faqs, 0, rand(3, 4)) as $faqData) {
                DonationCampaignFaq::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $allUsers->random()->id,
                    'question' => $faqData['question'],
                    'answer' => $faqData['answer'],
                    'answered_by' => $adminUser->id,
                    'answered_at' => Carbon::now()->subDays(rand(1, 10)),
                ]);
            }

            // 5. Seed 2-3 Updates for each campaign
            $updates = [
                [
                    'title' => 'ক্যাম্পেইনের কাজ সফলভাবে শুরু হলো!',
                    'content' => 'আলহামদুলিল্লাহ, আমাদের ক্যাম্পেইনটি আনুষ্ঠানিকভাবে চালু করা হয়েছে। প্রজেক্ট কমিটির সদস্যরা মিটিং করেছেন এবং উপকরণ কেনার প্রাথমিক লিস্ট তৈরি করেছেন। সবাই আন্তরিক দোআ ও দান দিয়ে শরিক হোন।',
                ],
                [
                    'title' => 'আমাদের ৫০% লক্ষ্যমাত্রা অর্জিত হয়েছে!',
                    'content' => 'আপনাদের স্বতঃস্ফূর্ত অংশগ্রহণ এবং আন্তরিক সহযোগিতায় আমরা আমাদের লক্ষ্যমাত্রার অর্ধেক অর্জন করেছি। সংগৃহীত অর্থ দিয়ে অলরেডি কাজ এগিয়ে নেওয়ার প্রস্তুতি নেওয়া হচ্ছে। জাজাকুমুল্লাহু খাইরান।',
                ],
                [
                    'title' => 'মাঠ পর্যায়ে কাজ বাস্তবায়ন শুরু',
                    'content' => 'আমাদের প্রজেক্টের কাজ মাঠ পর্যায়ে সফলভাবে শুরু হয়ে গেছে। কারিগরদের মজুরি ও প্রয়োজনীয় মালামাল কেনা বাবদ প্রথম কিস্তির অর্থ ব্যাংক থেকে রিলিজ করা হয়েছে। কাজের অগ্রগতি জানতে নিয়মিত আপডেট দেখুন।',
                ],
            ];

            foreach (array_slice($updates, 0, rand(2, 3)) as $upData) {
                DonationCampaignUpdate::create([
                    'campaign_id' => $campaign->id,
                    'user_id' => $adminUser->id,
                    'title' => $upData['title'],
                    'content' => $upData['content'],
                    'created_at' => Carbon::now()->subDays(rand(1, 12)),
                ]);
            }
        }

        // 6. Create 5 Donation Pledges for the first 2 users in various states
        $pledgesData = [
            // User 1 Pledges (Regular User: user@mail.com)
            [
                'user_id' => $user1->id,
                'amount' => 500.00,
                'frequency' => 'weekly',
                'starts_at' => Carbon::now()->subWeeks(4),
                'is_active' => true,
                'last_donated_at' => Carbon::now()->subDays(3),
                'next_due_at' => Carbon::now()->addDays(4),
            ],
            [
                'user_id' => $user1->id,
                'amount' => 2000.00,
                'frequency' => 'monthly',
                'starts_at' => Carbon::now()->subMonths(3),
                'is_active' => true,
                'last_donated_at' => Carbon::now()->subDays(15),
                'next_due_at' => Carbon::now()->addDays(15),
            ],
            [
                'user_id' => $user1->id,
                'amount' => 10000.00,
                'frequency' => 'yearly',
                'starts_at' => Carbon::now()->subMonths(6),
                'is_active' => false, // Cancelled / Inactive
                'last_donated_at' => Carbon::now()->subMonths(6),
                'next_due_at' => null,
            ],

            // User 2 Pledges (First department user)
            [
                'user_id' => $user2->id,
                'amount' => 1000.00,
                'frequency' => 'monthly',
                'starts_at' => Carbon::now()->subMonths(2),
                'is_active' => true,
                'last_donated_at' => Carbon::now()->subDays(20),
                'next_due_at' => Carbon::now()->addDays(10),
            ],
            [
                'user_id' => $user2->id,
                'amount' => 250.00,
                'frequency' => 'weekly',
                'starts_at' => Carbon::now()->subWeeks(2),
                'is_active' => false, // Cancelled / Inactive
                'last_donated_at' => Carbon::now()->subWeeks(1),
                'next_due_at' => null,
            ],
        ];

        $pledges = [];
        foreach ($pledgesData as $pData) {
            $pData['currency'] = 'BDT';
            $pledges[] = DonationPledge::create($pData);
        }

        // Get seeded halaqahs
        $halaqahs = Halaqah::all();

        // 7. Seed Donations with campaigns, pledges, and halaqahs (Bangla & Details)
        $donationNotes = [
            'মসজিদের লাইব্রেরি সম্প্রসারণ প্রকল্পে আমার ক্ষুদ্র অবদান। আল্লাহ কবুল করুন।',
            'অসহায় শিক্ষার্থীদের বৃত্তি তহবিলের জন্য সাহায্য পাঠালাম।',
            'উপকূলীয় শীতার্তদের কম্বল ক্রয়ের জন্য অর্থ সাহায্য।',
            'সাপ্তাহিক হালাকাহর সাধারণ সাদাকাহ বক্সের দান।',
            'মাসিক অঙ্গীকারনামা অনুযায়ী আমার নিয়মিত কন্ট্রিবিউশন।',
            'রমজান ইফতার প্রজেক্টে আমার পক্ষ থেকে অংশ। আল্লাহ বরকত দিন।',
            'পরিবেশ পরিচ্ছন্নতা ও বৃক্ষরোপণ কাজের জন্য আমার অনুদান।',
            'ইসলাম প্রচারের মহৎ কাজে সামান্য সাহায্য।',
        ];

        // Let's seed 30 donations in total
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $method = ['cash', 'bkash', 'nagad', 'bank'][rand(0, 3)];
            
            $bankAccId = match ($method) {
                'cash' => $cashAccount->id,
                'bkash' => $bkashAccount->id,
                'nagad' => $nagadAccount->id,
                'bank' => $bankAccount->id,
            };

            $campaign = null;
            $halaqah = null;
            $pledge = null;
            $type = 'general';
            $amount = rand(2, 40) * 50; // increments of 50 (100 to 2000 BDT)

            // Randomly link to Campaign, Halaqah, or Pledge (Recurring)
            $linkChoice = rand(0, 3);
            if ($linkChoice === 0 && !empty($campaigns)) {
                $campaign = $campaigns[array_rand($campaigns)];
                $type = 'campaign';
                $amount = rand(5, 50) * 100; // 500 to 5000 BDT for campaigns
            } elseif ($linkChoice === 1 && $halaqahs->isNotEmpty()) {
                $halaqah = $halaqahs->random();
                $type = 'halaqah';
                $amount = rand(2, 20) * 25; // 50 to 500 BDT for halaqahs
            } elseif ($linkChoice === 2) {
                // Link to simulated pledge (recurring)
                $pledge = $pledges[array_rand($pledges)];
                $type = 'recurring';
                $amount = $pledge->amount;
            }

            // Assign donor user
            // If linked to pledge, it must be the pledge's user
            $donorId = $pledge ? $pledge->user_id : $allUsers->random()->id;

            Donation::create([
                'user_id' => $donorId,
                'halaqah_id' => $halaqah ? $halaqah->id : null,
                'campaign_id' => $campaign ? $campaign->id : null,
                'type' => $type,
                'amount' => $amount,
                'currency' => 'BDT',
                'note' => $donationNotes[rand(0, count($donationNotes) - 1)],
                'collected_by' => $adminUser->id,
                'payment_method' => $method,
                'bank_account_id' => $bankAccId,
                'transaction_id' => $method !== 'cash' ? strtoupper(Str::random(4)) . rand(100000, 999999) : null,
                'status' => 'confirmed',
                'is_anonymous' => rand(0, 5) === 0, // 1 in 6 anonymous
                'donated_at' => $date,
            ]);
        }

        // 8. Seed Expenses linked to campaigns and halaqahs (Bangla)
        $expenseTitles = [
            'হালাকাহ খাবার ও আপ্যায়ন' => [
                'হালাকাহ শিক্ষার্থীদের বিকালের নাস্তা (সিংগাড়া ও চা)',
                'সাপ্তাহিক ইফতার সামগ্রী ক্রয় (খেজুর ও শরবত)',
                'হালাকাহর বিশেষ মেহমানদের রাতের খাবার আপ্যায়ন',
            ],
            'দাওয়াহ বই ও লিফলেট প্রিন্টিং' => [
                'দাওয়াহ লিফলেট প্রিন্ট খরচ (৫০০ কপি)',
                'বইমেলা উপলক্ষে দাওয়াহ ব্যানার ও পোস্টার প্রিন্ট',
                'ফ্রি বিতরণের জন্য ২৫টি সিরাতগ্রন্থ ক্রয়',
            ],
            'অসহায় শীতবস্ত্র ও জরুরি ত্রাণ' => [
                'উপকূলীয় এলাকার শীতার্তদের জন্য ৫০টি উন্নত কম্বল ক্রয়',
                'বন্যার্তদের জন্য চাল ও ডাল জরুরি ফুড ব্যাগ প্রিপারেশন',
                'শীতার্ত শিশুদের জ্যাকেট ও সোয়েটার ক্রয়',
            ],
            'ব্যাংক ও মোবাইল ট্রানজেকশন ফি' => [
                'বিকাশ মার্চেন্ট ক্যাশআউট চার্জ',
                'ব্যাংক চেক বুক ইস্যু ও অ্যাকাউন্ট ফি',
            ],
            'লাইব্রেরি বুক সেলফ ও আসবাবপত্র' => [
                'লাইব্রেরির জন্য বড় কাঠের বুক সেলফ অর্ডার',
                'পাঠকদের বসার জন্য ৪টি প্লাস্টিক চেয়ার ক্রয়',
                'লাইব্রেরি টেবিলে অতিরিক্ত এলইডি লাইটিং সেটআপ',
            ],
        ];

        // Seed 15 Expenses
        for ($i = 0; $i < 15; $i++) {
            $date = Carbon::now()->subDays(rand(1, 60));
            $method = ['cash', 'bkash', 'bank_transfer'][rand(0, 2)];
            
            $bankAccId = match ($method) {
                'cash' => $cashAccount->id,
                'bkash' => $bkashAccount->id,
                'bank_transfer' => $bankAccount->id,
            };

            // Random category
            $catChoice = rand(0, 4);
            $cat = match ($catChoice) {
                0 => $foodCat,
                1 => $printCat,
                2 => $reliefCat,
                3 => $feeCat,
                4 => $furnCat,
            };

            $titles = $expenseTitles[$cat->name];
            $title = $titles[array_rand($titles)];

            $expense = Expense::create([
                'title' => $title,
                'expense_category_id' => $cat->id,
                'recorded_by' => $adminUser->id,
                'bank_account_id' => $bankAccId,
                'amount' => rand(1, 30) * 100, // 100 to 3000 BDT
                'payment_method' => $method,
                'transaction_id' => $method !== 'cash' ? 'EXP' . rand(100000, 999999) : null,
                'expense_date' => $date,
                'status' => 'confirmed',
                'notes' => 'সিস্টেম জেনারেটেড বাংলা খরচের বিবরণী।',
            ]);

            // Link some expenses polymorphically
            if (rand(0, 1)) {
                if (rand(0, 1) && !empty($campaigns)) {
                    $campaign = $campaigns[array_rand($campaigns)];
                    $expense->linkable_type = DonationCampaign::class;
                    $expense->linkable_id = $campaign->id;
                } elseif ($halaqahs->isNotEmpty()) {
                    $halaqah = $halaqahs->random();
                    $expense->linkable_type = Halaqah::class;
                    $expense->linkable_id = $halaqah->id;
                }
                $expense->save();
            }
        }

        // 9. Seed Fund Transfers (Bangla & Realistic)
        $transfersData = [
            [
                'from' => $bkashAccount,
                'to' => $bankAccount,
                'amount' => 15000.00,
                'fee' => 270.00,
                'notes' => 'বিকাশ মার্চেন্ট অ্যাকাউন্ট থেকে ডাচ-বাংলা ব্যাংক অ্যাকাউন্টে তহবিল স্থানান্তর।',
                'ref' => 'TXN' . rand(100000, 999999),
                'date' => Carbon::now()->subDays(25),
            ],
            [
                'from' => $nagadAccount,
                'to' => $cashAccount,
                'amount' => 5000.00,
                'fee' => 75.00,
                'notes' => 'নগদ অ্যাকাউন্ট থেকে ক্যাশ আউট করে প্রধান ক্যাশ বক্সে জমা।',
                'ref' => 'TXN' . rand(100000, 999999),
                'date' => Carbon::now()->subDays(15),
            ],
            [
                'from' => $bankAccount,
                'to' => $cashAccount,
                'amount' => 10000.00,
                'fee' => 0.00,
                'notes' => 'হালাকাহ আপ্যায়ন ও আনুসাঙ্গিক খরচের জন্য ব্যাংক অ্যাকাউন্ট থেকে নগদ উত্তোলন।',
                'ref' => 'TXN' . rand(100000, 999999),
                'date' => Carbon::now()->subDays(5),
            ],
            [
                'from' => $bkashAccount,
                'to' => $cashAccount,
                'amount' => 3000.00,
                'fee' => 54.00,
                'notes' => 'বিকাশ পার্সোনাল অ্যাকাউন্ট থেকে ক্যাশ আউট করে ক্যাশ বক্সে স্থানান্তর।',
                'ref' => 'TXN' . rand(100000, 999999),
                'date' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($transfersData as $tData) {
            if (!$tData['from'] || !$tData['to']) {
                continue;
            }

            // Create fee expense if fee > 0
            $feeExpense = null;
            if ($tData['fee'] > 0) {
                $feeExpense = Expense::create([
                    'title' => 'তহবিল স্থানান্তর চার্জ (' . $tData['from']->name . ')',
                    'expense_category_id' => $feeCat->id,
                    'recorded_by' => $adminUser->id,
                    'bank_account_id' => $tData['from']->id,
                    'amount' => $tData['fee'],
                    'payment_method' => $tData['from']->type === 'bank' ? 'bank_transfer' : ($tData['from']->type === 'cash' ? 'cash' : $tData['from']->type),
                    'transaction_id' => $tData['ref'],
                    'expense_date' => $tData['date'],
                    'status' => 'confirmed',
                    'notes' => 'তহবিল স্থানান্তর প্রক্রিয়ায় চার্জকৃত ফি।',
                ]);
            }

            \App\Models\FundTransfer::create([
                'from_account_id' => $tData['from']->id,
                'to_account_id' => $tData['to']->id,
                'transferred_by' => $adminUser->id,
                'fee_expense_id' => $feeExpense ? $feeExpense->id : null,
                'amount' => $tData['amount'],
                'fee' => $tData['fee'],
                'transfer_date' => $tData['date'],
                'reference_id' => $tData['ref'],
                'notes' => $tData['notes'],
                'status' => 'completed',
            ]);
        }

        // 10. Seed Monthly Treasury Reports (Bangla & Dynamic)
        $monthsToSeed = [
            ['year' => 2026, 'month' => 5, 'published' => true, 'notes' => 'মে ২০২৬ মাসের আর্থিক প্রতিবেদন। আলহামদুলিল্লাহ, এই মাসে লক্ষ্যমাত্রার চেয়ে বেশি দান সংগৃহীত হয়েছে।'],
            ['year' => 2026, 'month' => 6, 'published' => true, 'notes' => 'জুন ২০২৬ মাসের আর্থিক প্রতিবেদন। হালাকাহ আপ্যায়ন এবং শীতবস্ত্র ক্রয়ের কারণে কিছুটা খরচ বৃদ্ধি পেয়েছে।'],
            ['year' => 2026, 'month' => 7, 'published' => false, 'notes' => 'চলতি জুলাই ২০২৬ মাসের খসড়া প্রতিবেদন। মাস শেষে চূড়ান্ত প্রতিবেদন প্রকাশ করা হবে ইনশাআল্লাহ।'],
        ];

        foreach ($monthsToSeed as $m) {
            $calc = \App\Models\MonthlyTreasuryReport::calculate($m['year'], $m['month']);
            
            // Calculate opening balance based on previous month's closing balance, or fallback to 50000
            $prevReport = \App\Models\MonthlyTreasuryReport::where('year', $m['year'])
                ->where('month', $m['month'] - 1)
                ->first();
            $openingBalance = $prevReport ? $prevReport->closing_balance : 50000.00;
            $closingBalance = $openingBalance + ($calc['total_income'] - $calc['total_expense'] - $calc['total_transfer_fees']);

            \App\Models\MonthlyTreasuryReport::updateOrCreate(
                ['year' => $m['year'], 'month' => $m['month']],
                [
                    'total_income' => $calc['total_income'],
                    'total_expense' => $calc['total_expense'],
                    'total_transfer_fees' => $calc['total_transfer_fees'],
                    'opening_balance' => $openingBalance,
                    'closing_balance' => $closingBalance,
                    'notes' => $m['notes'],
                    'generated_by' => $adminUser->id,
                    'published_at' => $m['published'] ? Carbon::now() : null,
                ]
            );
        }

        $this->command->info('Finance & Donations Portal data seeded successfully in Bangla!');
    }
}
