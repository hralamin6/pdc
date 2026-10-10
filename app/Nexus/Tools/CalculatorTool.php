<?php

namespace App\Nexus\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CalculatorTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Evaluate mathematical expressions and perform calculations. Supports arithmetic, algebra, percentages, and basic math functions.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'expression' => $schema->string()->required()->description('The mathematical expression to evaluate (e.g., "2 + 2", "15% of 200", "sqrt(144)")'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $expression = trim($request->get('expression'));

        try {
            // Pre-process common patterns
            $expr = $this->preProcess($expression);

            // Safe eval using tokenizer approach
            $result = $this->safeEval($expr);

            if ($result === false || $result === null) {
                return "Could not evaluate: {$expression}";
            }

            // Format result
            if (is_float($result) && floor($result) == $result && abs($result) < PHP_INT_MAX) {
                $formatted = number_format($result, 0);
            } else {
                $formatted = is_float($result) ? rtrim(rtrim(number_format($result, 10), '0'), '.') : $result;
            }

            return "Result: **{$formatted}**\nExpression: `{$expression}`";
        } catch (\Throwable $e) {
            return "Calculation error: {$e->getMessage()}. Please provide a valid math expression.";
        }
    }

    private function preProcess(string $expr): string
    {
        // Handle "X% of Y" patterns
        $expr = preg_replace_callback('/(\d+\.?\d*)\s*%\s*of\s*(\d+\.?\d*)/i', function ($m) {
            return "({$m[1]}/100*{$m[2]})";
        }, $expr);

        // Replace ^ with ** for exponentiation
        $expr = str_replace('^', '**', $expr);

        // Replace common math functions
        $expr = preg_replace('/\bsqrt\b/', 'sqrt', $expr);
        $expr = preg_replace('/\babs\b/', 'abs', $expr);
        $expr = preg_replace('/\bpi\b/i', M_PI, $expr);
        $expr = preg_replace('/\be\b/', M_E, $expr);

        return $expr;
    }

    private function safeEval(string $expr): mixed
    {
        // Only allow safe characters
        if (!preg_match('/^[\d\s\+\-\*\/\.\(\)\%\*\,\_]+$/', str_replace(['sqrt', 'abs', 'log', 'ceil', 'floor', 'round', 'min', 'max', 'pow', 'pi', 'M_PI', 'M_E'], '', $expr))) {
            throw new \InvalidArgumentException('Expression contains invalid characters.');
        }

        // Safe math functions whitelist
        $allowed = ['sqrt', 'abs', 'log', 'ceil', 'floor', 'round', 'min', 'max', 'pow'];
        $pattern = '/\b(?!' . implode('|', $allowed) . '\b)[a-zA-Z_]\w*/';
        if (preg_match($pattern, $expr)) {
            throw new \InvalidArgumentException('Unknown function in expression.');
        }

        return eval("return ({$expr});");
    }
}
