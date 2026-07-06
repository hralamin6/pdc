<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslationService
{
    protected string $langPath;

    protected array $supportedLanguages = ['en', 'bn', 'ar'];

    public function __construct()
    {
        $this->langPath = lang_path();
    }

    /**
     * Get all available languages
     */
    public function getLanguages(): array
    {
        $languages = [];
        $files = File::glob($this->langPath.'/*.json');

        foreach ($files as $file) {
            $code = pathinfo($file, PATHINFO_FILENAME);
            $languages[] = [
                'code' => $code,
                'name' => $this->getLanguageName($code),
                'file' => basename($file),
                'path' => $file,
            ];
        }

        return $languages;
    }

    /**
     * Get language name from code
     */
    public function getLanguageName(string $code): string
    {
        $names = [
            'en' => 'English',
            'bn' => 'বাংলা (Bangla)',
            'ar' => 'العربية (Arabic)',
            'es' => 'Español (Spanish)',
            'fr' => 'Français (French)',
            'de' => 'Deutsch (German)',
            'hi' => '��िन्दी (Hindi)',
            'ur' => 'اردو (Urdu)',
        ];

        return $names[$code] ?? strtoupper($code);
    }

    /**
     * Get all translations for a language
     */
    public function getTranslations(string $lang): array
    {
        $file = $this->langPath."/{$lang}.json";

        if (! File::exists($file)) {
            return [];
        }

        $content = File::get($file);

        return json_decode($content, true) ?? [];
    }

    /**
     * Save translations for a language
     */
    public function saveTranslations(string $lang, array $translations): bool
    {
        $file = $this->langPath."/{$lang}.json";

        ksort($translations);
        $json = json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return File::put($file, $json) !== false;
    }

    /**
     * Add a new language
     */
    public function addLanguage(string $code, array $translations = []): bool
    {
        $file = $this->langPath."/{$code}.json";

        if (File::exists($file)) {
            return false;
        }

        // If no translations provided, copy from English
        if (empty($translations)) {
            $translations = $this->getTranslations('en');
        }

        return $this->saveTranslations($code, $translations);
    }

    /**
     * Delete a language
     */
    public function deleteLanguage(string $code): bool
    {
        if ($code === 'en') {
            return false; // Protect English
        }

        $file = $this->langPath."/{$code}.json";

        if (File::exists($file)) {
            return File::delete($file);
        }

        return false;
    }

    /**
     * Update a single translation key
     */
    public function updateTranslation(string $lang, string $key, string $value): bool
    {
        $translations = $this->getTranslations($lang);
        $translations[$key] = $value;

        return $this->saveTranslations($lang, $translations);
    }

    /**
     * Add a new key to all languages
     */
    public function addKey(string $key, array $values = []): bool
    {
        $languages = $this->getLanguages();

        foreach ($languages as $lang) {
            $translations = $this->getTranslations($lang['code']);

            if (! isset($translations[$key])) {
                $translations[$key] = $values[$lang['code']] ?? $key;
                $this->saveTranslations($lang['code'], $translations);
            }
        }

        return true;
    }

    /**
     * Delete a key from all languages
     */
    public function deleteKey(string $key): bool
    {
        $languages = $this->getLanguages();

        foreach ($languages as $lang) {
            $translations = $this->getTranslations($lang['code']);

            if (isset($translations[$key])) {
                unset($translations[$key]);
                $this->saveTranslations($lang['code'], $translations);
            }
        }

        return true;
    }

    /**
     * Get translation statistics
     */
    public function getStatistics(): array
    {
        $stats = [];
        $languages = $this->getLanguages();
        $baseKeys = array_keys($this->getTranslations('en'));
        $totalKeys = count($baseKeys);

        foreach ($languages as $lang) {
            $translations = $this->getTranslations($lang['code']);
            $translatedKeys = 0;
            $missingKeys = [];

            foreach ($baseKeys as $key) {
                if (isset($translations[$key]) && ! empty($translations[$key]) && $translations[$key] !== $key) {
                    $translatedKeys++;
                } else {
                    $missingKeys[] = $key;
                }
            }

            $stats[$lang['code']] = [
                'language' => $lang['name'],
                'total_keys' => count($translations),
                'translated' => $translatedKeys,
                'missing' => count($missingKeys),
                'missing_keys' => $missingKeys,
                'percentage' => $totalKeys > 0 ? round(($translatedKeys / $totalKeys) * 100, 2) : 0,
            ];
        }

        return $stats;
    }

    /**
     * Scan application files for translation keys
     */
    public function scanForKeys(): array
    {
        $foundKeys = [];
        $patterns = [
            '/__\([\'"]([^\'"]+)[\'"]\)/',
            '/trans\([\'"]([^\'"]+)[\'"]\)/',
            '/@lang\([\'"]([^\'"]+)[\'"]\)/',
            '/{{[\s]*__\([\'"]([^\'"]+)[\'"]\)[\s]*}}/',
        ];

        $paths = [
            app_path(),
            resource_path('views'),
        ];

        foreach ($paths as $path) {
            $files = File::allFiles($path);

            foreach ($files as $file) {
                if (! in_array($file->getExtension(), ['php', 'blade.php'])) {
                    continue;
                }

                $content = File::get($file->getPathname());

                foreach ($patterns as $pattern) {
                    preg_match_all($pattern, $content, $matches);

                    if (! empty($matches[1])) {
                        foreach ($matches[1] as $key) {
                            $foundKeys[$key] = ($foundKeys[$key] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        return $foundKeys;
    }

    /**
     * Sync found keys with translation files
     */
    public function syncKeys(array $foundKeys): array
    {
        $languages = $this->getLanguages();
        $added = [];

        foreach ($languages as $lang) {
            $translations = $this->getTranslations($lang['code']);

            foreach ($foundKeys as $key => $count) {
                if (! isset($translations[$key])) {
                    $translations[$key] = $lang['code'] === 'en' ? $key : '';
                    $added[$lang['code']][] = $key;
                }
            }

            $this->saveTranslations($lang['code'], $translations);
        }

        return $added;
    }

    /**
     * Export translations to JSON
     */
    public function exportLanguage(string $lang): string
    {
        $translations = $this->getTranslations($lang);

        return json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Import translations from JSON
     */
    public function importLanguage(string $lang, string $json): bool
    {
        $translations = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON format: '.json_last_error_msg());
        }

        return $this->saveTranslations($lang, $translations);
    }

    /**
     * Get AI translation suggestion using Google Translate
     */
    public function getAITranslation(string $text, string $targetLang, string $sourceLang = 'en'): ?string
    {
        try {
            $translator = new GoogleTranslate;
            $translator->setSource($sourceLang);
            $translator->setTarget($targetLang);

            return $translator->translate($text);
        } catch (\Exception $e) {
            \Log::error('Translation error: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Auto-translate missing keys for a language
     */
    public function autoTranslate(string $targetLang, array $keys = []): array
    {
        $sourceTranslations = $this->getTranslations('en');
        $targetTranslations = $this->getTranslations($targetLang);
        $translated = [];

        $keysToTranslate = $keys ?: array_keys($sourceTranslations);

        foreach ($keysToTranslate as $key) {
            if (! isset($sourceTranslations[$key])) {
                continue;
            }

            // Skip already translated keys
            if (isset($targetTranslations[$key]) && ! empty($targetTranslations[$key]) && $targetTranslations[$key] !== $key) {
                continue;
            }

            $sourceText = $sourceTranslations[$key];

            // Skip empty source text
            if (empty($sourceText)) {
                continue;
            }

            try {
                $translation = $this->getAITranslation($sourceText, $targetLang);

                if ($translation && $translation !== $sourceText) {
                    $targetTranslations[$key] = $translation;
                    $translated[$key] = $translation;

                    \Log::info('Translated key', [
                        'key' => $key,
                        'source' => $sourceText,
                        'target' => $translation,
                        'language' => $targetLang,
                    ]);
                } else {
                    \Log::warning('Translation returned empty or same text', [
                        'key' => $key,
                        'source' => $sourceText,
                        'language' => $targetLang,
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Translation failed for key', [
                    'key' => $key,
                    'language' => $targetLang,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if (! empty($translated)) {
            $this->saveTranslations($targetLang, $targetTranslations);
            \Log::info('Auto-translation completed', [
                'language' => $targetLang,
                'count' => count($translated),
            ]);
        }

        return $translated;
    }

    /**
     * Search translations by key or value
     */
    public function search(string $query, ?string $lang = null): array
    {
        $results = [];
        $languages = $lang ? [['code' => $lang]] : $this->getLanguages();

        foreach ($languages as $language) {
            $translations = $this->getTranslations($language['code']);

            foreach ($translations as $key => $value) {
                if (
                    Str::contains(strtolower($key), strtolower($query)) ||
                    Str::contains(strtolower($value), strtolower($query))
                ) {
                    $results[$key][$language['code']] = $value;
                }
            }
        }

        return $results;
    }
}
