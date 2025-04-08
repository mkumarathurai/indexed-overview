## Technology Stack & Principles

The application leverages the latest Laravel ecosystem features:
- Backend: Laravel 12, PHP 8.1+ (strict types, modern syntax).
- Frontend: Livewire 3.6+ for dynamic UI components, Alpine.js for light JS interactions, Tailwind CSS for utility-first styling.
- UI Components: FluxUI v2 (free components like <flux:button>, <flux:input>, etc.) for consistent UI elements, along with custom Blade components (<x-page-heading>, <x-table>).
- Architecture: Modular (app/Modules), Component-Based (Livewire), SOLID principles, Dependency Injection, Repository Pattern (for data access).
- Database: mySQL.
- APIs: Integrations with Jira, Tempo, and HubSpot (planned).
- Task Handling: Commands for data synchronization, potentially Queues for long-running syncs.
- Security: Spatie laravel-permission for roles/permissions, standard Laravel security (CSRF).
- Testing: PestPHP for unit and feature tests.
- Localization: Built-in Laravel localization (lang/ directory, __('key') helper).