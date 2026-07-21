# ADR 0015 — jQuery deferral experiment: net-negative, permanently ruled out

**Date:** 2026-07-21
**Status:** Accepted
**Relates to:** [[jquery-deferral-permanently-ruled-out]], [[lcp-fix-session-postmortem]], [[rucss-saas-empty-css]], ADR 0007

## Context

A PageSpeed Insights Lighthouse report (Performance 25) showed jQuery (33.5KB) as the only render-blocking external resource. All other scripts were deferred by WP Rocket's `defer_all_js: 1`. Despite `defer_all_js` being active, `jquery-core` and `jquery-migrate` loaded synchronously — WP Rocket excludes jQuery from deferral to avoid breaking inline scripts that reference `$`/`jQuery` before DOMContentLoaded.

Hypothesis: adding `defer` to jQuery via `script_loader_tag` filter would eliminate the last render-blocking resource, reducing FCP and LCP.

## Method

- Created mu-plugin `io-defer-jquery.php` v0.1.0 adding `defer` to handles `jquery-core` and `jquery-migrate`
- Deployed to live, full cache purge (Rocket → Varnish → CDN)
- Verified: 0 render-blocking external scripts, HTML structure intact
- Ran PageSpeed Insights Lighthouse

## Results (net-negative)

| Metric | Before | After | Δ |
|---|---|---|---|
| Performance | 25 | 25 | 0 |
| FCP | 2.6s | 2.7s | +0.1s |
| LCP | 6.4s | 18.8s | +12.4s (+194%) |
| TBT | 7,250ms | 11,270ms | +4,020ms (+55%) |
| SI | 12.0s | 13.3s | +1.3s |
| CLS | 0 | 0 | 0 |
| LCP element | h1.eut-title.eut-light | div.termly-styles-message | CHANGED |

## Root cause analysis

Two mechanisms contributed:

### 1. LCP element changed to Termly consent banner

Deferring jQuery removed the render-blocking script from the critical path. This allowed Termly's `async`-loaded resource-blocker JS (153KB) to download, parse, and execute earlier relative to page rendering. The Termly consent banner DOM insertion now preceded the H1 becoming the largest visible element. The Termly banner rendered with 18,120ms (96%) render delay — it's a DOM-heavy widget with CSS animations that requires JS to construct and display.

The pre-test LCP element (`h1.eut-title.eut-light`) was a static text element with `opacity:1!important` in critical CSS. It rendered at ~6.4s despite main-thread saturation. The Termly banner is DOM-injected by JS, so it appears later but is larger, making it the LCP candidate.

### 2. RUCSS was broken (masked in pre-test)

The pre-test Lighthouse ran against CDN-cached HTML (age: ~4h) that contained a valid `wpr-usedcss` block. The post-test ran against freshly-purged CDN, revealing that RUCSS SaaS was returning empty CSS. 29 CSS files loaded synchronously (`media="all"`), flagged as "Reduce unused CSS — Est savings 130 KiB." This added CSS parse/blocking time to an already JS-saturated main thread.

The RUCSS failure was independent of the jQuery change but compounded the degradation.

## Why it's ruled out

The approach changed the LCP element from a static HTML text node (H1) to a JS-injected DOM widget (Termly banner). This is fundamentally worse because:

- JS-injected elements always have longer render delays than static HTML text
- The Termly banner's render time depends on async script download + execution + DOM construction + CSS animation
- Any optimization that moves the LCP element from static content to JS-injected content is regression

The RUCSS failure at the same time makes it impossible to isolate how much of the TBT increase was from jQuery defer vs. RUCSS failure. But the LCP element change alone is disqualifying.

## Decision

**jQuery deferral is permanently ruled out.** Editing `script_loader_tag` to add `defer`/`async` to jQuery changes which element becomes LCP, and the replacement (Termly banner) is always worse than the original (H1 text).

RULE 26 applies: net-negative → immediate rollback → approach permanently ruled out.

## Related

- [[jquery-deferral-permanently-ruled-out]] — memory file
- [[lcp-fix-session-postmortem]] — prior net-negative changes (2026-07-16)
- [[rucss-saas-empty-css]] — RUCSS SaaS failure masking
- [[termly-preconnect-async-fix]] — Termly already async; this experiment proved it's load-bearing for LCP element selection
- ADR 0007 — delay_js ruled out (similar mechanism: changing script execution timing changes LCP element)
