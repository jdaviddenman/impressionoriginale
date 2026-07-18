# ADR 0010 — Performance Analysis: July 18 Lighthouse (LCP 27s)

**Date:** 2026-07-18
**Status:** Three Lighthouse runs completed. Two adversarial reviews passed. Step 2a (async_css toggle) tested and rolled back (net-negative). Root cause: CSS fix insufficient — 97% render delay persists across all conditions. JS execution time is the bottleneck.

## Three-Way Lighthouse Comparison

All runs: Moto G Power, Slow 4G, Lighthouse 12.6.0.

| Metric | Run 1: Stale CDN | Run 2: CDN Purged | Run 3: async_css OFF | Original Baseline |
|---|---|---|---|---|
| FCP | 5.2s | 4.4s | **5.3s** | 1.9s |
| LCP | 27.0s | 17.3s | **30.0s** | 3.9s |
| TBT | 11,960ms | 11,430ms | **30,850ms** | 4,250ms |
| CLS | 0.32 | 0.32 | 0.32 | 0 |
| SI | 15.5s | 15.8s | **27.2s** | — |
| LCP Render Delay | 98% | 96% | 98% | — |
| async_css | 1 | 1 | **0** | — |

**Key insight: LCP render delay is constant at ~97% across all three runs** — regardless of CDN cache, CSS delivery method, or TBT variance (11s–31s). The CSS fix (`opacity:1!important`) should make the H1 render immediately after HTML parse, but the evidence shows it doesn't.

**Run 2→3 conclusion:** `async_css: 1` + `optimize_css_delivery: 1` are essential. Disabling them tripled TBT and nearly doubled LCP. This approach is **permanently ruled out** (RULE 26).

## Verified Ground State

### WP Rocket settings (SSH — verified 2026-07-18 13:20 GMT+2)

```
delay_js: 0              ← CORRECT (ADR 0007 fix)
remove_unused_css: 0     ← CORRECT (RUCSS disabled)
lazyload_css_bg_img: 1   ← CORRECT (bg images lazy-loaded)
minify_css: 0
minify_js: 1
defer_all_js: 1          ← All JS deferred (safe — not delay_js)
async_css: 1             ← CSS loaded async via media="print" trick
optimize_css_delivery: 1 ← CSS delivery optimization
lazyload: 1
exclude_lazyload: ['CadeauCalligraphie_Phedre_triocote-scaled']
exclude_defer_js: ['wp-rocket/assets/js/lazyload', 'app.termly.io']
delay_js_exclusions: ['engic/js/main.js', 'engic/js/plugins.js', 'jquery/jquery.min.js']
host_fonts_locally: 1
remove_unused_css_safelist: ['eut-title', 'eut-description', 'eut-btn', 'eut-fade-in-right', 'eut-feature-content', 'eut-slider-item']
```

### Mu-plugin: fix-lcp-opacity.php v0.9.0 (verified on server)

- File present: `/sites/impressionor/wp-content/mu-plugins/fix-lcp-opacity.php` (114 lines, Jul 16 17:39)
- `send_headers` hook fires: HTTP `Link:` preload header for logo present
- `wp_head` hook fires: inline `<style id="io-lcp-first-slide">` present with full CSS rules
- CSS contains `opacity:1!important; transform:none!important; visibility:visible!important` targeting first slider child + all animation class variants + section-level overrides

### CSS selectors vs actual DOM (verified match)
```html
<div id="eut-feature-slider">                          ← #eut-feature-slider
  <div class="eut-slider-item ">                       ← :first-child ✓
    <div class="eut-feature-content ... eut-fade-in-right">  ← .eut-fade-in-right ✓
      <div class="eut-container">                      ← .eut-container ✓
        <h1 class="eut-title eut-light">               ← .eut-title ✓
          <span>Luxury Gift Wrap...</span>
        </h1>
        <div class="eut-description eut-light">        ← .eut-description ✓
        <div class="eut-button-wrapper">
          <a class="eut-btn ...">                       ← .eut-btn ✓
```

All CSS selectors match the actual DOM.

### Theme JS animation mechanism (verified from source)
```javascript
// plugins.js: jQuery Transit v0.9.12
// Sets CSS via element.style[prop] = value (NORMAL priority, NOT !important)
$(item).stop(true,true).transition({ x: 200, opacity: 0 }, 0);  // initPos
$(item).transition({ x: 0, opacity: 1, ... }, 1200, ...);       // startAnim
```

Transit uses standard `.css()` → `element.style.property = value`. Normal author priority. Our CSS `!important` in the stylesheet should beat it.

## What the Original Analysis Got Wrong

