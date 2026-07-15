# LCP Hero Image Fix

**Date:** 2026-07-14
**Status:** Phase 2 complete — images compressed. Phase 1 (CSS) blocked by WP Rocket SaaS. Phase 3 (JS) deferred pending LCP measurement.

## Problem

Homepage LCP 9.1s (Google "Poor" bucket: >4.0s). The two eagerly-loaded hero slider images were the largest render-blocking resources after JS/CSS.

## Root Causes (Adversarially Reviewed)

| Bottleneck | Impact | Status |
|---|---|---|
| 28 render-blocking stylesheets | Browser waits for all CSS before first paint | Blocked — WP Rocket Remove Unused CSS SaaS can't reach origin (Cloudflare bot detection) |
| 1.38MB render-blocking JS (no defer/async) | ~4-6s delay on mobile before hero image starts | Deferred — measure LCP after image fix first |
| Portrait hero images in landscape slider | Wrong aspect ratio wastes 75% pixels; 226KB+537KB JPEGs | **Fixed** |
| `fetchpriority="high"` on below-fold thumbnails | Competes with hero preload for bandwidth | Not addressed (needs WPBakery edit) |

## What Was Done

### Phase 1 — Remove Unused CSS (reverted)

Enabled WP Rocket `remove_unused_css`. Setting took effect but SaaS generation failed — all 79 URLs returned "400: Job has no result" because Cloudflare bot detection blocks WP Rocket's SaaS crawler at scale. Fix requires WPE-support Cloudflare IP allowlist (https://docs.wp-rocket.me/article/1628). Reverted. `async_css` remains as fallback if LCP still >4s after image fixes.

### Phase 2 — Hero Image Compression (applied)

Two hero slider images replaced via GD on-server:

| Image | Before | After | Change |
|---|---|---|---|
| Slide 0 (CadeauCalligraphie) | 231KB, 1440×1920 portrait | 118KB, 1440×960 landscape | -49%, portrait→landscape crop |
| Slide 1 (Wraps HOME) | 550KB, 11069×7379 | 130KB, 1920×1280 | -76%, resized |
| **Combined LCP payload** | **781KB** | **248KB** | **-68%** |

**Method:** PHP GD `imagecreatefromjpeg()` + `imagejpeg()` at quality 75, run via `wp eval-file`. Backups preserved on-server at `*.backup-20260714-*`.

**Preload** already existed — WP Rocket added `<link rel="preload" as="image" fetchpriority="high">` for slide 0 before this fix. Slide 1 not preloaded (lazy CSS background).

### What Was NOT Done

- **Slider autoplay** — paused it would kill 10 CTAs (11 slides, each a product category). Autoplay 5500ms preserved.
- **`fetchpriority="high"` removal from below-fold thumbnails** — needs WPBakery edit, deferred.
- **Unused Slider Revolution CSS** — `sr7css` loads on homepage despite using theme slider, not SR. Low priority.

## Verification

- Pre/post fingerprints diffed across 13 pages: all 200, no regressions, no shortcode leaks, no mojibake
- Image sizes confirmed via `curl -sI`: 118,245 and 129,704 bytes
- Cloudflare cache purged, cf-cache-status confirmed MISS
- LCP re-measurement pending — PageSpeed Insights or Lighthouse

## Rollback

Backup files on server:
```
/sites/impressionor/wp-content/uploads/2026/02/IMPRESSION-ORIGINALE_CadeauCalligraphie_Phedre_triocote-scaled.jpg.backup-20260714-125552
/sites/impressionor/wp-content/uploads/2026/02/IMPRESSION-ORIGINALE_Wraps_HOME_Web.jpg.backup-20260714-125644
```

To rollback: copy each `.backup-*` file over the corresponding `.jpg` on the server, purge cache.

## Next Steps

1. Measure LCP via PageSpeed Insights — if <4s, gate condition met
2. If still >4s: enable `async_css` in WP Rocket (local, no SaaS dependency)
3. If still >4s: enable `delay_js` or `defer_all_js`
4. Remove `fetchpriority="high"` from below-fold thumbnails
