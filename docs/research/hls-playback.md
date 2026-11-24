# HLS playback and preview assets from the server in AVPlayer

Research for [#10](https://github.com/andresfb/epitube/issues/10) (parent #7, blocks #13 and #14).

**Question:** How does the server expose playable media and previews, and can AVPlayer use them as they are?

**Short answer:** The server has **no HLS**. Every playable asset is a static, progressive file (MP4 for playback, MP4 or WebM for previews, JPEG/PNG for thumbnails). The URLs are absolute and built from `APP_URL`. AVPlayer can play the MP4s directly as long as Caddy serves them as static files (Range requests work) over HTTPS with a certificate the device trusts. The client needs to handle a few gaps: filter by `mimeType`, parse a `srcset` string, rewrite the host, and do without a resume position.

> Paths below are relative to the repo root as committed on `main` (`37ac6ed`). The Laravel app sits at the root there. The `web/` folder described in `CLAUDE.md` has not been committed yet.

---

## 1. There is no HLS. `MimeType::canHls()` is a misnomer

- A search for `hls|m3u8|mpegurl` in `app/`, `config/`, `routes/`, `resources/` and `database/` finds only `MimeType::canHls()`, its cache tag, and an unused laravel-ffmpeg temp-dir setting. No code generates playlists or segments.
- `MimeType::canHls()` (`app/Models/Tube/MimeType.php`) returns every mime type whose `transcode` column is `false`. The seed in `database/migrations/2024_07_03_040549_create_mime_types_table.php` sets that flag only for `video/mp4`, `video/x-m4v` and `video/m4v`.
- Only `GenerateDownscalesService::canHandleMimeType()` calls it, to decide whether to make 720p/1080p downscales. It has nothing to do with HLS.
- Every other format in the seed (mkv, webm, avi, mov, wmv, flv, mpeg, 3gp, ogm, rmvb, octet-stream) has `transcode = 1`. `TranscodeVideoService` converts those files to `.mp4`, and the result goes into the `transcoded` collection.

**What this means for AVPlayer:** play the MP4 files directly with `AVURLAsset`/`AVPlayerItem`. Apple's guideline that required HLS for cellular video longer than 10 minutes is gone. App Store Review Guideline 2.5.7 now reads "Intentionally omitted" ([App Store Review Guidelines](https://developer.apple.com/app-store/review/guidelines/)). For a self-hosted app this was never a hard requirement anyway.

## 2. What the API returns (exact shapes)

Routes come from `routes/api/v1.php`, mounted at `/api/v1` with the route-name prefix `api.v1.` (`config/apiroute.php`, `'middleware' => []`, so there is **no auth**).

Both resources extend `Illuminate\Http\Resources\JsonApi\JsonApiResource` (Laravel v12.62.0), so the payload comes wrapped as `{"data": {"id", "type", "attributes": {...}}}`. The attributes come from `App\Factories\FeedItemFactory` and match `App\Dtos\Tube\FeedItem`.

### `GET /api/v1/videos/{slug}` → `FeedDetailResource` → `FeedItemFactory::forDetailArray()` (cached 1 h)

```jsonc
{
  "id": 1234, "slug": "vaDPs2OT410", "title": "...", "category_id": 1, "category": "...",
  "active": true, "viewed": false, "featured": false, "like_status": 0, "view_count": 3,
  "watch_later": false,
  "duration": "1 h 20 min",          // human string; numeric seconds live in videos[].duration and `length`
  "resolution": "1080p", "is_hd": true,
  "added_at": "3 days ago",          // diffForHumans, not a timestamp
  "thumbnail": "<srcset string>",    // see §4
  "tags": {"tag-slug": "Tag Name"},
  "service_url": "<jellyfin item url>",
  "previews": [],                    // ALWAYS empty in detail
  "videos": [                        // App\Dtos\Tube\VideoItem
    {"url": "https://<APP_URL host>/…/transcoded/5678/movie.mp4", "mimeType": "video/mp4", "duration": 4812, "height": 1080},
    {"url": "https://…/downscaled/5679/movie_720.mp4",            "mimeType": "video/mp4", "duration": 4812, "height": 720}
  ],
  "related": [ /* FeedItem listing arrays, with previews */ ]
}
```

`videos[]` is built by `ContentItemFactory::loadVideos()`:

1. The `transcoded` collection if it exists, otherwise the original `videos` collection (single file).
2. Plus every `downscaled` media item. Downscales are made only when the source is at least `content.min_down_res` (1080) high, at heights from `[720, 1080]` that are smaller than the source (`GenerateDownscalesService::RESOLUTIONS`).

No item is marked as the default. Pick one by `height`.

### Feed endpoints (`/`, `/duration/{d}`, `/selects/{s}`, `/random`, `/tags/{slug}`, `/search`) → `FeedListResource` → `forListingArray()` (cached 5 min)

The attributes are the same, except:

- `"videos": []` and `"related": []`.
- `"service_url": ""`.
- `"previews"` contains `VideoItem`s with `duration: 0` and `height: 0`:

```jsonc
"previews": [
  {"url": "https://…/previews/9001/preview_360_mp4.mp4",   "mimeType": "video/mp4",  "duration": 0, "height": 0},
  {"url": "https://…/previews/9002/preview_360_webm.webm", "mimeType": "video/webm", "duration": 0, "height": 0},
  {"url": "https://…/previews/9003/preview_180_mp4.mp4",   "mimeType": "video/mp4",  "duration": 0, "height": 0},
  {"url": "https://…/previews/9004/preview_180_webm.webm", "mimeType": "video/webm", "duration": 0, "height": 0}
]
```

The preview sizes and formats come from `config/content.php` → `preview_options` (`sizes: {360: 750 kbps, 180: 500 kbps}`, `extensions: [webm, mp4]`). A size is made only when the source is at least that tall. The size is stored as a media custom property but **is not in the payload**. Only the filename (`preview_{size}_{ext}.{ext}`, from `EncodePreviewService::createClipFile`) reveals it.

The web UI (`resources/views/components/preview-item.blade.php`, `preview-scripts.blade.php`) does the same thing a client must do. It adds every preview as a `<source>` on a muted, looping, `playsinline` `<video>` and lets the browser choose.

## 3. URL construction: absolute, from `APP_URL`, and saved in MongoDB

- Each URL is the result of Spatie Media Library's `Media::getFullUrl()` with `DefaultUrlGenerator` (`config/media-library.php`).
- Path layout (`app/Libraries/Tube/MediaPathGenerator.php`):
  - The model id is padded to 12 digits, split into groups of 3, and the groups are reversed. Then come `/{collection}/{mediaId}/{file}`.
  - Example: content `1234` → `234/001/000/000/transcoded/5678/movie.mp4`.
  - Thumbnails use `…/thumbnails/{mediaId}/responsive-images/…`.
- Disks (`config/filesystems.php`):
  - `videos` (originals) → disk `content` → `url = APP_URL.'/content'`.
  - `transcoded`, `downscaled`, `previews` and `thumbnails` → `config('media-library.disk_name')` = `env('MEDIA_DISK', 'media')`. **No `media` disk is defined in `config/filesystems.php`**, so the deployment `.env` must point `MEDIA_DISK` at a real disk. That disk's `url` decides the public prefix.
- The URLs are **baked into the MongoDB `Feed` documents**. `Feed::generate()` stores `ContentItemFactory::withRelated()` output, which includes `videos`, `previews` and `thumbnails`. They are also cached in Redis (`FeedItemFactory`: 5 min for listings, 1 h for detail; `ContentItemFactory::forListing`: 8 h).

**Gap: one host only.** `APP_URL` contains a single scheme and host, so a client on Tailscale gets the LAN hostname, and the reverse. Two ways to deal with this:

- **(a)** Use one hostname that resolves on both networks, such as the Tailscale MagicDNS `*.ts.net` name with split DNS on the LAN.
- **(b)** Have the client replace the scheme and host of every media URL with its configured server base URL. The path is all that matters.

Option (b) needs no server change and is the cheapest.

## 4. Posters and thumbnails

- The `thumbnail` field contains a **`srcset` string, not a URL**. `getBaseArray()` picks a random thumbnail (`collect($feed->thumbnails)->random()['srcset']`). The value comes from Spatie `Media::getSrcset()`, so it looks like `https://…/responsive-images/x___media_library_original_1280_720.jpg 1280w, https://… 1066w, …, data:image/svg+xml;base64,… 32w`. The final entry is a blurred base64 placeholder, because `responsive_images.use_tiny_placeholders` is `true`.
- **The client must parse this string.** Split on `, `, keep the `http` entries, and choose by width. The tiny `data:` entry can serve as a placeholder.
- The poster changes randomly each time the cache is rebuilt. The `urls` array (`ThumbnailItem::urls`) stays in Mongo and is never exposed.
- There is no separate "poster" field. The thumbnail is the poster.

## 5. Formats and codecs, and what AVPlayer accepts

| Asset | How it is produced | Container / codecs | AVPlayer |
|---|---|---|---|
| Original (`videos`), only mp4/m4v keep this as the playable file | untouched | Whatever the source used: H.264, HEVC (`hev1` or `hvc1`), AV1, ... | Usually fine. HEVC tagged `hev1` may not play, because Apple wants `hvc1` (HLS spec 1.10). AV1 needs hardware support. Check `AVURLAsset.isPlayable`. |
| `transcoded` | `ffmpeg -i in -q:v 0 -ar 44100 -ab 128k out.mp4` (`TranscodeVideoService::transcode`) | ffmpeg's defaults for `.mp4`: libx264 + AAC (when ffmpeg is built with them). **No `-pix_fmt`, `-profile`, or `-movflags +faststart`.** | Fine for typical 8-bit 4:2:0 sources. A 10-bit or 4:2:2 source becomes H.264 High10/High 4:2:2, which Apple devices do not decode in hardware. Because `moov` sits at the end of the file, playback starts with an extra range request to the tail. |
| `downscaled` | `ffmpeg -i master -vf scale=-2:{h} -crf 15 -c:a copy out.mp4` (`EncodeDownscaleService::downscale`) | libx264 with the source pixel format, audio copied | Same issues as above. The audio is whatever the MP4 original carried. |
| Preview `.mp4` | laravel-ffmpeg `X264('libmp3lame')`, `-an`, `fps=10`, `scale=-2:{360\|180}`, 3 clips joined with `concatWithoutTranscoding` | H.264, no audio | Plays. Use these. |
| Preview `.webm` | `WebM('libvorbis')`, `-an` | VP8/VP9 | **Skip these.** WebM is not one of AVFoundation's documented container types. Check at runtime with `AVURLAsset.audiovisualMIMETypes()` ("Returns an array of the MIME types the asset supports", [docs](https://developer.apple.com/documentation/avfoundation/avurlasset/audiovisualmimetypes())). |

Apple's codec baseline ([HLS Authoring Specification](https://developer.apple.com/documentation/http-live-streaming/hls-authoring-specification-for-apple-devices)), which also describes what AVFoundation decodes in general:

- 1.1: "All video MUST be encoded using H.264/AVC, HEVC/H.265, Dolby Vision, or AV1."
- 1.10: use `avc1`/`hvc1` rather than `avc3`/`hev1`.
- 1.6b: HEVC up to Main 10, Level 5.1.

**Gap: a feed item can expose a non-MP4 original.** `Content::hasVideos()` accepts either the `transcoded` collection or the `videos` collection, so a `.mkv` or `.webm` original whose transcode has not finished yet (or failed) can appear in the feed. Its `videos[]` then holds a single entry such as `video/x-matroska`. **Client rule:** keep only `mimeType` values in `video/mp4`, `video/x-m4v`, `video/m4v` and `video/quicktime`. If none remain, the item cannot be played yet.

## 6. Caddy, Range requests, CORS, redirects and ATS

- **Range requests.** Apple: "HTTP servers hosting media files for iOS must support byte-range requests, which iOS uses to perform random access in media playback." The suggested check is `curl --range 0-99 <url> -o /dev/null`, which must download exactly 100 bytes ([Safari Web Content Guide, Configuring Your Server](https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/CreatingVideoforSafarioniPhone/CreatingVideoforSafarioniPhone.html)). Caddy's `file_server` handles `Range`, `If-Range` and `ETag`/`Last-Modified` through Go's `http.ServeContent` ([caddy `staticfiles.go`](https://github.com/caddyserver/caddy/blob/master/modules/caddyhttp/fileserver/staticfiles.go), around lines 98 and 632). **This only holds if `/content/*` and the media disk's URL prefix are served by `file_server`, not passed through `php_fastcgi` or Laravel.** `public/content/` is committed as an empty directory (it holds only a `.gitignore`), so the deployment presumably symlinks or mounts storage there. Run the curl check against both hosts.
- **CORS.** CORS is a browser rule, and `AVPlayer`/`URLSession` do not apply it. Nothing is needed.
- **Redirects.** AVFoundation follows HTTP redirects. Make sure `APP_URL` uses `https://`. Otherwise every media URL starts with an `http→https` redirect, and the plain-HTTP first hop is subject to ATS.
- **ATS and certificates.** HTTPS must chain to a root CA the device trusts.
  - **Tailscale:** `*.ts.net` certificates (`tailscale cert` or Caddy's Tailscale integration) come from Let's Encrypt and work without changes.
  - **LAN with Caddy `tls internal`:** the Caddy local root CA must be installed on every iPhone/iPad (as a profile, plus Settings → General → About → Certificate Trust Settings) and on every Mac (System keychain, trusted). The alternative is a publicly trusted certificate obtained through the DNS challenge.
  - **IP addresses:** iOS 17 and macOS 14 no longer allow connections to IP addresses by default ([`NSAllowsLocalNetworking`](https://developer.apple.com/documentation/bundleresources/information-property-list/nsapptransportsecurity/nsallowslocalnetworking)). Use hostnames.
  - **`NSAllowsArbitraryLoadsForMedia`** "disable[s] App Transport Security restrictions for media loaded using the AV Foundation framework". Apple says to use it only for encrypted media, and it needs justification in App Store review ([docs](https://developer.apple.com/documentation/bundleresources/information-property-list/nsapptransportsecurity/nsallowsarbitraryloadsformedia)). It is not needed when the certificates are trusted.

## 7. Progress reporting

`PUT /api/v1/videos/{slug}/progress` → `VideoEngageController::update` (shared with the web routes)

```json
{ "current_time": 60.5, "duration": 4812.0, "completed": false }
```

- Validation (`App\Http\Requests\VideoProgressRequest`): all three fields are required. `current_time` and `duration` are `numeric`. `completed` is `boolean`. Send `Accept: application/json` to get a 422 JSON response instead of a redirect.
- Response: **`204 No Content`**. The work is queued (`VideoProgressJob` → `VideoProgressAction`). A 500 response returns `{"data":{"status":500,"message":"..."}}`.
- Effect: every call inserts a `views` row (`content_id`, `seconds_played = current_time`). The content is marked `viewed`, with `view_count++` and the `feed` cache cleared, when `completed` is true or the viewed percentage is at least `content.viewed_threshold` (60).
- The web player sends an update every 10 s of playback, on pause, and on `ended` with `completed: true` (`resources/views/video.blade.php`). Copy this with `AVPlayer.addPeriodicTimeObserver(forInterval: 10 s)`, pause, and `AVPlayerItemDidPlayToEndTime`.
- Related endpoint: `POST /api/v1/videos/{slug}/viewed` → 204 (marks the video viewed outright).

**Server gaps found here:**

1. **Threshold bug.** `VideoProgressAction::markViewed` computes `floor($current_time / $duration) * 100`. The floor runs before the multiplication, so the result is always 0 or 100, and the 60 % threshold never applies. Only `completed: true`, or reaching the very end, marks a video viewed. It should be `floor($current_time / $duration * 100)`.
2. **No resume position.** No field returns the last `seconds_played`. `FeedItem` has no `current_time`/`resume_at`, so the client cannot resume across devices. Resume could be kept on the device for now, or the server could add a field.
3. **Web player method mismatch.** `video.blade.php` sends `PATCH`, but the route is registered with `Route::put`. The web UI's progress calls probably get a 405. This does not affect native clients that use `PUT`.

## 8. Summary for the Swift client (#13, #14)

1. Detail: `data.attributes.videos[]`. Keep the MP4/QuickTime mime types, choose by `height` (largest, or ≤ screen), rewrite the host, then `AVPlayer(url:)`.
2. Feed hover/scrub previews: `data[].attributes.previews[]`. Keep `video/mp4` and choose 360 or 180 from the filename. Play muted and looped (`AVPlayerLooper`). They contain no audio.
3. Poster: parse `thumbnail` as a srcset string.
4. Progress: `PUT …/progress` every 10 s, on pause, and at the end with `completed: true`. Expect a 204.
5. Infrastructure: confirm `curl --range 0-99` returns 100 bytes on both hosts, the certificates are trusted on every device, and `APP_URL` is `https`.

**Optional server follow-ups (not required for a first client):**

- Expose the preview `size`.
- Mark a default video.
- Add `-movflags +faststart -pix_fmt yuv420p` to the transcode and downscale commands.
- Fix the viewed-threshold math and the web `PATCH`.
- Add a resume position.
- Generate real HLS only if adaptive bitrate over Tailscale or cellular turns out to matter.
