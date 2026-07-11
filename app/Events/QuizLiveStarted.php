<?php

namespace App\Events;

use App\Models\Quiz;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QuizLiveStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Quiz $quiz) {}

    /**
     * Channel: quiz.{id} — all participants listen here.
     *
     * @return Channel[]
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("quiz.{$this->quiz->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'QuizLiveStarted';
    }

    public function broadcastWith(): array
    {
        return [
            'quiz_id' => $this->quiz->id,
            'started_at' => now()->toISOString(),
            'time_limit_minutes' => $this->quiz->time_limit_minutes,
            'question_count' => $this->quiz->questions()->count(),
        ];
    }
}
