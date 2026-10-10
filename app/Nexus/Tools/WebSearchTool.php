<?php

namespace App\Nexus\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Http;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class WebSearchTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search the internet for real-time information, news, facts, and current events. Use this when the user asks about something that may require up-to-date information.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query'       => $schema->string()->required()->description('The search query to look up'),
            'max_results' => $schema->integer()->description('Maximum number of results (1-5)')->default(3),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request->get('query');
        $maxResults = min((int) $request->get('max_results', 3), 5);

        try {
            // DuckDuckGo Instant Answer API (free, no key needed)
            $response = Http::timeout(10)->get('https://api.duckduckgo.com/', [
                'q'      => $query,
                'format' => 'json',
                'no_html' => 1,
                'skip_disambig' => 1,
            ]);

            if ($response->failed()) {
                return "Search failed. Could not retrieve results for: {$query}";
            }

            $data = $response->json();
            $results = [];

            // Abstract (instant answer)
            if (!empty($data['AbstractText'])) {
                $results[] = "**Summary:** {$data['AbstractText']}";
                if (!empty($data['AbstractURL'])) {
                    $results[] = "**Source:** {$data['AbstractURL']}";
                }
            }

            // Related topics
            if (!empty($data['RelatedTopics']) && count($results) < $maxResults) {
                foreach (array_slice($data['RelatedTopics'], 0, $maxResults) as $topic) {
                    if (isset($topic['Text']) && isset($topic['FirstURL'])) {
                        $results[] = "- {$topic['Text']} ({$topic['FirstURL']})";
                    }
                }
            }

            if (empty($results)) {
                return "No direct results found for \"{$query}\". The query might be too specific or DuckDuckGo doesn't have an instant answer. Try rephrasing.";
            }

            return implode("\n", $results);
        } catch (\Exception $e) {
            return "Search error: " . $e->getMessage();
        }
    }
}
