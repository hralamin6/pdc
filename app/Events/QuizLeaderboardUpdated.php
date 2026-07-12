<?php

namespace App\Events;

use App\Models\Quiz;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizLeaderboardUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  array<int, array{rank: int, user_id: int, name: string, avatar: ?string, score: float, time_taken: int}>  $leaderboard
     */
    public function __construct(
        public Quiz $quiz,
        public array $leaderboard
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("quiz.{$this->quiz->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LeaderboardUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'quiz_id' => $this->quiz->id,
            'leaderboard' => $this->leaderboard,
            'total_participants' => count($this->leaderboard),
        ];
    }
}
