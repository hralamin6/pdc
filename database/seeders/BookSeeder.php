<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookUserInteraction;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::role(['super-admin', 'admin', 'mentor'])->first() ?? User::first();
        $users = User::role('user')->get();

        if ($users->isEmpty()) {
            $users = User::all();
        }

        if (! $adminUser || $users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // 1. Categories (Bangla)
        $categories = [
            ['name' => 'তাফসীর ও কুরআন শিক্ষা', 'slug' => 'tafseer-quran-studies', 'icon' => 'o-book-open'],
            ['name' => 'হাদিস ও সুন্নাহ', 'slug' => 'hadith-sunnah', 'icon' => 'o-chat-bubble-bottom-center-text'],
            ['name' => 'সীরাত ও ইতিহাস', 'slug' => 'seerah-history', 'icon' => 'o-user'],
            ['name' => 'ফিকহ ও মাসয়ালা-মাসায়েল', 'slug' => 'fiqh-jurisprudence', 'icon' => 'o-scale'],
            ['name' => 'আকীদা ও বিশ্বাস', 'slug' => 'aqeedah-theology', 'icon' => 'o-heart'],
            ['name' => 'आत्मশুদ্ধি ও দাওয়াহ', 'slug' => 'self-purification-dawah', 'icon' => 'o-sparkles'],
        ];

        foreach ($categories as $cat) {
            BookCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }

        // 2. Authors (Bangla)
        $authors = [
            ['name' => 'ইমাম ইবনে কাছীর (র.)', 'slug' => 'imam-ibn-kathir', 'bio' => 'বিখ্যাত মুফাসসির, ইতিহাসবিদ ও পণ্ডিত। তাঁর রচিত তাফসীর গ্রন্থটি বিশ্বজুড়ে সমাদৃত।'],
            ['name' => 'ইমাম আন-নওবী (র.)', 'slug' => 'imam-an-nawawi', 'bio' => 'বিখ্যাত হাদীস বিশারদ ও ফকীহ। রিয়াদুস সালেহীন এবং চল্লিশ হাদীস তাঁর অমর কীর্তি।'],
            ['name' => 'সফিউর রহমান মুবারকপুরী', 'slug' => 'safiur-rahman-mubarakpuri', 'bio' => 'বিশ্ববিখ্যাত সীরাত গ্রন্থ "আর-রাহীকুল মাখতূম"-এর লেখক, যিনি রাবেতা আল-আলম আল-ইসলামী আয়োজিত সীরাত প্রতিযোগিতায় প্রথম স্থান লাভ করেন।'],
            ['name' => 'ইমাম আল-গাজালী (র.)', 'slug' => 'imam-al-ghazali', 'bio' => 'হুজ্জাতুল ইসলাম নামে পরিচিত মহান দার্শনিক, ধর্মতত্ত্ববিদ ও আধ্যাত্মিক সাধক। এহয়াউ উলুমিদ্দীন তাঁর অন্যতম শ্রেষ্ঠ রচনা।'],
            ['name' => 'ড. খোন্দকার আব্দুল্লাহ জাহাঙ্গীর', 'slug' => 'dr-khandaker-abdullah-jahangir', 'bio' => 'বাংলাদেশের অন্যতম কৃতি হাদীস বিশারদ, শিক্ষাবিদ ও লেখক। তিনি বিশুদ্ধ আকীদা ও সুন্নাহ প্রচারের জন্য জীবন উৎসর্গ করেছিলেন।'],
            ['name' => 'আরিফ আজাদ', 'slug' => 'arif-azad', 'bio' => 'জনপ্রিয় সমসাময়িক মুসলিম লেখক। তাঁর বইগুলো তরুণ সমাজকে দ্বীনের পথে অনুপ্রাণিত করতে বিশেষ ভূমিকা রাখছে।'],
        ];

        foreach ($authors as $author) {
            Author::firstOrCreate(
                ['slug' => $author['slug']],
                ['name' => $author['name'], 'bio' => $author['bio']]
            );
        }

        // 3. Publications (Bangla)
        $publications = [
            ['name' => 'সমকালীন প্রকাশন', 'slug' => 'somokal-prokashon', 'description' => 'আধুনিক দৃষ্টিভঙ্গি ও মানসম্মত মননশীল ইসলামিক বই প্রকাশের অন্যতম অগ্রগামী প্রকাশনী।'],
            ['name' => 'গার্ডিয়ান পাবলিকেশনস', 'slug' => 'guardian-publications', 'description' => 'তরুণদের উপযোগী গবেষণাধর্মী ও সমাজ সংস্কারমূলক বই প্রকাশের বিশ্বস্ত নাম।'],
            ['name' => 'রুহামা পাবলিকেশন', 'slug' => 'ruhama-publication', 'description' => 'শুদ্ধ চিন্তা ও তাত্ত্বিক ইসলামিক বইয়ের নির্ভরযোগ্য পরিবেশক ও প্রকাশক।'],
            ['name' => 'দারুসসালাম বাংলাদেশ', 'slug' => 'darussalam-bangladesh', 'description' => 'বিশ্ববিখ্যাত দারুসসালাম পাবলিকেশনসের অনুমোদিত বাংলা সংস্করণ প্রকাশক।'],
            ['name' => 'তাওহীদ পাবলিকেশন্স', 'slug' => 'tawheed-publications', 'description' => 'সহীহ আকীদা ও হাদীসের বিশুদ্ধ অনুবাদ প্রকাশের জন্য সুপরিচিত।'],
        ];

        foreach ($publications as $pub) {
            Publication::firstOrCreate(
                ['slug' => $pub['slug']],
                ['name' => $pub['name'], 'description' => $pub['description']]
            );
        }

        // Get model records
        $cats = BookCategory::all();
        $auths = Author::all();
        $pubs = Publication::all();

        // 4. Books (Bangla & Detailed specs)
        $booksData = [
            [
                'title' => 'আর-রাহীকুল মাখতূম (মহিমান্বিত সীরাত)',
                'slug' => 'ar-raheeq-al-makhtum',
                'type' => 'physical',
                'description' => 'রাসূলুল্লাহ (সাঃ)-এর সীরাত বিষয়ক বিশ্ববিখ্যাত গ্রন্থ। এটি রাসূল (সাঃ)-এর মক্কী ও মাদানী জীবনের সবচেয়ে প্রামাণ্য ইতিহাস।',
                'status' => 'approved',
                'pages_count' => 620,
                'isbn' => '978-984-91321-1-6',
                'publication_year' => '2015',
                'cat_slug' => 'seerah-history',
                'auth_slug' => 'safiur-rahman-mubarakpuri',
                'pub_slug' => 'darussalam-bangladesh',
            ],
            [
                'title' => 'তাফসীর ইবনে কাছীর (১ম খণ্ড)',
                'slug' => 'tafsir-ibn-kathir-vol1',
                'type' => 'physical',
                'description' => 'কুরআনের শ্রেষ্ঠ তাফসীর গ্রন্থ। কুরআনের আয়াত দ্বারা কুরআনের ব্যাখ্যা ও হাদীসের সমন্বয়ে রচিত।',
                'status' => 'approved',
                'pages_count' => 850,
                'isbn' => '978-984-8932-10-2',
                'publication_year' => '2012',
                'cat_slug' => 'tafseer-quran-studies',
                'auth_slug' => 'imam-ibn-kathir',
                'pub_slug' => 'darussalam-bangladesh',
            ],
            [
                'title' => 'রিয়াদুস সালেহীন (পূর্ণাঙ্গ)',
                'slug' => 'riyadus-saliheen-complete',
                'type' => 'physical',
                'description' => 'ইমাম নওবী সংকলিত দৈনন্দিন জীবনের উপযোগী সহীহ হাদীসের অসাধারণ ভাণ্ডার।',
                'status' => 'approved',
                'pages_count' => 980,
                'isbn' => '978-984-92562-4-9',
                'publication_year' => '2018',
                'cat_slug' => 'hadith-sunnah',
                'auth_slug' => 'imam-an-nawawi',
                'pub_slug' => 'tawheed-publications',
            ],
            [
                'title' => 'প্যারাডক্সিক্যাল সাজিদ',
                'slug' => 'paradoxical-sajid-1',
                'type' => 'physical',
                'description' => 'নাস্তিকদের করা সাধারণ সংশয় ও যুক্তির বিপরীতে সাজিদ চরিত্রের সাবলীল বৈজ্ঞানিক ও তাত্ত্বিক খণ্ডন।',
                'status' => 'approved',
                'pages_count' => 176,
                'isbn' => '978-984-93108-0-4',
                'publication_year' => '2017',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'arif-azad',
                'pub_slug' => 'somokal-prokashon',
            ],
            [
                'title' => 'এহয়াউ উলুমিদ্দীন (১ম খণ্ড)',
                'slug' => 'ihya-ulum-al-din-vol1',
                'type' => 'physical',
                'description' => 'ইমাম গাজালীর কালজয়ী আধ্যাত্মিক ও আত্মশুদ্ধিমূলক গ্রন্থ। ধর্মীয় জ্ঞানের পুনরুজ্জীবনে এর জুড়ি মেলা ভার।',
                'status' => 'approved',
                'pages_count' => 520,
                'isbn' => '978-984-91204-5-8',
                'publication_year' => '2014',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'imam-al-ghazali',
                'pub_slug' => 'ruhama-publication',
            ],
            [
                'title' => 'রাহে বেলায়েত (রাসূলুল্লাহর (সাঃ) যিক্র-ওযীফা)',
                'slug' => 'rah-e-belayet',
                'type' => 'physical',
                'description' => 'ড. খোন্দকার আব্দুল্লাহ জাহাঙ্গীর রচিত সহীহ হাদীসের আলোকে যিকির, দোয়া ও মোনাজাতের বিশুদ্ধ নির্দেশিকা।',
                'status' => 'approved',
                'pages_count' => 430,
                'isbn' => '978-984-90057-0-1',
                'publication_year' => '2013',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'dr-khandaker-abdullah-jahangir',
                'pub_slug' => 'somokal-prokashon',
            ],
            [
                'title' => 'বেলা ফুরোবার আগে',
                'slug' => 'bela-furobar-age',
                'type' => 'physical',
                'description' => 'তরুণদের জীবনকে সুশৃঙ্খল ও আল্লাহর ভালোবাসায় রাঙিয়ে তোলার জন্য অত্যন্ত অনুপ্রেরণাদায়ক কিছু লেখার সংকলন।',
                'status' => 'approved',
                'pages_count' => 160,
                'isbn' => '978-984-95023-1-5',
                'publication_year' => '2020',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'arif-azad',
                'pub_slug' => 'guardian-publications',
            ],
            [
                'title' => 'হাদীসের নামে জালিয়াতি',
                'slug' => 'hadiser-name-jaliati',
                'type' => 'physical',
                'description' => 'সমাজে প্রচলিত জাল হাদীস, ভিত্তিহীন কথা এবং সেগুলোর উৎস সনাক্তকরণ নিয়ে ড. আব্দুল্লাহ জাহাঙ্গীরের অসাধারণ গবেষণামূলক বই।',
                'status' => 'approved',
                'pages_count' => 580,
                'isbn' => '978-984-90057-5-6',
                'publication_year' => '2011',
                'cat_slug' => 'hadith-sunnah',
                'auth_slug' => 'dr-khandaker-abdullah-jahangir',
                'pub_slug' => 'somokal-prokashon',
            ],
            [
                'title' => 'কুরআন মাজীদ (সহজ সরল বাংলা অনুবাদ)',
                'slug' => 'quran-simple-translation',
                'type' => 'ebook',
                'description' => 'কোনো জটিল ব্যাখ্যা ছাড়াই সাধারণ মানুষের সহজে বোঝার সুবিধার্থে সরল বাংলা অনুবাদের কুরআন।',
                'status' => 'approved',
                'pages_count' => 620,
                'isbn' => '978-984-8840-02-9',
                'publication_year' => '2021',
                'cat_slug' => 'tafseer-quran-studies',
                'auth_slug' => 'imam-ibn-kathir',
                'pub_slug' => 'tawheed-publications',
            ],
            [
                'title' => 'ডবল স্ট্যান্ডার্ড',
                'slug' => 'double-standard',
                'type' => 'physical',
                'description' => 'আমাদের সমাজের নানা দ্বিমুখী নীতি ও চিন্তার অসারতা তুলে ধরে সত্য পথের দিকনির্দেশনামূলক প্রবন্ধ।',
                'status' => 'pending',
                'pages_count' => 210,
                'isbn' => '978-984-94103-7-1',
                'publication_year' => '2019',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'arif-azad',
                'pub_slug' => 'guardian-publications',
            ],
            [
                'title' => 'ইসলামী আকীদা (বিশুদ্ধ বিশ্বাসের দিশারী)',
                'slug' => 'islami-aqeedah',
                'type' => 'physical',
                'description' => 'ইসলামের মৌলিক বিশ্বাসের খুঁটিনাটি ও সহীহ আকীদার বিস্তৃত ও প্রামাণ্য আলোচনা।',
                'status' => 'approved',
                'pages_count' => 320,
                'isbn' => '978-984-90057-8-7',
                'publication_year' => '2016',
                'cat_slug' => 'aqeedah-theology',
                'auth_slug' => 'dr-khandaker-abdullah-jahangir',
                'pub_slug' => 'somokal-prokashon',
            ],
            [
                'title' => 'মনন (ইসলাম ও মনস্তত্ত্ব)',
                'slug' => 'monon-islam-psychology',
                'type' => 'ebook',
                'description' => 'ডিপ্রেশন, এনজাইটি এবং মানসিক অস্থিরতা মোকাবেলায় ইসলামিক দৃষ্টিভঙ্গি ও আত্মশুদ্ধির মেলবন্ধন।',
                'status' => 'approved',
                'pages_count' => 180,
                'isbn' => '978-984-93452-9-5',
                'publication_year' => '2022',
                'cat_slug' => 'self-purification-dawah',
                'auth_slug' => 'arif-azad',
                'pub_slug' => 'somokal-prokashon',
            ],
        ];

        foreach ($booksData as $data) {
            $catId = $cats->where('slug', $data['cat_slug'])->first()?->id ?? $cats->random()->id;
            $authId = $auths->where('slug', $data['auth_slug'])->first()?->id ?? $auths->random()->id;
            $pubId = $pubs->where('slug', $data['pub_slug'])->first()?->id ?? $pubs->random()->id;

            Book::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'pages_count' => $data['pages_count'],
                    'isbn' => $data['isbn'],
                    'publication_year' => $data['publication_year'],
                    'uploaded_by' => $users->random()->id,
                    'book_category_id' => $catId,
                    'author_id' => $authId,
                    'publication_id' => $pubId,
                ]
            );
        }

        // 5. User Interactions (Ratings, reviews, reading status in Bangla)
        $allBooks = Book::where('status', 'approved')->get();
        $reviews = [
            'খুবই চমৎকার বই, প্রতিটি মুসলিমের অন্তত একবার হলেও পড়া উচিত। জীবন বদলে দেওয়ার মতো লেখা।',
            'বইটির বাঁধাই ও পৃষ্ঠার মান খুব ভালো। আর লেখকের লেখার সাবলীলতা তো অনন্য। জাজাকুমুল্লাহু খাইরান।',
            'নিজের মনের অনেকগুলো সংশয় দূর হয়ে গেল বইটি পড়ে। বিশেষভাবে তরুণদের জন্য অত্যন্ত দরকারি একটি বই।',
            'খুব সুন্দর ও সহজ ভাষায় ইসলামের মূল প্রজ্ঞাগুলো ব্যাখ্যা করা হয়েছে। পড়তে কোনো একঘেয়েমি লাগে না।',
            'আলহামদুলিল্লাহ! অনেক উপকৃত হয়েছি বইটি পড়ে। বিশেষ করে যিকির-আযকারের অংশটি খুবই উপকারী ছিল।',
            'অনেকদিন ধরে এমন একটি খাঁটি গবেষণাধর্মী বই খুঁজছিলাম। পড়ার পর মনে হলো এর প্রচার আরও বাড়ানো উচিত।',
        ];

        foreach ($allBooks as $book) {
            // Let 3-5 users interact with each book
            $selectedUsers = $users->random(rand(3, 5));
            foreach ($selectedUsers as $user) {
                $status = collect(['want_to_read', 'reading', 'completed'])->random();

                BookUserInteraction::updateOrCreate(
                    ['user_id' => $user->id, 'book_id' => $book->id],
                    [
                        'reading_status' => $status,
                        'rating' => rand(4, 5), // high ratings for these Islamic gems
                        'review' => rand(1, 10) <= 6 ? collect($reviews)->random() : null,
                        'pages_read' => $status === 'completed' ? $book->pages_count : rand(10, $book->pages_count - 10),
                    ]
                );
            }
        }

        $this->command->info('Book Library seeded successfully!');
    }
}
