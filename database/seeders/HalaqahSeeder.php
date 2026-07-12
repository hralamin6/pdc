<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Halaqah;
use App\Models\HalaqahSeries;
use App\Models\HalaqahAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class HalaqahSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch mentor and students
        $mentor = User::role('mentor')->first() ?? User::where('email', 'mentor@mail.com')->first() ?? User::first();
        $students = User::role('user')->get();

        if ($students->isEmpty()) {
            $this->command->warn('No students found to seed attendance. Creating some factory users.');
            $students = User::factory(10)->create()->each(function ($u) {
                $u->assignRole('user');
            });
        }

        // 2. Create detailed Halaqah Series (Bangla)
        $series1 = HalaqahSeries::create([
            'mentor_id' => $mentor->id,
            'title' => 'সুরা আল-কাহাফের তাফসীর ও সমসাময়িক শিক্ষা',
            'description' => 'সুরা আল-কাহাফের তাফসীর, গুরুত্ব এবং আধুনিক যুগে আমাদের জীবনে এর প্রাসঙ্গিকতা নিয়ে ৪ পর্বের ধারাবাহিক আলোচনা। এই সিরিজে গুহাবাসী যুবক, দুই বাগানের মালিক, মুসা ও খিজির (আঃ) এবং জুলকারনাইনের শিক্ষণীয় ঘটনাগুলো বিস্তারিত আলোচনা করা হবে।',
            'target_audience_level' => 'intermediate',
            'status' => 'active',
        ]);

        $series2 = HalaqahSeries::create([
            'mentor_id' => $mentor->id,
            'title' => 'বিশুদ্ধ আকীদার মৌলিক বিষয়সমূহ (ইসলামি বিশ্বাস)',
            'description' => 'কুরআন ও সুন্নাহর আলোকে ইসলামের ঈমানী স্তম্ভ এবং বিশুদ্ধ আকীদার মূল বিষয়গুলো সহজ ভাষায় বোঝা। শিক্ষার্থীদের জন্য ঈমান মজবুত করার একটি বুনিয়াদি কোর্স।',
            'target_audience_level' => 'beginner',
            'status' => 'active',
        ]);

        // 3. Create 10 detailed Sessions (Bangla)
        $sessions = [
            // Series 1 - Completed
            [
                'series_id' => $series1->id,
                'title' => 'গুহাবাসীদের ঈমানী সংগ্রাম (১ম পর্ব)',
                'topic' => 'ঈমান ও যুবসমাজ',
                'description' => 'তৎকালীন কুফরি সমাজে নিজেদের ঈমান রক্ষার্থে গুহাবাসী যুবকদের ত্যাগ, গুহায় আশ্রয় এবং আল্লাহর অসীম কুদরতের ঘটনার তাফসীর ও সমসাময়িক শিক্ষা।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subWeeks(2)->setHour(18)->setMinute(0),
                'location' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://youtube.com/watch?v=example1', 'https://drive.google.com/example-pdf'],
            ],
            // Series 1 - Completed
            [
                'series_id' => $series1->id,
                'title' => 'দুই বাগানের মালিকের গল্প (২য় পর্ব)',
                'topic' => 'ধন-সম্পদ ও কৃতজ্ঞতা',
                'description' => 'সম্পদের অহংকার, পার্থিব মোহে আল্লাহর নেয়ামতের প্রতি অকৃতজ্ঞতার পরিণতি এবং জান্নাত ও জাহান্নামের বিবরণ নিয়ে সুরা আল-কাহাফের গুরুত্বপূর্ণ বার্তা।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subWeeks(1)->setHour(18)->setMinute(0),
                'location' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://youtube.com/watch?v=example2'],
            ],
            // Series 1 - Upcoming
            [
                'series_id' => $series1->id,
                'title' => 'মুসা (আঃ) ও খিজির (আঃ) এর ভ্রমণ (৩য় পর্ব)',
                'topic' => 'জ্ঞান ও ধৈর্য',
                'description' => 'আল্লাহর তাকদীর ও প্রজ্ঞার রহস্য, এবং জ্ঞান অর্জনের পথে শিক্ষক-ছাত্রের সম্পর্ক ও ধৈর্যের গুরুত্ব নিয়ে আলোচনা।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(2)->setHour(18)->setMinute(0),
                'location' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ',
                'status' => 'published',
                'max_capacity' => 10, // low capacity to test waitlist if students count > 10
                'gender_restriction' => 'none',
            ],
            // Series 1 - Draft
            [
                'series_id' => $series1->id,
                'title' => 'বাদশাহ জুলকারনাইনের শাসন ও ন্যায়বিচার (৪র্থ পর্ব)',
                'topic' => 'ক্ষমতা ও সামাজিক ন্যায়বিচার',
                'description' => 'ন্যায়পরায়ণ শাসক জুলকারনাইনের ইয়াজুজ-মাজুজ প্রতিরোধে দেয়াল নির্মাণের ঘটনা এবং ক্ষমতার সঠিক ব্যবহার ও সীমানা প্রাচীর নির্মাণের তাফসীর।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addWeeks(1)->setHour(18)->setMinute(0),
                'location' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ',
                'status' => 'draft',
                'gender_restriction' => 'none',
            ],
            
            // Series 2 - Upcoming
            [
                'series_id' => $series2->id,
                'title' => 'তাওহীদের পরিচয় ও রুবুবিয়্যাহ (১ম পর্ব)',
                'topic' => 'ঈমান ও তাওহীদ',
                'description' => 'সৃষ্টিকর্তা, পালনকর্তা ও রিজিকদাতা হিসেবে আল্লাহর একত্ববাদের পরিচয়, রুবুবিয়্যাহর গুরুত্ব এবং আমাদের জীবনে এর প্রভাব।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(5)->setHour(17)->setMinute(30),
                'location' => 'লাইব্রেরি সেমিনার কক্ষ',
                'status' => 'published',
                'gender_restriction' => 'brothers_only',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            ],
            // Series 2 - Upcoming
            [
                'series_id' => $series2->id,
                'title' => 'তাওহীদুল উলুহিয়্যাহ ও ইবাদত (২য় পর্ব)',
                'topic' => 'একমাত্র আল্লাহর ইবাদত',
                'description' => 'ইবাদতে আল্লাহর নিরঙ্কুশ অধিকার, উলুহিয়্যাহর তাৎপর্য এবং শিরক ও এর ভয়াবহতা থেকে বেঁচে থাকার উপায়।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(12)->setHour(17)->setMinute(30),
                'location' => 'লাইব্রেরি সেমিনার কক্ষ',
                'status' => 'published',
                'gender_restriction' => 'brothers_only',
            ],
            
            // Standalone - Upcoming
            [
                'series_id' => null,
                'title' => 'পরীক্ষায় প্রস্তুতি ও আল্লাহর ওপর তাওয়াক্কুল',
                'topic' => 'ছাত্রজীবন ও দ্বীন',
                'description' => 'সেমিস্টার ফাইনাল পরীক্ষার কঠিন সময়ে পড়াশোনায় কঠোর পরিশ্রমের পাশাপাশি আল্লাহর ওপর তাওয়াক্কুল করার সঠিক নিয়ম ও আত্মিক প্রশান্তি।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(1)->setHour(20)->setMinute(0),
                'location' => 'অনলাইন জুমিং সেশন',
                'meeting_link' => 'https://zoom.us/j/123456789',
                'status' => 'published',
                'gender_restriction' => 'none',
            ],
            // Standalone - Cancelled
            [
                'series_id' => null,
                'title' => 'বোনদের সাপ্তাহিক হালাকাহ ও আত্মшুদ্ধি',
                'topic' => 'আত্মশুদ্ধি ও হৃদয়ের ব্যাধি',
                'description' => 'অহংকার, হিংসা, গীবত ও লোকদেখানো আমল থেকে নিজের হৃদয়কে মুক্ত করার ব্যবহারিক উপায় নিয়ে আলোচনা।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(3)->setHour(14)->setMinute(0),
                'location' => 'ছাত্রী মিলনায়তন কক্ষ',
                'status' => 'cancelled',
                'gender_restriction' => 'sisters_only',
            ],
            // Standalone - Past
            [
                'series_id' => null,
                'title' => 'ক্যাম্পাসে স্বাগতম: নবীন বরণ ও দ্বীনি আড্ডা',
                'topic' => 'দ্বীনি ভাইদের ভ্রাতৃত্ব',
                'description' => 'বিশ্ববিদ্যালয়ে নতুন ভর্তি হওয়া শিক্ষার্থীদের দ্বীনি ভ্রাতৃত্বের বলয়ে স্বাগত জানাতে এক বিশেষ চা-আড্ডা ও দিকনির্দেশনামূলক সেশন।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subMonths(1)->setHour(16)->setMinute(0),
                'location' => 'বিশ্ববিদ্যালয় খেলার মাঠ সংলগ্ন চত্বর',
                'status' => 'completed',
                'gender_restriction' => 'none',
            ],
            // Standalone - Past
            [
                'series_id' => null,
                'title' => 'রমজানের প্রস্তুতি ও করণীয়',
                'topic' => 'রমজানের সওগাত',
                'description' => 'পবিত্র রমজান মাসের আগেই শারীরিক ও মানসিকভাবে নিজেকে প্রস্তুত করার উপায়, প্রতিদিনের আমলের পরিকল্পনা এবং সিয়াম পালনের নিয়মাবলি।',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subMonths(3)->setHour(18)->setMinute(0),
                'location' => 'পিএসটিইউ কেন্দ্রীয় মসজিদ',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://drive.google.com/ramadan-planner'],
            ],
        ];

        foreach ($sessions as $data) {
            $data['qr_token'] = Str::random(12);
            $halaqah = Halaqah::create($data);

            // 4. Add realistic attendances (RSVPs, waitlists, and actual attendance)
            if ($halaqah->status !== 'draft' && $halaqah->status !== 'cancelled') {
                $rsvpCount = 0;
                foreach ($students as $student) {
                    // Skip around 30% of students randomly to make it look realistic
                    if (rand(1, 10) <= 3) {
                        continue;
                    }

                    $isWaitlist = $halaqah->max_capacity && $rsvpCount >= $halaqah->max_capacity;
                    $statusNew = $isWaitlist ? 'waitlist' : 'rsvp';

                    $attended = false;
                    $checkInMethod = null;
                    $checkedInAt = null;
                    $rating = null;
                    $feedback = null;

                    if ($halaqah->status === 'completed' && !$isWaitlist) {
                        // Completed sessions: 80% attendance rate
                        $attended = rand(1, 10) <= 8;
                        if ($attended) {
                            $checkInMethod = rand(0, 1) ? 'manual' : 'qr_scan';
                            $checkedInAt = Carbon::parse($halaqah->scheduled_at)->addMinutes(rand(1, 20));
                            
                            // 50% chance they left feedback/rating
                            if (rand(0, 1)) {
                                $rating = rand(4, 5);
                                $feedback = collect([
                                    'আলহামদুলিল্লাহ, সেশনটি খুবই তথ্যবহুল ছিল। অনেক কিছু শিখলাম।',
                                    'মেন্টর স্যারের আলোচনার ধরণ চমৎকার। পরবর্তী পর্বের অপেক্ষায় রইলাম।',
                                    'অত্যন্ত চমৎকার ও সময়োপযোগী আলোচনা। জাজাকুমুল্লাহু খাইরান।',
                                    'পরিবেশটা খুবই শান্ত ও চমৎকার ছিল। আলোচনা হৃদয়স্পর্শী হয়েছে।',
                                ])->random();
                            }
                        }
                    }

                    HalaqahAttendance::create([
                        'halaqah_id' => $halaqah->id,
                        'user_id' => $student->id,
                        'status' => 'rsvp',
                        'status_new' => $statusNew,
                        'attended' => $attended,
                        'check_in_method' => $checkInMethod,
                        'checked_in_at' => $checkedInAt,
                        'rating' => $rating,
                        'feedback' => $feedback,
                        'preparation_completed' => rand(0, 1) === 1,
                    ]);

                    if (!$isWaitlist) {
                        $rsvpCount++;
                    }
                }
            }
        }

        $this->command->info('Halaqah series, sessions and attendance data seeded successfully in Bangla!');
    }
}
