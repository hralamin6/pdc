<?php

namespace App\Services\BotBook;

use ArrayAccess;
use JsonSerializable;

/**
 * Structured response parser inspired by Laravel AI SDK
 * Handles JSON extraction and parsing from AI responses
 */
class StructuredResponse implements ArrayAccess, JsonSerializable
{
    protected array $structured = [];
    protected string $rawResponse;
    protected ?string $error = null;

    public function __construct(string $rawResponse)
    {
        $this->rawResponse = $rawResponse;
        $this->parse();
    }

    /**
     * Parse the raw response into structured data
     */
    protected function parse(): void
    {
        // Strip HTML tags first, then decode entities
        $cleanText = strip_tags($this->rawResponse);
        $cleanText = html_entity_decode($cleanText, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Strip markdown code blocks (```json ... ```)
        $cleanText = $this->stripMarkdownCodeBlocks($cleanText);

        // Extract JSON
        $jsonString = $this->extractJson($cleanText);

        if ($jsonString === null) {
            $this->error = 'No valid JSON found in response';
            return;
        }

        // Clean control characters while preserving newlines and tabs
        $jsonString = $this->cleanJsonString($jsonString);

        // Attempt to repair JSON if it's potentially truncated
        $jsonString = $this->repairJson($jsonString);

        // Decode JSON
        $data = json_decode($jsonString, true, 512, JSON_BIGINT_AS_STRING);
        $jsonError = json_last_error();

        if ($jsonError !== JSON_ERROR_NONE) {
            $this->error = json_last_error_msg();
            \Log::error('JSON decode error', [
                'error' => json_last_error_msg(),
                'error_code' => $jsonError,
                'json_preview' => substr($jsonString, 0, 500),
                'json_end_preview' => substr($jsonString, -100)
            ]);
            return;
        }

        if (!is_array($data)) {
            $this->error = 'Decoded JSON is not an array';
            return;
        }

        $this->structured = $data;
    }

    /**
     * Repair truncated JSON by closing open strings, objects, and arrays
     */
    protected function repairJson(string $json): string
    {
        $json = trim($json);
        
        // If it already decodes, no need to repair
        if (json_decode($json) !== null) {
            return $json;
        }

        $len = strlen($json);
        $stack = [];
        $inString = false;
        $escaped = false;

        for ($i = 0; $i < $len; $i++) {
            $char = $json[$i];

            if ($escaped) {
                $escaped = false;
                continue;
            }

            if ($char === '\\') {
                $escaped = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                continue;
            }

            if (!$inString) {
                if ($char === '{' || $char === '[') {
                    $stack[] = $char;
                } elseif ($char === '}' || $char === ']') {
                    if (!empty($stack)) {
                        $last = end($stack);
                        if (($char === '}' && $last === '{') || ($char === ']' && $last === '[')) {
                            array_pop($stack);
                        }
                    }
                }
            }
        }

        // If we are still in a string, close it
        if ($inString) {
            $json .= '"';
        }

        // Close open arrays and objects in reverse order
        while (!empty($stack)) {
            $last = array_pop($stack);
            if ($last === '{') {
                $json .= '}';
            } elseif ($last === '[') {
                $json .= ']';
            }
        }

        // If after repair it still doesn't decode, it might have been cut off in the middle of a key or value
        // We can try to progressively remove the last character and re-repair until it works or we run out of string
        $tempJson = $json;
        while (strlen($tempJson) > 10 && json_decode($tempJson) === null) {
            // Remove last char before our added closures
            // This is complex, let's just use a simpler fallback: 
            // If it fails, we keep the original and let json_decode error out
            break; 
        }

        return $json;
    }

    /**
     * Strip markdown code blocks from text
     */
    protected function stripMarkdownCodeBlocks(string $text): string
    {
        // Remove ```json ... ``` or ``` ... ``` code blocks
        // Make closing fence optional in case AI doesn't close it
        $text = preg_replace('/```(?:json)?\s*\n?(.*?)(?:\n?```)?/s', '$1', $text);
        return $text;
    }

    /**
     * Extract JSON from text using simple first/last brace approach
     * Handles truncated JSON by returning from first brace to end of string
     */
    protected function extractJson(string $text): ?string
    {
        $firstBrace = strpos($text, '{');
        
        if ($firstBrace === false) {
            return null;
        }

        $lastBrace = strrpos($text, '}');

        // If no closing brace, return until the end of string (repairJson will handle closures)
        if ($lastBrace === false || $lastBrace <= $firstBrace) {
            return substr($text, $firstBrace);
        }

        return substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
    }

    /**
     * Clean JSON string by removing problematic control characters
     * Preserves newlines (\n), carriage returns (\r), and tabs (\t)
     */
    protected function cleanJsonString(string $json): string
    {
        // Only remove truly problematic control chars (NULL, etc)
        // Keep \n (0x0A), \r (0x0D), \t (0x09) and space (0x20)
        $json = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json);
        
        // Ensure proper UTF-8 encoding
        if (!mb_check_encoding($json, 'UTF-8')) {
            $json = mb_convert_encoding($json, 'UTF-8', 'UTF-8');
        }
        
        return $json;
    }

    /**
     * Check if parsing was successful
     */
    public function isValid(): bool
    {
        return $this->error === null && !empty($this->structured);
    }

    /**
     * Get error message if parsing failed
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get the structured data as array
     */
    public function toArray(): array
    {
        return $this->structured;
    }

    /**
     * Get a specific field from structured data
     */
    public function get(string $key, $default = null)
    {
        return $this->structured[$key] ?? $default;
    }

    /**
     * Check if a field exists
     */
    public function has(string $key): bool
    {
        return isset($this->structured[$key]);
    }

    /**
     * Validate that required fields exist
     */
    public function hasFields(array $requiredFields): bool
    {
        foreach ($requiredFields as $field) {
            if (!$this->has($field)) {
                return false;
            }
        }
        return true;
    }

    // ArrayAccess implementation
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->structured[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->structured[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_null($offset)) {
            $this->structured[] = $value;
        } else {
            $this->structured[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->structured[$offset]);
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return $this->structured;
    }

    /**
     * Get string representation
     */
    public function __toString(): string
    {
        return json_encode($this->structured, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