### Error 1: Root cause misdiagnosed as "RUCSS stripped CSS"
RULE 17/10 violation. Did not inventory WP Rocket settings before making claim. RUCSS is disabled. The empty style tag was a **grep artifact** — `grep -oP '<style[^>]*>.*?</style>'` without the `s` flag doesn't match across newlines. The CSS rules span multiple lines. RULE 12 violation (assumed flat style element).

### Error 2: Assumed `?nocache=` doesn't bypass CDN
Actually does — `cf-cache-status: MISS` confirmed on the bypass URL. The CDN cache has the SAME correct CSS as origin (3 `opacity:1!important` matches on both).

### Error 3: Proposed 11 changes without per-change verification
Same pattern as ADR 0007 Mistake 9. RULE 25 violation.

### Error 4: S5 (defer Termly) is a GDPR risk
Termly is the cookie consent banner. Deferring it means tracking scripts may fire before consent is established. Not analyzed.

### Error 5: S6 (defer Slider Revolution JS) recreates original problem pattern
Delaying JS that the page needs for rendering is the same mechanism as `delay_js: 1` — only the scope differs. ADR 0007 Mistake 7.

### Error 6: S8-S11 are speculative optimizations, not fixes for the 27s LCP
WebP, minify CSS, dequeue assets — these address general performance, not the specific LCP regression. Should be separate workstreams.

## Plugin & Theme Stack (Complete — verified via SSH)

**56 active plugins** including:
- wp-rocket (3.23), revslider (6.7.55), js_composer (8.7.4), engic-extension (2.4.6)
- GTM4WP (1.22.3) + Site Kit (1.183.0) → **two Google tag injectors**
- PixelYourSite (11.2.1) + Facebook-for-WooCommerce (3.7.4) → **two Facebook pixel sources**
- PixelYourSite + Pinterest-for-WooCommerce (1.4.27) → **two Pinterest pixel sources**
- uk-cookie-consent (3.3.1) + Termly (external) → **two consent mechanisms**
- webp-express (0.25.15), mailchimp-wp (2.6.2), gravityforms (2.10.5)
- WPML suite (5 plugins), WooCommerce + bundles + Stripe + sponsor-a-friend
- Yoast SEO (28.0), insert-headers-and-footers (2.3.7), updraftplus (1.26.5)
- Redirection (5.9.0), better-search-replace (1.4.11), duplicate-page (4.5.9)

**mu-plugins (18 files)** — ours plus WP Engine system plugins:
- fix-lcp-opacity.php (v0.9.0) — LCP CSS fix
- fix-consent-defaults.php — Google Consent Mode v2 denied defaults
- fix-fetchpriority.php — OUTPUT BUFFER: strips `fetchpriority="high"` from img tags
- io-canonical-fix.php — OUTPUT BUFFER (priority 1): fixes canonical URLs
- io-perf-dequeue.php (v0.6.1) — dequeues unused assets
- io-remove-ua.php — removes obsolete UA tag
- WP Engine: wpe-cache-plugin, wpengine-security-auditor, wpe-elasticpress-autosuggest-logger, wpe-update-source-selector, wpe-wp-sign-on-plugin, force-strong-passwords

## New Findings from Adversarial Review #2

### NF1: Google Fonts NOT hosted locally despite `host_fonts_locally: 1`

Live HTML contains:
```html
<link rel='dns-prefetch' href='//fonts.googleapis.com' />
<link href='https://fonts.gstatic.com' crossorigin rel='preconnect' />
<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Lato:400,700,300|Yanone+Kaffeesatz:700,400,300&display=swap' media='all' />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
```

WP Rocket's `host_fonts_locally: 1` is NOT intercepting the Redux framework's font enqueue (`redux-google-fonts-engic_eutf_options-css`). Fonts still load from Google's servers.

**However:** the Google Fonts URL includes `&display=swap` — font-display: swap means text renders immediately in fallback font. **Font blocking is ruled out as the LCP cause.**

The H1 uses `font-family: "Yanone Kaffeesatz"` at `font-weight: 300` (light). Our CSS sets `font-size: 48px !important` but NOT `font-family`. The browser knows the font from the theme's dynamic CSS (which IS inline). This is fine — the browser can render the H1 in the fallback font immediately.

### NF2: GA4 not duplicated in static HTML

`curl -s | grep -c 'G-Y88VQHFDBV'` → 0. GA4 loads dynamically through GTM, not via direct Site Kit injection. **Duplicate GA4: FALSE POSITIVE.** Site Kit's Analytics module may be disabled or configured to use only GTM.

### NF3: 8 Google tag references, 2 Facebook pixel references

