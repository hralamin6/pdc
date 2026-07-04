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
        // Get some users for mentors and students
        $users = User::all();
        
        if ($users->count() < 5) {
            // Create some users if not enough
            $users = User::factory(5)->create();
        }

        $mentor = $users->first(); // We'll use the first user as mentor
        $students = $users->where('id', '!=', $mentor->id);

        // 1. Create a Series
        $series1 = HalaqahSeries::create([
            'mentor_id' => $mentor->id,
            'title' => 'Tafseer of Surah Al-Kahf',
            'description' => 'A deep dive into the meanings, lessons, and relevance of Surah Al-Kahf in our modern times. This is a 4-part series covering the key stories: The People of the Cave, The Owner of Two Gardens, Musa and Khidr, and Dhul-Qarnayn.',
            'target_audience_level' => 'intermediate',
            'status' => 'active',
        ]);

        $series2 = HalaqahSeries::create([
            'mentor_id' => $mentor->id,
            'title' => 'Foundations of Aqeedah',
            'description' => 'Understanding the core pillars of Islamic belief based on the Quran and Sunnah.',
            'target_audience_level' => 'beginner',
            'status' => 'active',
        ]);

        // 2. Create 10 Sessions (Past, Present, Future)
        $sessions = [
            // Series 1 - Completed
            [
                'series_id' => $series1->id,
                'title' => 'Story 1: The People of the Cave',
                'topic' => 'Faith & Youth',
                'description' => 'Exploring the trial of faith in a disbelieving society.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subWeeks(2)->setHour(18)->setMinute(0),
                'location' => 'Main Campus Mosque',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://youtube.com/watch?v=example1', 'https://drive.google.com/example-pdf'],
            ],
            // Series 1 - Completed
            [
                'series_id' => $series1->id,
                'title' => 'Story 2: The Owner of Two Gardens',
                'topic' => 'Wealth & Gratitude',
                'description' => 'The trial of wealth and how it can distract from the ultimate purpose.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subWeeks(1)->setHour(18)->setMinute(0),
                'location' => 'Main Campus Mosque',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://youtube.com/watch?v=example2'],
            ],
            // Series 1 - Upcoming
            [
                'series_id' => $series1->id,
                'title' => 'Story 3: Musa and Khidr',
                'topic' => 'Knowledge & Patience',
                'description' => 'The trial of knowledge and understanding the divine decree.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(2)->setHour(18)->setMinute(0),
                'location' => 'Main Campus Mosque',
                'status' => 'published',
                'max_capacity' => 2, // Low capacity to test waitlist
                'gender_restriction' => 'none',
            ],
            // Series 1 - Draft
            [
                'series_id' => $series1->id,
                'title' => 'Story 4: Dhul-Qarnayn',
                'topic' => 'Power & Authority',
                'description' => 'The trial of power and establishing justice on earth.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addWeeks(1)->setHour(18)->setMinute(0),
                'location' => 'Main Campus Mosque',
                'status' => 'draft',
                'gender_restriction' => 'none',
            ],
            
            // Series 2 - Upcoming
            [
                'series_id' => $series2->id,
                'title' => 'Belief in Allah (Tawheed)',
                'topic' => 'Tawheed Ar-Rububiyyah',
                'description' => 'Understanding the oneness of Allah in His Lordship.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(5)->setHour(17)->setMinute(30),
                'location' => 'Library Seminar Room',
                'status' => 'published',
                'gender_restriction' => 'brothers_only',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            ],
            // Series 2 - Upcoming
            [
                'series_id' => $series2->id,
                'title' => 'Belief in the Angels',
                'topic' => 'Pillars of Iman',
                'description' => 'The nature and duties of Angels in Islam.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(12)->setHour(17)->setMinute(30),
                'location' => 'Library Seminar Room',
                'status' => 'published',
                'gender_restriction' => 'brothers_only',
            ],
            
            // Standalone - Upcoming
            [
                'series_id' => null,
                'title' => 'Exam Prep & Tawakkul',
                'topic' => 'Student Life',
                'description' => 'How to balance hard work with reliance on Allah during final exams.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(1)->setHour(20)->setMinute(0),
                'location' => 'Online Only',
                'meeting_link' => 'https://zoom.us/j/123456789',
                'status' => 'published',
                'gender_restriction' => 'none',
            ],
            // Standalone - Cancelled
            [
                'series_id' => null,
                'title' => 'Sisters Weekly Reflection',
                'topic' => 'Self-Purification',
                'description' => 'Focusing on purifying the heart from spiritual diseases.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->addDays(3)->setHour(14)->setMinute(0),
                'location' => 'Sisters Common Room',
                'status' => 'cancelled',
                'gender_restriction' => 'sisters_only',
            ],
            // Standalone - Past
            [
                'series_id' => null,
                'title' => 'Welcome to Campus: Freshers Meetup',
                'topic' => 'Community Building',
                'description' => 'A special welcoming circle for the new incoming batch.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subMonths(1)->setHour(16)->setMinute(0),
                'location' => 'Campus Field',
                'status' => 'completed',
                'gender_restriction' => 'none',
            ],
            // Standalone - Past
            [
                'series_id' => null,
                'title' => 'Ramadan Readiness',
                'topic' => 'Ramadan',
                'description' => 'Preparing ourselves physically and spiritually for the blessed month.',
                'speaker_id' => $mentor->id,
                'scheduled_at' => Carbon::now()->subMonths(3)->setHour(18)->setMinute(0),
                'location' => 'Main Campus Mosque',
                'status' => 'completed',
                'gender_restriction' => 'none',
                'resources' => ['https://drive.google.com/ramadan-planner'],
            ],
        ];

        foreach ($sessions as $index => $data) {
            $data['qr_token'] = Str::random(12);
            $halaqah = Halaqah::create($data);

            // Add attendances
            if ($halaqah->status !== 'draft' && $halaqah->status !== 'cancelled') {
                $rsvpCount = 0;
                foreach ($students as $student) {
                    // Randomly skip some students
                    if (rand(0, 1) == 0) continue;
                    
                    $isWaitlist = $halaqah->max_capacity && $rsvpCount >= $halaqah->max_capacity;
                    
                    HalaqahAttendance::create([
                        'halaqah_id' => $halaqah->id,
                        'user_id' => $student->id,
                        'status' => 'rsvp',
                        'status_new' => $isWaitlist ? 'waitlist' : 'rsvp',
                        'attended' => $halaqah->status === 'completed' ? (rand(0, 1) == 1) : false,
                        'preparation_completed' => rand(0, 1) == 1,
                    ]);
                    
                    if (!$isWaitlist) {
                        $rsvpCount++;
                    }
                }
            }
        }
    }
}
