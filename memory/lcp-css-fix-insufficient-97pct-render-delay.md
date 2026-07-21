---
name: lcp-css-fix-insufficient-97pct-render-delay
description: "CSS !important fix (v0.9.0) is deployed and correct but LCP render delay stays at 97% across all conditions. CSS-only approach is insufficient — JS execution time is the bottleneck."
metadata:
  type: project
  originSessionId: current
---

# LCP CSS Fix Insufficient — 97% Render Delay Persists

**Date:** 2026-07-18
**Evidence:** Three Lighthouse runs (Moto G Power, Slow 4G, Lighthouse 12.6.0)

## Three-Run Comparison

| Run | LCP | TBT | Render Delay | async_css |
|---|---|---|---|---|
| Stale CDN | 27.0s | 11,960ms | 98% | 1 |
| CDN Purged | 17.3s | 11,430ms | 96% | 1 |
| async_css OFF | 30.0s | 30,850ms | 98% | 0 |

**Key finding:** Render delay is constant at ~97% regardless of CDN state, TBT variance (11s–31s), or CSS delivery method.

The CSS fix (`opacity:1!important; transform:none!important`) is present in HTML and logically correct (selectors match DOM, !important beats Transit's normal-priority inline styles). But it doesn't make the H1 render early. LCP stays dominated by render delay (96-98%).

## Why

The CSS fix overrides the symptom (hidden H1) but doesn't address the cause. Theme JS (jQuery Transit, Slider Revolution, WPBakery) consumes the main thread for 11-31 seconds on Slow 4G + 4× CPU slowdown. The browser either:
1. Can't commit a paint frame because the main thread is saturated (hypothesis 2)
2. Paints the H1 early but then resizes it when slider images load → LCP updates to later time (hypothesis 1)
3. The CSS genuinely doesn't apply in-practice despite being present in HTML (hypothesis 3, least likely)

**How to apply:** The CSS fix is necessary but not sufficient. Next step must address JS execution time — either defer non-critical JS, reduce third-party scripts, or prevent the slider resize that triggers LCP update. See [[lcp-fix-session-postmortem]], [[async-css-mandatory-for-this-site]], [[original-baseline-was-better]].

## Update 2026-07-21: io-lcp Critical CSS Deployment Improved LCP

On Jul 21, the io-lcp-first-slide inline CSS block was restored to full content (1,431 chars — was emptied to just `/*io-lcp*/` by `minify_css: 1` on Jul 19-20). The fix involved disabling minify_css briefly to repopulate the block, then re-enabling it. Results (CLI Lighthouse):

| Metric | Jul 20 (empty block) | Jul 21 (full block) |
|---|---|---|
| LCP | 16.5s | 8.2s (-50%) |
| CLS | 0.32 | 0 (fixed) |
| Render Delay | 96% (15,850ms) | 91% (7,470ms) |

CLS eliminated — the `min-height: 400px/600px` reservation on `#eut-feature-slider` prevents owl-carousel height jump. LCP render delay dropped 8,380ms.

**Remaining problem:** RUCSS SaaS API broken ([[rucss-saas-empty-css]]), 29 CSS files load sync (24,470ms render-blocking on pagespeed.dev). LCP pagespeed.dev still 17.6s. Full fix requires RUCSS working. See [[io-lcp-critical-css-fix-deployed]], [[async-css-not-working-without-rucss]].
