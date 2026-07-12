1. must use @lang() or __() for all static text
2. follow project structure
3. modify (create new) permission seeder for new actions, (do not modify roles or do not use roles for action), just use permissionsfor action,
4. make the design very professional and responsive
5. do not write test file or do note use chrome broswer directly

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

## 🗄️ Database & Migration Guidelines

To keep the database schema clean and avoid a cluttered migration folder during development, we use **Consolidated Feature Migrations**:

1. **Logical Grouping**: Rather than creating a separate migration file for every table or column modification, group related tables together in a single migration file named after the module (e.g. `create_geography_tables`, `create_messenger_tables`, `create_library_tables`, `create_quiz_tables`, etc.).
2. **In-File Order**: Ensure parent tables are created before child tables (which have foreign keys) within the same file's `up()` method.
3. **No Migration Pollution**: Do not create a new migration file for adding columns to existing tables during development. Instead, merge the new columns directly into the original creation migration file.
4. **Cascade Drop with Constraints Disabled**: When dropping tables in the `down()` method, disable foreign key checks to avoid deletion constraint failures:
   ```php
   public function down(): void
   {
       Schema::disableForeignKeyConstraints();
       Schema::dropIfExists('child_table');
       Schema::dropIfExists('parent_table');
       Schema::enableForeignKeyConstraints();
   }
   ```
5. **Fresh Seed Validation**: After making any migration changes, always verify by running `php artisan migrate:fresh --seed` to ensure the seeding database pipeline remains fully functional.