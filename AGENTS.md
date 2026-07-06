# AGENTS.md - Agentic Coding Guidelines

## Project Overview
Laravel 12 application with Livewire v4, Pest v4 testing, and Tailwind CSS v4.

**Key Versions:**
- PHP 8.3
- Laravel 12
- Livewire v4
- Pest v4
- Tailwind CSS v4

---

## Build / Test / Lint Commands

```bash
# Run all tests
php artisan test
# or
composer run test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run a specific test by name
php artisan test --filter=testName

# Format code (run before committing)
vendor/bin/pint --dirty

# Build frontend assets
npm run build

# Development server
npm run dev          # Vite only
composer run dev     # Full stack (Laravel + Vite + Queue + Logs)
```

---

## Code Style Guidelines

### PHP Conventions
- Use **PHP 8 constructor property promotion**: `public function __construct(public GitHub $github) {}`
- Always use **explicit return types** on methods and functions
- Always use **curly braces** for control structures, even single-line
- Use **PHPDoc blocks** over inline comments (unless something is very complex)
- Add array shape type definitions for arrays in PHPDoc when appropriate

### Naming
- Use descriptive names: `isRegisteredForDiscounts`, not `discount()`
- Enum keys should be TitleCase: `FavoritePerson`, `Monthly`

### Imports & Types
```php
// Use explicit return types
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

### Error Handling
- Type-hint exceptions in catch blocks
- Log errors with context: `Log::error('Message', ['key' => $value])`
- Use Laravel's exception handling; avoid empty catch blocks

### Configuration
- Use `config('app.name')` — **never** use `env()` outside config files

---

## Laravel 12 Specifics

### Structure Changes
- **No middleware files** in `app/Http/Middleware/` — register in `bootstrap/app.php`
- **No Console/Kernel.php** — use `routes/console.php` or `bootstrap/app.php`
- **Commands auto-register** — files in `app/Console/Commands/` are automatically available
- Service providers go in `bootstrap/providers.php`

### Code Patterns
- Use Eloquent relationships with return type hints
- Avoid `DB::`; prefer `Model::query()`
- Use eager loading to prevent N+1 queries
- Create Form Request classes for validation (not inline)
- Use named routes and `route()` helper for URLs
- Use `casts()` method on models (not `$casts` property)

### Artisan Commands
```bash
# Create files properly
php artisan make:model Post --factory --seed
php artisan make:test --pest TestName
php artisan make:livewire Posts/CreatePost

# Always pass --no-interaction for automation
php artisan migrate --no-interaction
```

---

## Testing (Pest v4)

### Writing Tests
```php
// tests/Feature/ExampleTest.php
it('returns all', function () {
    $response = $this->postJson('/api/docs', []);
    $response->assertSuccessful();
});

// Use specific assertions
$response->assertForbidden();
$response->assertNotFound();
```

### Test Organization
- Feature tests in `tests/Feature/`
- Unit tests in `tests/Unit/`
- Browser tests in `tests/Browser/`
- Use factories for model creation: `User::factory()->create()`
- Use datasets for validation rule testing

### Running Tests
- Run minimal tests with filter after changes
- Ask user before running full suite

---

## Livewire v4

```php
// Single root element required
// Use lifecycle hooks
public function mount(User $user) { $this->user = $user; }
public function updatedSearch() { $this->resetPage(); }
```

```blade
{{-- Add wire:key in loops --}}
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">
        {{ $item->name }}
    </div>
@endforeach

{{-- Use wire:loading for states --}}
<button wire:loading.attr="disabled">Save</button>
```

---

## Tailwind CSS v4

### CSS Import
```css
/* Use this (v4 style) */
@import "tailwindcss";

/* NOT these (v3 style) */
@tailwind base;
@tailwind components;
@tailwind utilities;
```

### Key Changes
- `corePlugins` not supported
- Use `bg-black/50` instead of `bg-opacity-50`
- Use `shrink-*` instead of `flex-shrink-*`
- Use `grow-*` instead of `flex-grow-*`

---

## From Copilot Instructions

- Use `search-docs` tool for Laravel ecosystem documentation
- Use `tinker` for PHP debugging
- Use `database-query` for read-only DB access
- Check sibling files for existing conventions
- Do not create verification scripts when tests exist
- Be concise in explanations

---

## Checklist Before Committing

1. Run `vendor/bin/pint --dirty` to format code
2. Run relevant tests with `--filter` 
3. Build frontend: `npm run build` if assets changed
4. Ensure no `env()` calls outside config files
5. Verify explicit return types on all methods
