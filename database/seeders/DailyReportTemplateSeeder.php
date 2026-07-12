<?php

namespace Database\Seeders;

use App\Models\DailyReportTemplate;
use Illuminate\Database\Seeder;

class DailyReportTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Ibadah & Salah (ইবাদত ও সালাত)
            ['title' => 'ফজর সালাত (Fajr Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 10],
            ['title' => 'যোহর সালাত (Dhuhr Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 20],
            ['title' => 'আসর সালাত (Asr Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 30],
            ['title' => 'মাগরিব সালাত (Maghrib Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 40],
            ['title' => 'এশা সালাত (Isha Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 50],
            ['title' => 'জামায়াতে সালাত আদায় (Salah in Jamaat)', 'type' => 'number', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 60],
            ['title' => 'তাহাজ্জুদ সালাত (Tahajjud Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 70],
            ['title' => 'নফল রোজা (Nafl/Sunnah Fasting)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 80],
            ['title' => 'আজান ও ইকামতের জবাব (Responding to Adhan)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 90],
            ['title' => 'পাঁচ ওয়াক্ত সালাত শেষে তাসবীহ (Post-Salah Tasbih)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 100],
            ['title' => 'ইশরাক সালাত (Ishraq/Duha Salah)', 'type' => 'boolean', 'category' => 'ইবাদত ও সালাত', 'sort_order' => 110],

            // Quran & Adhkar (কুরআন ও আযকার)
            ['title' => 'কুরআন তিলাওয়াত - পৃষ্ঠা (Quran Recitation - Pages)', 'type' => 'number', 'category' => 'কুরআন ও আযকার', 'sort_order' => 120],
            ['title' => 'কুরআন মুখস্থ/হিফয - আয়াত (Quran Memorization - Verses)', 'type' => 'number', 'category' => 'কুরআন ও আযকার', 'sort_order' => 130],
            ['title' => 'সকাল-সন্ধ্যার দোআ ও আযকার (Morning/Evening Adhkar)', 'type' => 'boolean', 'category' => 'কুরআন ও আযকার', 'sort_order' => 140],
            ['title' => 'তওবা ও ইস্তিগফার (Repentance & Istighfar)', 'type' => 'number', 'category' => 'কুরআন ও আযকার', 'sort_order' => 150],
            ['title' => 'দরূদ শরীফ পাঠ (Durood Recitals)', 'type' => 'number', 'category' => 'কুরআন ও আযকার', 'sort_order' => 160],
            ['title' => 'ঘুমের আগে সুন্নাহ আমল (Pre-sleep Sunnah Adhkar)', 'type' => 'boolean', 'category' => 'কুরআন ও আযকার', 'sort_order' => 170],

            // Study & Knowledge (পড়াশোনা ও জ্ঞান)
            ['title' => 'একাডেমিক পড়াশোনা - ঘণ্টা (Academic Study - Hours)', 'type' => 'number', 'category' => 'পড়াশোনা ও জ্ঞান', 'sort_order' => 180],
            ['title' => 'দ্বীনি বই পড়া - মিনিট (Islamic Reading - Minutes)', 'type' => 'number', 'category' => 'পড়াশোনা ও জ্ঞান', 'sort_order' => 190],
            ['title' => 'নতুন স্কিল/প্রযুক্তি শেখা (Learning New Skill - Minutes)', 'type' => 'number', 'category' => 'পড়াশোনা ও জ্ঞান', 'sort_order' => 200],
            ['title' => 'লেকচার বা ভালো আলোচনা শোনা (Listening to Islamic Lectures)', 'type' => 'boolean', 'category' => 'পড়াশোনা ও জ্ঞান', 'sort_order' => 210],

            // Health & Exercise (স্বাস্থ্য ও শরীরচর্চা)
            ['title' => 'শারীরিক ব্যায়াম - মিনিট (Physical Exercise - Minutes)', 'type' => 'number', 'category' => 'স্বাস্থ্য ও শরীরচর্চা', 'sort_order' => 220],
            ['title' => 'পর্যাপ্ত হাঁটা (Walking)', 'type' => 'boolean', 'category' => 'স্বাস্থ্য ও শরীরচর্চা', 'sort_order' => 230],
            ['title' => 'পর্যাপ্ত ঘুমানো - ঘণ্টা (Sleep Duration - Hours)', 'type' => 'number', 'category' => 'স্বাস্থ্য ও শরীরচর্চা', 'sort_order' => 240],
            ['title' => 'দাঁত মেসওয়াক করা (Miswak / Brushing)', 'type' => 'boolean', 'category' => 'স্বাস্থ্য ও শরীরচর্চা', 'sort_order' => 250],
            ['title' => 'ওজন নিয়ন্ত্রণ ও ট্র্যাকিং (Weight Tracking)', 'type' => 'mixed', 'category' => 'স্বাস্থ্য ও শরীরচর্চা', 'sort_order' => 260],

            // Food & Nutrition (খাদ্য ও পুষ্টি)
            ['title' => 'পর্যাপ্ত পানি পান - লিটার (Drinking Water - Liters)', 'type' => 'number', 'category' => 'খাদ্য ও পুষ্টি', 'sort_order' => 270],
            ['title' => 'সুন্নাহ অনুযায়ী পানাহার (Sunnah Eating & Drinking)', 'type' => 'boolean', 'category' => 'খাদ্য ও পুষ্টি', 'sort_order' => 280],
            ['title' => 'অতিরিক্ত খাদ্য পরিহার (Avoiding Overeating)', 'type' => 'boolean', 'category' => 'খাদ্য ও পুষ্টি', 'sort_order' => 290],
            ['title' => 'বাইরের খাবার/ফাস্ট ফুড পরিহার (Avoiding Junk Food)', 'type' => 'boolean', 'category' => 'খাদ্য ও পুষ্টি', 'sort_order' => 300],
            ['title' => 'ফল ও শাকসবজি গ্রহণ (Eating Fruits/Vegetables)', 'type' => 'boolean', 'category' => 'খাদ্য ও পুষ্টি', 'sort_order' => 310],

            // Personal & Time Management (ব্যক্তিগত ও সময় ব্যবস্থাপনা)
            ['title' => 'মোবাইল/স্ক্রিন টাইম নিয়ন্ত্রণ - ঘণ্টা (Screen Time Limit - Hours)', 'type' => 'number', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 320],
            ['title' => 'সোশ্যাল মিডিয়া অপচয় রোধ (Avoiding Social Media Waste)', 'type' => 'boolean', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 330],
            ['title' => 'তাড়াতাড়ি ঘুমানো (Slept Early)', 'type' => 'boolean', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 340],
            ['title' => 'সকাল সকাল ঘুম থেকে ওঠা (Waking Up Early)', 'type' => 'boolean', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 350],
            ['title' => 'পরিকল্পিত দিনলিপি/কাজের তালিকা (Day Planning/Task List)', 'type' => 'boolean', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 360],
            ['title' => 'ব্যক্তিগত ডায়েরি/মূল্যায়ন (Personal Journal/Reflection)', 'type' => 'text', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 370],
            ['title' => 'ঘরবাড়ি ও নিজের বিছানা পরিষ্কার রাখা (Cleaning Room & Desk)', 'type' => 'boolean', 'category' => 'ব্যক্তিগত ও সময় ব্যবস্থাপনা', 'sort_order' => 380],

            // Character & Self-Purification (চরিত্র ও আত্মশুদ্ধি)
            ['title' => 'পরনিন্দা/গীবত পরিহার (Avoiding Backbiting/Gossip)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 390],
            ['title' => 'চোখের হিফাযত (Guarding the Eyes/Gaze)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 400],
            ['title' => 'রাগ নিয়ন্ত্রণ করা (Anger Management)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 410],
            ['title' => 'মিথ্যা পরিহার করা (Avoiding Lies)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 420],
            ['title' => 'ধৈর্য ধারণের অনুশীলন (Practicing Patience)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 430],
            ['title' => 'মানুষকে ক্ষমা করা (Forgiving Others)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 440],
            ['title' => 'অপ্রয়োজনীয় কথা না বলা (Avoiding Idle Talk)', 'type' => 'boolean', 'category' => 'চরিত্র ও আত্মশুদ্ধি', 'sort_order' => 450],

            // Social & Family (সামাজিক ও পরিবার)
            ['title' => 'মা-বাবার সেবা/খোঁজ নেওয়া (Serving/Caring Parents)', 'type' => 'boolean', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 460],
            ['title' => 'পরিবারকে দ্বীন শিক্ষা দেওয়া (Teaching Deen to Family)', 'type' => 'boolean', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 470],
            ['title' => 'আত্মীয়তার সম্পর্ক রক্ষা (Contacting Family/Relatives)', 'type' => 'boolean', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 480],
            ['title' => 'দাওয়াহ কার্যক্রম (Dawah Activity)', 'type' => 'mixed', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 490],
            ['title' => 'সাদাকাহ/দান (Sadaqah/Charity)', 'type' => 'mixed', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 500],
            ['title' => 'মানুষকে সাহায্য করা (Helping Someone in Need)', 'type' => 'mixed', 'category' => 'সামাজিক ও পরিবার', 'sort_order' => 510],
        ];

        foreach ($items as $item) {
            DailyReportTemplate::updateOrCreate(
                ['title' => $item['title']],
                array_merge($item, ['is_system_default' => true])
            );
        }
    }
}
