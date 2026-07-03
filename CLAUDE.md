# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Epicurus Tube** is a self-hosted video streaming application built with Laravel 12. The system provides:

- Personal video content management and streaming
- Video transcoding capabilities using FFmpeg
- Direct video streaming with progressive download
- Full-text search with real-time word suggestions via Meilisearch
- Advanced tagging system with autocomplete and tag-based filtering
- Video engagement tracking (likes, views, progress, featured status)
- Duration-based filtering and curated collections
- Video preview generation for quick browsing
- Media library integration with Spatie packages for file handling
- Queue-based background processing for video operations
- MongoDB integration for flexible feed storage

## Architecture & Key Components

### Core Models
- **Content**: Main video content entity with media collections, tags, file hashing, engagement tracking (likes, views, featured status), and slug-based routing
- **Feed**: MongoDB-based denormalized video metadata for fast querying, integrated with Scout/Meilisearch
- **SearchableWord**: Scout-indexed word database for real-time search suggestions (1-word, 2-word, and 3-word combinations)
- **Media**: Extended Spatie Media model with soft deletes
- **MimeType**: Supported video formats and transcoding requirements
- **SpecialTag**: Tag management system with types (BANDED, DE_TITLE_WORDS, RE_TITLE_WORDS)
- **TitleTag**: Automatic tag assignment based on title keywords
- **SharedTag/SharedTagItem**: Tag grouping and organization
- **View**: Video viewing history with millisecond-precision progress tracking

### Service Layer
- **ImportVideosService**: Scans content paths and queues videos for import
- **ImportVideoService**: Handles individual video file processing
- **TranscodeVideoService**: Converts videos to MP4 format
- **SearchableWordsService**: Extracts 1-word, 2-word, and 3-word combinations from titles/tags with banned word filtering
- **SyncFeedRecordsService**: Synchronizes Content to MongoDB Feed records
- **DeleteDisabledService**: Complete cleanup of disabled content across all relations
- **ContentItemFactory**: Centralized content item creation with caching
- **FeedItemFactory**: Context-specific feed item creation (listing vs detail views)

### Queue Architecture
The application heavily relies on background job processing:
- **ImportVideosJob**: Bulk video discovery and queuing
- **ImportVideoJob**: Individual video import processing
- **TranscodeVideoJob**: Video format conversion
- **SearchableWordsFromContentJob**: Extract searchable words from content titles/tags
- **SearchableWordsFromMediaJob**: Extract searchable words from media filenames
- **VideoProgressJob**: Async video progress updates
- **DeleteDisabledJob**: Background cleanup of disabled content

### Media Collections
Content uses multiple Spatie Media Library collections:
- `videos`: Original uploaded files (various formats)
- `transcoded`: MP4 converted videos
- `thumb`: Generated thumbnails with responsive images
- `previews`: Video previews at multiple resolutions (360p, 180p) in multiple formats (webm, mp4)

### Key Enums
- **Durations**: Video length categories (Quick: 1-3min, Short: 3-10min, Medium: 10-30min, Long: 30-60min, Feature: 60+min)
- **Selects**: Curated collections (Featured, Watched, Liked, Disliked)

## Development Commands

### Build & Development
- `composer run dev`: Start complete development environment (server, queue, logs, Vite)
- `npm run dev`: Frontend asset development server
- `npm run build`: Build production assets
- `php artisan serve`: Laravel development server
- `php artisan queue:listen`: Process background jobs
- `php artisan pail`: Real-time log monitoring

### Testing
- `php artisan test`: Run all tests using Pest
- `php artisan test --filter=testName`: Run specific test
- `composer run test`: Clear config and run tests

### Code Quality
- `vendor/bin/pint --dirty`: Format changed files
- `vendor/bin/pint`: Format all files

### Video Processing
- `php artisan horizon`: Queue dashboard and monitoring
- `php artisan extract:words`: Extract searchable words from content
- `php artisan tube:delete-disabled`: Delete disabled content with full cleanup
- `php artisan tube:recreate-symlinks`: Recreate missing symbolic links
- Content import and processing happen via scheduled jobs

## Key Technologies

### Backend Stack
- **Laravel 12** with streamlined structure
- **Laravel Horizon 5** for queue management
- **Laravel Scout 10** with Meilisearch for full-text search
- **MongoDB** via jenssegers/mongodb for flexible feed storage
- **Pest 4** for testing (with browser testing capabilities)
- **Spatie Laravel Data** for DTOs and data transformation

