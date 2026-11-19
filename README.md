# Epicurus Tube

A self-hosted video streaming platform for a personal video library: a Laravel server with a web UI and API, plus native Swift clients.

## Repository Layout

| Path | What it is |
| --- | --- |
| [`web/`](web/) | Laravel 12 server: video import, transcoding, search, tagging, web UI, and the `/api/v1` API. See [`web/README.md`](web/README.md). |
| [`client/common/`](client/common/) | Shared Swift package (API client, models, auth) used by every client. Platform-agnostic so it also builds on Linux. |
| [`client/apple/`](client/apple/) | SwiftUI multiplatform app for iPhone, iPad, and Mac. |
| `client/tui/` | *Planned:* terminal client in Swift. |
| `client/linux/` | *Planned:* Linux desktop client in Swift. |
| [`.bruno/`](.bruno/) | [Bruno](https://www.usebruno.com/) API collection, the shared contract between the server and the clients. |

## Getting Started

### Server

```bash
cd web
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
composer run dev
```

The server needs MySQL/MariaDB, MongoDB, Meilisearch, Redis, and FFmpeg. See [`web/README.md`](web/README.md) for features and architecture.

### Apple clients

Open the Xcode project in `client/apple/`. It depends on the local `client/common` Swift package.

## License

See [LICENSE](LICENSE).
