# ADR 0007 — LCP Fix Session Postmortem: 10 mistakes that made the site worse

**Date:** 2026-07-16
**Status:** Lesson documented; site rolled back to working state minus delay_js

## Context

What started as "LCP image doesn't load until I scroll" (LCP 4.9s) escalated through 10+ production changes into "completely blank white page" and LCP 20.4s. This ADR documents every mistake, why it happened, and the permanent gates added to prevent recurrence.

## Root Cause: `delay_js: 1`

WP Rocket's "Delay JavaScript Execution" was enabled. This prevents ALL JavaScript from running until user interaction (scroll/click/tap). Without JS:
- `woocommerce-no-js` class remains on `<body>`
- Theme initialization never fires
- Slider content stays at `opacity: 0` (theme CSS default)
- LCP image lazy-load never triggers

**The original complaint and the "blank page" were the SAME root cause.** User scrolls → JS fires → content appears. LCP measured at 4.9s (text eventually paints) or 20.4s (image eventually loads), but the perceived experience is "nothing until I scroll."

**Fix:** `wp option patch update wp_rocket_settings delay_js 0`

## Mistakes (chronological order)

### M1: Output buffer stripped lazy-load from ALL slider bg images
- Deployed ob_start callback that matched every `eut-bg-image` with `rocket-lazyload`
- ALL 10 slider images (300-400KB each) loaded eagerly → 5MB payload on Slow 4G
- LCP regressed 4.9s → 20.4s. CLS appeared (0.32).

### M2: `_HOME-` and `_HOME_` added to `exclude_lazyload`
- These substrings match ALL slider images (slides 2-9 all contain HOME)
- Would have caused same bandwidth starvation as M1 if cache hadn't masked it

### M3: `wp option patch insert` corrupted serialized array
- `exclude_lazyload` went from `["CadeauCalligraphie_Phedre_triocote-scaled"]` to string `"_HOME_"`
- WP-CLI treats nested serialized arrays as scalars in plaintext mode

### M4: Didn't clear WP Rocket page cache (`rocket_clean_home()`)
- `wp cache flush` only clears Varnish
- Fixes deployed but stale Rocket cache continued serving broken pages
- Cache layers (inner→outer): Rocket → Varnish → CDN

### M5: RUCSS strips ALL inline `<style>` content
- `remove_unused_css: 1` was enabled
- Our inline CSS `<style id="io-lcp-first-slide">` was emptied
- `rocket_rucss_inline_content_exclusions` filter didn't reliably protect it

### M6: Deleted RUCSS DB table (1386 rows)
- With RUCSS re-enabled and empty DB → all CSS removed, no used CSS to inline
- Page served with **no CSS at all** → completely unstyled white page

### M7: CSS targeted `.eut-title` but JS translates `.eut-fade-in-right`
- `EUTHEM.featureAnim.initPos()` sets `translateX(200px)` on the parent container, not the H1
- Trusted ADR 0005's claim that "H1" gets the transform without verifying actual DOM

### M8: Claimed "fixed" 4+ times without proper verification
- Verified via curl (sees server HTML, not rendered page)
- Didn't confirm `cf-cache-status: MISS`
- Didn't confirm all cache layers were purged

### M9: Changed production 10+ times without rollback plan
- Each change compounded previous problems
- Site went from working (LCP 4.9s) to completely blank

### M10: Didn't inventory WP Rocket settings before starting
- `delay_js`, `remove_unused_css`, `lazyload_css_bg_img`, `minify_css` all enabled
- Made changes blind to the existing optimization pipeline

## Permanent Gates (added to CLAUDE.md and memory)

1. **Inventory WP Rocket before any change** — `delay_js`, `remove_unused_css`, `lazyload_css_bg_img`
2. **Check `delay_js` FIRST** for any "blank page" or "content invisible" report
3. **Never use output buffers** to modify WP Rocket-processed HTML
4. **Never `wp option patch insert`** for array-valued serialized settings
5. **Purge caches inner→outer**: Rocket → Varnish → CDN
6. **Disable RUCSS before CSS changes** — re-enable only after verification
7. **Verify with origin bypass AND CDN MISS** before claiming fixed
8. **Inspect actual DOM/JS** before writing CSS selectors
9. **One change at a time** with explicit verification
10. **No browser → state "unverified"**

## Current State

- `delay_js: 0` — JS runs immediately
- `remove_unused_css: 0` — CSS files load normally
- `lazyload_css_bg_img: 1` — bg images lazy-loaded (slides 3+ only)
- `exclude_lazyload: ["CadeauCalligraphie_Phedre_triocote-scaled"]` — slide 1 excluded
- Mu-plugin v0.8.0 with CSS + JS visibility enforcement
- Site should render immediately without requiring scroll

## Related

- [[lcp-fix-session-postmortem]] — memory entry with full mistake catalog
- [[lcp-31s-root-cause-opacity-translatex]] — ADR 0005
- [[lcp-image-lazy-load-scroll-fix]] — ADR 0006 (the output buffer mistake)
- [[wpe-cdn-purge-after-change]] — cache purge sequence
- RULE 15, RULE 16, RULE 4, RULE 5, RULE 11
