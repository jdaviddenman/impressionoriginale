# Live mu-plugins

Production PHP **deployed to the live site**, mirrored here for tracking. This repo is the audit workspace, not the site code — these files are the exact custom code applied to the WP Engine install (`impressionor`).

## `io-perf-dequeue.php`

Conditional front-end asset dequeue for performance. Tracks GH #45 (§B) + #56 (icon-font dedup).

**Deployed to:** `wp-content/mu-plugins/io-perf-dequeue.php` on live (auto-loaded — no activation step).

**What it does (v0.6.1):**
1. **WP Google Maps** — dequeues the plugin's front-end assets (`wpgmp-frontend` style + `wpgmp-frontend` / `wpgmp-google-api` / `wpgmp-google-map-main` scripts + the Google Maps API, ~650 KB) on every page **except** one that embeds the `[put_wpgm]` shortcode.
2. **Dashicons** — deregisters the WP core admin-bar icon font (~35 KB, render-blocking) when the admin bar isn't showing (i.e. logged-out visitors). Deregistered (not just dequeued) because it is pulled in via a style dependency.
3. **Mailchimp WP Font Awesome** (`fca-eoi-font-awesome`, ~96 KB) — suppresses the plugin's bundled Font Awesome **v4.1.0** (`fontawesome-webfont.woff`), a third full copy of FA on top of the theme's canonical FA6 (`fa-solid-900` + `fa-brands-400`) + v4-shims. Tracks #56 §1. **Suppressed via `style_loader_tag` filter, not `wp_dequeue_style`:** the plugin enqueues this handle inside its shortcode handler (`EasyOptInsShortcodes::enqueue_assets`, run during content rendering — *after* `wp_enqueue_scripts`), so a dequeue on `wp_enqueue_scripts` is a no-op (style not queued yet). That was the **v0.6.0 bug** (deployed, observed still loading); fixed in **v0.6.1** by dropping the printed `<link>` at render time (no CSS → no `@font-face` → woff never fetched). Registered front-end-only, so the wp-admin Optin editor is untouched.

**Why it's safe:** verified 2026-07-06 —
- Maps: the plugin renders no map anywhere (no `[put_wpgm]` in any post content, widget, or postmeta; `/where-to-find-us/` + homepage render zero `gm-style` maps). The `has_shortcode` guard auto-preserves assets on any future real map page.
- Dashicons: no front-end element renders a dashicons glyph (0 rendered glyphs incl. `::before`/`::after` on home/shop/product). The hook is `wp_enqueue_scripts` (front-end only) + `!is_admin_bar_showing()`, so wp-admin and logged-in users are untouched — the only dashicons consumers found (Yoast import screen, WC PDF setup wizard) are admin-side.
- Mailchimp FA: live headless glyph audit found exactly **one** homepage element resolving to the v4 `FontAwesome` family — the `.eut-top-btn.fa-angle-up` back-to-top button. Disabling this stylesheet in the DOM falls its `::before` glyph back to the theme's `"Font Awesome 6 Free"` and it **still renders** (v4-shims maps `.fa-angle-up`; codepoint U+F106 present in `fa-solid-900`). No mailchimp-form FA glyph renders on the homepage. Only the FA copy is dequeued — the plugin's form styling (`tooltipster` / `featherlight` / `style-new`) is left intact. **Correction to #56:** the issue's §2 proposed dequeuing Simple-Line-Icons "if unused" — it **is** used (5 rendered glyphs: globe-alt, credit-card, location-pin, present, paper-plane in the feature row), so its 52 KB is **kept**. The `< 150 KB` icon-font target in #56 is not reachable without subsetting the theme's own FA (147 + 107 KB), a higher-risk theme-file change — out of scope here.

