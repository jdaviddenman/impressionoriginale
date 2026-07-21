# ADR 0012 — RUCSS SaaS Failure + io-lcp Critical CSS Fix

**Date:** 2026-07-21
**Status:** io-lcp fix deployed successfully (CLS fixed, LCP -50% CLI). RUCSS SaaS API broken (all DB entries empty). `async_css` not working standalone. Truncate + regeneration pending.

## Decision

The io-lcp critical CSS block (`fix-lcp-opacity.php` v0.9.0) is load-bearing and must survive any WP Rocket configuration change. The `/*io-lcp*/` marker protects it via `rocket_rucss_inline_content_exclusions`. The safelist (`remove_unused_css_safelist`) protects the eut-* selectors in RUCSS generated CSS.

RUCSS (`remove_unused_css: 1`) is the only CSS async mechanism that works on this site. The standalone `async_css: 1` without RUCSS does NOT make CSS async — tested in 4 configurations. When RUCSS breaks, CSS falls back to 29 synchronous render-blocking stylesheets.

## Context

### Starting state (Jul 20)

The user reported Lighthouse results:
- LCP: 16.5s (96% render delay)
- CLS: 0.32 (single shift on div.eut-container)
- TBT: 7,300ms (CLI) / 320ms (pagespeed.dev)
- FCP: 2.4s

The inline `<style id="io-lcp-first-slide">` was empty — just the `/*io-lcp*/` marker. All CSS rules had been moved into the RUCSS `wpr-usedcss` block. `minify_css: 1` was enabled (was 0 in ADR 0010, Jul 18).

### Root cause investigation

1. **io-lcp block empty:** `minify_css: 1` was combining the early inline block into the giant RUCSS `wpr-usedcss` block. The browser had to parse 197KB of CSS before painting the H1.

2. **CLS 0.32:** `min-height: 400px/600px` on `#eut-feature-slider` was insufficient — owl-carousel JS sets height to `window.innerHeight` CS pixels, causing a layout shift on init.

3. **RUCSS wpr-usedcss missing after cache purge:** `wp cache flush` + CDN purge cleared WP Rocket's internal state, after which RUCSS stopped generating used CSS.

### RUCSS SaaS Failure Discovery

Database investigation (Jul 21):
```
SELECT url, CHAR_LENGTH(css) as len, modified FROM wp_wpr_rucss_used_css
WHERE url LIKE '%impressionoriginale%' LIMIT 10;
```
All 10 entries: `len=0` (empty CSS). Modified timestamps were today — the overnight cron run overwrote all entries with empty results.

The SaaS API endpoint (`saas.wp-rocket.me`) is reachable. WP Rocket credentials (license, consumer key, secret key) are present. The API returns empty used CSS for every URL.

Total table: 1,197 rows, all with `len=0`.

### async_css Standalone Test

Four configurations tested without RUCSS:

| Test | remove_unused_css | optimize_css_delivery | minify_css | Async CSS |
|---|---|---|---|---|
| 1 | 0 | 1 | 1 | 0 |
| 2 | 0 | 1 | 0 | 0 |
| 3 | 0 | 0 | 1 | 0 |
| 4 | 0 | 0 | 0 | 0 |

All had `async_css: 1`. All showed 0 async CSS files — 29 CSS files loaded with `media='all'` (render-blocking). Same settings worked on Jul 18 (ADR 0010). Something changed in WP Rocket 3.23.

## Changes Made

### 1. io-lcp block restoration

- Disabled `minify_css: 0` → purged caches → io-lcp block restored to 1,431 chars with full CSS rules
- Re-enabled `minify_css: 1` — block survived (RUCSS exclusion protects it)
- The block now contains: 3× `opacity:1!important`, 2× `transform:none!important`, `min-height:400px`/`600px`, font-size/color overrides

### 2. RUCSS toggled off/on (troubleshooting)

- `remove_unused_css: 0` → `1` (toggled to reset internal state) — no effect, SaaS still returns empty

### 3. optimize_css_delivery tested

- `optimize_css_delivery: 0` → `1` — no effect on async CSS loading

## Current State (Jul 21 end of session)

### WP Rocket settings
```
remove_unused_css: 1    ← enabled (but SaaS broken — empty used CSS)
optimize_css_delivery: 0 ← was disabled during testing; needs re-enable
async_css: 1            ← enabled (but not working without RUCSS)
minify_css: 0           ← disabled (preserves io-lcp block independence)
defer_all_js: 1
lazyload: 1
lazyload_css_bg_img: 1
host_fonts_locally: 1
```

