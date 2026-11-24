# Research: generate or handwrite the Swift API client?

Ticket: andresfb/epitube#8 (parent #7, blocks #14). Researched 2026-11-24.

**Question.** Should `client/common` use Apple's `swift-openapi-generator` on the Scramble-produced OpenAPI spec, or handwritten `Codable` models with a thin `URLSession` client?

**Short answer.** On the generator side, Linux is fine. The spec is the problem. Today, Scramble's export cannot drive a generated client: the raw output fails to parse, the routes that matter most (the video feed) have no schema, and the schemas it does produce have the wrong types. Getting the spec right means substantial server-side annotation work, plus a full app environment (MongoDB extension and databases) every time the spec is exported. The recommendation is to **handwrite `Codable` models and a thin `URLSession` client** for the 21 `/api/v1` routes, and to revisit the generator only if the API becomes spec-first. See [Recommendation](#recommendation).

Versions checked: swift-openapi-generator 1.14.4, swift-openapi-runtime 1.13.4, swift-openapi-urlsession 1.4.0 (latest GitHub releases); dedoc/scramble v0.13.30 (from `composer.lock`); Laravel 12.70; local Swift 6.2.4.

> Note on paths: in the committed tree the Laravel app still sits at the repo root (`routes/api/v1.php`, `app/...`). The `web/` + `client/` layout from `CLAUDE.md` exists only as an uncommitted local restructure. The paths below are relative to the Laravel app.

---

## 1. Does the generator stack build and run on Linux?

**Yes, all three pieces support Linux.**

