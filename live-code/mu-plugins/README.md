# Live mu-plugins

Production PHP **deployed to the live site**, mirrored here for tracking. This repo is the audit workspace, not the site code — these files are the exact custom code applied to the WP Engine install (`impressionor`).

## `io-perf-dequeue.php`

Conditional front-end asset dequeue for performance. Tracks GH #45 (§B) + #56 (icon-font dedup).

**Deployed to:** `wp-content/mu-plugins/io-perf-dequeue.php` on live (auto-loaded — no activation step).

**What it does (v0.6):**
1. **WP Google Maps** — dequeues the plugin's front-end assets (`wpgmp-frontend` style + `wpgmp-frontend` / `wpgmp-google-api` / `wpgmp-google-map-main` scripts + the Google Maps API, ~650 KB) on every page **except** one that embeds the `[put_wpgm]` shortcode.
2. **Dashicons** — deregisters the WP core admin-bar icon font (~35 KB, render-blocking) when the admin bar isn't showing (i.e. logged-out visitors). Deregistered (not just dequeued) because it is pulled in via a style dependency.
3. **Mailchimp WP Font Awesome** (`fca-eoi-font-awesome`, ~96 KB) — dequeues the plugin's bundled Font Awesome **v4.1.0** (`fontawesome-webfont.woff`), a third full copy of FA on top of the theme's canonical FA6 (`fa-solid-900` + `fa-brands-400`) + v4-shims. Tracks #56 §1.

**Why it's safe:** verified 2026-07-06 —
- Maps: the plugin renders no map anywhere (no `[put_wpgm]` in any post content, widget, or postmeta; `/where-to-find-us/` + homepage render zero `gm-style` maps). The `has_shortcode` guard auto-preserves assets on any future real map page.
- Dashicons: no front-end element renders a dashicons glyph (0 rendered glyphs incl. `::before`/`::after` on home/shop/product). The hook is `wp_enqueue_scripts` (front-end only) + `!is_admin_bar_showing()`, so wp-admin and logged-in users are untouched — the only dashicons consumers found (Yoast import screen, WC PDF setup wizard) are admin-side.
- Mailchimp FA: live headless glyph audit found exactly **one** homepage element resolving to the v4 `FontAwesome` family — the `.eut-top-btn.fa-angle-up` back-to-top button. Disabling this stylesheet in the DOM falls its `::before` glyph back to the theme's `"Font Awesome 6 Free"` and it **still renders** (v4-shims maps `.fa-angle-up`; codepoint U+F106 present in `fa-solid-900`). No mailchimp-form FA glyph renders on the homepage. Only the FA copy is dequeued — the plugin's form styling (`tooltipster` / `featherlight` / `style-new`) is left intact. **Correction to #56:** the issue's §2 proposed dequeuing Simple-Line-Icons "if unused" — it **is** used (5 rendered glyphs: globe-alt, credit-card, location-pin, present, paper-plane in the feature row), so its 52 KB is **kept**. The `< 150 KB` icon-font target in #56 is not reachable without subsetting the theme's own FA (147 + 107 KB), a higher-risk theme-file change — out of scope here.

**Verification (deterministic):**
- Per-page map assets = 0 across `/`, `/fr/`, `/shop/`, `/our-products/`, `/where-to-find-us/`, `/wrap/`; all HTTP 200, 0 PHP errors.
- `harness/fingerprint.sh` (12 pages): all 200, no errors/warns/shortcode-leak/mojibake, per-page script/file counts reduced. See `reports/perf-after-mapdequeue-2026-07-06/`.
- Homepage requests 181 → 154 (headless); `maps.google.com` host absent.
- Icon fonts (#56): after deploy, homepage `.woff2?`/`.ttf` icon-font transfer drops by ~96 KB (`fontawesome-webfont.woff` absent); all icons still render EN + FR (theme `fa-solid-900`/`fa-brands-400`/`Simple-Line-Icons` intact), no missing-glyph boxes. Re-measure via headless `PerformanceResourceTiming`.

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
