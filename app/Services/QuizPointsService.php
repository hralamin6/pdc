<?php

namespace App\Services;

use App\Models\QuizAttempt;
use Illuminate\Support\Facades\Log;

class QuizPointsService
{
    /**
     * Award gamification points for a completed quiz attempt.
     * Called after score is calculated and rank is assigned.
     */
    public function award(QuizAttempt $attempt): int
    {
        $quiz = $attempt->quiz;
        $totalPoints = 0;

        // 1. Points for passing
        if ($attempt->passed && $quiz->points_on_pass > 0) {
            $totalPoints += $quiz->points_on_pass;
        }

        // 2. Rank bonus points (from JSON config)
        $bonusMap = $quiz->bonus_points_for_rank ?? [];
        $rank = $attempt->rank;
        if ($rank && isset($bonusMap[(string) $rank])) {
            $totalPoints += (int) $bonusMap[(string) $rank];
        }

        // 3. Perfect score bonus (+20%)
        if ($attempt->score_percentage >= 100) {
            $totalPoints = (int) ceil($totalPoints * 1.2);
        }

        // 4. Check and update streak (Activity counts towards daily streak)
        $this->updateStreak($attempt->user_id);

        if ($totalPoints <= 0) {
            return 0;
        }

        // 5. Persist points to attempt and user
        $attempt->update(['points_awarded' => $totalPoints]);

        $user = $attempt->user;
        $user->increment('gamification_points', $totalPoints);

        // 6. Log to activity system
        ActivityLogger::log(
            description: "Earned {$totalPoints} points from quiz: {$quiz->title}",
            subject: $attempt,
            properties: [
                'quiz_id' => $quiz->id,
                'quiz_title' => $quiz->title,
                'score_percentage' => $attempt->score_percentage,
                'rank' => $attempt->rank,
                'points' => $totalPoints,
            ],
            logName: 'quiz',
            event: 'points_awarded'
        );

        Log::info('QuizPointsService: Points awarded', [
            'user_id' => $attempt->user_id,
            'quiz_id' => $quiz->id,
            'points' => $totalPoints,
            'rank' => $rank,
        ]);

        return $totalPoints;
    }

    private function updateStreak(int $userId): void
    {
        $streak = \App\Models\UserStreak::firstOrCreate(
            ['user_id' => $userId],
            ['current_streak' => 0, 'longest_streak' => 0]
        );

        $today = now()->format('Y-m-d');
        $lastDate = $streak->last_report_date ? \Carbon\Carbon::parse($streak->last_report_date)->format('Y-m-d') : null;

        if ($lastDate !== $today) {
            if ($lastDate === now()->subDay()->format('Y-m-d')) {
                // Consecutive day
                $streak->increment('current_streak');
            } elseif ($lastDate !== null) {
                // Streak broken
                $streak->current_streak = 1;
            } else {
                // First ever activity
                $streak->current_streak = 1;
            }

            if ($streak->current_streak > $streak->longest_streak) {
                $streak->longest_streak = $streak->current_streak;
            }

            $streak->last_report_date = $today;
            $streak->increment('total_reports');
            $streak->save();
        }
    }
}
