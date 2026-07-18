---
name: async-css-mandatory-for-this-site
description: "Disabling async_css tripled TBT (11s→31s) and nearly doubled LCP (17s→30s). This setting must stay enabled. Approach permanently ruled out by RULE 26."
metadata:
  type: project
  originSessionId: current
---

# async_css + optimize_css_delivery Are Mandatory

**Date:** 2026-07-18
**Evidence:** Lighthouse run after toggling `async_css: 0` + `optimize_css_delivery: 0`

## Result: NET-NEGATIVE (RULE 26 — approach permanently ruled out)

| Metric | async_css=1 | async_css=0 | Delta |
|---|---|---|---|
| LCP | 17.3s | 30.0s | +73% |
| TBT | 11,430ms | 30,850ms | +170% |
| FCP | 4.4s | 5.3s | +20% |

## Why

Without `async_css`, all CSS files load synchronously (render-blocking). The browser waits for ALL external CSS before painting anything. Even though our inline `<style id="io-lcp-first-slide">` is present, the browser defers paint until external stylesheets arrive. On Slow 4G, that's catastrophic.

With `async_css: 1`, CSS files load via `media="print"` and swap to `all` on load — non-render-blocking. Inline styles apply immediately. This is essential for the CSS fix to have any chance of working.

## How to apply

Never disable `async_css` or `optimize_css_delivery` on this site. They are load-bearing. Any future CSS fix must work WITH these settings, not by disabling them. See [[lcp-css-fix-insufficient-97pct-render-delay]], [[lcp-fix-session-postmortem]].
