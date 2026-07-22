---
name: io-font-preloads-cdn-fix
description: "SUPERSEDED by [[io-font-preloads-broke-site]]. 4 CDN-correct font preloads — deployed 2026-07-22, broke site, rolled back same day. auto_preload_fonts restored to 1. ADR 0016."
metadata:
  type: project
  originSessionId: current
  modified: 2026-07-22T13:36:38.775Z
  superseded: true
---

# Font Preloads — CDN-Correct URLs

## Problem

WP Rocket `auto_preload_fonts: 1` scans `@font-face` declarations in CSS and auto-generates `<link rel="preload" as="font">` tags. But it resolves relative URLs against `site_url()` (`www.impressionoriginale.com`), while the actual CSS files are served from the RocketCDN CNAME (`5ec66156.delivery.rocketcdn.me`). The browser fetches the preloaded font from the origin, but the CSS `@font-face` resolves to the CDN — different URLs, different cache keys. The 2 preloaded fonts (~260KB: fa-solid-900 + fa-brands-400) were downloaded from the wrong origin and discarded. Wasted bandwidth + connection slots.

Additionally, fonts loaded late in the critical path chain (each ~1s), discovered sequentially as CSS parsed — not preloaded.

## Fix (deployed 2026-07-22)

**Two-part fix:**

1. **WP-CLI:** `wp option patch update wp_rocket_settings auto_preload_fonts 0` — stops the 260KB wasted downloads
2. **New mu-plugin `io-font-preloads.php`** — 4 manual CDN-correct preloads via `wp_head` priority 1

Preloaded fonts (all verified HTTP 200, font/woff2, BunnyCDN):

| Font | URL | Size |
|---|---|---|
| FA Solid 900 | `.../engic/webfonts/fa-solid-900.woff2` | 150KB |
| FA Brands 400 | `.../engic/webfonts/fa-brands-400.woff2` | 110KB |
| Yanone Kaffeesatz | `.../google-fonts/fonts/s/yanonekaffeesatz/v34/...` | 27KB |
| Lato 400 | `.../google-fonts/fonts/s/lato/v25/...` | 24KB |

Total: 4 preloads, 311KB. FA Solid first (largest, header icons), then FA Brands (social icons), then Yanone Kaffeesatz (H1 LCP text), then Lato 400 (body text).

`crossorigin` required per spec for `as="font"` — CDN is cross-origin (BunnyCDN).

## Known Limitations

- **Google Fonts cache paths hardcoded** (`yanonekaffeesatz/v34`, `lato/v25`). When WP Rocket updates its font cache, these URLs may 404. Check `wp-content/cache/fonts/1/google-fonts/fonts/s/` for current paths.
- **euthemians.woff2 NOT preloaded** — 404 on CDN (separate pre-existing issue).
- **Hardcoded URLs are fragile** but better than `auto_preload_fonts` generating wrong-origin URLs that never match.

## Deployment

1. `wp option patch update wp_rocket_settings auto_preload_fonts 0`
2. Deploy `io-font-preloads.php` to `/nas/content/live/impressionor/wp-content/mu-plugins/`
3. Full cache purge (RULE 20: Rocket → Varnish → CDN)

## Verification (2026-07-22)

- `cf-cache-status: MISS`
- 4 CDN font preloads present (`grep -c 'delivery.rocketcdn.me.*woff2'` → 5, includes CSS reference)
- 0 origin font preloads (`grep -c 'rocket-preload.*font.*impressionoriginale.com'` → 0)
- Site HTTP 200

## Rollback

```bash
ssh impressionor@impressionor.ssh.wpengine.net 'wp option patch update wp_rocket_settings auto_preload_fonts 1'
ssh impressionor@impressionor.ssh.wpengine.net 'rm /nas/content/live/impressionor/wp-content/mu-plugins/io-font-preloads.php'
# Full cache purge
```

## Related

- [[termly-preconnect-async-fix]] — companion fix: Termly preconnect crossorigin removal (2026-07-22)
- [[rucss-enabled-css-async-works]] — RUCSS state
- [[lcp-fix-session-postmortem]] — RULE 21, RULE 25
- PR #115

**Why:** WP Rocket's `auto_preload_fonts` is incompatible with RocketCDN — URL resolution uses the wrong base. The 2 auto-generated preloads wasted 260KB and a connection slot on every page load. Manual CDN-correct preloads fix this.

**How to apply:** Deploy both the WP-CLI setting change AND the mu-plugin. The mu-plugin alone won't work if `auto_preload_fonts: 1` is still generating wrong-origin preloads alongside it. Deploy order: disable setting first, then deploy mu-plugin, then purge.
