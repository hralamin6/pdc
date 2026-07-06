<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Terms of Service',
                'slug' => 'terms',
                'content' => '<h2>1. Terms</h2>
<p>By accessing this website, you are agreeing to be bound by these Terms of Service, all applicable laws and regulations, and agree that you are responsible for compliance with any applicable local laws.</p>

<h2>2. Use License</h2>
<p>Permission is granted to temporarily download one copy of the materials on our website for personal, non-commercial transitory viewing only.</p>

<h2>3. Disclaimer</h2>
<p>The materials on our website are provided on an "as is" basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property or other violation of rights.</p>

<h2>4. Limitations</h2>
<p>In no event shall we or our suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on our website.</p>

<h2>5. Revisions</h2>
<p>The materials appearing on our website could include technical, typographical, or photographic errors. We do not warrant that any of the materials on our website are accurate, complete or current.</p>',
                'meta_title' => 'Terms of Service',
                'meta_description' => 'Read our terms of service to understand the rules and regulations governing the use of our website.',
                'meta_keywords' => 'terms, service, legal, agreement',
                'status' => 'published',
                'published_at' => now(),
                'order' => 1,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => '<h2>1. Information We Collect</h2>
<p>We collect information from you when you register on our site, place an order, subscribe to our newsletter, respond to a survey or fill out a form.</p>

<h2>2. How We Use Your Information</h2>
<p>Any of the information we collect from you may be used in one of the following ways:</p>
<ul>
<li>To personalize your experience</li>
<li>To improve our website</li>
<li>To improve customer service</li>
<li>To process transactions</li>
<li>To send periodic emails</li>
</ul>

<h2>3. Information Protection</h2>
<p>We implement a variety of security measures to maintain the safety of your personal information when you place an order or enter, submit, or access your personal information.</p>

<h2>4. Cookie Usage</h2>
<p>Yes. Cookies are small files that a site or its service provider transfers to your computer\'s hard drive through your Web browser (if you allow) that enables the sites or service providers systems to recognize your browser and capture and remember certain information.</p>

<h2>5. Third Party Disclosure</h2>
<p>We do not sell, trade, or otherwise transfer to outside parties your personally identifiable information unless we provide users with advance notice.</p>

<h2>6. Your Consent</h2>
<p>By using our site, you consent to our privacy policy.</p>',
                'meta_title' => 'Privacy Policy',
                'meta_description' => 'Learn how we collect, use, and protect your personal information on our website.',
                'meta_keywords' => 'privacy, policy, data, protection, personal information',
                'status' => 'published',
                'published_at' => now(),
                'order' => 2,
            ],
            [
                'title' => 'About Us',
                'slug' => 'about',
                'content' => '<h2>Welcome to Our Company</h2>
<p>We are dedicated to providing the best service to our customers. Our team works tirelessly to ensure your satisfaction.</p>

<h2>Our Mission</h2>
<p>Our mission is to deliver exceptional value and innovation to our customers while maintaining the highest standards of integrity and professionalism.</p>

<h2>Our Values</h2>
<ul>
<li><strong>Excellence:</strong> We strive for excellence in everything we do</li>
<li><strong>Innovation:</strong> We embrace new ideas and technologies</li>
<li><strong>Integrity:</strong> We conduct business with honesty and transparency</li>
<li><strong>Customer Focus:</strong> Our customers are at the heart of our business</li>
</ul>

<h2>Contact Us</h2>
<p>Have questions? Feel free to reach out to us anytime. We\'re here to help!</p>',
                'meta_title' => 'About Us - Learn More About Our Company',
                'meta_description' => 'Discover our mission, values, and what makes us unique. Learn more about our company and team.',
                'meta_keywords' => 'about, company, mission, values, team',
                'status' => 'published',
                'published_at' => now(),
                'order' => 3,
            ],
        ];

        foreach ($pages as $pageData) {
            Page::updateOrCreate(
                ['slug' => $pageData['slug']],
                $pageData
            );
        }
    }
}
