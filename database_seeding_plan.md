# Database Seeding Plan & Analysis

This document details the current state of database seeders for the **TALL Kit** application, identifies gaps in the current implementation, and proposes a complete seeding strategy to enable comprehensive end-to-end testing across all features.

---

## 1. Seeder Coverage & Gaps Analysis

We have analyzed all files in `app/Models/` and matched them against existing classes in `database/seeders/`. Below is the coverage map of all application models:

| Module / Feature | Model | Database Table | Seeder Status | Seeded Content / Gaps |
| :--- | :--- | :--- | :--- | :--- |
| **System & Auth** | `User` | `users` | **Partial** | Seeds `admin@mail.com`, `user@mail.com`, and 10 factory users. Gaps: No dedicated credentials for `accountant` or `mentor` roles. |
| | `UserDetail` | `user_details` | **Missing** | **No profiles seeded.** Active users lack phone, bio, address, socials, or geographical linkage. |
| | `Setting` | `settings` | **Complete** | Seeds basic configuration parameters. |
| **Locations** | `Division`, `District`, `Upazila`, `Union` | Multiple | **Complete** | Seeds full geographic lookup data for Bangladesh. |
| **Donations Portal** | `DonationCampaign` | `donation_campaigns` | **Missing** | **No campaigns seeded.** Gaps: Donations cannot link to campaigns; campaigns index is empty. |
| | `DonationCampaignFaq` | `donation_campaign_faqs` | **Missing** | **No FAQs seeded.** |
| | `DonationCampaignUpdate` | `donation_campaign_updates`| **Missing** | **No campaign updates seeded.** |
| | `DonationPledge` | `donation_pledges` | **Missing** | **No pledges seeded.** Inability to test pledge confirmation workflow. |
| **Treasury & Finance** | `BankAccount` | `bank_accounts` | **Complete** | Seeds Cash, bKash, Nagad, and DBBL accounts. |
| | `ExpenseCategory` | `expense_categories` | **Complete** | Seeds 5 default expense categories. |
| | `Donation` | `donations` | **Partial** | Seeds 20 donations, but without campaign linkages. |
| | `Expense` | `expenses` | **Complete** | Seeds 15 random expense entries. |
| | `FundTransfer` | `fund_transfers` | **Missing** | **No transfers seeded.** Cannot view internal account transfers. |
| | `MonthlyTreasuryReport` | `monthly_treasury_reports`| **Missing** | **No pre-compiled reports seeded.** |
| **Halaqah Circles** | `HalaqahSeries` | `halaqah_series` | **Complete** | Seeds 2 Tafseer & Aqeedah series. |
| | `Halaqah` | `halaqahs` | **Complete** | Seeds 10 sessions (completed, upcoming, draft, cancelled). |
| | `HalaqahAttendance` | `halaqah_attendances` | **Complete** | Seeds student RSVPs, waitlists, and attendance. |
| **P2P Library** | `LibraryHub` | `library_hubs` | **Complete** | Seeds `PSTU Central Dawah Library`. |
| | `BookCategory`, `Author`, `Publication` | Multiple | **Complete** | Seeds metadata tables. |
| | `Book` | `books` | **Complete** | Seeds 5 ebooks and physical books. |
| | `BookCopy` | `book_copies` | **Complete** | Seeds hub and user copies. |
| | `BorrowRequest` | `borrow_requests` | **Complete** | Seeds 5 borrow requests (pending, active, returned). |
| | `BookUserInteraction` | `book_user_interactions`| **Complete** | Seeds ratings, reviews, and reading states. |
| **Quiz & Gamification** | `Quiz` | `quizzes` | **Partial** | Seeds 1 Async (General Knowledge) and 1 Live Quiz in Bangla. |
| | `QuizQuestion`, `QuizOption` | Multiple | **Complete** | Seeds questions (MCQ, True/False, Short Text) & options. |
| | `QuizAttempt` | `quiz_attempts` | **Missing** | **No attempts seeded.** Admin cannot view leaderboard or test grading. |
| | `QuizAnswer` | `quiz_answers` | **Missing** | **No answers seeded.** |
| **Daily Habit Reports**| `DailyReportTemplate` | `daily_report_templates`| **Complete** | Seeds 17 default habits (Prayers, Quran, Dawah, Slept Early). |
| | `DailyReport` | `daily_reports` | **Missing** | **No daily reports seeded.** Users see empty history charts. |
| | `DailyReportEntry` | `daily_report_entries` | **Missing** | **No entries seeded.** |
| | `UserStreak` | `user_streaks` | **Missing** | **No streaks seeded.** Cannot test gamified streaks/points logic. |
| **Messenger & AI** | `Conversation`, `Message` | Multiple | **Partial** | Seeds one conversation between 2 users. |
| | `MessageReaction` | `message_reactions` | **Complete** | Seeds sample message reactions. |
| | `AiConversation`, `AiMessage`| Multiple | **Missing** | **No AI assistant chat history seeded.** |
| **Community Feed** | `Post`, `Comment`, `PostReaction` | Multiple | **Complete** | Seeds feed posts, comments, and reactions. |
| **Miscellaneous** | `Feedback` | `feedback` | **Missing** | **No feedback (Nasiha) seeded.** Nasiha inbox is empty. |
| | `GalleryAlbum` | `gallery_albums` | **Missing** | **No showcase albums seeded.** Gallery is empty. |
| | `GuestSubscription` | `guest_subscriptions` | **Missing** | **No newsletter subscriptions seeded.** |

