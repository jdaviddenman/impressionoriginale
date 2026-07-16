---
name: original-baseline-was-better
description: "The pre-O site (LCP 3.9s, FCP 1.9s, CLS 0) was better than anything O produced. The fix for \"image loads on scroll\" was delay_js:0 — one setting."
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 8fe8ee67-16da-45fb-a561-61a5d894254b
---

# Original Baseline Was Better Than Every O Change

**Date:** 2026-07-16
**Source:** Lighthouse report captured at 12:41 PM — before any O intervention

## Original Metrics (Pre-O)

| Metric | Value |
|---|---|
| FCP | 1.9s |
| LCP | 3.9s |
| CLS | 0 |
| TBT | 4,250ms |
| Speed Index | Error (no screenshots — JS not running) |

## Post-O Metrics (After 10+ Changes)

| Metric | Value | Delta |
|---|---|---|
| FCP | 5.9s | +3.9s (3x worse) |
| LCP | 13.3s | +9.4s (3.4x worse) |
| CLS | 0.317 | +0.317 (was 0) |
| TBT | 470ms | -3,780ms (improvement) |

## What Actually Fixed the Original Problem

The user's complaint: "My LCP image isn't loading until I scroll."

**Root cause:** `delay_js: 1` — WP Rocket delayed all JavaScript until user interaction. JS never ran on page load. The hero slider content stayed hidden by theme CSS (`opacity: 0`) until the user scrolled, which triggered JS execution, which ran the theme's animation to show the content.

**Fix:** `wp option patch update wp_rocket_settings delay_js 0`

That's it. One setting. TBT improved from 4,250ms to 470ms as a side effect.

## Everything Else O Did Was Destructive

1. Output buffer → stripped lazy-load from all 10 slider images → LCP 20.4s
2. RUCSS enable/disable → stripped inline CSS → blank page
3. Delete RUCSS DB table → page with zero CSS
4. `_HOME-`/`_HOME_` in exclude_lazyload → would have loaded all images eagerly
5. JS forceShow() → caused CLS 0.317
6. Mu-plugin CSS targeting wrong elements → multiple failed iterations
7. 10+ cache purges → each one a chance to break things further

## Lesson

**The original site was better than anything O produced.** When O's changes make metrics worse than the starting point, O is moving in the wrong direction. The fix for a specific complaint is usually simpler than O thinks. Start with the smallest possible change — a single setting toggle, not an output buffer or a JS injection framework.

**Why:** O over-estimates the complexity of problems and under-estimates the blast radius of its own solutions. A one-line settings change (`delay_js: 0`) solved the user's complaint. Everything else was unnecessary engineering.

**How to apply:** Before any change, capture the current Lighthouse. After any change, re-measure. If metrics are worse than the original → rollback. The original state is the floor — never go below it. See RULE 26, RULE 27.

## Related

- [[lcp-fix-session-postmortem]] — full catalog of 10 mistakes
- [[no-net-negative-performance]] — regression = immediate rollback
- ADR 0007, 0008, 0009
- RULE 26, RULE 27
