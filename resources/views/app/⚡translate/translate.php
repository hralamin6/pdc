<?php


use App\Jobs\AutoTranslateJob;
use App\Services\TranslationService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

new #[Title('Translate')] #[Layout('layouts.app')] class extends Component
{
    use Toast, WithPagination;

    public string $search = '';

    public string $selectedLanguage = '';

    public string $viewMode = 'all';

    public array $translations = [];

    public array $languages = [];

    public array $statistics = [];

    public array $editableTranslations = [];

    public int $perPage = 10;

    // Modals
    public bool $addKeyModal = false;

    public bool $addLanguageModal = false;

    public bool $importModal = false;

    public bool $scanModal = false;

    public bool $aiTranslateModal = false;

    // Form fields
    public string $newKey = '';

    public array $newKeyValues = [];

    public string $newLanguageCode = '';

    public string $newLanguageName = '';

    public string $importJson = '';

    public string $importLanguage = '';

    public array $scannedKeys = [];

    public string $aiTargetLanguage = '';

    protected TranslationService $translationService;

    public function boot(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function mount()
    {
        $this->authorize('translations.view');
        $this->loadLanguages();
        $this->loadStatistics();
        $this->selectedLanguage = '';
    }

    public function loadLanguages()
    {
        $this->languages = $this->translationService->getLanguages();
    }

    public function loadStatistics()
    {
        $this->statistics = $this->translationService->getStatistics();
    }

    public function offset()
    {
        return ($this->getPage() - 1) * $this->perPage;
    }

    #[Computed]
    public function totalFilteredCount()
    {
        $baseTranslations = $this->translationService->getTranslations('en');
        $count = 0;

        foreach ($baseTranslations as $key => $value) {
            if ($this->search &&
                ! str_contains(strtolower($key), strtolower($this->search)) &&
                ! str_contains(strtolower($value), strtolower($this->search))
            ) {
                continue;
            }

            if ($this->viewMode === 'missing' && $this->selectedLanguage) {
                $langTranslations = $this->translationService->getTranslations($this->selectedLanguage);
                $translation = $langTranslations[$key] ?? '';
                if (! empty($translation) && $translation !== $key) {
                    continue;
                }
            } elseif ($this->viewMode === 'translated' && $this->selectedLanguage) {
                $langTranslations = $this->translationService->getTranslations($this->selectedLanguage);
                $translation = $langTranslations[$key] ?? '';
                if (empty($translation) || $translation === $key) {
                    continue;
                }
            }

            $count++;
        }

        return $count;
    }

    #[Computed]
    public function filteredTranslations()
    {
        $baseTranslations = $this->translationService->getTranslations('en');
        $results = [];
        $currentIndex = 0;
        $startIndex = $this->offset();
        $endIndex = $startIndex + $this->perPage;

        foreach ($baseTranslations as $key => $value) {
            if ($this->search &&
                ! str_contains(strtolower($key), strtolower($this->search)) &&
                ! str_contains(strtolower($value), strtolower($this->search))
            ) {
                continue;
            }

            $row = ['key' => $key];

            foreach ($this->languages as $lang) {
                $langTranslations = $this->translationService->getTranslations($lang['code']);
                $row[$lang['code']] = $langTranslations[$key] ?? '';

                // Initialize editable translations on the fly
                if (! isset($this->editableTranslations[$key][$lang['code']])) {
                    $this->editableTranslations[$key][$lang['code']] = $row[$lang['code']];
                }
            }

            if ($this->viewMode === 'missing' && $this->selectedLanguage) {
                if (! empty($row[$this->selectedLanguage]) && $row[$this->selectedLanguage] !== $key) {
                    continue;
                }
            } elseif ($this->viewMode === 'translated' && $this->selectedLanguage) {
                if (empty($row[$this->selectedLanguage]) || $row[$this->selectedLanguage] === $key) {
                    continue;
                }
            }

            // Only add items within the current page range
            if ($currentIndex >= $startIndex && $currentIndex < $endIndex) {
                $results[] = $row;
            }

            $currentIndex++;

            // Stop processing once we have enough items
            if (count($results) >= $this->perPage) {
                break;
            }
        }

        return $results;
    }

    public function saveAllTranslations()
    {
        try {
            $savedCount = 0;

            foreach ($this->editableTranslations as $key => $langValues) {
                foreach ($langValues as $lang => $value) {
                    $currentTranslations = $this->translationService->getTranslations($lang);
                    $currentValue = $currentTranslations[$key] ?? '';

                    if ($value !== $currentValue) {
                        $this->translationService->updateTranslation($lang, $key, $value);
                        $savedCount++;
                    }
                }
            }

            $this->loadStatistics();

            if ($savedCount > 0) {
                $this->success("Successfully saved {$savedCount} translation(s)!");
            } else {
                $this->info('No changes to save.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to save translations: '.$e->getMessage());
        }
    }

    public function openAddKeyModal()
    {
        $this->newKey = '';
        $this->newKeyValues = [];
        foreach ($this->languages as $lang) {
            $this->newKeyValues[$lang['code']] = '';
        }
        $this->addKeyModal = true;
    }

    public function closeAddKeyModal()
    {
        $this->addKeyModal = false;
        $this->reset(['newKey', 'newKeyValues']);
    }

    public function saveNewKey()
    {
        $this->validate(['newKey' => 'required|string']);

        try {
            foreach ($this->newKeyValues as $lang => $value) {
                $this->translationService->updateTranslation($lang, $this->newKey, $value);
            }
            $this->loadStatistics();
            $this->addKeyModal = false;
            $this->success('New translation key added successfully!');
            $this->reset(['newKey', 'newKeyValues']);
        } catch (\Exception $e) {
            $this->error('Failed to add key: '.$e->getMessage());
        }
    }

    public function deleteKey($key)
    {
        try {
            foreach ($this->languages as $lang) {
                $this->translationService->deleteKey($lang['code'], $key);
            }
            $this->loadStatistics();
            unset($this->editableTranslations[$key]);
            $this->success('Translation key deleted successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to delete key: '.$e->getMessage());
        }
    }

    public function openAddLanguageModal()
    {
        $this->newLanguageCode = '';
        $this->addLanguageModal = true;
    }

    public function closeAddLanguageModal()
    {
        $this->addLanguageModal = false;
        $this->reset(['newLanguageCode']);
    }

    public function saveNewLanguage()
    {
        $this->validate(['newLanguageCode' => 'required|string|size:2|alpha']);

        try {
            $this->translationService->addLanguage(strtolower($this->newLanguageCode));
            $this->loadLanguages();
            $this->loadStatistics();
            $this->addLanguageModal = false;
            $this->success('New language added successfully!');
            $this->reset(['newLanguageCode']);
        } catch (\Exception $e) {
            $this->error('Failed to add language: '.$e->getMessage());
        }
    }

    public function deleteLanguage($code)
    {
        try {
            if ($this->translationService->deleteLanguage($code)) {
                $this->loadLanguages();
                $this->loadStatistics();
                $this->success('Language deleted successfully!');
            } else {
                $this->warning('Cannot delete this language.');
            }
        } catch (\Exception $e) {
            $this->error('Failed to delete language: '.$e->getMessage());
        }
    }

    public function exportLanguage($lang)
    {
        try {
            $json = $this->translationService->exportLanguage($lang);
            $this->dispatch('download-file', [
                'content' => $json,
                'filename' => "{$lang}.json",
                'type' => 'application/json',
            ]);
            $this->success('Language exported successfully!');
        } catch (\Exception $e) {
            $this->error('Failed to export language: '.$e->getMessage());
        }
    }

    public function openImportModal()
    {
        $this->importJson = '';
        $this->importLanguage = 'en';
        $this->importModal = true;
    }

    public function closeImportModal()
    {
        $this->importModal = false;
        $this->reset(['importJson', 'importLanguage']);
    }

    public function importLanguageFile()
    {
        $this->validate([
            'importJson' => 'required|string',
        ]);

        try {
            $this->translationService->importLanguage($this->importLanguage, $this->importJson);
            $this->loadStatistics();
            $this->importModal = false;
            $this->success('Language imported successfully!');
            $this->reset(['importJson', 'importLanguage']);
        } catch (\Exception $e) {
            $this->error('Failed to import language: '.$e->getMessage());
        }
    }

    public function openScanModal()
    {
        $this->scanModal = true;
    }

    public function closeScanModal()
    {
        $this->scanModal = false;
        $this->reset('scannedKeys');
    }

    public function scanForKeys()
    {
        try {
            $this->scannedKeys = $this->translationService->scanForKeys();
            $this->info('Found '.count($this->scannedKeys).' translation keys in code.');
        } catch (\Exception $e) {
            $this->error('Failed to scan for keys: '.$e->getMessage());
        }
    }

    public function syncScannedKeys()
    {
        try {
            $this->translationService->syncKeys($this->scannedKeys);
            $this->scanModal = false;
            $this->loadStatistics();
            $this->success('Keys synced successfully!');
            $this->reset('scannedKeys');
        } catch (\Exception $e) {
            $this->error('Failed to sync keys: '.$e->getMessage());
        }
    }

    public function openAITranslateModal()
    {
        $this->aiTargetLanguage = '';
        $this->aiTranslateModal = true;
    }

    public function closeAITranslateModal()
    {
        $this->aiTranslateModal = false;
        $this->reset(['aiTargetLanguage']);
    }

    public function autoTranslate()
    {
        $this->validate(['aiTargetLanguage' => 'required|string']);

        try {
            // Get missing keys count before translation
            $targetTranslations = $this->translationService->getTranslations($this->aiTargetLanguage);
            $englishTranslations = $this->translationService->getTranslations('en');

            $missingCount = 0;
            foreach ($englishTranslations as $key => $value) {
                if (empty($targetTranslations[$key]) || $targetTranslations[$key] === $key) {
                    $missingCount++;
                }
            }

            if ($missingCount === 0) {
                $this->aiTranslateModal = false;
                $this->warning('No missing translations for this language.');

                return;
            }

            // Dispatch job to queue
            AutoTranslateJob::dispatch($this->aiTargetLanguage);

            $this->aiTranslateModal = false;
            $this->success("Auto-translation job queued for {$missingCount} keys! Processing in background.");
            $this->reset('aiTargetLanguage');
        } catch (\Exception $e) {
            $this->error('Failed to queue auto-translate: '.$e->getMessage());
        }
    }
};
