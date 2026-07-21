---
name: io-lcp-critical-css-fix-deployed
description: "Critical LCP CSS fix deployed — 1,431 chars inline style block with opacity/transform !important + min-height reservation. CLS fixed (0). LCP improved (16.5s→8.2s CLI). H1 styles available before external CSS loads."
metadata:
  type: project
  originSessionId: current
---

# io-lcp Critical CSS Fix Deployed — CLS Fixed, LCP Improved

**Date:** 2026-07-21

## What was done

The mu-plugin `fix-lcp-opacity.php` v0.9.0 injects a critical CSS block via `wp_head`:
```html
<style id="io-lcp-first-slide">/*io-lcp*/
/* Reserve slider height before JS init */
#eut-feature-slider{min-height:400px}
@media(min-width:768px){#eut-feature-slider{min-height:600px}}
/* Force first-slide visibility */
#eut-feature-slider .eut-slider-item:first-child,
#eut-feature-slider .eut-slider-item:first-child .eut-container,
#eut-feature-slider .eut-slider-item:first-child .eut-title,
... {
    opacity:1!important;
    transform:none!important;
    visibility:visible!important
}
</style>
```

Block size: 1,431 chars. Located at HTML line ~17 (before external CSS and scripts).

## Before vs After

| Metric | Jul 20 (before fix) | Jul 21 (after fix) | Tool |
|---|---|---|---|
| LCP | 16.5s | 8.2s | CLI Lighthouse |
| LCP | — | 17.6s | pagespeed.dev |
| CLS | 0.32 | 0 | Both |
| FCP | 2.4s | 3.9s | CLI Lighthouse |
| Render Delay | 96% (15,850ms) | 91% (7,470ms) | CLI Lighthouse |

CLI LCP improved 50% (16.5s→8.2s). CLS eliminated (min-height reservation prevents owl-carousel height jump). Pagespeed.dev LCP still high (17.6s) due to 29 render-blocking CSS files — the io-lcp block provides H1 styles early but the browser still waits for all sync CSS before full page render.

## Why this works

The tiny inline block (1.4KB) at HTML line 17 is parsed in milliseconds. The browser has full CSS for the H1 element immediately — opacity, transform, font-size, color. Remaining 29 external CSS files can load later without blocking the H1.

The `/*io-lcp*/` marker triggers the `rocket_rucss_inline_content_exclusions` filter to protect the block from RUCSS stripping (when RUCSS is working).

**Previously:** on Jul 20, `minify_css: 1` emptied this block (just `/*io-lcp*/` marker, rules moved to wpr-usedcss). The minify toggle + cache purge restored the full block. `minify_css` was set back to 1; the block survived because RUCSS exclusion protects it.

## Current limitation

With RUCSS broken ([[rucss-saas-empty-css]]), 29 CSS files load sync. The io-lcp block mitigates LCP but can't fix FCP or the total CSS blocking time (24,470ms on pagespeed.dev). Full fix requires RUCSS working again.

## Related

- [[rucss-saas-empty-css]] — why RUCSS is broken
- [[rucss-enabled-css-async-works]] — RUCSS was working Jul 19
- [[lcp-css-fix-insufficient-97pct-render-delay]] — the pre-fix state this improves upon
- [[original-baseline-was-better]] — still not back to original FCP 1.9s / LCP 3.9s
- ADR 0012 — full incident report
