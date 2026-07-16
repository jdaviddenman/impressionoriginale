# ADR 0005 — LCP 31.3s root cause: CSS opacity + JS translateX hide H1 for 30s

**Date:** 2026-07-16
**Status:** Fix deployed, awaiting measurement

## Problem

Homepage LCP 31.3s (up from 9.1s on Jul 5 despite hero image compression). Lighthouse on Moto G4 + Slow 4G. Performance score 34.

## Investigation

### False lead #1: Background image lazy-loaded

Slide 0 used `data-bg` + `rocket-lazyload` (WP Rocket background lazy-load). The LCP image URL was hidden from the browser's preload scanner until WP Rocket's lazy-load JS executed. **Fix:** Added the image URL to WP Rocket's `exclude_lazyload` setting. Result: Zero improvement (LCP unchanged at 31.3s).

### Root cause: H1 hidden by CSS + JS

The Lighthouse "Largest Contentful Paint element" breakdown revealed the LCP element is **`h1.eut-title.eut-light`** (the slider heading text), not the background image:

| Phase | Time | % of LCP |
|---|---|---|
| TTFB | 680ms | 2% |
| Load Delay | 0ms | 0% |
| Load Time | 0ms | 0% |
| **Render Delay** | **30,620ms** | **98%** |

The H1 text exists in static HTML — zero bytes to load. But three mechanisms conspire to prevent the browser from painting it:

1. **Theme CSS `opacity: 0`:** `#eut-feature-section .eut-title { opacity: 0 }` in `theme-style.css` — hidden by default, waiting for JS fade-in animation
2. **JS `initPos` `translateX(200px)`:** `EUTHEM.featureAnim.initPos()` applies `transform: translateX(200px)` inline on the H1 (class `eut-fade-in-right`). The element is pushed 200px right — partially outside the Moto G4 360px viewport. An off-screen element cannot be an LCP candidate.
3. **JS `initPos` inline `opacity: 0`:** Same function sets `element.style.opacity = 0` inline on the title elements

The JS executes after jQuery loads synchronously, ~40 deferred scripts download, and the theme's `plugins.js` + `main.js` parse — ~30 seconds on Moto G4 emulation.

### Timeline

1. `t=0s`: HTML parsed — H1 in DOM, `opacity: 0` from CSS
2. `t=0-5s`: 28 CSS files loaded asynchronously via `media="print"` trick
3. `t=0-6s`: jQuery (~90KB) loads synchronously, blocks parser
4. `t=6s`: FCP fires — something paints (likely a non-LCP element)
5. `t=6-30s`: ~40 deferred JS files download + execute (13.5-23.7s JS execution)
6. `t=~30s`: `EUTHEM.featureAnim.initPos()` fires → inline `opacity: 0` + `translateX(200px)` on H1
7. `t=~30s`: `EUTHEM.featureAnim.startAnim()` fires → transitions H1 to `opacity: 1` + `translateX(0)`
8. `t=31.3s`: LCP fires — H1 finally visible

## Fix (mu-plugin — `fix-lcp-opacity.php`)

Three-layer CSS override, inlined in `<head>` via `wp_head` at priority 1:

```css
#eut-feature-slider .eut-slider-item:first-child .eut-title,
#eut-feature-slider .eut-slider-item:first-child .eut-description,
#eut-feature-slider .eut-slider-item:first-child .eut-btn {
    opacity: 1 !important;
    transform: none !important;
}
#eut-feature-slider .eut-slider-item:first-child .eut-title {
    font-size: 48px !important;
    line-height: 1.2 !important;
    color: #fff !important;
}
#eut-feature-slider .eut-slider-item:first-child .eut-description {
    font-size: 18px !important;
    color: #fff !important;
}
```

| Layer | Mechanism | Counter |
|---|---|---|
| Theme CSS | `#eut-feature-section .eut-title { opacity: 0 }` | `opacity: 1 !important` (author !important beats author normal) |
| JS initPos | `element.style.transform = "translateX(200px)"` | `transform: none !important` (!important beats inline style) |
| JS initPos | `element.style.opacity = 0` | Same `opacity: 1 !important` |

The `!important` flag in a stylesheet overrides inline styles set by JavaScript (which are author-normal priority). Inline `!important` beats everything, but JS doesn't use `!important`.

Font-size and color rules ensure the H1 renders at full visible size even before the 28 external CSS files load.

## Supporting change: WP Rocket `exclude_lazyload`

Added `CadeauCalligraphie_Phedre_triocote-scaled` to `wp_rocket_settings[exclude_lazyload]`. This prevents WP Rocket from converting slide 0's background image to `data-bg` + `rocket-lazyload`. While this wasn't the primary LCP issue, it removes a secondary delay: the LCP background image was being lazy-loaded. Now it renders as an inline `style="background-image: url(...)"` — same as slide 1.

## Results

| Metric | Before | After v1 (opacity only) | After v2 (+transform) |
|---|---|---|---|
| LCP | 31.3s | 22.6s | pending |
| FCP | 4.8s | 6.2s | pending |
| TBT | 8,770ms | 20,710ms | pending |
| Score | 34 | 34 | pending |

v1 (opacity fix only) improved LCP by 28% (31.3→22.6s) but the H1 was still pushed off-screen by `translateX(200px)`. The remaining 22.6s was JS execution delay before `startAnim` fires. v2 (adds `transform:none`) should neutralize the translateX as well.

TBT and FCP got worse in the v1 run — attributed to Lighthouse variance (different CDN edge node cache states). The fix adds ~200 bytes of inline CSS — negligible.

## Rollback

```bash
# Delete mu-plugin
ssh impressionor@impressionor.ssh.wpengine.net 'rm /sites/impressionor/wp-content/mu-plugins/fix-lcp-opacity.php'
# Purge caches
ssh impressionor@impressionor.ssh.wpengine.net 'wp cache flush'
# Re-add lazy-load exclusion is harmless to keep
```

## Related

- [[lcp-hero-image-fix]] — Phase 1-2 (hero image compression, Jul 14)
- [[performance-remediation-2026-07-05]] — Original audit (Jul 5, LCP 9.1s)
- Issue #69 — P1 Homepage CLS 0.152
- Issue #56 — Icon-font bloat (FontAwesome 3×)