---

## 2. Proposed Seeding Strategy & Features

To deliver a fully cohesive testing environment, we propose creating/updating the following seeders:

### A. Dedicated Role Accounts & Profiles
*   Create distinct test credentials for each application role:
    *   **Super Admin**: `superadmin@mail.com` (full power)
    *   **Admin**: `admin@mail.com` (general management)
    *   **Accountant**: `accountant@mail.com` (finances & treasury)
    *   **Mentor**: `mentor@mail.com` (halaqahs, quizzes)
    *   **Regular User**: `user@mail.com` (student, reader, donor)
*   Seed realistic `UserDetail` records for all 5 roles plus generated users, including:
    *   Randomized phone numbers, occupations, bios, and links.
    *   Geographical assignments (matching seeded divisions/districts/upazilas/unions).

### B. Complete Donations Portal Data
*   Seed **Donation Campaigns** with rich Bangla/English titles (e.g., "PSTU Mosque Library Expansion", "Winter Sadaqah Project", "Ramadan Iftar Distribution").
*   Seed **Campaign FAQs** (e.g., "How will the funds be used?", "Can I donate in cash?") and **Campaign Updates** (milestone progress logs with images).
*   Create **Donation Pledges** in various states (Pending, Completed, Cancelled).
*   Update `FinanceSeeder` to link generated donations directly to these campaigns.

### C. Daily Habit Reports, Entries, and Streaks
*   Generate **Daily Reports** and **Daily Report Entries** for multiple users (e.g., `user@mail.com`, `mentor@mail.com`) spanning the past 15 to 30 days.
*   Simulate random completion of habits (e.g., praying all Salah, reading Quran pages).
*   Seed **UserStreak** records (current streak, longest streak, last activity timestamp) to populate dashboard progress widgets.

### D. Quiz Attempts & Live Quiz Leaderboards
*   Generate **QuizAttempts** for both the Async and Live Quizzes.
*   Seed **QuizAnswers** containing correct/incorrect responses for MCQs and text-based answers for Short Text questions.
*   Leave some Short Text answers uncompleted (Pending Grading) so mentors can test the `/app/quizzes/grade` panel.
*   Distribute gamification points to users based on passed quizzes and rank.

### E. AI Chat History
*   Seed sample **AiConversation** and **AiMessage** threads (e.g., user asking Islamic history questions, and the AI agent responding).

### F. Treasury Transfers & Reports
*   Seed **FundTransfer** records between Cash, bKash, and Bank Accounts.
*   Generate pre-compiled **MonthlyTreasuryReport** records for previous months.

### G. Showcase Gallery, Anonymous Nasiha & Subscriptions
*   Seed **GalleryAlbum** items with realistic description copy.
*   Seed **Feedback** records representing anonymous advice/inquiries from users.
*   Seed a list of **GuestSubscriptions** emails.
