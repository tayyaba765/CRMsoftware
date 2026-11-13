<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## Updates & Changelog — 5 October 2026

### 1. Role & Permission Controller Refactoring (`RolePermissionController.php`)
- **Simplified Controller Architecture**: Streamlined `RolePermissionController` logic to match the clean, readable pattern used in `UserController`.
- **Full CRUD Support**:
  - `index()`: Loads roles with related modules and parent roles for DataTables rendering.
  - `store()`: Validates and creates a new role with optional `module_id` and `parent_role_id`.
  - `edit()`: Returns role details and current assigned permissions in JSON for modal popups.
  - `update()`: Supports updating role attributes (`name`, `module_id`, `parent_role_id`) as well as syncing permissions.
  - `destroy()`: Deletes roles safely.
- **Bug Fix (Null Values on Permission Update)**:
  - Fixed an issue where saving permissions from the Permissions modal set `module_id` and `parent_role_id` to `null`.
  - Added `$request->has('module_id')` and `$request->has('parent_role_id')` checks so partial updates (permission-only payloads) preserve existing relationships.

### 2. Role Model Enhancements (`app/Models/Role.php`)
- Extended Spatie's `Role` model.
- Added fillable attributes: `name`, `guard_name`, `module_id`, and `parent_role_id`.
- Defined Eloquent relationships:
  - `module()`: `belongsTo(Module::class)`
  - `parentRole()`: `belongsTo(Role::class, 'parent_role_id')`
  - `childRoles()`: `hasMany(Role::class, 'parent_role_id')`

### 3. Roles Blade & AJAX View (`resources/views/backend/roles/index.blade.php`)
- **Beginner-Friendly Clean JavaScript**: Simplified the frontend AJAX code, making DOM manipulation and API calls straightforward to maintain.
- **Tailwind CSS & Styling Retention**:
  - Maintained exact Tailwind styling matching the User Management page (`bg-brand`, dark mode classes, card shadows).
  - Kept custom DataTables layout, pagination styling, column fixed widths, and sort indicators (`↕↑↓`).
- **Interactive Modals & Features**:
  - **Add Role Modal**: Quick creation of roles with Module and Parent Role dropdown selection.
  - **Edit Role Modal**: Seamless inline editing of role name and hierarchy.
  - **Manage Permissions Modal**: Modular view grouping permissions by system modules with select/deselect all functionality.
  - **Delete Role Action**: Integrated confirmation flow with AJAX deletion and automatic table reload.

### 4. Profiles Management Feature (Lead & Customer Profiles)
- **Sidebar Integration (`resources/views/backend/layouts/sidebar.blade.php`)**:
  - Added "Profiles" navigation link with icon directly beneath "Dashboard" with dynamic active route indicator.
- **Migration & Database (`database/migrations/2026_10_05_000001_create_profiles_table.php`)**:
  - `first_name` & `last_name` (required strings)
  - `username` (unique, nullable)
  - `source` (WhatsApp, Phone, Facebook, Instagram, In-person, Other)
  - `email` & `password` (hashed)
  - `avatar` (image path in `public/uploads/avatars`)
  - `cnic` (13 digits validation)
  - `phone` (11 digits validation)
  - `address_1` & `address_2`
  - `latitude` & `longitude` (for interactive map viewbox)
  - `status` (prospective, working, postponed, closed)
  - `assigned_to` (foreign key linked to `users.id` with `nullOnDelete`)
- **Eloquent Models (`app/Models/Profile.php` & `app/Models/User.php`)**:
  - `Profile` model with fillables, hidden password, `assignedUser()` relation, `full_name` accessor, and `avatar_url` accessor with UI-Avatars fallback.
  - `User` model updated with `assignedProfiles()` relationship.
- **Controller & Routes (`app/Http/Controllers/ProfileController.php` & `routes/web.php`)**:
  - Registered resource routes under prefix `/profiles` (`index`, `data`, `store`, `edit`, `show`, `update`, `destroy`).
  - Full Yajra DataTables AJAX response with formatted badges, avatar images, 13-digit CNIC formatting, and user assignment badges.
  - Image file upload processing for profile avatars.
- **Single-Page View (`resources/views/backend/profiles/index.blade.php`)**:
  - 1-page complete CRUD architecture styled with Cork / Tailwind CSS.
  - **Address 2 "Same as above"**: Interactive checkbox that copies Address 1 into Address 2 and keeps them synchronized.
  - **Embedded Leaflet Map Viewbox**: Located directly underneath the address fields. Includes draggable pin, click-to-pin, address geocoding search ("Locate on Map"), and "Use My Current GPS" button.
  - **Add & Edit Modal**: Upload avatar with live circular preview, 13-digit CNIC constraint, 11-digit phone constraint, user assignment select, and status selector.
  - **View Details Modal**: Inspect full customer profile with an embedded map view popup.

### 5. Organizations Management Feature
- **Sidebar Integration (`resources/views/backend/layouts/sidebar.blade.php`)**:
  - Added "Organizations" navigation item with building icon directly beneath "Profiles" with active route highlighting.
- **Migration & Database (`database/migrations/2026_10_05_000002_create_organizations_table.php`)**:
  - `name`: Organization name (required, string 150)
  - `size`: Size dropdown (`2-15`, `15-50`, `50-100`, `100-500`, `500+`)
  - `website_domain`: Domain or URL
  - `logo`: File path in `public/uploads/organizations`
  - `organization_type`: Industry classification (IT, Healthcare, Finance, Education, etc.)
  - `revenue`: Annual revenue float in USD
  - `ownership_type`: `public`, `private`, `non-profit`, `government`
  - `phone` & `phone_2`: Primary and secondary corporate contact numbers
  - `address` & `address_2`: Headquarters and branch addresses
  - `tier`: Classification tier (`Tier 1`, `Tier 2`, `Tier 3`, `Tier 4`)
  - `focal_person`: Designated corporate representative
- **Eloquent Model (`app/Models/Organization.php`)**:
  - Fillable fields, logo URL accessor with UI-Avatars fallback, and formatted revenue accessor.
- **Controller & Routes (`app/Http/Controllers/OrganizationController.php` & `routes/web.php`)**:
  - CRUD routes under `/organizations` with Yajra DataTables AJAX response, logo uploads, and modals.
- **Single-Page View (`resources/views/backend/organizations/index.blade.php`)**:
  - Tailwind CSS 1-page CRUD with DataTables, Add/Edit modal, logo preview, "Same as above" checkbox for Address 2, and View Details modal.

### 6. Profile Demographics & Career Extension
- **Migration (`database/migrations/2026_10_05_000003_add_demographics_to_profiles_table.php`)**:
  - Added columns to `profiles` table:
    - `job_type`: `private`, `government`, `unemployed`, `public`
    - `income`: float in USD ($)
    - `education`: `highschool`, `bs`, `masters`, `phd`, `other`
    - `age_group`: `teen`, `adult`, `olderadult`, `senior`
- **Model Update (`app/Models/Profile.php`)**:
  - Added `job_type`, `income`, `education`, and `age_group` to `$fillable`.
- **Controller Update (`app/Http/Controllers/ProfileController.php`)**:
  - Added validation, storage, update, edit, and show responses for all demographic fields.
- **View Update (`resources/views/backend/profiles/index.blade.php`)**:
  - Added Section 3 "Demographics & Career" to Add/Edit modal with dropdowns and currency input.
  - Added demographic summary cards to the View Details modal.