### Frontend Stack
- **Tailwind CSS 4** for styling
- **Alpine.js** for reactive components
- **htmx** for AJAX interactions
- **Tagify** (Yaireo) for rich tag editing interface
- **Flowbite** for UI components
- **Vite** for asset building

### Media Processing
- **pbmedia/laravel-ffmpeg**: Video transcoding and preview generation
- **Spatie Media Library**: File management with responsive images
- **Spatie Tags**: Content categorization and tagging

## Database Schema

### Core Tables
- `contents`: Video metadata with name/file hashing, slug, featured status, like_status, view tracking
- `feeds`: MongoDB collection for denormalized video data with Scout integration
- `searchable_words`: Word index (1-3 word combinations) with Scout/Meilisearch integration
- `media`: Spatie media library files
- `mime_types`: Supported formats and transcoding flags
- `tags`/`taggables`: Content categorization system
- `special_tags`: Tag management with type categorization
- `title_tags`: Automatic tag assignment based on keywords
- `shared_tags`/`shared_tag_items`: Tag grouping system
- `views`: Video viewing history with millisecond-precision progress tracking
- Queue tables (`jobs`, `failed_jobs`, `job_batches`)

### Key Relationships
- Content → Media (videos, transcoded, thumbnails, previews)
- Content → Tags (many-to-many)
- Content → Feed (synchronized via observer)
- Content → SearchableWords (via jobs)
- Content → Views (user viewing history)
- Media → MimeType (format validation)
- TitleTag → Tag (keyword-based auto-assignment)
- SharedTag → SharedTagItem → Tag (grouping)

## File Structure Notes

This Laravel 12 application uses the modern streamlined structure:
- No `app/Http/Middleware/` directory
- Service providers in `bootstrap/providers.php`
- Middleware registration in `bootstrap/app.php`
- Console commands auto-register from `app/Console/Commands/`
- Uses Livewire Volt for interactive pages (single-file components)
- DTOs using Spatie Laravel Data in `app/Data/`
- Enums for type-safe constants in `app/Enums/`
- Factory pattern services in `app/Factories/`
- Observer pattern for model lifecycle events in `app/Observers/`

## Key Features & Routes

### Search System
- `/search` - Full video search
- `/search/words` - Real-time word suggestions (AJAX)
- Meilisearch-powered full-text search on Feed and SearchableWord models
- 1-3 word combination indexing for accurate suggestions

### Tag Management
- `/tags-list` - Browse all tags
- `/tags/{slug}` - Videos filtered by specific tag
- `/tags/` - Tag search with autocomplete (AJAX)
- Tagify.js integration for rich tag editing

### Video Engagement
- `/videos/{slug}/viewed` - Mark video as viewed
- `/videos/{slug}/progress` - Update viewing progress
- `/videos/{slug}/feature` - Toggle featured status
- Like/Dislike tracking with integer status field

### Filtering & Collections
- `/duration/{duration}` - Filter by video length (Quick, Short, Medium, Long, Feature)
- `/selects/{select}` - Curated collections (Featured, Watched, Liked, Disliked)

### Content Management
- `/contents` - Content listing with editing capabilities
- `/contents/{slug}/edit` - Edit content metadata and tags

## Environment Setup

The application processes video files from a configured content path and manages them through the queue system. Video processing operations are resource-intensive and designed to run in the background.

### Required Services
- **MySQL/MariaDB**: Primary relational database
- **MongoDB**: Feed storage and flexible querying
- **Meilisearch**: Full-text search engine for Scout
- **Redis**: Queue backend and caching
- **FFmpeg**: Video transcoding and preview generation

### Configuration Files
- `config/content.php`: Content-specific settings (search indexes, featured customization, preview encoding)
- `config/scout.php`: Meilisearch configuration with filterable/sortable attributes
- `config/database.php`: MongoDB connection alongside MySQL

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.3
- laravel/framework (LARAVEL) - v12
- laravel/horizon (HORIZON) - v5
- laravel/prompts (PROMPTS) - v0
- laravel/sanctum (SANCTUM) - v4
- laravel/scout (SCOUT) - v10
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== spatie/laravel-medialibrary rules ===

## Media Library

- `spatie/laravel-medialibrary` associates files with Eloquent models, with support for collections, conversions, and responsive images.
- Always activate the `medialibrary-development` skill when working with media uploads, conversions, collections, responsive images, or any code that uses the `HasMedia` interface or `InteractsWithMedia` trait.

</laravel-boost-guidelines>
