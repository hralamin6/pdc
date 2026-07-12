<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\BorrowRequest;
use App\Models\LibraryHub;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class P2PLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $users = User::role('user')->get();
        if ($users->isEmpty()) {
            $users = User::all();
        }

        $manager = User::role(['super-admin', 'admin', 'accountant'])->first() ?? User::first();

        if ($users->isEmpty() || !$manager) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        // 1. Create a Library Hub (PSTU Central Dawah Library in Bangla)
        $hub = LibraryHub::updateOrCreate(
            ['name' => 'পিএসটিইউ কেন্দ্রীয় দাওয়াহ লাইব্রেরি'],
            [
                'location' => 'কেন্দ্রীয় মসজিদ সংলগ্ন লাইব্রেরি কক্ষ',
                'manager_id' => $manager->id,
            ]
        );

        $physicalBooks = Book::where('type', 'physical')->where('status', 'approved')->get();
        if ($physicalBooks->isEmpty()) {
            $this->command->warn('No approved physical books found to seed copies.');
            return;
        }

        // 2. Add Book Copies to Hub and Users
        $conditions = ['নতুন (Intact)', 'ভালো (Good)', 'সাধারণ (Fair)'];

        foreach ($physicalBooks as $book) {
            // Seed 1-2 copies for the Hub
            $hubCopyCount = rand(1, 2);
            for ($c = 0; $c < $hubCopyCount; $c++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'library_hub_id' => $hub->id,
                    'owner_id' => null,
                    'status' => 'available',
                    'is_borrowable' => true,
                    'condition' => $conditions[0], // Hub copies are usually new/intact
                    'added_by' => $manager->id,
                ]);
            }

            // Seed 2 copies owned by random students
            $selectedStudents = $users->random(min(2, $users->count()));
            foreach ($selectedStudents as $student) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'library_hub_id' => null,
                    'owner_id' => $student->id,
                    'status' => 'available',
                    'is_borrowable' => true,
                    'condition' => collect($conditions)->random(),
                    'added_by' => $student->id,
                ]);
            }
        }

        // 3. Create Borrow Requests in various states
        $allCopies = BookCopy::all();
        $borrowStatuses = ['pending', 'accepted', 'rejected', 'given', 'active', 'returned'];

        // Let's generate 10 realistic borrow requests
        for ($i = 0; $i < 10; $i++) {
            $copy = $allCopies->random();
            
            // Borrower should not be the owner of the copy or the manager of the hub
            if ($copy->owner_id) {
                $borrower = $users->where('id', '!=', $copy->owner_id)->random();
            } else {
                $borrower = $users->random();
            }

            // Check if there is already a pending or active borrow request for this borrower and copy to avoid duplicate key errors
            $existing = BorrowRequest::where('borrower_id', $borrower->id)
                ->where('book_copy_id' , $copy->id)
                ->first();
            if ($existing) {
                continue;
            }

            $status = collect($borrowStatuses)->random();
            $requestedDays = collect([7, 10, 15, 30])->random();
            
            $dueDate = null;
            $returnedAt = null;

            if (in_array($status, ['given', 'active'])) {
                $dueDate = Carbon::now()->addDays($requestedDays - rand(1, 5));
                $copy->update(['status' => 'borrowed']);
            } elseif ($status === 'returned') {
                $dueDate = Carbon::now()->subDays(rand(1, 10));
                $returnedAt = Carbon::parse($dueDate)->subDays(rand(0, 3));
                $copy->update(['status' => 'available']);
            } elseif ($status === 'accepted') {
                $dueDate = Carbon::now()->addDays($requestedDays);
            }

            BorrowRequest::create([
                'borrower_id' => $borrower->id,
                'book_copy_id' => $copy->id,
                'requested_days' => $requestedDays,
                'status' => $status,
                'due_date' => $dueDate,
                'returned_at' => $returnedAt,
            ]);
        }

        $this->command->info('P2P Physical Library seeded successfully!');
    }
}