- **Generator.** "The generator is used during development and is supported on macOS, Linux, and Windows." Supports OpenAPI 3.0 and 3.1, with preliminary support for 3.2. ([README](https://github.com/apple/swift-openapi-generator))
- **Runtime.** CI runs the unit tests on Linux for Swift 6.2, 6.3, 6.4 and nightlies. ([`.github/workflows/pull_request.yml`](https://github.com/apple/swift-openapi-runtime/blob/main/.github/workflows/pull_request.yml))
- **URLSession transport.** The README's platform table marks "Linux, Windows" as supported. The source switches to `FoundationNetworking` on non-Darwin platforms (`#if canImport(FoundationNetworking)` in [`URLSessionTransport.swift`](https://github.com/apple/swift-openapi-urlsession/blob/main/Sources/OpenAPIURLSession/URLSessionTransport.swift)), and CI runs unit tests on Linux for 6.1 through 6.3 and nightlies.
  - **Caveat.** "Streaming support only available on macOS 12+, iOS 15+, …" The README says: "For streaming support on Linux, please use the" AsyncHTTPClient transport. ([README](https://github.com/apple/swift-openapi-urlsession)) This does not matter for EpiTube's JSON endpoints, since video bytes do not go through the API client.
- **Platform UI.** Generated code imports only `OpenAPIRuntime`, `Foundation` and `HTTPTypes`, so it does not break the "no SwiftUI/AppKit/UIKit/AVKit in `client/common`" rule.

Handwritten `Codable` plus `URLSession` (through `FoundationNetworking` on Linux) has the same platform story and no third-party dependencies. **Linux does not separate the two options.**

## 2. How accurate is the Scramble spec for `/api/v1`?

### How I tested

- Ran `php artisan scramble:export --path=<scratch>` from the local `web/` checkout. Its routes and `app/Http` are identical to `main`.
- The full export **fails**: `Class "MongoDB\Driver\Manager" not found`. The local CLI PHP (MacPorts 8.3) lacks `ext-mongodb`, and Scramble re-throws any per-route analysis error (`vendor/dedoc/scramble/src/Generator.php`, `generateOperations`).
- Exported route by route with `--routes=api.v1.<name>` instead:
  - **14 of 21 routes exported.**
  - The 7 feed routes failed, all on the Mongo driver: `home`, `duration`, `selects`, `random`, `search`, `tag`, `videos`.
- Merged the 14 per-route documents and fed the result to swift-openapi-generator 1.14.4 (build plugin) in a throwaway package.

### Findings

1. **The raw export does not parse in the generator.**
   - Scramble emits PHP empty arrays as JSON `[]` where JSON Schema needs an object:
     - `"additionalProperties": []` on `ContentItem` and `TagListItem`
     - `"items": []` on `POST /search/words`
   - The generator fails with: `Found neither a Bool nor a JSONSchema in Document.components.schemas.ContentItem.additionalProperties`.
   - After hand-patching those three values to `{}`, the spec generated about 5,200 lines of Swift that compiled cleanly.
2. **The most important payload, the video feed, has no schema.**
   - `FeedListResource` and `FeedDetailResource` build their attributes in `FeedItemFactory::forListingArray` / `forDetailArray` from `$feed->toArray()` on a **MongoDB** model (`Feed`, `$connection = 'mongodb'`), wrapped in `Cache::remember`.
   - Scramble infers resource fields from `toArray`, from the linked model's casts, and from the model's **database table** ([Scramble responses docs](https://scramble.dedoc.co/usage/response)). None of that applies to a schemaless Mongo document built behind a cache closure.
   - Even with `ext-mongodb` installed, these attributes would come out as an untyped object.
   - These 7 routes are most of what a client does: home, duration, selects, random, search, tag, and video detail.
3. **Types are wrong where the model can't be resolved.**
   - Scramble warns `JR001 Cannot infer the resource model` for `CategoryResource` and `TagListResource`, and `MD001 Cannot read database schema` for `Content` and `Tag` (the local DB lacks the tables).
   - Per the docs: "If no model is found, all fields become `string`." Results:
     - `CategoryResource.main` is `String?`, although `Category` casts `main` to `boolean`.
     - `CategoryResource.id` is a string.
     - `TagListResource.count` is `String?`.
   - JSON:API attributes are also documented as **optional**, so every generated attribute is optional.
4. **Laravel Data DTOs are blank.**
   - `ContentItem` (returned by `PUT /contents`) and `TagListItem` (returned by `POST /search/tags`) are `spatie/laravel-data` objects. They export as empty `{type: object}` and generate as `[String: OpenAPIValueContainer]`.
   - The export prints "Scramble PRO will document these endpoints accurately". Laravel Data support is PRO-only ([docs](https://scramble.dedoc.co/packages/laravel-data)).
5. **What is accurate:**
   - Request bodies from FormRequests (`ContentUpdateRequest`, `VideoProgressRequest`, `TagSearchRequest`, `WordSearchRequest`).
   - The `422 ValidationException` response.
   - JSON:API `fields[...]` / `include` query parameters and `included` unions.
   - `const` status strings.
   - The `like`, `dislike` and `watch-later` response envelopes.
   - Scramble supports `LengthAwarePaginator` (`data` / `links` / `meta`) and backed enums (`enum` keyword) ([docs](https://scramble.dedoc.co/usage/response)). Every paginated or enum-bound route (`duration/{duration}` → `Durations`, `selects/{select}` → `Selects`) is among the 7 that failed, so I could not observe that output here.
6. **Other contract quirks a client must handle either way:**
   - Responses mix `application/vnd.api+json` (resources) with plain `application/json` (actions). The generator treats any `+json` as JSON, so this is fine.
   - `GET /switch/{category}` mutates **session** state, which a native client has no cookie jar for by default.
   - `meta.title` and similar `additional([...])` keys vary by route.
   - The spec declares no auth scheme.
   - `.bruno/` holds requests only, with no response examples, so it is not a second source of response shapes.

### What would need fixing server-side to make generation viable

- Make the export valid: a Scramble document transformer (or upstream fix) that turns empty `[]` schemas into `{}`/`true`.
- Give the feed real shapes:
  - Either replace `FeedItemFactory::forListingArray` / `forDetailArray` output with explicit, typed `toAttributes()` arrays (or `@var`/`@response` PHPDoc array shapes) in `FeedListResource` and `FeedDetailResource`,
  - or buy Scramble PRO and return `FeedItem` (already a typed Data object) directly.
- Link the resources to models (`@mixin` / `@property`) or annotate fields with `@var`. This fixes the `string` fallbacks (`main`, `count`, `id`).
- Decide how to handle JSON:API attributes being optional, or accept optionals everywhere in Swift.
- Make export reproducible: run it in an environment with `ext-mongodb`, a reachable MongoDB, and the SQL schema migrated. In practice that means CI or Docker. Today it does not run on the dev machine's CLI PHP at all.

## 3. What does keeping generated code in sync cost?

Per the generator docs ([FAQ](https://github.com/apple/swift-openapi-generator/blob/main/Sources/swift-openapi-generator/Documentation.docc/Articles/Frequently-asked-questions.md), [Manually invoking the CLI](https://github.com/apple/swift-openapi-generator/blob/main/Sources/swift-openapi-generator/Documentation.docc/Articles/Manually-invoking-the-generator-CLI.md)):

- **Build plugin (recommended upstream).**
  - Generates at build time from an `openapi.yaml`/`.json` placed in the target's sources. Nothing generated is committed.
  - The spec file itself is committed into `client/common`, so every API change still means re-exporting and copying the spec.
  - Costs:
    - Xcode requires "Trust & Enable" for the plugin.
    - Xcode Cloud needs a `ci_post_clone.sh` that sets `IDESkipPackagePluginFingerprintValidatation`.
    - Every clean build compiles the generator and its dependencies (OpenAPIKit, Yams, swift-argument-parser).
- **Command plugin or CLI (checked-in code).**
  - `swift package plugin generate-code-from-openapi --target …` writes `GeneratedSources/` for you to commit.
  - No build-time plugin and diffs you can review, but regeneration is a manual step that can be forgotten.
  - The code still depends on `swift-openapi-runtime`.
- **Either way, the sync loop is:**
  1. Change a Laravel route.
  2. Run `scramble:export` in a full environment (Mongo plus DB).
  3. Run the post-processing fix.
  4. Copy the spec to `client/common`.
  5. Regenerate.
  6. Update the hand-written wrapper code that maps generated `Operations.*.Output` enums into app-friendly models.
- **The generated API is verbose for this spec.**
  - Method names like `v1_period_contents_period_edit` (from Scramble's `operationId`s like `v1.contents.edit`).
  - Nested `…attributesPayload?` types.
  - A typed `Output` enum per status code.
  - In practice you write a wrapper layer anyway.

**Handwritten cost.** About 6 model types (feed item and detail, video, category, tag, content) plus 21 small endpoint functions. Sync means editing a Swift struct in the same PR as the PHP change, which `CLAUDE.md` already requires ("update `.bruno/` and the models in `client/common/` in the same change"). Drift is caught by decode tests against recorded JSON fixtures, not by a tool.

---

## Recommendation

**Handwrite the client for now.**

- `Codable` models matching the JSON:API envelope (`data` / `attributes` / `included` / `links` / `meta`).
- A small `APIClient` over `URLSession`, with `FoundationNetworking` on Linux.
- Fixture-based decode tests that the Linux CI build runs.

Why:

- Linux support is equal for both options.
- The generator would be fed a spec that, today, is unparseable, missing the feed routes, and wrong on types.
- Fixing that is real server-side work (feed resource shapes, model linkage, an export post-processor, a Mongo-capable export environment), and it makes Scramble's static inference the contract.

**Revisit generation** if the API is later reshaped to be spec-first. For example, if feed resources get explicit typed attributes (or Scramble PRO is adopted so `FeedItem` documents itself) and the export runs in CI. At that point, the **command plugin with checked-in `GeneratedSources/`** fits a monorepo better than the build plugin: no Xcode plugin trust, diffs you can review in the same PR as the PHP change, and no generator compile on every clean build.

## Sources

- apple/swift-openapi-generator: README; Documentation.docc articles `Supported-OpenAPI-features.md`, `Frequently-asked-questions.md`, `Manually-invoking-the-generator-CLI.md` (main, release 1.14.4).
- apple/swift-openapi-runtime: README; `.github/workflows/pull_request.yml` (release 1.13.4).
- apple/swift-openapi-urlsession: README; `Sources/OpenAPIURLSession/URLSessionTransport.swift`; `.github/workflows/pull_request.yml` (release 1.4.0).
- Scramble docs: https://scramble.dedoc.co/usage/response, https://scramble.dedoc.co/packages/laravel-data; source `vendor/dedoc/scramble/src/Generator.php` and `Console/Commands/ExportDocumentation.php` (v0.13.30).
- This repo: `routes/api/v1.php`, `app/Http/Controllers/Api/V1/*`, `app/Http/Resources/Api/V1/*`, `app/Factories/FeedItemFactory.php`, `app/Models/Tube/{Feed,Category}.php`, `app/Dtos/Tube/FeedItem.php`, `.bruno/EpiTube/**`.
- Experiment: per-route `scramble:export` plus a throwaway SwiftPM package using the `OpenAPIGenerator` build plugin (Swift 6.2.4, macOS). Not committed.
