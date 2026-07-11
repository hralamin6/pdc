<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use App\Models\LibraryHub;
use App\Models\User;
use Illuminate\Database\Seeder;

class P2PLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::limit(10)->get();
        if ($users->isEmpty()) {
            return;
        }

        // 1. Create a Library Hub
        $hub = LibraryHub::firstOrCreate(
            ['name' => 'PSTU Central Dawah Library'],
            [
                'location' => 'Central Mosque Library Room',
                'manager_id' => User::role('admin')->first()?->id ?? $users->first()->id,
            ]
        );

        $books = Book::where('type', 'physical')->where('status', 'approved')->get();
        if ($books->isEmpty()) {
            return;
        }

        // 2. Add Book Copies to Users and Hub
        foreach ($books as $book) {
            // Give a copy to the hub
            BookCopy::firstOrCreate([
                'book_id' => $book->id,
                'library_hub_id' => $hub->id,
            ], [
                'owner_id' => null,
                'status' => 'available',
                'is_borrowable' => true,
                'condition' => 'Good',
            ]);

            // Give a copy to random users
            foreach ($users->random(3) as $user) {
                BookCopy::firstOrCreate([
                    'book_id' => $book->id,
                    'owner_id' => $user->id,
                ], [
                    'library_hub_id' => null,
                    'status' => 'available',
                    'is_borrowable' => true,
                    'condition' => collect(['New', 'Good', 'Fair'])->random(),
                ]);
            }
        }

        // 3. Create Borrow Requests
        $copies = BookCopy::whereNotNull('owner_id')->get();
        $copiesToSeed = min(5, $copies->count());
        foreach ($copies->random($copiesToSeed) as $copy) {
            $borrower = $users->where('id', '!=', $copy->owner_id)->random();

            $statuses = ['pending', 'accepted', 'active', 'returned'];
            $status = collect($statuses)->random();

            $req = BorrowRequest::firstOrCreate([
                'borrower_id' => $borrower->id,
                'book_copy_id' => $copy->id,
            ], [
                'requested_days' => collect([7, 14, 30])->random(),
                'status' => $status,
                'due_date' => in_array($status, ['active', 'returned']) ? now()->addDays(7) : null,
                'returned_at' => $status === 'returned' ? now() : null,
            ]);

            if (in_array($status, ['accepted', 'given', 'active'])) {
                $copy->update(['status' => 'borrowed']);
            }
        }

        $this->command->info('P2P Physical Library seeded successfully!');
    }
}
