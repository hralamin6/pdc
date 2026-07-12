<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use App\Models\Division;
use App\Models\District;
use App\Models\Upazila;
use App\Models\Union;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class UserDetailSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Fetch geographic lookups for Bangladesh
        $divisions = Division::all();
        $districts = District::all();
        $upazilas = Upazila::all();
        $unions = Union::all();

        if ($divisions->isEmpty()) {
            $this->command->warn('Divisions table is empty. Geographical lookup will not be seeded.');
        }

        // Helper function to get valid hierarchical geodata
        $getGeodata = function () use ($divisions, $districts, $upazilas, $unions) {
            if ($divisions->isEmpty()) {
                return [
                    'division_id' => null,
                    'district_id' => null,
                    'upazila_id' => null,
                    'union_id' => null,
                ];
            }

            $division = $divisions->random();
            $district = $districts->where('division_id', $division->id)->random() ?? $districts->random();
            $upazila = $upazilas->where('district_id', $district->id)->random() ?? $upazilas->random();
            
            // Filter unions by upazila, fallback to random if none found
            $unionFiltered = $unions->where('upazila_id', $upazila->id);
            $union = $unionFiltered->isNotEmpty() ? $unionFiltered->random() : $unions->random();

            return [
                'division_id' => $division->id,
                'district_id' => $district->id,
                'upazila_id' => $upazila->id,
                'union_id' => $union ? $union->id : null,
            ];
        };

        // 2. User Detail Data for the 5 Primary Accounts
        $primaryUsers = [
            'superadmin@mail.com' => [
                'phone' => '01711122233',
                'dob' => '1990-05-12',
                'gender' => 'male',
                'address' => 'ঢাকা রোড, পটুয়াখালী সদর, পটুয়াখালী',
                'postal_code' => '8600',
                'occupation' => 'প্রধান সিস্টেম প্রকৌশলী',
                'bio' => 'সিস্টেমের সার্বিক রক্ষণাবেক্ষণ ও পরিচালনার দায়িত্বে নিয়োজিত। দ্বীনের প্রচার ও প্রসারে প্রযুক্তির ব্যবহারকে সহজ করাই আমার লক্ষ্য।',
            ],
            'admin@mail.com' => [
                'phone' => '01711122244',
                'dob' => '1992-08-20',
                'gender' => 'male',
                'address' => 'পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয় ক্যাম্পাস',
                'postal_code' => '8602',
                'occupation' => 'লাইব্রেরি ও দাওয়াহ এডমিনিস্ট্রেটর',
                'bio' => 'পিএসটিইউ কেন্দ্রীয় দাওয়াহ লাইব্রেরি ও হালাকাহ প্রোগ্রামের সার্বিক ব্যবস্থাপনার দায়িত্বে নিয়োজিত।',
            ],
            'accountant@mail.com' => [
                'phone' => '01711122255',
                'dob' => '1994-03-15',
                'gender' => 'male',
                'address' => 'পটুয়াখালী সদর, পটুয়াখালী',
                'postal_code' => '8600',
                'occupation' => 'হিসাবরক্ষক ও কোষাধ্যক্ষ',
                'bio' => 'দাওয়াহ লাইব্রেরি ও ফিনান্সিয়াল ট্রাস্টের হিসাব ও ফান্ড পরিচালনার দায়িত্বে নিয়োজিত।',
            ],
            'mentor@mail.com' => [
                'phone' => '01711122266',
                'dob' => '1988-11-30',
                'gender' => 'male',
                'address' => 'শিক্ষক ডরমিটরি, পিএসটিইউ ক্যাম্পাস, পটুয়াখালী',
                'postal_code' => '8602',
                'occupation' => 'হালাকাহ মেন্টর ও সহযোগী অধ্যাপক',
                'bio' => 'নিয়মিত ইসলামিক হালাকাহ পরিচালনা করি। তরুণ প্রজন্মের মাঝে সঠিক ইসলামিক আকিদা ও আমল শিক্ষা দেওয়াই আমার মূল ফোকাস।',
            ],
            'user@mail.com' => [
                'phone' => '01711122277',
                'dob' => '2002-01-10',
                'gender' => 'male',
                'address' => 'শেরেবাংলা হল-১, পিএসটিইউ',
                'postal_code' => '8602',
                'occupation' => 'শিক্ষার্থী, সিএসই অনুষদ',
                'bio' => 'আমি দ্বীন শিক্ষার উদ্দেশ্যে নিয়মিত হালাকায় যুক্ত থাকি। হালাকার সকল নিয়ম ও শিষ্টাচার বজায় রাখতে সচেষ্ট থাকি।',
            ],
        ];

        foreach ($primaryUsers as $email => $data) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $geo = $getGeodata();
                UserDetail::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'phone' => $data['phone'],
                        'date_of_birth' => Carbon::parse($data['dob']),
                        'gender' => $data['gender'],
                        'address' => $data['address'],
                        'postal_code' => $data['postal_code'],
                        'occupation' => $data['occupation'],
                        'bio' => $data['bio'],
                        'division_id' => $geo['division_id'],
                        'district_id' => $geo['district_id'],
                        'upazila_id' => $geo['upazila_id'],
                        'union_id' => $geo['union_id'],
                        'website' => 'https://pstu.ac.bd',
                        'facebook' => 'https://facebook.com/' . Str::slug($user->name),
                        'github' => 'https://github.com/' . Str::slug($user->name),
                        'is_active' => true,
                    ]
                );
            }
        }

        // 3. Create 20 realistic student users with departments and user details
        $departments = ['cse', 'eee', 'ag', 'dvm', 'bba', 'dm', 'nfs', 'fms', 'ce'];
        
        $banglaNames = [
            ['name' => 'আব্দুল্লাহ আল মারুফ', 'gender' => 'male', 'bio' => 'দ্বীন শিক্ষার প্রতি গভীর আগ্রহী। পিএসটিইউ দাওয়াহ লাইব্রেরির একজন নিয়মিত পাঠক।', 'hall' => 'শেরেবাংলা হল-২'],
            ['name' => 'সাইফুর রহমান', 'gender' => 'male', 'bio' => 'সদা হাস্যোজ্জ্বল ও সামাজিক কাজে উৎসাহী। হালাকায় নিয়মিত শরিক হওয়ার চেষ্টা করি।', 'hall' => 'এম. কেরামত আলী হল'],
            ['name' => 'উম্মে হাফসা', 'gender' => 'female', 'bio' => 'সিস্টার্স হালাকার সদস্য। পড়াশোনার পাশাপাশি কোরআন স্টাডি গ্রুপে নিয়মিত অংশগ্রহণ করি।', 'hall' => 'কবি সুফিয়া কামাল হল'],
            ['name' => 'তানজিলা আক্তার', 'gender' => 'female', 'bio' => 'দ্বীনকে নিজের জীবনে বাস্তবায়িত করাই মূল স্বপ্ন। লাইব্রেরির বই পড়া আমার অন্যতম অভ্যাস।', 'hall' => 'শেখ হাসিনা হল'],
            ['name' => 'রিফাত চৌধুরী', 'gender' => 'male', 'bio' => 'প্রযুক্তির মাধ্যমে দাওয়াহ কার্যক্রমে সাহায্য করতে চাই। সিএসই অনুষদের শিক্ষার্থী।', 'hall' => 'শেরেবাংলা হল-১'],
            ['name' => 'ফাহমিদা সুলতানা', 'gender' => 'female', 'bio' => 'ইসলামের সৌন্দর্য নিজের চরিত্রে ফুটিয়ে তোলার চেষ্টা করছি। নিয়মিত ইসলামিক লেকচার শুনি।', 'hall' => 'কবি সুফিয়া কামাল হল'],
            ['name' => 'নাজমুল হুদা', 'gender' => 'male', 'bio' => 'দ্বীনের কাজে স্বেচ্ছাসেবী হিসেবে সময় দিতে ভালোবাসি। হালাকার বন্ধুদের সাথে আলোচনা পছন্দ করি।', 'hall' => 'এম. কেরামত আলী হল'],
            ['name' => 'মারুফ হাসান', 'gender' => 'male', 'bio' => 'আল্লাহর সন্তুষ্টি অর্জনই জীবনের আসল লক্ষ্য। প্রতিদিন কিছু সময় ইসলামিক বই পড়ি।', 'hall' => 'শেরেবাংলা হল-২'],
            ['name' => 'মোস্তফা কামাল', 'gender' => 'male', 'bio' => 'ইসলামের সঠিক বাণী সাধারণ মানুষের কাছে পৌঁছে দেওয়ার জন্য ছোট ছোট কাজ করি।', 'hall' => 'বঙ্গবন্ধু হল'],
            ['name' => 'খাদিজা বেগম', 'gender' => 'female', 'bio' => 'সদাচারী ও দ্বীনদার জীবন যাপনের চেষ্টা করি। উম্মাহর সেবায় কাজ করার ইচ্ছা আছে।', 'hall' => 'শেখ হাসিনা হল'],
            ['name' => 'মেহেরুন নেসা', 'gender' => 'female', 'bio' => 'ইসলামের বুনিয়াদি জ্ঞান অর্জন করা আমার কাছে অত্যন্ত গুরুত্বপূর্ণ। নিয়মিত নোট রাখি।', 'hall' => 'কবি সুফিয়া কামাল হল'],
            ['name' => 'ইশতিয়াক আহমেদ', 'gender' => 'male', 'bio' => 'একজন আদর্শ মুসলিম হিসেবে নিজেকে গড়ে তুলতে চাই। পড়াশোনা এবং ইবাদতে মগ্ন থাকতে ভালোবাসি।', 'hall' => 'শেরেবাংলা হল-১'],
            ['name' => 'সাদমান সাকিব', 'gender' => 'male', 'bio' => 'পিএসটিইউ ইসলামী ছাত্র সংসদের কার্যক্রমের সাথে জড়িত। দাওয়াহ কাজে সবসময় নিয়োজিত।', 'hall' => 'বঙ্গবন্ধু হল'],
            ['name' => 'তানিয়া সুলতানা', 'gender' => 'female', 'bio' => 'ইসলামি সমাজ গঠনে মুসলিম বোনদের সচেতনতা বৃদ্ধির লক্ষ্যে কাজ করতে চাই।', 'hall' => 'শেখ হাসিনা হল'],
            ['name' => 'মাহমুদুল হাসান', 'gender' => 'male', 'bio' => 'কুরআন ও হাদিসের আলোকে জীবন গঠনের নিরন্তর প্রচেষ্টা। বই পড়া আমার শখ।', 'hall' => 'এম. কেরামত আলী হল'],
            ['name' => 'সাবিনা ইয়াসমিন', 'gender' => 'female', 'bio' => 'সব পরিস্থিতিতে আল্লাহর ওপর তাওয়াক্কুল করা আমার সবচেয়ে বড় শক্তির জায়গা।', 'hall' => 'কবি সুফিয়া কামাল হল'],
            ['name' => 'শাফায়াত হোসেন', 'gender' => 'male', 'bio' => 'দ্বীনি ভাইদের সাথে সুন্দর সময় কাটানো এবং তাদের সুখে-দুঃখে পাশে থাকা পছন্দ করি।', 'hall' => 'শেরেবাংলা হল-২'],
            ['name' => 'জান্নাতুল ফেরদৌস', 'gender' => 'female', 'bio' => 'ইসলামের ইতিহাস ও ঐতিহ্য জানা আমার কাছে দারুণ লাগে। সিরাহ গ্রন্থ পড়তে ভালোবাসি।', 'hall' => 'শেখ হাসিনা হল'],
            ['name' => 'আতিকুর রহমান', 'gender' => 'male', 'bio' => 'দৈনন্দিন জীবনে সুন্নাহ বাস্তবায়নের প্র্যাকটিস করছি। হালাকার মেন্টরের দিকনির্দেশনা অনুসরণ করি।', 'hall' => 'বঙ্গবন্ধু হল'],
            ['name' => 'মারজিয়া রহমান', 'gender' => 'female', 'bio' => 'ইসলামি শিষ্টাচার ও শালীন জীবনযাপন বজায় রাখার চেষ্টা করি। দাওয়াহ ফোরামের মেম্বার।', 'hall' => 'শেখ হাসিনা হল'],
        ];

        $this->command->info('Creating 20 department users and their details...');

        foreach ($banglaNames as $index => $student) {
            $studentId = 200000 + ($index + 1) * 37 + rand(5, 29); // realistic 6-digit student ID
            $dept = $departments[$index % count($departments)];
            $email = "{$studentId}@{$dept}.pstu.ac.bd";
            
            // Create user
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $student['name'],
                    'email_verified_at' => now(),
                    'password' => bcrypt('000000'), // default simple password
                ]
            );

            // Assign 'user' role
            if ($user && ! $user->hasRole('user')) {
                $user->assignRole('user');
            }

            // Create detail
            $geo = $getGeodata();
            $phoneNumber = '01' . collect([3, 4, 5, 6, 7, 8, 9])->random() . rand(10000000, 99999999);
            $dob = Carbon::now()->subYears(rand(20, 24))->subDays(rand(1, 365));

            UserDetail::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'phone' => $phoneNumber,
                    'date_of_birth' => $dob,
                    'gender' => $student['gender'],
                    'address' => "{$student['hall']}, পটুয়াখালী বিজ্ঞান ও প্রযুক্তি বিশ্ববিদ্যালয়, দুমকি, পটুয়াখালী",
                    'postal_code' => '8602',
                    'occupation' => 'শিক্ষার্থী, ' . strtoupper($dept) . ' বিভাগ',
                    'bio' => $student['bio'],
                    'division_id' => $geo['division_id'],
                    'district_id' => $geo['district_id'],
                    'upazila_id' => $geo['upazila_id'],
                    'union_id' => $geo['union_id'],
                    'website' => null,
                    'facebook' => 'https://facebook.com/student' . $studentId,
                    'github' => $dept === 'cse' ? 'https://github.com/student' . $studentId : null,
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('20 department users and their details created successfully!');
    }
}
