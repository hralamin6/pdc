<?php

namespace Database\Seeders;

use App\Models\DailyReport;
use App\Models\DailyReportEntry;
use App\Models\DailyReportTemplate;
use App\Models\User;
use App\Models\UserReportItem;
use App\Models\UserStreak;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DailyReportSeeder extends Seeder
{
    public function run(): void
    {
        $templates = DailyReportTemplate::all();
        if ($templates->isEmpty()) {
            $this->command->warn('No daily report templates found. Run DailyReportTemplateSeeder first.');
            return;
        }

        $students = User::role('user')->get();
        if ($students->isEmpty()) {
            $students = User::all();
        }

        if ($students->isEmpty()) {
            $this->command->warn('No users found to seed daily reports.');
            return;
        }

        // We will seed detailed reports for 6 active students to show full history
        $activeStudents = $students->random(min(6, $students->count()));

        // Choose a standard set of 16 templates to assign to these users
        $standardTemplateTitles = [
            'ফজর সালাত (Fajr Salah)',
            'যোহর সালাত (Dhuhr Salah)',
            'আসর সালাত (Asr Salah)',
            'মাগরিব সালাত (Maghrib Salah)',
            'এশা সালাত (Isha Salah)',
            'জামায়াতে সালাত আদায় (Salah in Jamaat)',
            'তাহাজ্জুদ সালাত (Tahajjud Salah)',
            'কুরআন তিলাওয়াত - পৃষ্ঠা (Quran Recitation - Pages)',
            'সকাল-সন্ধ্যার দোআ ও আযকার (Morning/Evening Adhkar)',
            'একাডেমিক পড়াশোনা - ঘণ্টা (Academic Study - Hours)',
            'দ্বীনি বই পড়া - মিনিট (Islamic Reading - Minutes)',
            'শারীরিক ব্যায়াম - মিনিট (Physical Exercise - Minutes)',
            'মোবাইল/স্ক্রিন টাইম নিয়ন্ত্রণ - ঘণ্টা (Screen Time Limit - Hours)',
            'সোশ্যাল মিডিয়া অপচয় রোধ (Avoiding Social Media Waste)',
            'তাড়াতাড়ি ঘুমানো (Slept Early)',
            'সাদাকাহ/দান (Sadaqah/Charity)',
        ];

        $selectedTemplates = $templates->filter(function ($t) use ($standardTemplateTitles) {
            return in_array($t->title, $standardTemplateTitles);
        });

        $reflections = [
            'আজকের দিনটি চমৎকার কেটেছে। আলহামদুলিল্লাহ, পাঁচ ওয়াক্ত নামাজ মসজিদে জামায়াতের সাথে পড়তে পেরেছি।',
            'আজকে পড়াশোনায় একটু মনযোগ কম ছিল। সোশ্যাল মিডিয়ায় সময় বেশি নষ্ট হয়েছে। কাল থেকে সচেতন হব, ইনশাআল্লাহ।',
            'আজকে সকালে তাড়াতাড়ি উঠে তাফসীর পড়েছি। মনটা অনেক হালকা ও ফুরফুরে লাগছে।',
            'আজ সেমিস্টার পরীক্ষার প্রস্তুতি ভালো হয়েছে। শরীরচর্চা করতে পেরেছি, আলহামদুলিল্লাহ।',
            'পড়াশোনা ও সালাত ঠিকঠাক হয়েছে। তবে ঘুমাতে একটু দেরি হয়ে গেল।',
            'আলহামদুলিল্লাহ, আজ এক গরীব মানুষকে সাহায্য করতে পেরে দারুণ আত্মতৃপ্তি পেয়েছি।',
        ];

        foreach ($activeStudents as $student) {
            // 1. Assign report items to the student
            $userReportItems = [];
            foreach ($selectedTemplates as $template) {
                $item = UserReportItem::updateOrCreate([
                    'user_id' => $student->id,
                    'daily_report_template_id' => $template->id,
                ], [
                    'custom_title' => null,
                    'type' => $template->type,
                    'is_active' => true,
                    'sort_order' => $template->sort_order,
                ]);
                $userReportItems[] = $item;
            }

            // 2. Seed reports for the past 30 days
            $startDate = Carbon::now()->subDays(30);
            $reportDates = [];

            for ($day = 0; $day < 30; $day++) {
                $currentDate = (clone $startDate)->addDays($day);
                
                // 85% submission probability to create streaks and realistic gaps
                if (rand(1, 100) > 85) {
                    continue;
                }

                $reportDates[] = $currentDate->format('Y-m-d');

                $report = DailyReport::updateOrCreate([
                    'user_id' => $student->id,
                    'date' => $currentDate->format('Y-m-d'),
                ], [
                    'privacy_level' => collect(['private', 'mentor_only', 'public'])->random(),
                    'notes' => rand(1, 10) <= 4 ? collect($reflections)->random() : null,
                    'status' => 'submitted',
                ]);

                // Seed entries for each of the user's report items
                foreach ($userReportItems as $item) {
                    $booleanVal = false;
                    $numericVal = null;
                    $textVal = null;

                    // Customize values based on the habit item title
                    if (str_contains($item->template->title, 'Salah')) {
                        // 90% chance of praying
                        $booleanVal = rand(1, 100) <= 90;
                    } elseif (str_contains($item->template->title, 'Salah in Jamaat')) {
                        $numericVal = rand(3, 5); // 3 to 5 prayers in jamaat
                    } elseif (str_contains($item->template->title, 'Tahajjud')) {
                        $booleanVal = rand(1, 100) <= 25; // 25% chance of tahajjud
                    } elseif (str_contains($item->template->title, 'Quran Recitation')) {
                        $numericVal = rand(2, 10); // 2 to 10 pages
                    } elseif (str_contains($item->template->title, 'Adhkar')) {
                        $booleanVal = rand(1, 100) <= 80;
                    } elseif (str_contains($item->template->title, 'Academic Study')) {
                        $numericVal = rand(2, 6); // 2 to 6 hours
                    } elseif (str_contains($item->template->title, 'Islamic Reading')) {
                        $numericVal = rand(15, 60); // 15 to 60 mins
                    } elseif (str_contains($item->template->title, 'Physical Exercise')) {
                        $numericVal = rand(20, 45); // 20 to 45 mins
                    } elseif (str_contains($item->template->title, 'Screen Time')) {
                        $numericVal = rand(1, 4); // 1 to 4 hours
                    } elseif (str_contains($item->template->title, 'Social Media')) {
                        $booleanVal = rand(1, 100) <= 85; // true = succeeded in avoiding waste
                    } elseif (str_contains($item->template->title, 'Slept Early')) {
                        $booleanVal = rand(1, 100) <= 70;
                    } elseif (str_contains($item->template->title, 'Sadaqah')) {
                        $booleanVal = rand(1, 100) <= 30; // 30% chance
                        if ($booleanVal) {
                            $textVal = collect(['১০ টাকা দান করেছি', 'এক দরিদ্রকে দুপুরের খাবার খাইয়েছি', 'মসজিদের বাক্সে দান করেছি'])->random();
                        }
                    }

                    DailyReportEntry::updateOrCreate([
                        'daily_report_id' => $report->id,
                        'user_report_item_id' => $item->id,
                    ], [
                        'boolean_value' => $booleanVal,
                        'numeric_value' => $numericVal,
                        'text_value' => $textVal,
                    ]);
                }
            }

            // 3. Calculate Streaks & Badges based on reportDates
            // Sort dates to calculate streaks correctly
            sort($reportDates);
            
            $currentStreak = 0;
            $longestStreak = 0;
            $tempStreak = 0;
            $lastReportDate = null;
            $totalReports = count($reportDates);

            if ($totalReports > 0) {
                $lastReportDate = Carbon::parse(end($reportDates));
                $today = Carbon::today();
                $yesterday = Carbon::yesterday();

                // Calculate streaks
                $prevDate = null;
                foreach ($reportDates as $dateStr) {
                    $d = Carbon::parse($dateStr);
                    if ($prevDate === null) {
                        $tempStreak = 1;
                    } else {
                        $diff = $d->diffInDays($prevDate);
                        if ($diff === 1) {
                            $tempStreak++;
                        } elseif ($diff > 1) {
                            if ($tempStreak > $longestStreak) {
                                $longestStreak = $tempStreak;
                            }
                            $tempStreak = 1;
                        }
                    }
                    $prevDate = $d;
                }
                if ($tempStreak > $longestStreak) {
                    $longestStreak = $tempStreak;
                }

                // Check if current streak is active (last report is today or yesterday)
                if ($lastReportDate->equalTo($today) || $lastReportDate->equalTo($yesterday)) {
                    // Current streak is the last streak segment
                    $currentStreak = $tempStreak;
                } else {
                    $currentStreak = 0;
                }
            }

            // Award badges based on total reports & longest streak
            $badges = [];
            if ($totalReports >= 5) $badges[] = 'নবাগত অভ্যাসকারী';
            if ($totalReports >= 15) $badges[] = 'অভ্যাস যোদ্ধা';
            if ($longestStreak >= 7) $badges[] = '১ সপ্তাহের ধারাবাহিকতা';
            if ($longestStreak >= 15) $badges[] = 'অদম্য অভ্যাসী';

            UserStreak::updateOrCreate([
                'user_id' => $student->id,
            ], [
                'current_streak' => $currentStreak,
                'longest_streak' => $longestStreak,
                'last_report_date' => $lastReportDate ? $lastReportDate->format('Y-m-d') : null,
                'total_reports' => $totalReports,
                'badges' => $badges,
            ]);
        }

        $this->command->info('Daily Habit Reports, items, entries, and streaks seeded successfully!');
    }
}