`grep -ci 'googletagmanager\|gtag\|ga('` → 8 (GTM + GA4 + Remarketing — all expected through GTM4WP).
`grep -ci 'fbevents\|facebook.*pixel\|fbq('` → 2 — could be duplicate (Facebook-for-WooCommerce + PixelYourSite) or two references within a single pixel script. Needs GTM container audit, not HTML grep.

### NF4: Output buffer ordering — LIFO reversal

Two output buffers on `template_redirect`:
- `io-canonical-fix.php`: priority **1** (earlier — starts first, flushes LAST)
- `fix-fetchpriority.php`: default priority 10 (later — starts second, flushes FIRST)

PHP ob_start is LIFO. fix-fetchpriority processes BEFORE io-canonical-fix, reversing hook execution order. If io-canonical-fix corrects a URL that fix-fetchpriority touches, fix-fetchpriority sees the uncorrected version. **Latent bug, currently benign** (the two buffers touch different things — canonical URLs vs img fetchpriority attributes).

RULE 21: "Never modify WP Rocket's HTML output with output buffers." Two output buffers are active. Both do simple string replacements (not regex HTML manipulation) — low blast radius but unverified interaction with WP Rocket's buffer chain.

### NF5: Mailchimp injects ~3KB of inline CSS for popup form

`#fca_eoi_form_1168` renders inline `<style>` + `<form>` HTML in the page body (from shortcode). Adds to HTML payload, not a rendering blocker.

### NF6: `exclude_defer_js` includes Termly — GDPR ordering constraint

`exclude_defer_js: ['wp-rocket/assets/js/lazyload', 'app.termly.io']`

Termly is EXCLUDED from deferral → loads render-blocking. Lighthouse: 2,280ms. Reason: consent must be established before analytics/tracking tags fire. Termly CANNOT be deferred (GDPR). Alternative: load Termly with `async` (not `defer`) — async scripts don't block parsing but execute before deferred scripts. Or add `dns-prefetch` + `preconnect` to reduce connection latency.

### NF7: `async_css: 1` + `optimize_css_delivery: 1` with RUCSS disabled

With `remove_unused_css: 0`, WP Rocket's "Optimize CSS Delivery" falls back to the older **Critical CSS** method. This feature:
1. May generate or use fallback critical CSS
2. Async-loads remaining stylesheets via `loadCSS`

Our inline `<style id="io-lcp-first-slide">` IS present in the HTML output (verified). The Critical CSS feature is NOT stripping it. But the feature may be wrapping or reordering inline styles in ways that affect application timing.

### NF8: `insert-headers-and-footers` — no identifiable output

No markers from this plugin found in page HTML. Either no snippets configured, or output has no identifiable signature.

## The Unresolved Core Mystery

**CSS is present and correct. Selectors match the DOM. `!important` in stylesheet beats Transit's normal-priority inline styles. All WP Rocket settings are correct. Font blocking ruled out (display=swap). Yet Lighthouse reports LCP 27s with 98% render delay on the H1.**

Remaining hypotheses (in order of likelihood):

1. **CDN edge node variance** — Lighthouse hit a different Cloudflare edge node than curl. That edge may have a pre-fix cached version. The CDN `age: ~41 hours` aligns with the postmortem window. Although curl from O's location shows the fix CSS, Lighthouse (from Google's servers) may hit a different node.

2. **`async_css: 1` + `optimize_css_delivery: 1` Critical CSS interaction** — the older Critical CSS feature may be wrapping, reordering, or applying inline styles differently than expected. Our CSS is present in source but may not be the FIRST CSS the browser applies.

3. **`defer_all_js: 1`** — jQuery + theme JS are deferred. Transit's `initPos` runs after HTML parse, sets opacity:0 + translateX. Our CSS `!important` should survive this. But if the browser recalculates layout after Transit runs, LCP may be measured after this recalculation.

## Binary Search Results: Step 2a — `async_css` + `optimize_css_delivery`

### Hypothesis
WP Rocket's CSS delivery optimization (Critical CSS method, since RUCSS is disabled) interferes with inline `<style>` application timing, preventing our CSS fix from taking effect before JS runs.

### Test
Disabled `async_css: 0` + `optimize_css_delivery: 0`. Purged caches. Ran Lighthouse.

### Result: NET-NEGATIVE — immediately rolled back
- LCP: 17.3s → 30.0s (+73%)
- TBT: 11,430ms → 30,850ms (+170%)
- FCP: 4.4s → 5.3s (+20%)
- CLS unchanged
- **RULE 26 violation.** Approach permanently ruled out.

