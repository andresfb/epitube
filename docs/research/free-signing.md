# Running builds on Mac and iPhone without a paid Developer Program

Research for [#9](https://github.com/andresfb/epitube/issues/9) (parent #7, blocks #14).
Researched 2026-11-24 against Apple developer documentation and Apple DTS forum answers.

## Question

Without a paid Apple Developer Program (ADP) membership, how can the owner build and run the
SwiftUI multiplatform app on their own Mac (macOS 26) and iPhone (iOS 26)? Which entitlements and
capabilities need a paid account, and how does that constrain the design now?

## Short answer

- **Use a free Personal Team with automatic "Apple Development" signing on both platforms.** Do
  not use "Sign to Run Locally" (ad-hoc) as the default, even on the Mac.
- **Mac:** effectively no limits for this app. App Sandbox, outgoing network, Hardened Runtime,
  Keychain Sharing and App Groups are all available to a free account. Builds run from Xcode (or
  copied locally) are not quarantined, so Gatekeeper and notarization do not apply.
- **iPhone:** works, but the provisioning profile expires after **7 days**, so the app must be
  rebuilt and reinstalled from Xcode at least weekly. Limits are 3 devices, 3 apps per device and
  10 App IDs (all expiring after 7 days). No TestFlight, no ad-hoc or App Store distribution.
- **Design constraint:** everything this app needs (HTTPS streaming with AVPlayer, background
  audio and Picture in Picture, Keychain token storage) is free. Avoid Push Notifications, iCloud
  (CloudKit, KVS, documents), Associated Domains (universal links, shared web credentials),
  Sign in with Apple, Network Extensions and Low Latency HLS. Use polling or foreground refresh
  instead of push, and server-side storage (the Laravel API) instead of iCloud sync.

## Findings

### 1. Free Personal Team limits (both platforms)

Apple's account help page, "Enable a personal team in Xcode"
([About your developer account](https://developer.apple.com/help/account/basics/about-your-developer-account)):

> If your account is not associated with a developer program membership, Xcode will indicate it's
> a Personal Team. Your account's App IDs, devices, certificates, and provisioning profiles are
> managed directly in Xcode, and you'll be required to reprovision your apps to a device
> periodically.
>
> - You can register up to 10 App IDs, which expire after 7 days.
> - You can register up to 3 devices, which expire after 7 days.
> - You can install up to 3 apps per device. Provisioning profiles that enable apps to be installed
>   on a device will expire 7 days from issuance. You'll need to rebuild and reinstall your app to
>   your device after expiration.

The same page says "To build more advanced app capabilities and distribute apps, you'll need to be
an Apple Developer Program member." TestFlight, App Store Connect, notarization and Xcode Cloud are
membership features ([Compare memberships](https://developer.apple.com/support/compare-memberships/)).

Implications for Epicurus Tube:

- The multiplatform app is one bundle ID, so iPhone + iPad + Mac uses 1 App ID. Each extra target
  with its own bundle ID (widget, share extension, tvOS later) uses another App ID, and an
  extension counts toward the 3-apps-per-device limit on iOS. Keep the iOS app to a single target
  for now.
- 3 devices total for the team (e.g. iPhone, iPad, and the Mac if Xcode registers it). Enough for
  a single user, but there is no room for a family member's phone.
- Weekly reinstall on iOS is the main operational cost. Reinstalling over the existing app keeps
  its data container, so settings and Keychain items survive a rebuild with the same bundle ID and
  team.

### 2. Capabilities available to a free account

Apple publishes a per-capability table with an **Apple Developer** column, defined as "Apple
Account holders who have agreed to the Apple Developer Agreement ... No cost is associated with
this agreement and developers can't distribute apps."
Sources: [Supported capabilities (iOS)](https://developer.apple.com/help/account/reference/supported-capabilities-ios),
[Supported capabilities (macOS)](https://developer.apple.com/help/account/reference/supported-capabilities-macos)
(tables parsed from the live pages on 2026-11-24).

**Available with a free account**

| iOS / iPadOS | macOS |
|---|---|
| App groups | App groups |
| Background modes | App Sandbox |
| Data protection | Hardened runtime |
| HealthKit, HomeKit | Keychain sharing |
| Inter-App Audio | Maps |
| Keychain sharing | |
| Maps (no routing directions) | |
| Wireless Accessory Configuration | |

**Paid only (relevant ones)**

| Capability | Why it might come up | Free-account alternative |
|---|---|---|
| Push notifications | "new video" alerts | Poll the API on launch / foreground; local notifications need no entitlement |
| iCloud (CloudKit, KVS, documents) | sync watch progress, settings | Store progress and preferences on the server via `/api/v1` |
| Associated domains | universal links, shared web credentials / password AutoFill for the server login | Custom URL scheme; plain Keychain storage of the token |
| Sign in with Apple | login | Existing server auth (token) |
| Network extensions, Personal VPN | in-app tunnel to the LAN | Rely on the system Tailscale app, which provides the route |
| Low Latency HLS | live streams | Not needed for VOD (see note) |
| Game Center, In-App Purchase, WeatherKit, Shared with You, HLS Interstitial Previews | not needed | n/a |
| Access WiFi information, Multipath, Siri, Time Sensitive Notifications | not needed now | n/a |

Note on Low Latency HLS: the capability is ADP-only, but it applies only to LL-HLS live streams.
Apple's LL-HLS article says clients fall back to regular-latency playback when the server does not
support it
([Enabling Low-Latency HLS](https://developer.apple.com/documentation/http-live-streaming/enabling-low-latency-http-live-streaming-hls)),
and Epicurus Tube serves VOD HLS, so ordinary `AVPlayer` HLS playback needs no entitlement.

**Background playback and Picture in Picture are free.** Apple's media-playback guide requires the
Background Modes capability with "Audio, AirPlay, and Picture in Picture" plus the `.playback`
audio session category
([Configuring your app for media playback](https://developer.apple.com/documentation/avfoundation/configuring-your-app-for-media-playback)),
and Background modes is checked for the free Apple Developer column on iOS.

### 3. macOS: use Apple Development signing, not "Sign to Run Locally"

- On macOS, App Sandbox, Hardened Runtime and `com.apple.security.application-groups` are
  **unrestricted** entitlements that need no provisioning profile; "A Mac app that uses no
  restricted entitlements doesn't need a provisioning profile." `keychain-access-groups` is the
  example of a restricted one
  ([TN3125: Provisioning profiles](https://developer.apple.com/documentation/technotes/tn3125-inside-code-signing-provisioning-profiles)).
- The sandbox entitlement this app needs is `com.apple.security.network.client` ("whether your app
  may open outgoing network connections"); the sandbox itself is required only for Mac App Store
  distribution ([App Sandbox](https://developer.apple.com/documentation/security/app-sandbox)).
  Keep the sandbox on anyway; it is free and matches iOS behaviour.
- Ad-hoc signing ("Sign to Run Locally") has two documented drawbacks: "Your code can't use
  restricted entitlements" and "Your code's identity isn't stable, so it runs into TCC problems"
  (Apple DTS, [forum thread 751658](https://developer.apple.com/forums/thread/751658)). TN3127
  explains that an ad-hoc signature's designated requirement "is tied to that specific version of
  the code", so macOS "can't reliably track the identity of the code" across rebuilds
  ([TN3127: Requirements](https://developer.apple.com/documentation/technotes/tn3127-inside-code-signing-requirements)).
  In practice that means repeated privacy prompts (e.g. Local Network) and Keychain items that
  the next build may not be able to read.
- Apple DTS also states that development signing needs no paid membership: it "works with a
  Personal Team, available to any Apple ID", and Sign to Run Locally "can cause all sorts of weird
  issues", including local network privacy on macOS 15
  ([forum thread 763141](https://developer.apple.com/forums/thread/763141)).

**Gatekeeper:** Gatekeeper only evaluates quarantined files. Code you build and run locally is not
quarantined, so Gatekeeper and notarization never come into play
(Apple DTS, [Avoiding notarisation & Gatekeeper for development builds](https://developer.apple.com/forums/thread/666452),
[Resolving Trusted Execution Problems](https://developer.apple.com/forums/thread/706442)).
Exporting the `.app` from an Archive ("Copy App" / development export) to `/Applications` on the
same Mac is fine for daily use. Copying it to *another* Mac via a download path that adds
`com.apple.quarantine` would trigger Gatekeeper, because notarization needs ADP. Mac development
certificates do not expire weekly, so the Mac build has no 7-day problem (the 7-day expiry in
finding 1 is about iOS device provisioning profiles).

### 4. iOS: device setup

- Developer Mode must be on: Settings > Privacy & Security > Developer Mode, restart, confirm. It
  "only appears in Settings if you initiate pairing or if you previously paired the device to a
  Mac" ([Enabling Developer Mode on a device](https://developer.apple.com/documentation/xcode/enabling-developer-mode-on-a-device)).
- First install from a Personal Team also requires trusting the developer certificate under
  Settings > General > VPN & Device Management (Xcode shows this prompt on first launch).
- Sideloading alternatives do not remove the limits: tools that install with a free Apple Account
  sign through the same Personal Team, so they inherit the 7-day / 3-app limits; EU alternative
  marketplaces and web distribution require ADP membership. Rebuilding from Xcode weekly is the
  practical path.

### 5. Networking: HTTPS on LAN/Tailscale needs no paid capability

- Outgoing HTTPS is a plain `URLSession`/`AVPlayer` call; the only entitlement is the free
  `com.apple.security.network.client` on the sandboxed Mac build.
- Keep App Transport Security on by serving a certificate the device trusts (e.g. Tailscale's
  HTTPS certs on `*.ts.net`, or a private CA installed on the devices). DTS recommends a test CA
  over disabling ATS, and notes ATS keys go in `Info.plist`, not the entitlements file
  ([forum thread 763141](https://developer.apple.com/forums/thread/763141)).
- If the app connects directly to a LAN IP or `.local` host, iOS and macOS show a Local Network
  privacy prompt (needs `NSLocalNetworkUsageDescription`). No paid capability is involved, but a
  stable (non-ad-hoc) signature is what keeps that grant across rebuilds (finding 3). Traffic over
  Tailscale's `100.x` addresses goes through the Tailscale VPN interface rather than the local
  network.

## Recommendation for the design now

1. Xcode project: automatic signing, Team = the owner's Personal Team, one multiplatform target,
   one bundle ID. Mac: App Sandbox + Outgoing Connections (Client). iOS: Background Modes >
   Audio, AirPlay, and Picture in Picture.
2. Store the API token in the Keychain without a custom access group (free, works on both).
3. Do not design features that need Push, iCloud, Associated Domains, Sign in with Apple or
   Network Extensions. Put sync state (watch progress, watch later, settings) on the server.
4. Avoid app extensions on iOS until there is a reason; each costs an App ID and one of the 3 app
   slots.
5. Accept the weekly iOS reinstall. If it becomes a burden, ADP (paid) is the only first-party fix:
   1-year profiles and TestFlight.

## Sources

- Apple, [About your developer account](https://developer.apple.com/help/account/basics/about-your-developer-account) (Personal Team limits)
- Apple, [Compare memberships](https://developer.apple.com/support/compare-memberships/)
- Apple, [Supported capabilities (iOS)](https://developer.apple.com/help/account/reference/supported-capabilities-ios)
- Apple, [Supported capabilities (macOS)](https://developer.apple.com/help/account/reference/supported-capabilities-macos)
- Apple, [TN3125: Inside Code Signing: Provisioning Profiles](https://developer.apple.com/documentation/technotes/tn3125-inside-code-signing-provisioning-profiles)
- Apple, [TN3127: Inside Code Signing: Requirements](https://developer.apple.com/documentation/technotes/tn3127-inside-code-signing-requirements)
- Apple, [App Sandbox](https://developer.apple.com/documentation/security/app-sandbox)
- Apple, [Enabling Developer Mode on a device](https://developer.apple.com/documentation/xcode/enabling-developer-mode-on-a-device)
- Apple, [Configuring your app for media playback](https://developer.apple.com/documentation/avfoundation/configuring-your-app-for-media-playback)
- Apple, [Enabling Low-Latency HLS](https://developer.apple.com/documentation/http-live-streaming/enabling-low-latency-http-live-streaming-hls)
- Apple DTS forum answers: [751658](https://developer.apple.com/forums/thread/751658), [763141](https://developer.apple.com/forums/thread/763141), [666452](https://developer.apple.com/forums/thread/666452), [706442](https://developer.apple.com/forums/thread/706442)

Not verified from a primary source: the exact behaviour of Personal Team Mac development
certificates over time (expiry/revocation), and the claim that Tailscale `100.x` traffic avoids the
Local Network prompt. Confirm both empirically when the Xcode project is set up (#14).
