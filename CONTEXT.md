# PSTU Dawah - Application Context & Guidelines

This document outlines key architectural paradigms, security models, and developer guidelines for the **PSTU Dawah** portal. Please read and update this context when implementing new features or making modifications.

---

## 🔐 Authorization & Permission-Based Access Control

The application uses **Spatie Laravel Permission** for robust, dynamic Role-Based Access Control (RBAC). 

### 1. Permission-First Architecture
* **Rule**: Do NOT check roles directly in the code (e.g., avoid `auth()->user()->hasRole('admin')`). Always authorize against specific permissions (e.g., `auth()->user()->can('expenses.manage')`).
* This allows the site administrator to dynamically adjust permissions for any role via the dashboard UI.

### 2. Standard Usage in Livewire / Volt Components
When implementing a new screen or action, strictly enforce authorization check inside the Livewire/Volt component:
```php
public function mount(): void
{
    // Protect entire view access
    $this->authorize('feature.view');
}

public function performAction(): void
{
    // Protect critical state mutations
    $this->authorize('feature.manage');
}
```

### 3. Database Seeding of Permissions
All permissions must be defined in the `$groups` array of `database/seeders/PermissionSeeder.php` under their logical category. 

To seed new permissions in production/staging environments, always run:
```bash
php artisan db:seed --class=PermissionSeeder
```

---

## 🏛️ Application Layout Structure

The codebase differentiates strictly between administrative spaces and member-facing/public interfaces:

1. **Admin/Management Dashboard (`resources/views/app/`)**
   * Uses the `layouts.app` base layout.
   * Typically guarded by specific administrative permissions (e.g. `settings.view`, `expenses.manage`, `translations.manage`).
   * Designed for supervisors, accountants, moderators, and super-admins.

2. **Web / Public Portal (`resources/views/web/`)**
   * Uses the `layouts.web` base layout.
   * Contains public content pages (e.g., posts, halaqah showcases, public campaigns).
   * Also contains member-exclusive features (e.g., personal library shelves, quizzes, personal daily reports) which must check user permissions like `library.view`, `quiz.attempt`, `daily-reports.view`.

---

## 🛠️ Module Audits & Implemented Guards

Refer to these configurations when refactoring or adding elements to the following modules:

| Module / Component | Namespace / Path | Standard Permission Guarded |
| :--- | :--- | :--- |
| **Bank Accounts** | `app/⚡bank-accounts/` | `expenses.bank-accounts.manage` |
| **Expense Categories** | `app/⚡expense-categories/` | `expenses.categories.manage` |
| **Expenses Admin** | `app/⚡expenses-admin/` | `expenses.manage` |
| **Fund Transfers** | `app/⚡fund-transfers/` | `expenses.transfers.manage` |
| **Treasury Reports** | `app/⚡treasury-report/` | `expenses.reports.manage` |
| **Daily Report Settings** | `app/⚡daily-reports-settings/` | `daily-reports.view` |
| **Daily Report Form** | `app/⚡daily-reports-form/` | `daily-reports.view` |
| **Translation Engine (View)** | `app/⚡translate/` | `translations.view` (on `mount()`) |
| **Translation Engine (Write)**| `app/⚡translate/` | `translations.manage` (on mutation actions) |
| **Library Hub & User Shelves**| `web/⚡library-*` / `web/⚡my-books` | `library.view` |
| **Quizzes Show / List / History**| `web/⚡quiz*` / `web/⚡quizzes*` | `quiz.view` |
| **Quiz Attempts / Take** | `web/⚡quiz-take/` | `quiz.attempt` |
| **User Profile Management** | `web/⚡profile/` | `profile.update` |