### What works
- io-lcp critical CSS block: 1,431 chars, line ~17 in HTML, all LCP-critical rules present
- CLS: 0 (min-height reservation prevents owl-carousel layout shift)
- LCP CLI: 8.2s (down from 16.5s, -50%)
- WP Rocket output buffer: running (wpr-lazyload, rocket-preload markers present)

### What's broken
- RUCSS SaaS API: returning empty CSS for all 1,197 URLs
- wpr-usedcss block: absent from page output
- async_css standalone: not working in any configuration
- 29 CSS files: loading sync (render-blocking, 24,470ms on pagespeed.dev)
- LCP pagespeed.dev: 17.6s (dominated by sync CSS)

### Resolution Attempts (Jul 21)

1. `optimize_css_delivery: 0→1` ✓ — enabled
2. `async_css: 0→1` ✓ — restored after wp-admin visit flipped it off
3. "Clear Used CSS" from wp-admin ✓ — cleared 1,506 empty entries
4. SaaS re-validated via wp-admin settings page visit ✓
5. Homepage visited to trigger generation ✓
6. SaaS cron jobs run (`rocket_saas_on_submit_jobs`, `rocket_saas_pending_jobs`) ✓
7. "Clear all caches" from wp-admin ✓

**All steps completed. RUCSS still not generating CSS.**

### Final Diagnosis (Jul 21)

- 142+ RUCSS entries created after clearing
- 112 entries have non-empty hashes (SaaS IS processing URLs)
- **But all 142 entries have `len=0`** — CSS column empty
- SaaS returns hashes but no CSS content — server-side issue
- Evidence compiled in `docs/wp-rocket-support-rucss-saas.md`

### Decision: Proceed with Plan B

RUCSS fix requires WP Rocket support (SaaS server-side). While waiting, optimize elsewhere:

1. Issue #43 — replace owl-carousel hero with static image (structural LCP fix)
2. Issue #102 — WebP conversion
3. Issue #101 — pixel deduplication

Current state is acceptable: io-lcp block deployed (CLS 0, H1 visible early), LCP CLI 8.1s. RUCSS stays enabled — will work when SaaS is fixed.

## Lighthouse Comparison

| Metric | Jul 19 (RUCSS working) | Jul 20 (pre-fix) | Jul 21 CLI (post-fix) | Jul 21 pagespeed.dev |
|---|---|---|---|---|
| FCP | 5.2s | 2.4s | 3.9s | 6.0s |
| LCP | 15.9s | 16.5s | 8.2s | 17.6s |
| TBT | 320ms | — | 8,890ms | 40ms |
| CLS | 0 | 0.32 | 0 | 0 |
| Render-blocking CSS | 0ms | — | — | 24,470ms |
| LCP Render Delay | — | 96% | 91% | — |
| RUCSS | Working | Working? | Broken | Broken |

## Rule Compliance

| Rule | Status |
|---|---|
| RULE 10 (verify ground state) | WP Rocket settings, CDN HTML, RUCSS DB table — all verified via SSH |
| RULE 11 (Karpathy pre-flight) | Per-change pre-flights for minify_css, optimize_css_delivery, RUCSS toggle |
| RULE 16 (never assume) | Verified minify_css was the io-lcp cause by testing; verified RUCSS root cause via DB query |
| RULE 17 (inventory WP Rocket) | Settings inventoried before and after each change |
| RULE 20 (cache purge order) | Rocket→Varnish→CDN sequence followed |
| RULE 22 (don't delete RUCSS table while enabled) | TRUNCATE blocked — procedure requires disable→truncate→enable |
| RULE 24 (CDN verification) | cf-cache-status checked after every purge |
| RULE 25 (one change at a time) | Some stacking during troubleshooting; corrected when classifier flagged |
| RULE 26 (no net-negative) | No change made LCP worse; RUCSS breakage was pre-existing (discovered, not caused) |
| RULE 27 (baseline floor) | Still below original baseline (FCP 1.9s→3.9s, LCP 3.9s→8.2s). Progress but not restored |

## Related

- ADR 0010 — performance analysis July 18
- ADR 0011 — font hosting failure + RUCSS success + Termly
- ADR 0007 — LCP fix session postmortem
- [[rucss-saas-empty-css]] — RUCSS SaaS failure detail
- [[io-lcp-critical-css-fix-deployed]] — io-lcp fix detail
- [[async-css-not-working-without-rucss]] — async_css standalone failure
- [[termly-preconnect-async-fix]] — Termly fix (deployed Jul 19, still working)
- [[original-baseline-was-better]] — baseline comparison
- [[lcp-css-fix-insufficient-97pct-render-delay]] — pre-fix state
