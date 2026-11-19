# Epicurus Tube

A self-hosted video streaming application built with Laravel 12, designed for personal video library management with powerful search, tagging, and engagement features.

## Overview

Epicurus Tube transforms your personal video collection into a feature-rich streaming platform. Import videos from local directories, automatically transcode them, organize with tags, and enjoy advanced search capabilities with real-time suggestions.

## Key Features

### Smart Search
- **Full-text search** powered by Meilisearch for instant video discovery
- **Real-time word suggestions** as you type, using intelligent 1-3 word combination indexing
- **Tag-based filtering** for precise content organization
- **Duration filters** to find videos by length (Quick, Short, Medium, Long, Feature)

### Video Management
- **Automatic import** from configured content directories
- **Video transcoding** to MP4 format using FFmpeg
- **Thumbnail generation** with responsive image support
- **Video previews** at multiple resolutions (360p, 180p) in multiple formats
- **Slug-based URLs** for clean, shareable video links

### Content Organization
- **Advanced tagging system** with autocomplete and tag browsing
- **Automatic tag assignment** based on title keywords
- **Tag grouping** with SharedTags for better organization
- **Special tag types** for advanced categorization (BANDED, DE_TITLE_WORDS, RE_TITLE_WORDS)

### Engagement Tracking
- **View history** with millisecond-precision progress tracking
- **Like/Dislike system** for rating your content
- **Featured videos** to highlight your favorites
- **View counts** to track popularity
- **Mark as viewed** for tracking watched content

### Curated Collections
- **Featured**: Your highlighted videos
- **Watched**: Complete viewing history
- **Liked**: Positively rated content
- **Disliked**: Negatively rated content

## Technology Stack

### Backend
- **Laravel 12** - Modern PHP framework with streamlined structure
- **MySQL** - Primary relational database
- **MongoDB** - Flexible feed storage for denormalized data
- **Meilisearch** - Lightning-fast full-text search engine
- **Redis** - Queue backend and caching layer
- **Laravel Horizon** - Queue monitoring and management
- **Laravel Scout** - Full-text search integration
- **FFmpeg** - Video transcoding and preview generation

### Frontend
- **Tailwind CSS 4** - Utility-first CSS framework
- **Alpine.js** - Lightweight reactive framework
- **htmx** - AJAX interactions without complex JavaScript
- **Tagify** - Rich tag editing interface
- **Flowbite** - UI component library
- **Vite** - Modern asset bundler

### Media Processing
- **Spatie Media Library** - Powerful media management with responsive images
- **Spatie Tags** - Flexible tagging system
- **pbmedia/laravel-ffmpeg** - Video transcoding and manipulation

## Architecture Highlights

### Data Flow
1. **Import**: Videos scanned from configured directories
2. **Process**: Automatic transcoding to MP4, thumbnail, and preview generation
3. **Index**: Search words extracted and indexed in Meilisearch
4. **Sync**: Content synchronized to MongoDB Feed for fast querying
5. **Stream**: Direct video streaming with progress tracking

### Queue-Based Processing
All resource-intensive operations run asynchronously:
- Video imports and transcoding
- Search word extraction
- Feed synchronization
- Progress updates
- Content cleanup

### Search Architecture
- **Primary Search**: Feed model (MongoDB) indexed in Meilisearch
- **Suggestions**: SearchableWord model with 1-3 word combinations
- **Smart Filtering**: Filterable by tags, duration, engagement status
- **Cached Results**: Redis caching for frequently accessed queries

### Factory Pattern
- **ContentItemFactory**: Centralized content item creation with caching
- **FeedItemFactory**: Context-specific representations (listing vs detail)

### Observer Pattern
- **ContentObserver**: Automatic Feed synchronization on content changes
- Lifecycle event handling for consistent data state

## Key Models

- **Content**: Core video entity with metadata, engagement, and relationships
- **Feed**: MongoDB-stored denormalized video data for fast queries
- **SearchableWord**: Indexed word combinations for search suggestions
- **Media**: File management with multiple collections (videos, transcoded, thumbs, previews)
- **SpecialTag**: Advanced tag management and categorization
- **TitleTag**: Keyword-based automatic tag assignment
- **View**: Viewing history with millisecond-precision progress

## Development Tools

### Quality Assurance
- **Pest 4** testing framework with browser testing support
- **Laravel Pint** for consistent code formatting
- **Larastan** for static analysis

### Monitoring
- **Laravel Horizon** dashboard for queue monitoring
- **Laravel Pail** for real-time log viewing

### Custom Artisan Commands
- `extract:words` - Extract searchable words from existing content
- `tube:delete-disabled` - Clean up disabled content across all relations
- `tube:recreate-symlinks` - Recreate missing symbolic links

## Project Philosophy

Epicurus Tube is built for personal use, prioritizing:
- **Performance**: Fast search, efficient caching, async processing
- **User Experience**: Clean interface, responsive design, intuitive navigation
- **Flexibility**: MongoDB for schema-less feeds, comprehensive tagging
- **Reliability**: Queue-based processing, proper error handling, comprehensive testing
- **Modern Standards**: Latest Laravel features, strict typing, PSR compliance

---

Built with Laravel 12 and modern web technologies for a personal video streaming experience.
