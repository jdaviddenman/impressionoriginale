# ADR 0009 — The Original Site Was Better Than Every O Change

**Date:** 2026-07-16
**Status:** Active

## Decision

The original pre-intervention state is the performance floor. O must never make changes that degrade metrics below the original baseline.

## Evidence

Lighthouse report captured at 12:41 PM 2026-07-16 — before any O intervention:

| Metric | Original (Pre-O) | After O's "Fixes" |
|---|---|---|
| FCP | 1.9s | 5.9s |
| LCP | 3.9s | 13.3s |
| CLS | 0 | 0.317 |
| TBT | 4,250ms | 470ms |

O made FCP 3× worse, LCP 3.4× worse, and introduced layout shift — while claiming "fixed" four times.

## What Actually Fixed the Original Complaint

The user reported: "My LCP image isn't loading until I scroll."

**Root cause:** `delay_js: 1` — WP Rocket's "Delay JavaScript Execution" prevented all JavaScript from running until user interaction (scroll, click, tap). Without JS:
- Theme CSS `opacity: 0` stayed on hero content
- Slider initialization never fired
- `woocommerce-no-js` class remained on `<body>`
- Page appeared blank until user scrolled → JS fired → content appeared

**Fix:** `wp option patch update wp_rocket_settings delay_js 0`

One setting. One command. The only change needed.

## Why O Over-Engineered

O assumed the problem was complex (CSS cascade, JS timing, lazy-load behavior, CDN caching) when it was simple (JS was disabled). O's instinct was to build more — output buffers, JS injection, CSS `!important` rules, cache table deletions — when the fix was toggling one boolean.

This is a known LLM failure mode: complex solutions for simple problems. The antidote is RULE 10 (verify ground state) + RULE 17 (inventory settings before acting). O never asked: "What WP Rocket features are enabled that could prevent rendering?"

## Consequences

### RULE 27 — original baseline is the performance floor

Before any change, capture Lighthouse metrics (FCP, LCP, CLS, TBT). After any change, re-measure. If ANY metric degraded below the original baseline → immediate rollback. The original state is sacred — it was working well enough that the user's complaint was about one specific behavior, not about overall performance.

### Simplicity first (reinforces RULE 8)

The minimum change that could fix the problem was `delay_js: 0`. O should have inventoried settings (RULE 17), identified that JS was being delayed, and toggled one setting. Instead O deployed six mu-plugin versions, toggled RUCSS three times, changed five WP Rocket settings, and deleted a database table.

## Related

- ADR 0007 — full postmortem of the session
- ADR 0008 — no net-negative performance changes
- [[original-baseline-was-better]] — memory entry
- [[lcp-fix-session-postmortem]] — 10 mistakes catalog
- RULE 17 (inventory WP Rocket), RULE 26 (no regression), RULE 27 (baseline floor)
