# Bot User Generator Refactoring

## Overview
Refactored the `GenerateBotUsers` command and `BotUserGeneratorService` to use Laravel AI SDK with a structured agent approach, following the same pattern as `PostWriter`.

## Changes Made

### 1. Created New Agent: `BotUserGenerator`
**File:** `app/Ai/Agents/BotUserGenerator.php`

- Implements Laravel AI SDK interfaces: `Agent`, `Conversational`, `HasTools`, `HasStructuredOutput`
- Uses `#[Provider('custom')]` and `#[Model('nvidia')]` attributes
- Defines structured output schema with required fields:
  - `name`: Full Bangla name
  - `gender`: Either 'male' or 'female'
  - `address`: Village/neighborhood name only
  - `bio`: Professional bio (150-200 words)

### 2. Refactored `BotUserGeneratorService`
**File:** `app/Services/BotBook/BotUserGeneratorService.php`

#### Before:
- Used `AiServiceFactory::make('nvidia')` directly
- Manual JSON parsing with regex and HTML stripping
- Complex error handling for malformed responses
- Hardcoded fallback value of `0` on error

#### After:
- Uses `\App\Ai\Agents\BotUserGenerator::make()->prompt($prompt)`
- Automatic structured output validation via Laravel AI SDK
- Cleaner error handling with proper fallback profile
- Returns structured array directly from `$response->structured`

#### Key Method Changes:

**`generateBotProfile()`**
```php
// Old: Passed $aiService parameter, manual JSON parsing
private function generateBotProfile($aiService): array

// New: No parameters, uses agent directly
private function generateBotProfile(): array
```

**Added `getFallbackProfile()` method**
- Provides realistic fallback data when AI fails
- Returns properly structured array with Bangladeshi names

**`generateProfileImage()` & `generateBannerImage()`**
- Removed unused `$personality` parameter
- Simplified method signatures to only require `User` and `$profileData`

### 3. Updated Test Suite
**File:** `tests/Unit/BotUserGeneratorTest.php`

Created comprehensive Pest tests:
- ✅ Validates structured output contains all required fields
- ✅ Checks field types and content validation
- ✅ Ensures bio has substantial content (50+ characters)
- ✅ Tests multiple request handling
- ✅ Verifies unique name generation

Tests are skipped by default (require AI service) but can be run manually for validation.

## Benefits

### 1. **Consistency**
- Follows same pattern as `PostWriter` agent
- Uses standardized Laravel AI SDK approach
- Consistent error handling across agents

### 2. **Type Safety**
- Structured output schema ensures data integrity
- No manual JSON parsing errors
- Automatic validation by SDK

### 3. **Maintainability**
- Cleaner code without regex/HTML stripping
- Easier to add new fields to schema
- Better separation of concerns

### 4. **Reliability**
- Proper fallback mechanism with `getFallbackProfile()`
- Better error logging and recovery
- More robust error handling

### 5. **Developer Experience**
- Simpler to understand and modify
- Clear agent instructions
- Self-documenting schema

## Usage

### Command Line
```bash
# Generate 5 bot users (default)
php artisan botbook:generate-users

# Generate custom number of bot users
php artisan botbook:generate-users 10
```

### Programmatic Usage
```php
use App\Services\BotBook\BotUserGeneratorService;

$service = app(BotUserGeneratorService::class);

// Generate single bot user
$user = $service->generateBotUser();

// Generate multiple bot users
$users = $service->generateMultipleBots(5);
```

### Direct Agent Usage
```php
use App\Ai\Agents\BotUserGenerator;

$response = BotUserGenerator::make()
    ->prompt('একজন বাংলাদেশী ব্যবহারকারীর প্রোফাইল তৈরি করো।');

$profileData = $response->structured;

// Access structured fields
$name = $profileData['name'];
$gender = $profileData['gender'];
$address = $profileData['address'];
$bio = $profileData['bio'];
```

## Schema Definition

```php
public function schema(JsonSchema $schema): array
{
    return [
        'name' => $schema->string()->required(),
        'gender' => $schema->string()->required(),
        'address' => $schema->string()->required(),
        'bio' => $schema->string()->required(),
    ];
}
```

## Fallback Data
When AI service fails, the system provides realistic fallback profiles:

**Male Names:**
- আব্দুল করিম
- মোহাম্মদ রহিম
- আলী আহমেদ
- সাজেদুল ইসলাম

**Female Names:**
- ফাতেমা বেগম
- রুবিনা আক্তার
- নাজমা খাতুন
- সাবিনা ইয়াসমিন

## Testing

Run the specific test suite:
```bash
# Run all bot user generator tests (skipped by default)
php artisan test --filter=BotUserGenerator

# Run without skip flag (requires AI service)
php artisan test tests/Unit/BotUserGeneratorTest.php
```

## Migration Notes

### No Breaking Changes
- Public API remains the same
- `GenerateBotUsers` command works identically
- Service methods maintain same signatures (except internal private methods)

### Internal Changes Only
- Private method `generateBotProfile()` no longer needs `$aiService` parameter
- Private methods `generateProfileImage()` and `generateBannerImage()` simplified
- New private method `getFallbackProfile()` added

## Future Enhancements

1. **Add More Schema Fields**
   - Interests/hobbies
   - Education level
   - Professional expertise

2. **Customizable Personalities**
   - Pass personality traits to agent
   - Generate diverse user types

3. **Localization Support**
   - Support multiple languages
   - Regional variations

4. **Rate Limiting**
   - Add built-in rate limiting
   - Queue-based generation for large batches

## Related Files

- `app/Ai/Agents/BotUserGenerator.php` - Main agent implementation
- `app/Ai/Agents/PostWriter.php` - Similar agent for reference
- `app/Services/BotBook/BotUserGeneratorService.php` - Service layer
- `app/Console/Commands/GenerateBotUsers.php` - Artisan command
- `tests/Unit/BotUserGeneratorTest.php` - Unit tests

## References

- Laravel AI SDK Documentation
- PostWriter Agent Implementation
- BotBook System Documentation
