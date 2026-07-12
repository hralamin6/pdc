<?php

namespace Database\Seeders;

use App\Models\Halaqah;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $halaqah = Halaqah::first();
        if (! $halaqah) {
            $this->command->warn('No halaqah found to attach quizzes. Skipping.');
            return;
        }

        $adminUser = User::role(['super-admin', 'admin'])->first() ?? User::first();
        $createdBy = $adminUser ? $adminUser->id : 1;

        // Fetch students to take quizzes
        $students = User::role('user')->get();
        if ($students->isEmpty()) {
            $students = User::all();
        }

        // =========================================================================
        // QUIZ 1: ইসলামের ইতিহাস ও ঐতিহ্য (সাধারণ জ্ঞান) - Async
        // =========================================================================
        $quiz1 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => $createdBy,
            'title' => 'ইসলামের ইতিহাস ও ঐতিহ্য (সাধারণ জ্ঞান)',
            'description' => 'এই কুইজটি ইসলামের প্রাথমিক যুগের ইতিহাস সম্পর্কে আপনার জ্ঞান যাচাই করার জন্য। সাবধানে উত্তর দিন, ভুল উত্তরের জন্য নেগেটিভ মার্কিং রয়েছে।',
            'mode' => 'async',
            'status' => 'published',
            'time_limit_minutes' => 10,
            'shuffle_questions' => true,
            'negative_marking' => true,
            'negative_mark_value' => 0.25,
            'points_on_pass' => 50,
            'bonus_points_for_rank' => ['1' => 30, '2' => 20, '3' => 10],
            'available_from' => now()->subDays(3),
        ]);

        $q1_1 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'mcq',
            'question_text' => 'হিজরি সাল গণনা শুরু হয় কবে থেকে?',
            'ai_explanation' => 'হিজরি সাল মহানবী (সাঃ)-এর মক্কা থেকে মদিনায় হিজরতের বছর (৬২২ খ্রিস্টাব্দ) থেকে গণনা শুরু হয়।',
            'marks' => 2,
            'order' => 1,
        ]);
        $opt1_1_1 = QuizOption::create(['question_id' => $q1_1->id, 'option_text' => 'রাসূল (সাঃ) এর জন্মের বছর থেকে', 'is_correct' => false]);
        $opt1_1_2 = QuizOption::create(['question_id' => $q1_1->id, 'option_text' => 'ওহী নাযিলের সময় থেকে', 'is_correct' => false]);
        $opt1_1_3 = QuizOption::create(['question_id' => $q1_1->id, 'option_text' => 'মক্কা থেকে মদিনায় হিজরতের বছর থেকে', 'is_correct' => true]);
        $opt1_1_4 = QuizOption::create(['question_id' => $q1_1->id, 'option_text' => 'মক্কা বিজয়ের বছর থেকে', 'is_correct' => false]);

        $q1_2 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multi_select',
            'question_text' => 'নিচের কোন সাহাবীদেরকে "খুলাফায়ে রাশেদীন" বলা হয়? (একাধিক উত্তর হতে পারে)',
            'ai_explanation' => 'খুলাফায়ে রাশেদীন ছিলেন আবু বকর (রাঃ), উমর (রাঃ), উসমান (রাঃ) এবং আলী (রাঃ)।',
            'marks' => 4,
            'order' => 2,
        ]);
        $opt1_2_1 = QuizOption::create(['question_id' => $q1_2->id, 'option_text' => 'আবু বকর (রাঃ)', 'is_correct' => true]);
        $opt1_2_2 = QuizOption::create(['question_id' => $q1_2->id, 'option_text' => 'খালিদ বিন ওয়ালিদ (রাঃ)', 'is_correct' => false]);
        $opt1_2_3 = QuizOption::create(['question_id' => $q1_2->id, 'option_text' => 'উমর (রাঃ)', 'is_correct' => true]);
        $opt1_2_4 = QuizOption::create(['question_id' => $q1_2->id, 'option_text' => 'আবু হুরায়রা (রাঃ)', 'is_correct' => false]);

        $q1_3 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'true_false',
            'question_text' => 'বদরের যুদ্ধ ২য় হিজরিতে সংঘটিত হয়েছিল।',
            'ai_explanation' => 'বদরের যুদ্ধ ১৭ রমজান, ২ হিজরিতে (৬২৪ খ্রিস্টাব্দ) সংঘটিত হয়।',
            'marks' => 2,
            'order' => 3,
        ]);
        $opt1_3_1 = QuizOption::create(['question_id' => $q1_3->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        $opt1_3_2 = QuizOption::create(['question_id' => $q1_3->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        $q1_4 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'short_text',
            'question_text' => 'কুরআনে কতগুলো সূরা রয়েছে? শুধুমাত্র সংখ্যায় লিখুন।',
            'ideal_answer' => '১১৪',
            'ai_explanation' => 'কুরআন মাজিদে মোট ১১৪ টি সূরা রয়েছে।',
            'marks' => 2,
            'order' => 4,
        ]);

        $q1_5 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'mcq',
            'question_text' => 'ইসলামের প্রথম মুয়াযযিন কে ছিলেন?',
            'ai_explanation' => 'হযরত বিলাল ইবনে রাবাহ (রাঃ) ছিলেন ইসলামের প্রথম মুয়াযযিন।',
            'marks' => 2,
            'order' => 5,
        ]);
        $opt1_5_1 = QuizOption::create(['question_id' => $q1_5->id, 'option_text' => 'হযরত সালমান ফারসী (রাঃ)', 'is_correct' => false]);
        $opt1_5_2 = QuizOption::create(['question_id' => $q1_5->id, 'option_text' => 'হযরত বিলাল ইবনে রাবাহ (রাঃ)', 'is_correct' => true]);
        $opt1_5_3 = QuizOption::create(['question_id' => $q1_5->id, 'option_text' => 'হযরত আবু বকর (রাঃ)', 'is_correct' => false]);

        // =========================================================================
        // QUIZ 2: দৈনন্দিন সুন্নাহ ও আদব (লাইভ কুইজ) - Live
        // =========================================================================
        $quiz2 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => $createdBy,
            'title' => 'দৈনন্দিন সুন্নাহ ও আদব (লাইভ কুইজ)',
            'description' => 'এই লাইভ কুইজটি হালাকাহর পর সরাসরি অনুষ্ঠিত হবে। দ্রুত উত্তর দিয়ে লিডারবোর্ডে নিজের অবস্থান নিশ্চিত করুন!',
            'mode' => 'live',
            'status' => 'published',
            'time_limit_minutes' => 5,
            'shuffle_questions' => false,
            'negative_marking' => false,
            'negative_mark_value' => 0.0,
            'points_on_pass' => 100,
            'bonus_points_for_rank' => ['1' => 50, '2' => 30, '3' => 15],
            'available_from' => now()->subDays(2),
        ]);

        $q2_1 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'mcq',
            'question_text' => 'খাওয়ার পূর্বে কোন দোয়া পড়তে হয়?',
            'ai_explanation' => 'খাওয়ার পূর্বে বিসমিল্লাহ বলতে হয়।',
            'marks' => 2,
            'order' => 1,
        ]);
        $opt2_1_1 = QuizOption::create(['question_id' => $q2_1->id, 'option_text' => 'আলহামদুলিল্লাহ', 'is_correct' => false]);
        $opt2_1_2 = QuizOption::create(['question_id' => $q2_1->id, 'option_text' => 'বিসমিল্লাহ', 'is_correct' => true]);
        $opt2_1_3 = QuizOption::create(['question_id' => $q2_1->id, 'option_text' => 'আল্লাহু আকবার', 'is_correct' => false]);

        $q2_2 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'true_false',
            'question_text' => 'ডান হাত দিয়ে খাওয়া একটি সুন্নাহ।',
            'ai_explanation' => 'রাসূল (সাঃ) সবসময় ডান হাতে খাওয়ার নির্দেশ দিয়েছেন।',
            'marks' => 2,
            'order' => 2,
        ]);
        $opt2_2_1 = QuizOption::create(['question_id' => $q2_2->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        $opt2_2_2 = QuizOption::create(['question_id' => $q2_2->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        $q2_3 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'mcq',
            'question_text' => 'ঘুমানোর সুন্নাহ পদ্ধতি কোনটি?',
            'ai_explanation' => 'ডান কাতে ঘুমানো রাসূলুল্লাহ (সাঃ)-এর সুন্নাহ।',
            'marks' => 2,
            'order' => 3,
        ]);
        $opt2_3_1 = QuizOption::create(['question_id' => $q2_3->id, 'option_text' => 'বাম কাতে শোয়া', 'is_correct' => false]);
        $opt2_3_2 = QuizOption::create(['question_id' => $q2_3->id, 'option_text' => 'উপুড় হয়ে শোয়া', 'is_correct' => false]);
        $opt2_3_3 = QuizOption::create(['question_id' => $q2_3->id, 'option_text' => 'ডান কাতে শোয়া', 'is_correct' => true]);

        $q2_4 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'true_false',
            'question_text' => 'হাঁচি দেওয়ার পর "আলহামদুলিল্লাহ" বলা সুন্নাহ।',
            'ai_explanation' => 'হাঁচি দেওয়ার পর আলহামদুলিল্লাহ এবং শুনলে ইয়ারহামুকাল্লাহ বলতে হয়।',
            'marks' => 2,
            'order' => 4,
        ]);
        $opt2_4_1 = QuizOption::create(['question_id' => $q2_4->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        $opt2_4_2 = QuizOption::create(['question_id' => $q2_4->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);


        // =========================================================================
        // QUIZ 3: কুরআন ও তাফসীর কুইজ (সুরা আল-কাহফ) - Async
        // =========================================================================
        $quiz3 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => $createdBy,
            'title' => 'কুরআন ও তাফসীর কুইজ (সুরা আল-কাহফ)',
            'description' => 'সুরা আল-কাহাফের তাফসীর ও পাঠসমূহ সম্পর্কে আপনার সাধারণ জ্ঞান যাচাই করুন।',
            'mode' => 'async',
            'status' => 'published',
            'time_limit_minutes' => 15,
            'shuffle_questions' => true,
            'negative_marking' => false,
            'negative_mark_value' => 0.00,
            'points_on_pass' => 60,
            'bonus_points_for_rank' => ['1' => 40, '2' => 25, '3' => 15],
            'available_from' => now()->subDays(1),
        ]);

        $q3_1 = QuizQuestion::create([
            'quiz_id' => $quiz3->id,
            'type' => 'mcq',
            'question_text' => 'গুহাবাসী যুবকরা কত বছর গুহায় ঘুমিয়েছিলেন?',
            'ai_explanation' => 'সুরা কাহাফ অনুসারে গুহাবাসীরা ৩০৯ বছর গুহায় ঘুমন্ত অবস্থায় ছিলেন।',
            'marks' => 2,
            'order' => 1,
        ]);
        $opt3_1_1 = QuizOption::create(['question_id' => $q3_1->id, 'option_text' => '১০০ বছর', 'is_correct' => false]);
        $opt3_1_2 = QuizOption::create(['question_id' => $q3_1->id, 'option_text' => '২০০ বছর', 'is_correct' => false]);
        $opt3_1_3 = QuizOption::create(['question_id' => $q3_1->id, 'option_text' => '৩০৯ বছর', 'is_correct' => true]);
        $opt3_1_4 = QuizOption::create(['question_id' => $q3_1->id, 'option_text' => '৪০০ বছর', 'is_correct' => false]);

        $q3_2 = QuizQuestion::create([
            'quiz_id' => $quiz3->id,
            'type' => 'mcq',
            'question_text' => 'সুরা আল-কাহাফে মোট কয়টি শিক্ষণীয় গল্প বা কাহিনী বর্ণিত রয়েছে?',
            'ai_explanation' => 'সুরা আল-কাহাফে ৪টি গল্প আছে: আশহাবে কাহাফ, দুই বাগানের মালিক, মুসা ও খিজির, এবং জুলকারনাইন।',
            'marks' => 2,
            'order' => 2,
        ]);
        $opt3_2_1 = QuizOption::create(['question_id' => $q3_2->id, 'option_text' => '২টি', 'is_correct' => false]);
        $opt3_2_2 = QuizOption::create(['question_id' => $q3_2->id, 'option_text' => '৩টি', 'is_correct' => false]);
        $opt3_2_3 = QuizOption::create(['question_id' => $q3_2->id, 'option_text' => '৪টি', 'is_correct' => true]);

        $q3_3 = QuizQuestion::create([
            'quiz_id' => $quiz3->id,
            'type' => 'mcq',
            'question_text' => 'মুসা (আঃ) ও খিজির (আঃ)-এর প্রথম মোলাকাত বা সাক্ষাৎ কোন স্থানে হয়েছিল?',
            'ai_explanation' => 'তাদের সাক্ষাৎ ঘটেছিল দুই সমুদ্রের মিলনস্থলে (মাজমাউল বাহরাইন)।',
            'marks' => 2,
            'order' => 3,
        ]);
        $opt3_3_1 = QuizOption::create(['question_id' => $q3_3->id, 'option_text' => 'তূর পাহাড়ে', 'is_correct' => false]);
        $opt3_3_2 = QuizOption::create(['question_id' => $q3_3->id, 'option_text' => 'দুই সমুদ্রের সংযোগস্থলে', 'is_correct' => true]);
        $opt3_3_3 = QuizOption::create(['question_id' => $q3_3->id, 'option_text' => 'মিশর সীমান্তে', 'is_correct' => false]);

        $q3_4 = QuizQuestion::create([
            'quiz_id' => $quiz3->id,
            'type' => 'true_false',
            'question_text' => 'প্রতি জুমাবারে সুরা আল-কাহাফ তেলাওয়াত করা একটি সুন্নাত আমল।',
            'ai_explanation' => 'হাদীস শরীফে জুমার দিনে সুরা কাহাফ পাঠের বিশেষ ফযীলত বর্ণিত হয়েছে।',
            'marks' => 2,
            'order' => 4,
        ]);
        $opt3_4_1 = QuizOption::create(['question_id' => $q3_4->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        $opt3_4_2 = QuizOption::create(['question_id' => $q3_4->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        $q3_5 = QuizQuestion::create([
            'quiz_id' => $quiz3->id,
            'type' => 'short_text',
            'question_text' => 'সুরা আল-কাহাফ কুরআনের কততম সুরা? সংখ্যায় লিখুন।',
            'ideal_answer' => '১৮',
            'ai_explanation' => 'সুরা আল-কাহাফ পবিত্র কুরআনের ১৮ নম্বর সুরা।',
            'marks' => 2,
            'order' => 5,
        ]);


        // =========================================================================
        // QUIZ 4: পবিত্রতা ও সালাত (লাইভ কুইজ) - Live
        // =========================================================================
        $quiz4 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => $createdBy,
            'title' => 'পবিত্রতা ও সালাত (লাইভ কুইজ)',
            'description' => 'সালাত ও তাহারাত (পবিত্রতা) বিষয়ক বুনিয়াদি প্রশ্নের লাইভ কুইজ। দ্রুত সঠিক উত্তর নির্বাচন করুন।',
            'mode' => 'live',
            'status' => 'published',
            'time_limit_minutes' => 6,
            'shuffle_questions' => false,
            'negative_marking' => false,
            'negative_mark_value' => 0.00,
            'points_on_pass' => 80,
            'bonus_points_for_rank' => ['1' => 40, '2' => 25, '3' => 15],
            'available_from' => now(),
        ]);

        $q4_1 = QuizQuestion::create([
            'quiz_id' => $quiz4->id,
            'type' => 'mcq',
            'question_text' => 'ওযুর ফরয কাজ কয়টি?',
            'ai_explanation' => 'ওযুর ফরয কাজ ৪টি: মুখমণ্ডল ধোয়া, উভয় হাত কনুইসহ ধোয়া, মাথার এক চতুর্থাংশ মাসেহ করা এবং উভয় পা টাখনুসহ ধোয়া।',
            'marks' => 2,
            'order' => 1,
        ]);
        $opt4_1_1 = QuizOption::create(['question_id' => $q4_1->id, 'option_text' => '৩টি', 'is_correct' => false]);
        $opt4_1_2 = QuizOption::create(['question_id' => $q4_1->id, 'option_text' => '৪টি', 'is_correct' => true]);
        $opt4_1_3 = QuizOption::create(['question_id' => $q4_1->id, 'option_text' => '৫টি', 'is_correct' => false]);

        $q4_2 = QuizQuestion::create([
            'quiz_id' => $quiz4->id,
            'type' => 'mcq',
            'question_text' => 'সালাতে রুকু থেকে সোজা হয়ে দাঁড়ানোকে পরিভাষায় কী বলা হয়?',
            'ai_explanation' => 'রুকু থেকে দাঁড়ানোকে কওমা এবং দুই সেজদার মাঝে বসাকে জলসা বলা হয়।',
            'marks' => 2,
            'order' => 2,
        ]);
        $opt4_2_1 = QuizOption::create(['question_id' => $q4_2->id, 'option_text' => 'কওমা', 'is_correct' => true]);
        $opt4_2_2 = QuizOption::create(['question_id' => $q4_2->id, 'option_text' => 'জলসা', 'is_correct' => false]);
        $opt4_2_3 = QuizOption::create(['question_id' => $q4_2->id, 'option_text' => 'তাশাহহুদ', 'is_correct' => false]);

        $q4_3 = QuizQuestion::create([
            'quiz_id' => $quiz4->id,
            'type' => 'true_false',
            'question_text' => 'ওযুর শুরুতে বিসমিল্লাহ বলা একটি সুন্নাহ আমল।',
            'ai_explanation' => 'ওযুর পূর্বে বিসমিল্লাহির রহমানির রাহীম বলা সুন্নাহ।',
            'marks' => 2,
            'order' => 3,
        ]);
        $opt4_3_1 = QuizOption::create(['question_id' => $q4_3->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        $opt4_3_2 = QuizOption::create(['question_id' => $q4_3->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        $q4_4 = QuizQuestion::create([
            'quiz_id' => $quiz4->id,
            'type' => 'short_text',
            'question_text' => 'দৈনিক পাঁচ ওয়াক্ত ফরয সালাতে মোট কত রাকআত ফরয সালাত পড়তে হয়? সংখ্যায় লিখুন।',
            'ideal_answer' => '১৭',
            'ai_explanation' => 'ফজর ২, যোহর ৪, আসর ৪, মাগরিব ৩ এবং এশা ৪; সর্বমোট ১৭ রাকআত ফরয সালাত পড়তে হয়।',
            'marks' => 2,
            'order' => 4,
        ]);


        // =========================================================================
        // SEEDING PARTICIPANTS & ATTEMPTS & ANSWERS
        // =========================================================================

        // Let's seed for Quiz 1 (16 participants)
        $p1 = $students->random(min(16, $students->count()));
        $attempts1 = [];
        foreach ($p1 as $student) {
            $started = Carbon::now()->subHours(rand(12, 48));
            $duration = rand(100, 450);
            $submitted = (clone $started)->addSeconds($duration);

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz1->id,
                'user_id' => $student->id,
                'started_at' => $started,
                'submitted_at' => $submitted,
                'status' => 'submitted',
                'time_taken_seconds' => $duration,
                'question_order' => [1, 2, 3, 4, 5],
            ]);

            $totalMarks = 0;

            // Q1_1
            $isCorrect = rand(1, 10) <= 7;
            $sel = $isCorrect ? $opt1_1_3->id : collect([$opt1_1_1->id, $opt1_1_2->id, $opt1_1_4->id])->random();
            $marks = $isCorrect ? 2.00 : -0.25;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q1_1->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(5, 30)),
            ]);
            $totalMarks += $marks;

            // Q1_2
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? [$opt1_2_1->id, $opt1_2_3->id] : (rand(0, 1) ? [$opt1_2_1->id] : [$opt1_2_2->id, $opt1_2_4->id]);
            $isCorr = $sel === [$opt1_2_1->id, $opt1_2_3->id];
            $marks = $isCorr ? 4.00 : (count(array_intersect($sel, [$opt1_2_1->id, $opt1_2_3->id])) > 0 ? 1.00 : 0.00);
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q1_2->id,
                'selected_option_ids' => $sel, 'is_correct' => $isCorr,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(35, 80)),
            ]);
            $totalMarks += $marks;

            // Q1_3
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt1_3_1->id : $opt1_3_2->id;
            $marks = $isCorrect ? 2.00 : -0.25;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q1_3->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(85, 115)),
            ]);
            $totalMarks += $marks;

            // Q1_4
            $ansChoice = rand(1, 10);
            $text = $ansChoice <= 8 ? '১১৪' : ($ansChoice <= 9 ? '114' : '১১২');
            $isCorrect = in_array($text, ['১১৪', '114']);
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q1_4->id,
                'text_answer' => $text, 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(120, 160)),
            ]);
            $totalMarks += $marks;

            // Q1_5
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? $opt1_5_2->id : collect([$opt1_5_1->id, $opt1_5_3->id])->random();
            $marks = $isCorrect ? 2.00 : -0.25;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q1_5->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(165, 200)),
            ]);
            $totalMarks += $marks;

            $maxPossible = 12.00; // 2+4+2+2+2
            $percentage = ($totalMarks / $maxPossible) * 100;
            $passed = $percentage >= 50.00;
            $points = $passed ? $quiz1->points_on_pass : 0;

            $attempt->update([
                'score_raw' => $totalMarks, 'score_percentage' => $percentage,
                'passed' => $passed, 'points_awarded' => $points,
            ]);
            $attempts1[] = $attempt;
        }

        // Rank Quiz 1
        usort($attempts1, function ($a, $b) {
            if ($a->score_raw == $b->score_raw) { return $a->time_taken_seconds <=> $b->time_taken_seconds; }
            return $b->score_raw <=> $a->score_raw;
        });
        foreach ($attempts1 as $index => $att) {
            $rank = $index + 1;
            $bonus = 0;
            if ($rank == 1) $bonus = 30;
            elseif ($rank == 2) $bonus = 20;
            elseif ($rank == 3) $bonus = 10;
            $att->update(['rank' => $rank, 'points_awarded' => $att->points_awarded + $bonus]);
            if ($att->user) { $att->user->increment('gamification_points', $att->points_awarded); }
        }


        // Let's seed for Quiz 2 (12 participants)
        $p2 = $students->random(min(12, $students->count()));
        $attempts2 = [];
        foreach ($p2 as $student) {
            $started = Carbon::now()->subHours(8, 24);
            $duration = rand(60, 240);
            $submitted = (clone $started)->addSeconds($duration);

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz2->id, 'user_id' => $student->id,
                'started_at' => $started, 'submitted_at' => $submitted,
                'status' => 'submitted', 'time_taken_seconds' => $duration,
                'question_order' => [1, 2, 3, 4],
            ]);

            $totalMarks = 0;

            // Q2_1
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? $opt2_1_2->id : collect([$opt2_1_1->id, $opt2_1_3->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q2_1->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(5, 20)),
            ]);
            $totalMarks += $marks;

            // Q2_2
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt2_2_1->id : $opt2_2_2->id;
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q2_2->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(25, 45)),
            ]);
            $totalMarks += $marks;

            // Q2_3
            $isCorrect = rand(1, 10) <= 7;
            $sel = $isCorrect ? $opt2_3_3->id : collect([$opt2_3_1->id, $opt2_3_2->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q2_3->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(50, 75)),
            ]);
            $totalMarks += $marks;

            // Q2_4
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? $opt2_4_1->id : $opt2_4_2->id;
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q2_4->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(80, 110)),
            ]);
            $totalMarks += $marks;

            $maxPossible = 8.00;
            $percentage = ($totalMarks / $maxPossible) * 100;
            $passed = $percentage >= 50.00;
            $points = $passed ? $quiz2->points_on_pass : 0;

            $attempt->update([
                'score_raw' => $totalMarks, 'score_percentage' => $percentage,
                'passed' => $passed, 'points_awarded' => $points,
            ]);
            $attempts2[] = $attempt;
        }

        // Rank Quiz 2
        usort($attempts2, function ($a, $b) {
            if ($a->score_raw == $b->score_raw) { return $a->time_taken_seconds <=> $b->time_taken_seconds; }
            return $b->score_raw <=> $a->score_raw;
        });
        foreach ($attempts2 as $index => $att) {
            $rank = $index + 1;
            $bonus = 0;
            if ($rank == 1) $bonus = 50;
            elseif ($rank == 2) $bonus = 30;
            elseif ($rank == 3) $bonus = 15;
            $att->update(['rank' => $rank, 'points_awarded' => $att->points_awarded + $bonus]);
            if ($att->user) { $att->user->increment('gamification_points', $att->points_awarded); }
        }


        // Let's seed for Quiz 3 (14 participants)
        $p3 = $students->random(min(14, $students->count()));
        $attempts3 = [];
        foreach ($p3 as $student) {
            $started = Carbon::now()->subHours(2, 10);
            $duration = rand(120, 500);
            $submitted = (clone $started)->addSeconds($duration);

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz3->id, 'user_id' => $student->id,
                'started_at' => $started, 'submitted_at' => $submitted,
                'status' => 'submitted', 'time_taken_seconds' => $duration,
                'question_order' => [1, 2, 3, 4, 5],
            ]);

            $totalMarks = 0;

            // Q3_1
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? $opt3_1_3->id : collect([$opt3_1_1->id, $opt3_1_2->id, $opt3_1_4->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q3_1->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(5, 30)),
            ]);
            $totalMarks += $marks;

            // Q3_2
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt3_2_3->id : collect([$opt3_2_1->id, $opt3_2_2->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q3_2->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(35, 60)),
            ]);
            $totalMarks += $marks;

            // Q3_3
            $isCorrect = rand(1, 10) <= 7;
            $sel = $isCorrect ? $opt3_3_2->id : collect([$opt3_3_1->id, $opt3_3_3->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q3_3->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(65, 90)),
            ]);
            $totalMarks += $marks;

            // Q3_4
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt3_4_1->id : $opt3_4_2->id;
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q3_4->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(95, 120)),
            ]);
            $totalMarks += $marks;

            // Q3_5
            $ansChoice = rand(1, 10);
            $text = $ansChoice <= 8 ? '১৮' : ($ansChoice <= 9 ? '18' : '১৫');
            $isCorrect = in_array($text, ['১৮', '18']);
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q3_5->id,
                'text_answer' => $text, 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(125, 160)),
            ]);
            $totalMarks += $marks;

            $maxPossible = 10.00;
            $percentage = ($totalMarks / $maxPossible) * 100;
            $passed = $percentage >= 50.00;
            $points = $passed ? $quiz3->points_on_pass : 0;

            $attempt->update([
                'score_raw' => $totalMarks, 'score_percentage' => $percentage,
                'passed' => $passed, 'points_awarded' => $points,
            ]);
            $attempts3[] = $attempt;
        }

        // Rank Quiz 3
        usort($attempts3, function ($a, $b) {
            if ($a->score_raw == $b->score_raw) { return $a->time_taken_seconds <=> $b->time_taken_seconds; }
            return $b->score_raw <=> $a->score_raw;
        });
        foreach ($attempts3 as $index => $att) {
            $rank = $index + 1;
            $bonus = 0;
            if ($rank == 1) $bonus = 40;
            elseif ($rank == 2) $bonus = 25;
            elseif ($rank == 3) $bonus = 15;
            $att->update(['rank' => $rank, 'points_awarded' => $att->points_awarded + $bonus]);
            if ($att->user) { $att->user->increment('gamification_points', $att->points_awarded); }
        }


        // Let's seed for Quiz 4 (10 participants)
        $p4 = $students->random(min(10, $students->count()));
        $attempts4 = [];
        foreach ($p4 as $student) {
            $started = Carbon::now()->subMinutes(rand(5, 55));
            $duration = rand(45, 180);
            $submitted = (clone $started)->addSeconds($duration);

            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz4->id, 'user_id' => $student->id,
                'started_at' => $started, 'submitted_at' => $submitted,
                'status' => 'submitted', 'time_taken_seconds' => $duration,
                'question_order' => [1, 2, 3, 4],
            ]);

            $totalMarks = 0;

            // Q4_1
            $isCorrect = rand(1, 10) <= 8;
            $sel = $isCorrect ? $opt4_1_2->id : collect([$opt4_1_1->id, $opt4_1_3->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q4_1->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(5, 15)),
            ]);
            $totalMarks += $marks;

            // Q4_2
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt4_2_1->id : collect([$opt4_2_2->id, $opt4_2_3->id])->random();
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q4_2->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(16, 30)),
            ]);
            $totalMarks += $marks;

            // Q4_3
            $isCorrect = rand(1, 10) <= 9;
            $sel = $isCorrect ? $opt4_3_1->id : $opt4_3_2->id;
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q4_3->id,
                'selected_option_ids' => [$sel], 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(31, 45)),
            ]);
            $totalMarks += $marks;

            // Q4_4
            $ansChoice = rand(1, 10);
            $text = $ansChoice <= 8 ? '১৭' : ($ansChoice <= 9 ? '17' : '১২');
            $isCorrect = in_array($text, ['১৭', '17']);
            $marks = $isCorrect ? 2.00 : 0.00;
            QuizAnswer::create([
                'attempt_id' => $attempt->id, 'question_id' => $q4_4->id,
                'text_answer' => $text, 'is_correct' => $isCorrect,
                'marks_awarded' => $marks, 'answered_at' => (clone $started)->addSeconds(rand(46, 70)),
            ]);
            $totalMarks += $marks;

            $maxPossible = 8.00;
            $percentage = ($totalMarks / $maxPossible) * 100;
            $passed = $percentage >= 50.00;
            $points = $passed ? $quiz4->points_on_pass : 0;

            $attempt->update([
                'score_raw' => $totalMarks, 'score_percentage' => $percentage,
                'passed' => $passed, 'points_awarded' => $points,
            ]);
            $attempts4[] = $attempt;
        }

        // Rank Quiz 4
        usort($attempts4, function ($a, $b) {
            if ($a->score_raw == $b->score_raw) { return $a->time_taken_seconds <=> $b->time_taken_seconds; }
            return $b->score_raw <=> $a->score_raw;
        });
        foreach ($attempts4 as $index => $att) {
            $rank = $index + 1;
            $bonus = 0;
            if ($rank == 1) $bonus = 40;
            elseif ($rank == 2) $bonus = 25;
            elseif ($rank == 3) $bonus = 15;
            $att->update(['rank' => $rank, 'points_awarded' => $att->points_awarded + $bonus]);
            if ($att->user) { $att->user->increment('gamification_points', $att->points_awarded); }
        }

        $this->command->info('Quiz system seeded successfully with 4 quizzes, detailed questions, extensive attempts and ranks!');
    }
}
