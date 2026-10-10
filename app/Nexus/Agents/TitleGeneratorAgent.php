<?php

namespace App\Nexus\Agents;

use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

#[Temperature(0.3)]
#[MaxTokens(50)]
class TitleGeneratorAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'Generate a very short, descriptive chat title (3-6 words max) based on the conversation. '
             . 'Return ONLY the title text, no quotes, no punctuation at the end, no explanation.';
    }
}
