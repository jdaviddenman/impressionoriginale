---
name: cli-lighthouse-tbt-misleading
description: "CLI Lighthouse TBT (7k-30k ms) is an artifact of weak emulation hardware. Pagespeed.dev (Google infra) shows real TBT = 110-160ms. Always cross-validate before optimizing for TBT."
metadata:
  type: feedback
  originSessionId: current
---

# CLI Lighthouse TBT Is Misleading — Cross-Validate with Pagespeed.dev

**Date:** 2026-07-19

## Evidence

Four simultaneous Lighthouse runs (Moto G Power, Slow 4G, same page):

| Source | TBT | LCP |
|---|---|---|
| CLI Lighthouse 12.6.0 | 7,220-11,960ms | 17.2-17.3s |
| Pagespeed.dev (Lighthouse 13.4) | 110-160ms | 15.4-22.6s |

**TBT discrepancy: 50-100×.** CLI Lighthouse reports massive TBT because the emulation hardware is weak. Google's actual infrastructure shows TBT is essentially zero for this site.

## Why This Matters

O spent two sessions (Jul 18-19) optimizing for TBT:
- Minifying CSS
- Disabling PYS dead modules (GTM, GA)
- Attempting font hosting
- Investigating pixel deduplication

None of these addressed the real bottleneck because TBT was never actually a problem. Pagespeed.dev consistently shows TBT < 200ms.

## Permanent Rule

**Always cross-validate CLI Lighthouse against Pagespeed.dev before optimizing for any specific metric.** If there's a >5× discrepancy, believe Pagespeed.dev. CLI Lighthouse is useful for relative comparisons (did this change help or hurt?) but not for absolute TBT measurements.

**Why:** O optimized for a measurement artifact for two full sessions. The real bottleneck — 30+ render-blocking CSS files due to inert `optimize_css_delivery` — was only found when Pagespeed.dev's render-blocking-requests audit showed 22,410ms of CSS blocking time.

**How to apply:** Before any performance investigation, run BOTH CLI Lighthouse AND Pagespeed.dev. Compare all metrics. If TBT differs by >5×, do not optimize for TBT — focus on LCP render delay and render-blocking resources instead. See [[font-hosting-experiment-failure]], [[lcp-css-fix-insufficient-97pct-render-delay]].
