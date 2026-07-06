# Live mu-plugins

Production PHP **deployed to the live site**, mirrored here for tracking. This repo is the audit workspace, not the site code — these files are the exact custom code applied to the WP Engine install (`impressionor`).

## `io-perf-dequeue.php`

Conditional front-end asset dequeue for performance. Tracks GH #45 (§B).

**Deployed to:** `wp-content/mu-plugins/io-perf-dequeue.php` on live (auto-loaded — no activation step).

**What it does (v0.4):** Dequeues the WP Google Maps plugin's front-end assets (`wpgmp-frontend` style + `wpgmp-frontend` / `wpgmp-google-api` / `wpgmp-google-map-main` scripts + the Google Maps API, ~650 KB) on every page **except** one that embeds the `[put_wpgm]` shortcode.

**Why it's safe:** verified 2026-07-06 — the plugin renders no map anywhere on the site (no `[put_wpgm]` in any post content, widget, or postmeta; `/where-to-find-us/` and the homepage render zero `gm-style` maps). The `has_shortcode` guard auto-preserves assets on any future page that adds a real map.

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
