<?php

namespace App\Helpers;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

class MarkdownHelper
{
    protected static ?MarkdownConverter $converter = null;

    /**
     * Get configured markdown converter
     */
    protected static function getConverter(): MarkdownConverter
    {
        if (self::$converter === null) {
            // Configure the Environment with all the CommonMark and GFM extensions
            $config = [
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level' => 10,
                'commonmark' => [
                    'enable_em' => true,
                    'enable_strong' => true,
                    'use_asterisk' => true,
                    'use_underscore' => true,
                ],
                'table' => [
                    'wrap' => [
                        'enabled' => false,
                    ],
                ],
            ];

            // Create environment with all extensions
            // Note: GithubFlavoredMarkdownExtension includes Table, TaskList, Strikethrough, and Autolink
            $environment = new Environment($config);
            $environment->addExtension(new CommonMarkCoreExtension());
            $environment->addExtension(new GithubFlavoredMarkdownExtension());

            self::$converter = new MarkdownConverter($environment);
        }

        return self::$converter;
    }

    /**
     * Convert markdown to HTML
     */
    public static function toHtml(string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        $converter = self::getConverter();
        $html = $converter->convert($markdown)->getContent();

        // Add custom classes for styling
        $html = self::addCustomClasses($html);

        return $html;
    }

    /**
     * Add custom CSS classes to HTML elements
     */
    protected static function addCustomClasses(string $html): string
    {
        // Add wrapper and header to code blocks with language detection
        $html = preg_replace_callback(
            '/<pre><code class="language-(\w+)">(.*?)<\/code><\/pre>/s',
            function ($matches) {
                $language = htmlspecialchars($matches[1]);
                $code = $matches[2];
                return '<div class="code-block-wrapper">'
                    . '<div class="code-header">'
                    . '<span class="code-language">' . $language . '</span>'
                    . '<button onclick="copyCode(this)" class="copy-code-btn">'
                    . '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                    . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>'
                    . '</svg>'
                    . 'Copy'
                    . '</button>'
                    . '</div>'
                    . '<pre><code class="language-' . $language . ' hljs">' . $code . '</code></pre>'
                    . '</div>';
            },
            $html
        );

        // Add wrapper and header to code blocks without language
        $html = preg_replace_callback(
            '/<pre><code>(.*?)<\/code><\/pre>/s',
            function ($matches) {
                $code = $matches[1];
                return '<div class="code-block-wrapper">'
                    . '<div class="code-header">'
                    . '<span class="code-language">code</span>'
                    . '<button onclick="copyCode(this)" class="copy-code-btn">'
                    . '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">'
                    . '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>'
                    . '</svg>'
                    . 'Copy'
                    . '</button>'
                    . '</div>'
                    . '<pre><code class="hljs">' . $code . '</code></pre>'
                    . '</div>';
            },
            $html
        );

        return $html;
    }
}
