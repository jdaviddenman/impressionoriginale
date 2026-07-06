# Live mu-plugins

Production PHP **deployed to the live site**, mirrored here for tracking. This repo is the audit workspace, not the site code — these files are the exact custom code applied to the WP Engine install (`impressionor`).

## `io-perf-dequeue.php`

Conditional front-end asset dequeue for performance. Tracks GH #45 (§B).

**Deployed to:** `wp-content/mu-plugins/io-perf-dequeue.php` on live (auto-loaded — no activation step).

**What it does (v0.5):**
1. **WP Google Maps** — dequeues the plugin's front-end assets (`wpgmp-frontend` style + `wpgmp-frontend` / `wpgmp-google-api` / `wpgmp-google-map-main` scripts + the Google Maps API, ~650 KB) on every page **except** one that embeds the `[put_wpgm]` shortcode.
2. **Dashicons** — deregisters the WP core admin-bar icon font (~35 KB, render-blocking) when the admin bar isn't showing (i.e. logged-out visitors). Deregistered (not just dequeued) because it is pulled in via a style dependency.

**Why it's safe:** verified 2026-07-06 —
- Maps: the plugin renders no map anywhere (no `[put_wpgm]` in any post content, widget, or postmeta; `/where-to-find-us/` + homepage render zero `gm-style` maps). The `has_shortcode` guard auto-preserves assets on any future real map page.
- Dashicons: no front-end element renders a dashicons glyph (0 rendered glyphs incl. `::before`/`::after` on home/shop/product). The hook is `wp_enqueue_scripts` (front-end only) + `!is_admin_bar_showing()`, so wp-admin and logged-in users are untouched — the only dashicons consumers found (Yoast import screen, WC PDF setup wizard) are admin-side.

**Verification (deterministic):**
- Per-page map assets = 0 across `/`, `/fr/`, `/shop/`, `/our-products/`, `/where-to-find-us/`, `/wrap/`; all HTTP 200, 0 PHP errors.
- `harness/fingerprint.sh` (12 pages): all 200, no errors/warns/shortcode-leak/mojibake, per-page script/file counts reduced. See `reports/perf-after-mapdequeue-2026-07-06/`.
- Homepage requests 181 → 154 (headless); `maps.google.com` host absent.

**Deploy / rollback:**
```bash
# deploy (lint-gated so a syntax error never reaches the auto-loading dir)
D=/nas/content/live/impressionor/wp-content
scp io-perf-dequeue.php <ssh>:$D/_stage.php   # or: ssh <ssh> 'cat > $D/_stage.php' < io-perf-dequeue.php
ssh <ssh> "php -l $D/_stage.php && mv $D/_stage.php $D/mu-plugins/io-perf-dequeue.php"
# rollback: delete the file
ssh <ssh> "rm $D/mu-plugins/io-perf-dequeue.php"
# then purge WP Rocket + WPE + Cloudflare
```
