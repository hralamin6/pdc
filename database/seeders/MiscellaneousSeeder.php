<?php

namespace Database\Seeders;

use App\Models\Feedback;
use App\Models\GalleryAlbum;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MiscellaneousSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Feedback (Nasiha)
        $feedbacks = [
            [
                'type' => 'advice',
                'message' => 'দ্বীনি কাজের পরিধি বাড়াতে পটুয়াখালীতে আরও বেশি করে অফলাইন সেমিনার বা হালাকাহর আয়োজন করা উচিত।',
                'is_read' => false,
            ],
            [
                'type' => 'suggestion',
                'message' => 'আমাদের মোবাইল অ্যাপ বা ওয়েবসাইটের ডার্ক মোড ফিচার যোগ করলে ভালো হতো। রাতের বেলা পড়তে সুবিধা হয়।',
                'is_read' => true,
            ],
            [
                'type' => 'complaint',
                'message' => 'লাইব্রেরির বই ফেরত দেওয়ার নোটিফিকেশন সিস্টেম মাঝে মাঝে একটু দেরিতে আসে, এটা ঠিক করা প্রয়োজন।',
                'is_read' => false,
            ],
            [
                'type' => 'advice',
                'message' => 'ছাত্রদের জন্য পড়াশোনার পাশাপাশি ক্যারিয়ার গাইডেন্স সেশন শুরু করা দরকার। হালাল উপার্জনের ব্যাপারে সচেতনতা বাড়ানো দরকার।',
                'is_read' => true,
            ],
            [
                'type' => 'suggestion',
                'message' => 'কুইজ প্রতিযোগিতায় বিজয়ীদের জন্য ছোটখাটো বই উপহার দেওয়ার ব্যবস্থা করা যায় কি? এতে ছাত্রদের আগ্রহ বাড়বে।',
                'is_read' => false,
            ],
            [
                'type' => 'advice',
                'message' => 'হালাকাহর অডিও রেকর্ড বা সংক্ষিপ্ত নোট ওয়েবসাইটে আপলোড করলে যারা সরাসরি উপস্থিত থাকতে পারেনি তারা উপকৃত হতো।',
                'is_read' => false,
            ],
            [
                'type' => 'complaint',
                'message' => 'কিছু ব্যবহারকারী ফোরাম বা পোস্টের কমেন্টে অপ্রাসঙ্গিক কথা লিখছে, অ্যাডমিনদের এ বিষয়ে কঠোর হওয়া দরকার।',
                'is_read' => true,
            ],
            [
                'type' => 'suggestion',
                'message' => 'পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়ের কেন্দ্রীয় মসজিদের উন্নয়ন ফান্ডে সাহায্য পাঠানোর জন্য সরাসরি একটা পেমেন্ট গেটওয়ে যুক্ত করা যায়।',
                'is_read' => false,
            ],
        ];

        foreach ($feedbacks as $fb) {
            Feedback::create($fb);
        }

        $this->command->info('Nasiha/Feedback inbox seeded successfully!');

        // 2. Seed Gallery Albums
        $albumsData = [
            [
                'title' => 'পটুয়াখালী দাওয়াহ সম্মেলন ২০২৬ (PSTU Dawah Conference 2026)',
                'description' => 'পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়ে আয়োজিত বার্ষিক দাওয়াহ সম্মেলন ও ক্যারিয়ার গাইডেন্স প্রোগ্রাম ২০২৬ এর আলোকচিত্র।',
                'category' => 'ইভেন্ট (Events)',
                'image_count' => 4,
            ],
            [
                'title' => 'রমজান ইফতার ও ফুড প্যাক বিতরণ (Ramadan Iftar & Food Distribution)',
                'description' => 'পটুয়াখালী ও দুমকী এলাকার দরিদ্র পরিবারের মাঝে রমজান উপলক্ষে খাদ্য সামগ্রী বিতরণ কার্যক্রম।',
                'category' => 'সমাজসেবা (Social Service)',
                'image_count' => 3,
            ],
            [
                'title' => 'সাপ্তাহিক তাফসীরুল কুরআন হালাকাহ (Weekly Tafseer Halaqah)',
                'description' => 'বিশ্ববিদ্যালয়ের শিক্ষার্থীদের নিয়ে আয়োজিত সাপ্তাহিক তাফসীর ও আকীদা বিষয়ক হালাকাহর সেশনসমূহ।',
                'category' => 'হালাকাহ (Halaqah)',
                'image_count' => 4,
            ],
            [
                'title' => 'মসজিদ লাইব্রেরি লাইভ প্রদর্শন ও বই মেলা (Mosque Library Book Exhibition)',
                'description' => 'পটুয়াখালী সেন্ট্রাল দাওয়াহ লাইব্রেরির উদ্যোগে বই মেলা ও তরুণদের মাঝে বই পড়ার সচেতনতা বৃদ্ধি কার্যক্রম।',
                'category' => 'লাইব্রেরি (Library)',
                'image_count' => 3,
            ],
        ];

        foreach ($albumsData as $ad) {
            $album = GalleryAlbum::create([
                'title' => $ad['title'],
                'slug' => Str::slug($ad['title']),
                'description' => $ad['description'],
                'category' => $ad['category'],
                'is_published' => true,
            ]);

            // Add dynamic mock images via Spatie Media Library using GD
            for ($i = 1; $i <= $ad['image_count']; $i++) {
                $tempImage = tempnam(sys_get_temp_dir(), 'gallery_') . '.jpg';
                $img = imagecreatetruecolor(800, 600);
                
                // Color palette for professional look
                $colors = [
                    [33, 150, 243],  // Blue
                    [76, 175, 80],   // Green
                    [156, 39, 176],  // Purple
                    [255, 152, 0],   // Orange
                    [0, 150, 136]    // Teal
                ];
                $c = $colors[rand(0, count($colors) - 1)];
                $bg = imagecolorallocate($img, $c[0], $c[1], $c[2]);
                imagefill($img, 0, 0, $bg);

                // Add text label
                $textColor = imagecolorallocate($img, 255, 255, 255);
                imagestring($img, 5, 30, 280, "PSTU Dawah - " . $ad['category'] . " - Img $i", $textColor);
                
                imagejpeg($img, $tempImage);
                imagedestroy($img);

                // Add to collection
                $album->addMedia($tempImage)
                    ->usingName("pstu_dawah_{$i}")
                    ->usingFileName("pstu_dawah_{$i}.jpg")
                    ->toMediaCollection('gallery_images');
            }
        }

        $this->command->info('Gallery albums and images seeded successfully!');
    }
}