**Verification (deterministic):**
- Per-page map assets = 0 across `/`, `/fr/`, `/shop/`, `/our-products/`, `/where-to-find-us/`, `/wrap/`; all HTTP 200, 0 PHP errors.
- `harness/fingerprint.sh` (12 pages): all 200, no errors/warns/shortcode-leak/mojibake, per-page script/file counts reduced. See `reports/perf-after-mapdequeue-2026-07-06/`.
- Homepage requests 181 → 154 (headless); `maps.google.com` host absent.
- Icon fonts (#56): **deployed + verified live 2026-07-06 (v0.6.1).** Headless `PerformanceResourceTiming`, EN `/` + FR `/fr/` (cache-buster + full cache purge): `fontawesome-webfont.woff` **absent**, FA `<link>` **gone**, icon-font transfer **407 → 311 KB** (−96 KB). All icons still render: back-to-top `.fa-angle-up` now resolves to theme `"Font Awesome 6 Free"` (was v4 `FontAwesome`); 5 Simple-Line-Icons + all `.fa-*`/`.fab` brand icons (search, facebook, instagram, linkedin, pinterest, youtube) render; 0 console errors both languages.

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

## `io-remove-ua.php`

Strips the obsolete Universal Analytics tag `UA-85910237-1` from front-end output. Tracks GH #3.

**Deployed to:** `wp-content/mu-plugins/io-remove-ua.php` on live (auto-loaded — no activation step). **Deployed + verified 2026-07-06.**

**What it does (v1.0):** unhooks the UA emitter — `remove_action('wp_head', 'io_analytics', 20)`. The UA block is emitted by `io_analytics()` in the bespoke plugin `wp-content/plugins/impression_originale/impression_originale.php` (`add_action('wp_head','io_analytics',20)`). That function outputs **only** the dead UA gtag loader + config, so removing the action removes exactly the UA tag and nothing else. The unhook is registered on `wp_head` priority 1 (before the plugin's priority 20 fires).

**Why a mu-plugin, not a plugin-file edit:** editing `impression_originale.php` directly is a live edit to a bespoke plugin with no clone (ADR 0001); the mu-plugin `remove_action` needs no source edit and rolls back by deleting one file — same pattern as `io-perf-dequeue.php`.

**Correction to the prior finding:** #3 / `docs/analytics-ga4-migration.md` said the UA tag was "hardcoded in the **theme** PHP." Wrong — it is in the **custom plugin** `impression_originale`, function `io_analytics`. (Better Search Replace's "0 DB rows" was correct — it is in a PHP file, not the DB — but the *theme* inference was not.)

**Why it's safe:** GA4 (`G-Y88VQHFDBV`) fires from the GTM container `GTM-MT7G7Z3C` at runtime, **not** from `io_analytics()`, so removing UA does not touch GA4 or GTM. Verified live (2026-07-06, external fetch, normal + cache-buster): UA `2 → 0`, `GTM-MT7G7Z3C` unchanged (1), `GT-5TPLSSZ` unchanged (pre-existing, not introduced).

**Deploy / rollback:**
```bash
# deploy (lint-gated)
D=/nas/content/live/impressionor/wp-content
scp io-remove-ua.php <ssh>:$D/_stage.php   # or: ssh <ssh> 'cat > $D/_stage.php' < io-remove-ua.php
ssh <ssh> "php -l $D/_stage.php && mv $D/_stage.php $D/mu-plugins/io-remove-ua.php"
# rollback: delete the file
ssh <ssh> "rm $D/mu-plugins/io-remove-ua.php"
# then purge WP Rocket + WPE + Cloudflare
```

**Verify (deterministic, after deploy + cache purge):**
```bash
curl -sL https://www.impressionoriginale.com/ | grep -c 'UA-85910237-1'          # expect 0
curl -sL https://www.impressionoriginale.com/ | grep -oiE 'gtag/js\?id=[A-Z0-9-]+' # UA gone
curl -sL https://www.impressionoriginale.com/ | grep -c 'GTM-MT7G7Z3C'            # expect >=1 (unchanged)
# GA4 still collecting: Google Tag Assistant / GA4 Realtime for G-Y88VQHFDBV (accept the Termly banner first)
```
