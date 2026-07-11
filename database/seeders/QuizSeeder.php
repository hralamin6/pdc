<?php

namespace Database\Seeders;

use App\Models\Halaqah;
use App\Models\Quiz;
use App\Models\QuizOption;
use App\Models\QuizQuestion;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $halaqah = Halaqah::first();
        if (! $halaqah) {
            $this->command->warn('No halaqah found to attach quizzes. Skipping.');

            return;
        }

        // Quiz 1: General Knowledge (Async)
        $quiz1 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => 1, // assume user 1 exists
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
            'available_from' => now()->subDay(),
        ]);

        // Q1: Single Select
        $q1 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'mcq',
            'question_text' => 'হিজরি সাল গণনা শুরু হয় কবে থেকে?',
            'ai_explanation' => 'হিজরি সাল মহানবী (সাঃ)-এর মক্কা থেকে মদিনায় হিজরতের বছর (৬২২ খ্রিস্টাব্দ) থেকে গণনা শুরু হয়।',
            'marks' => 2,
            'order' => 1,
        ]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'রাসূল (সাঃ) এর জন্মের বছর থেকে', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'ওহী নাযিলের সময় থেকে', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'মক্কা থেকে মদিনায় হিজরতের বছর থেকে', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q1->id, 'option_text' => 'মক্কা বিজয়ের বছর থেকে', 'is_correct' => false]);

        // Q2: Multi Select
        $q2 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'multi_select',
            'question_text' => 'নিচের কোন সাহাবীদেরকে "খুলাফায়ে রাশেদীন" বলা হয়? (একাধিক উত্তর হতে পারে)',
            'ai_explanation' => 'খুলাফায়ে রাশেদীন ছিলেন আবু বকর (রাঃ), উমর (রাঃ), উসমান (রাঃ) এবং আলী (রাঃ)।',
            'marks' => 4,
            'order' => 2,
        ]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'আবু বকর (রাঃ)', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'খালিদ বিন ওয়ালিদ (রাঃ)', 'is_correct' => false]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'উমর (রাঃ)', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q2->id, 'option_text' => 'আবু হুরায়রা (রাঃ)', 'is_correct' => false]);

        // Q3: True/False
        $q3 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'true_false',
            'question_text' => 'বদরের যুদ্ধ ২য় হিজরিতে সংঘটিত হয়েছিল।',
            'ai_explanation' => 'বদরের যুদ্ধ ১৭ রমজান, ২ হিজরিতে (৬২৪ খ্রিস্টাব্দ) সংঘটিত হয়।',
            'marks' => 2,
            'order' => 3,
        ]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        QuizOption::create(['question_id' => $q3->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        // Q4: Short Text
        $q4 = QuizQuestion::create([
            'quiz_id' => $quiz1->id,
            'type' => 'short_text',
            'question_text' => 'কুরআনে কতগুলো সূরা রয়েছে? শুধুমাত্র সংখ্যায় লিখুন।',
            'ai_explanation' => 'কুরআন মাজিদে মোট ১১৪ টি সূরা রয়েছে।',
            'marks' => 2,
            'order' => 4,
        ]);
        // Short text doesn't need options, the AI/Admin grades it.

        // Quiz 2: Live Quiz
        $quiz2 = Quiz::create([
            'quizzable_type' => Halaqah::class,
            'quizzable_id' => $halaqah->id,
            'created_by' => 1,
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
            'available_from' => now(),
        ]);

        // Q1
        $l1 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'mcq',
            'question_text' => 'খাওয়ার পূর্বে কোন দোয়া পড়তে হয়?',
            'ai_explanation' => 'খাওয়ার পূর্বে বিসমিল্লাহ বলতে হয়।',
            'marks' => 2,
            'order' => 1,
        ]);
        QuizOption::create(['question_id' => $l1->id, 'option_text' => 'আলহামদুলিল্লাহ', 'is_correct' => false]);
        QuizOption::create(['question_id' => $l1->id, 'option_text' => 'বিসমিল্লাহ', 'is_correct' => true]);
        QuizOption::create(['question_id' => $l1->id, 'option_text' => 'আল্লাহু আকবার', 'is_correct' => false]);

        // Q2
        $l2 = QuizQuestion::create([
            'quiz_id' => $quiz2->id,
            'type' => 'true_false',
            'question_text' => 'ডান হাত দিয়ে খাওয়া একটি সুন্নাহ।',
            'ai_explanation' => 'রাসূল (সাঃ) সবসময় ডান হাতে খাওয়ার নির্দেশ দিয়েছেন।',
            'marks' => 2,
            'order' => 2,
        ]);
        QuizOption::create(['question_id' => $l2->id, 'option_text' => 'সত্য', 'is_correct' => true]);
        QuizOption::create(['question_id' => $l2->id, 'option_text' => 'মিথ্যা', 'is_correct' => false]);

        $this->command->info('Quiz system seeded successfully with Bangla questions!');
    }
}
