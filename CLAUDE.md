# CLAUDE.md

Epicurus Tube monorepo: a self-hosted video streaming server plus its native clients.

## Layout

- `web/` — Laravel 12 server (web UI + `/api/v1`). Has its own `CLAUDE.md`, `AGENTS.md`, skills and tooling. Run all `php artisan`, `composer`, `npm` and `vendor/bin/*` commands from `web/`.
- `client/` — native clients, all written in Swift.
  - `client/common/` — shared Swift package (API client, models, auth). Platform-agnostic: must build on Linux, so no SwiftUI/AppKit/UIKit/AVKit imports.
  - `client/apple/` — one SwiftUI multiplatform Xcode app (iPhone, iPad, Mac) that depends on `client/common`.
  - `client/tui/`, `client/linux/` — planned; will also depend on `client/common`.
- `.bruno/` — Bruno API collection; the shared contract between `web/` and the clients.

## Rules

- When changing the API in `web/routes/api/`, update `.bruno/` and the models in `client/common/` in the same change.
- Keep platform UI code out of `client/common/`.
