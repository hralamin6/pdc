# Advanced Translation System

## Overview
A comprehensive translation management system for your Laravel application with support for multiple languages, inline editing, AI-powered translation, and more.

## Features Implemented

### ✅ Core Features
- **View translations by key** - All translations organized by translation keys
- **Search & Filter** - Search translations by key or value, filter by language
- **Inline Editing** - Edit translations directly in the table
- **Translation Statistics** - View completion percentage for each language
- **Import/Export** - Export translations as JSON and import back
- **Auto-detection of missing translations** - Visual indicators for missing keys
- **Code Scanner** - Scan your codebase for `__()`, `trans()`, `@lang()` calls
- **Sync Functionality** - Automatically add new keys found in code to all language files
- **AI Translation** - Auto-translate missing keys using Google Translate API
- **Language Management** - Add/delete languages dynamically
- **Permission-based Access** - Only authorized users can manage translations

## File Structure

```
app/
├── Livewire/App/Translate.php          # Main Livewire component
├── Services/TranslationService.php      # Core translation logic
config/
└── services.php                         # Added Google Translate API config
database/seeders/
└── PermissionSeeder.php                 # Translation permissions
lang/
├── en.json                              # English translations
├── bn.json                              # Bengali translations
└── ar.json                              # Arabic translations
resources/views/livewire/app/
└── translate.blade.php                  # UI interface
```

## Permissions

The following permissions have been added:
- `translations.view` - View translation manager
- `translations.create` - Add new translation keys
- `translations.update` - Edit translations
- `translations.delete` - Delete translation keys
- `translations.scan` - Scan code for translation keys
- `translations.import` - Import translation files
- `translations.export` - Export translation files
- `translations.ai-translate` - Use AI auto-translation

**Super-admin** role has all permissions by default.

## Setup Instructions

### 1. Run Database Seeder
```bash
php artisan db:seed --class=PermissionSeeder
```

### 2. AI Translation Ready!
The AI auto-translation feature is ready to use out of the box! It uses the `stichoza/google-translate-php` package which accesses Google Translate's free web service - **no API key required**.

## Usage Guide

### Accessing the Translation Manager
Navigate to: `/app/translate/`

### Managing Translations

#### Add New Translation Key
1. Click "Add Key" button
2. Enter the translation key (e.g., `welcome_message`)
3. Provide translations for each language
4. Click "Save"

#### Edit Translation
Simply click on any translation field and type. Changes are saved automatically on blur.

#### Delete Translation Key
Click the trash icon next to the key. This removes the key from all language files.

#### Search Translations
Use the search box to find translations by key or value.

#### Filter by Language
Select a language to focus on specific language translations.

### View Modes
- **All Keys** - Show all translation keys
- **Missing Only** - Show only keys missing translation for selected language
- **Translated Only** - Show only translated keys for selected language

### Language Management

#### Add New Language
1. Click "Add Language" button
2. Enter 2-letter ISO language code (e.g., `es`, `fr`, `de`)
3. Click "Add Language"
4. New language file will be created with English translations as placeholders

#### Delete Language
Click the trash icon on the language statistics card. Note: English cannot be deleted.

#### Export Language
Click the download icon on the language card to download the JSON file.

#### Import Language
1. Click "Import" button
2. Select target language
3. Paste JSON content
4. Click "Import"

### Code Scanning

#### Scan for Translation Keys
1. Click "Scan Code" button
2. Click "Start Scan"
3. Review found keys and their usage count
4. Click "Sync to Translation Files" to add missing keys

The scanner looks for:
- `__('key')`
- `trans('key')`
- `@lang('key')`

### AI Auto-Translation

1. Click "AI Translate" button
2. Select target language
3. Click "Auto-Translate"
4. Missing translations will be automatically filled using Google Translate API

**Note**: Requires Google Translate API key configuration.

## Statistics Dashboard

Each language card shows:
- **Language name** and code
- **Completion percentage**
- **Translated keys** count
- **Total keys** count
- Quick **Export** and **Delete** actions

## Best Practices

### 1. Use Consistent Key Naming
```php
// Good
__('user.profile.edit')
__('auth.login.failed')

// Avoid
__('Edit Profile')
__('login failed')
```

### 2. Keep English as Base Language
All new keys should be added to English first, then translated to other languages.

### 3. Regular Scanning
Run code scans regularly to find new translation keys in your codebase.

### 4. Review AI Translations
Always review auto-translated content for accuracy before deploying.

### 5. Export Backups
Regularly export your translation files as backups.

## Usage in Code

### Blade Templates
```blade
{{ __('Welcome') }}
{{ __('user.greeting', ['name' => $user->name]) }}
@lang('Dashboard')
```

### PHP Code
```php
__('Welcome')
trans('user.greeting', ['name' => $user->name])
```

### JavaScript (with Ziggy)
```javascript
// Use translation keys in your frontend
```

## Nested Keys Support

While the system uses flat JSON files, you can organize keys with dot notation:

```json
{
    "auth.login": "Login",
    "auth.logout": "Logout",
    "user.profile": "Profile",
    "user.settings": "Settings"
}
```

## Troubleshooting

### Permission Denied
Make sure you have the `translations.view` permission assigned to your role.

### AI Translation Not Working
1. Verify `GOOGLE_TRANSLATE_API_KEY` is set in `.env`
2. Check Google Cloud Console for API quota
3. Ensure billing is enabled on your Google Cloud project

### Scanned Keys Not Syncing
1. Make sure language files in `lang/` directory are writable
2. Check file permissions: `chmod 755 lang/`

### Missing Translations Not Showing
1. Verify the base language (English) has the key
2. Check that JSON files are valid
3. Clear Laravel cache: `php artisan cache:clear`

## Advanced Features

### Custom Language Names
Edit `TranslationService::getLanguageName()` to add more languages:

```php
$names = [
    'en' => 'English',
    'bn' => 'বাংলা (Bangla)',
    'ar' => 'العربية (Arabic)',
    'es' => 'Español (Spanish)',
    // Add your custom languages here
];
```

### Extending the Service
You can extend `TranslationService` to add custom features:

```php
use App\Services\TranslationService;

class CustomTranslationService extends TranslationService
{
    // Add your custom methods
}
```

## API Reference

### TranslationService Methods

- `getLanguages()` - Get all available languages
- `getTranslations($lang)` - Get translations for a language
- `saveTranslations($lang, $translations)` - Save translations
- `addLanguage($code, $translations)` - Add new language
- `deleteLanguage($code)` - Delete a language
- `updateTranslation($lang, $key, $value)` - Update single translation
- `addKey($key, $values)` - Add key to all languages
- `deleteKey($key)` - Delete key from all languages
- `getStatistics()` - Get translation completion stats
- `scanForKeys()` - Scan code for translation keys
- `syncKeys($foundKeys)` - Sync found keys to files
- `exportLanguage($lang)` - Export as JSON
- `importLanguage($lang, $json)` - Import from JSON
- `getAITranslation($text, $targetLang, $sourceLang)` - Get AI translation
- `autoTranslate($targetLang, $keys)` - Auto-translate missing keys
- `search($query, $lang)` - Search translations

## Support

For issues or feature requests, please check the documentation or contact your development team.

---

**Version**: 1.0  
**Last Updated**: 2025-11-04