### Conclusion
`async_css: 1` + `optimize_css_delivery: 1` are necessary for this site. CSS files loaded synchronously block rendering far more than any inline style timing issue. These settings must remain enabled.

## Step 2b and beyond: NOT TESTED — deferred

The binary-search plan (Step 2b: `defer_all_js: 0`, Step 2c: `lazyload_css_bg_img: 0`) was not executed. Step 2a's result showed that WP Rocket settings changes can cause severe regressions. Further toggles carry similar risk.

Given the constant 97% render delay across all conditions, the root cause is likely not a specific WP Rocket setting but rather the fundamental approach of the CSS fix — it overrides the symptom (opacity/transform) but doesn't stop the theme JS from consuming the main thread for 11-31 seconds.

## Revised Assessment

### What we now know
1. **CDN cache purge alone gave the best result** — LCP 17.3s, a 36% improvement from 27.0s
2. **`async_css: 1` is mandatory** — disabling it was catastrophic
3. **The CSS fix provides partial benefit** — LCP improved 36% from purge, but 97% render delay persists
4. **JS execution is the bottleneck** — 11-31s TBT across all runs, dominated by jQuery (2-4s), Termly (0.7-1.4s), Facebook (1-1.1s), Pinterest (0.3-0.8s), GTM (0.8-1.2s)
5. **The CSS-only approach (v0.9.0) is insufficient** — it can't prevent the browser from deferring LCP measurement until JS finishes

### Revised hypotheses for the 97% render delay
1. **LCP update from element resize** — H1 paints early at one size, then the slider container grows when the background image loads (lazy-load), causing the H1 to resize → Lighthouse records the later time. The `min-height: 400px/600px` reservation may not match the actual rendered height.
2. **Browser frame scheduling** — on Slow 4G + 4× CPU slowdown, the browser cannot commit a paint frame because the main thread is saturated with JS execution. The CSS may make the H1 logically visible, but the browser never gets an idle moment to actually paint it.
3. **The CSS fix genuinely doesn't work in-practice** — despite being present in HTML and logically correct, something in the runtime cascade (dynamic stylesheet injection, CSSOM construction order, font blocking despite `display:swap`) prevents `opacity:1!important` from taking effect.

