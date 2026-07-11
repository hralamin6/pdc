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
        $users = User::limit(10)->get();

        if (! $adminUser || $users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');

            return;
        }

        // 1. Categories
        $categories = [
            ['name' => 'Tafseer (Exegesis)', 'icon' => 'o-book-open'],
            ['name' => 'Hadith', 'icon' => 'o-chat-bubble-bottom-center-text'],
            ['name' => 'Seerah (Biography)', 'icon' => 'o-user'],
            ['name' => 'Fiqh (Jurisprudence)', 'icon' => 'o-scale'],
            ['name' => 'Aqeedah (Theology)', 'icon' => 'o-heart'],
        ];

        foreach ($categories as $cat) {
            BookCategory::firstOrCreate(
                ['slug' => Str::slug($cat['name'])],
                ['name' => $cat['name'], 'icon' => $cat['icon']]
            );
        }

        // 2. Authors
        $authors = [
            ['name' => 'Imam Ibn Kathir', 'bio' => 'A renowned Islamic scholar, historian, and exegete.'],
            ['name' => 'Imam An-Nawawi', 'bio' => 'A prominent Sunni Muslim scholar and jurist.'],
            ['name' => 'Safiur Rahman Mubarakpuri', 'bio' => 'Author of The Sealed Nectar (Ar-Raheeq Al-Makhtum).'],
            ['name' => 'Imam Al-Ghazali', 'bio' => 'A highly influential philosopher, theologian, and mystic.'],
        ];

        foreach ($authors as $author) {
            Author::firstOrCreate(
                ['slug' => Str::slug($author['name'])],
                ['name' => $author['name'], 'bio' => $author['bio']]
            );
        }

        // 3. Publications
        $publications = [
            ['name' => 'Darussalam Publications', 'description' => 'A global leader in Islamic publishing.'],
            ['name' => 'IIPH (International Islamic Publishing House)', 'description' => 'Publishing authentic Islamic books in English.'],
            ['name' => 'Kube Publishing', 'description' => 'Independent Islamic publisher.'],
        ];

        foreach ($publications as $pub) {
            Publication::firstOrCreate(
                ['slug' => Str::slug($pub['name'])],
                ['name' => $pub['name'], 'description' => $pub['description']]
            );
        }

        // Get IDs
        $cats = BookCategory::all();
        $auths = Author::all();
        $pubs = Publication::all();

        // 4. Books
        $booksData = [
            [
                'title' => 'Tafsir Ibn Kathir (Abridged)',
                'type' => 'ebook',
                'description' => 'The most renowned and accepted explanation of the Quran.',
                'status' => 'approved',
                'pages_count' => 840,
                'isbn' => '978-9960-892-71-9',
                'publication_year' => '2000',
            ],
            [
                'title' => 'Riyad-us-Saliheen',
                'type' => 'ebook',
                'description' => 'Gardens of the Righteous, compiled by Imam An-Nawawi.',
                'status' => 'approved',
                'pages_count' => 1100,
                'isbn' => '978-2-02-049876-8',
                'publication_year' => '1999',
            ],
            [
                'title' => 'The Sealed Nectar (Ar-Raheeq Al-Makhtum)',
                'type' => 'physical',
                'description' => 'A complete authoritative book on the life of Prophet Muhammad (S).',
                'status' => 'approved',
                'pages_count' => 588,
                'isbn' => '978-1591440710',
                'publication_year' => '2002',
            ],
            [
                'title' => 'Fortress of the Muslim',
                'type' => 'ebook',
                'description' => 'Invocations from the Quran and Sunnah.',
                'status' => 'approved',
                'pages_count' => 250,
                'isbn' => '978-1234567890',
                'publication_year' => '1995',
            ],
            [
                'title' => 'Purification of the Heart',
                'type' => 'physical',
                'description' => 'A translation of Imam al-Mawlud\'s poem with commentary.',
                'status' => 'pending',
                'pages_count' => 200,
                'isbn' => '978-1929694150',
                'publication_year' => '2004',
            ],
        ];

        foreach ($booksData as $data) {
            $book = Book::firstOrNew(['slug' => Str::slug($data['title'])]);
            if (! $book->exists) {
                $book->fill([
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'description' => $data['description'],
                    'status' => $data['status'],
                    'pages_count' => $data['pages_count'],
                    'isbn' => $data['isbn'],
                    'publication_year' => $data['publication_year'],
                    'uploaded_by' => $users->random()->id,
                    'book_category_id' => $cats->random()->id,
                    'author_id' => $auths->random()->id,
                    'publication_id' => $pubs->random()->id,
                ]);
                $book->save();
            }
        }

        // 5. User Interactions
        $allBooks = Book::where('status', 'approved')->get();
        foreach ($allBooks as $book) {
            foreach ($users->random(3) as $user) {
                $statuses = ['want_to_read', 'reading', 'completed'];
                $status = $statuses[array_rand($statuses)];

                BookUserInteraction::firstOrCreate(
                    ['user_id' => $user->id, 'book_id' => $book->id],
                    [
                        'reading_status' => $status,
                        'rating' => rand(3, 5),
                        'review' => rand(0, 1) ? 'This book completely changed my perspective. Highly recommended!' : null,
                        'pages_read' => $status === 'completed' ? $book->pages_count : rand(10, $book->pages_count - 10),
                    ]
                );
            }
        }

        $this->command->info('Book Library seeded successfully!');
    }
}
