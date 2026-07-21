---
name: async-css-not-working-without-rucss
description: "async_css: 1 does not make CSS async without RUCSS. Tested 4 config combinations — all show 29 sync CSS files. Same settings worked Jul 18 (ADR 0010). Something changed."
metadata:
  type: project
  originSessionId: current
---

# async_css Not Working Without RUCSS

**Date:** 2026-07-21

## Evidence

Four WP Rocket configurations tested after RUCSS broke. All show 29 external CSS files loading with `media='all'` (render-blocking). Zero CSS files use the `media="print" onload` async pattern.

| Test | remove_unused_css | optimize_css_delivery | minify_css | Async CSS | Sync CSS |
|---|---|---|---|---|---|
| 1 | 0 | 1 | 1 | 0 | 29 |
| 2 | 0 | 1 | 0 | 0 | 29 |
| 3 | 0 | 0 | 1 | 0 | 29 |
| 4 | 0 | 0 | 0 | 0 | 29 |

All tests had `async_css: 1` enabled.

## Why this is unexpected

ADR 0010 (Jul 18) documented the same settings (rucss=0, opt=1, async=1, minify=0) with CSS loading async. The `async_css` feature should add `media="print" onload="this.media='all'"` to CSS `<link>` tags via WP Rocket's output buffer.

WP Rocket's output buffer IS running — markers like `wpr-lazyload`, `rocket-preload` appear in the HTML. The `async_css` transformation specifically is not happening.

## Hypotheses

1. **WP Rocket 3.23 changed `async_css` behavior** — upgraded from 3.22 (per [[rucss-enabled-css-async-works]]), possibly removed standalone async CSS support
2. **`optimize_css_delivery` now requires RUCSS** — the older Critical CSS fallback was removed in 3.23
3. **Critical CSS must exist** — `optimize_css_delivery: 1` without RUCSS requires generated critical CSS in `/cache/critical-css/`, which is empty (just index.html)

## Impact

Without RUCSS AND without async_css working, the only CSS delivery option is 29 synchronous render-blocking stylesheets. The io-lcp block ([[io-lcp-critical-css-fix-deployed]]) provides H1 styles but can't make the other 29 CSS files async.

## How to apply

Do NOT disable RUCSS expecting async_css to take over — it won't. If RUCSS must be disabled (e.g., for truncate/reload cycle), accept that CSS will load sync until RUCSS is re-enabled and generating real used CSS.

See [[rucss-saas-empty-css]] for the RUCSS fix path.

## Related

- [[rucss-saas-empty-css]] — why RUCSS is broken
- [[rucss-enabled-css-async-works]] — RUCSS WAS working, CSS was async
- [[async-css-mandatory-for-this-site]] — async CSS is load-bearing for this site
- ADR 0012 — full incident report