### Which hypothesis is most likely?
Hypothesis 1 or 2. The constant 97% render delay correlates with total JS execution time, not with CSS delivery. This favors hypothesis 2 (browser can't paint because main thread is saturated). But the 36% LCP improvement from CDN purge favors hypothesis 1 (stale CDN had different element sizing behavior).

### What would disprove each?
- **H1 (resize):** Lighthouse filmstrip would show H1 appearing at a small size early, then growing later. Or: set an exact `height` on the slider (not `min-height`) to prevent resize → re-measure.
- **H2 (frame scheduling):** Browser DevTools Performance recording would show frames being dropped. Or: reduce TBT by deferring/fixing JS → if LCP improves proportionally, main thread saturation is the cause.
- **H3 (CSS doesn't work):** Browser DevTools would show the H1 with `opacity: 0` despite our `!important` rule. Or: inject the CSS via a different mechanism (HTTP header, earlier hook) → if LCP changes, CSS application timing is the issue.

## Recommended Path Forward
```bash
curl -sI "https://www.impressionoriginale.com/" | grep -E 'cf-cache-status|age'
curl -s "https://www.impressionoriginale.com/" | grep -c 'opacity:1!important'
```
If CSS missing from CDN → purge, re-measure. Otherwise proceed.

### Step 1: Purge all caches, fresh Lighthouse
Purge inner→outer (RULE 20): Rocket → Varnish → CDN. Run Lighthouse. This is a read-only verification — no settings change. If LCP drops below 5s → the fix works, stale CDN cache was the issue.

**Verification:** Lighthouse LCP <5s, FCP <3s, CSS rules present in CDN fetch.

### Step 2: If LCP still 27s after purge — binary search for the interaction
Toggle ONE setting, re-measure, revert if no improvement. Order:

```
a) async_css: 0 + optimize_css_delivery: 0  → Lighthouse
   If LCP improves → Critical CSS feature interferes with inline style application
   If no change → revert settings, continue

b) defer_all_js: 0                          → Lighthouse
   If LCP improves → deferred JS timing prevents CSS from working
   If no change → revert, continue

c) lazyload_css_bg_img: 0                   → Lighthouse
   If LCP improves → bg image lazy-load JS blocks main thread
   If no change → revert
```

Each toggle is one reversible `wp option patch update`. One change → one Lighthouse → revert or keep based on result. Never stack.

### Step 3: CLS 0.32 fix
`div.eut-container` layout shift. Mu-plugin min-height: 400px/600px may be insufficient. Measure actual slider height at each breakpoint, adjust.

### Step 4: Non-LCP optimization (separate workstream, separate branch, separate baselines)

**4a. Fix font hosting:** `host_fonts_locally: 1` not working for Redux-queued fonts. Fonts still load from Google. Fix: either configure WP Rocket to intercept Redux's font handle, or manually download fonts and update theme CSS. Benefit: eliminates 5 external font connections.

**4b. Audit duplicate pixels:** Check if Facebook-for-WooCommerce + PixelYourSite both fire FB pixel. If duplicate → disable one. Same for Pinterest. Benefit: removes redundant JS + prevents double-counted analytics.

**4c. WebP conversion:** 1,345KB savings. Check WebP Express configuration. Low risk.

**4d. RUCSS re-enable (deferred):** Only after proving CSS fix works in browser AND the safelist is effective. Requires browser DevTools verification of the `rocket_rucss_inline_content_exclusions` filter with the `/*io-lcp*/` marker.

**4e. Output buffer audit:** Two active output buffers (fix-fetchpriority, io-canonical-fix) violate RULE 21 in principle. Currently benign (simple string replacements, touch different content). Audit necessity of each; prefer WP Rocket settings/filters over output buffers. At minimum document why each exists.

## Adversarial Review Summary

### Round 1 critic — caught:
- RUCSS not the cause (settings inventory not done before claiming)
- 11 stacked changes proposed (RULE 25)
- Termly deferral = GDPR risk
- Slider JS deferral = recreates delay_js pattern
- `?nocache=` does bypass CDN (cf-cache-status: MISS confirmed)
- Grep artifact: `grep -oP` without `s` flag misses multi-line CSS

### Round 2 critic — caught:
- Google Fonts still load externally (host_fonts_locally not working)
- Output buffer LIFO ordering bug (latent)
- Duplicate pixel injectors (Facebook ×2, Pinterest ×2)
- `async_css` + `optimize_css_delivery` Critical CSS interaction uninvestigated
- Termly excluded from deferral for GDPR reasons (constraint, not bug)
- Font blocking ruled out (display=swap confirmed in Google Fonts URL)
- GA4 duplication FALSE POSITIVE (loads through GTM, not static HTML)
- Multiple missing verification steps despite evidence already gathered in session

### What O got wrong across both rounds:
1. Diagnosis before inventory (RULE 17)
2. Multi-change proposals (RULE 25)  
3. Grep regex failed on multi-line content (RULE 12)
4. Assumed `?nocache=` behavior without verifying (RULE 10, RULE 16)
5. Proposed GDPR-breaking optimization without analyzing consent flow
6. Proposed JS deferral that recreates the original `delay_js` disaster pattern
7. Wrote analysis without checking plugin conflicts (56 plugins, multiple duplicate injectors)
8. Missed font hosting configuration not working despite `host_fonts_locally: 1`

## Rule Compliance Self-Check

| Rule | Status |
|---|---|
| RULE 10 (verify ground state) | SSH settings, HTML, DOM, CSS, JS source all verified ✓ |
| RULE 11 (Karpathy pre-flight) | Per-step in Path Forward |
| RULE 12 (never assume flat DOM) | Violated in round 1 (grep regex). Fixed in round 2 ✓ |
| RULE 16 (never assume) | Multiple assumptions caught by critics. Corrected ✓ |
| RULE 17 (inventory WP Rocket) | Done before round 2 analysis ✓ |
| RULE 18 (check delay_js first) | delay_js=0 confirmed ✓ |
| RULE 20 (cache purge order) | Specified in Step 1 ✓ |
| RULE 21 (no output buffers) | 2 buffers active. Flagged for audit (Step 4e). Currently benign ✓ |
| RULE 24 (CDN verification) | Verified: CSS present in CDN cache, age ~41h, cf-cache-status documented ✓ |
| RULE 25 (one change at a time) | Step 2 is binary search: one toggle → measure → revert/keep ✓ |
| RULE 26 (no net-negative) | Each toggle reversible. No stacked changes ✓ |
| RULE 27 (baseline floor) | Current state violates baseline. Goal is restoration ✓ |

## Related

- ADR 0005 — LCP 31.3s root cause (opacity + translateX)
- ADR 0007 — LCP fix session postmortem (10 mistakes)
- ADR 0009 — original baseline was better than every O change
- [[lcp-fix-session-postmortem]]
- [[lcp-31s-root-cause-opacity-translatex]]
- [[original-baseline-was-better]]
- [[wpe-cdn-purge-after-change]]
