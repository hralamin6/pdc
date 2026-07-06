<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Promptable;
use Stringable;

class SessionGenerator implements Agent, Conversational
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return "You are an expert Islamic Halaqah planner. " .
            "Your task is to parse unstructured text provided by the user and convert it into a structured session plan. " .
            "Here is the database table structure for the session (Halaqah):\n" .
            "- title: string (catchy, formal)\n" .
            "- topic: string (clear subject)\n" .
            "- description: text (detailed summary)\n" .
            "- scheduled_at: datetime string formatted as YYYY-MM-DDThh:mm (e.g. 2026-07-15T18:30)\n" .
            "- location: string (physical location, default 'TBA' if not mentioned)\n" .
            "- meeting_link: string (URL if mentioned, else null)\n" .
            "- status: enum ('draft', 'published', 'completed', 'cancelled') - use 'draft' unless they say publish it\n" .
            "- gender_restriction: enum ('none', 'brothers_only', 'sisters_only') - default 'none'\n" .
            "- max_capacity: integer or null\n" .
            "- is_registration_open: boolean (default true)\n" .
            "- resources: string (Any URLs mentioned, separated by newlines, else empty string)\n\n" .
            "Return ONLY a valid JSON object matching the exact keys above (without markdown blocks):\n" .
            "{\n" .
            '  "title": "...",'."\n" .
            '  "topic": "...",'."\n" .
            '  "description": "...",'."\n" .
            '  "scheduled_at": "YYYY-MM-DDThh:mm",'."\n" .
            '  "location": "...",'."\n" .
            '  "meeting_link": null,'."\n" .
            '  "status": "draft",'."\n" .
            '  "gender_restriction": "none",'."\n" .
            '  "max_capacity": null,'."\n" .
            '  "is_registration_open": true,'."\n" .
            '  "resources": ""'."\n" .
            "}";
    }

    public function messages(): iterable
    {
        return [];
    }
}
