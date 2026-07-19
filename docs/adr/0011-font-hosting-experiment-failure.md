# ADR 0011 — Font Hosting Experiment: Net-Negative, Permanently Ruled Out

**Date:** 2026-07-19
**Status:** Font hosting experiment failed (rolled back). RUCSS re-enabled successfully — LCP -42%.

## Decision

Local font hosting via mu-plugin is permanently ruled out. Google's font CDN (`fonts.gstatic.com`) is faster than our origin for serving font files. Attempting to replace it introduced a render-blocking 404 that made LCP worse.

## What Was Attempted

Replace the external Google Fonts CSS link (`fonts.googleapis.com`) with locally-hosted font CSS from WP Rocket's cache. Motivation: eliminate 5 external font connections identified in ADR 0010 NF1.

### Mechanism (mu-plugin `io-local-fonts.php` v0.2.0)
1. `style_loader_tag` filter to replace `redux-google-fonts-engic_eutf_options-css` with local CSS
2. `wp_enqueue_scripts` fallback enqueuing same local CSS
3. `wp_resource_hints` filter to strip Google Fonts dns-prefetch/preconnect

### Why Each Mechanism Failed

1. **`style_loader_tag` was dead code.** Redux framework outputs its font link through its own pipeline (`class-redux-output.php` → `add_style_attributes()`), bypassing `WP_Styles::do_item()` where `style_loader_tag` fires. The filter silently never caught the target handle.

2. **Hardcoded cache hash became stale immediately.** The file path `/wp-content/cache/fonts/1/google-fonts/css/f/6/2/b39287db467fd28f305338d10820d.css` is a WP Rocket content-derived hash. It changed from `b39287...` to `29e5105...` on the next cache regeneration. The mu-plugin served a 404.

3. **The enqueued `<link>` was render-blocking.** `media='all'` meant the browser waited for the 404 response before painting. This directly undermined the LCP fix (`fix-lcp-opacity.php` v0.9.0) by adding 1-2 seconds of render-blocking delay to the critical path.

4. **No `is_front_page()` guard.** Unlike the LCP fix, this fired on every page including cart and checkout.

## Lighthouse Evidence (Net-Negative)

| State | LCP | TBT | Notes |
|---|---|---|---|
| Pre-experiment (best) | 17.3s | 11,430ms | Google Fonts, async_css=1 |
| Local fonts active | 22.6s | ~8,000ms | Local CSS loading but correct |
| 404 link active | 32.1s | 8,090ms | Render-blocking 404 discovered |
| After rollback | 17.2s | 7,220ms | Back to baseline |

Each font-hosting change made LCP worse. RULE 26: approach permanently ruled out.

## Code Review Findings

Full `/code-review max` produced 11 findings (8 correctness bugs, 3 CLAUDE.md rule violations). Key bugs:
- Hardcoded hash → 404 (confirmed on production)
- `style_loader_tag` dead code for Redux handle
- Duplicate `<link>` tags (belt-and-suspenders emitting same broken URL twice)
- `array_filter` without `array_values` → sparse array keys
- `strpos()` on potentially non-string hint elements → PHP 8.x warning
- `PHP_INT_MAX` filter priority race with WP Rocket's own rewrite

## Rule Violations

| Rule | How |
|---|---|
| RULE 11 | No Karpathy pre-flight before production deploy |
| RULE 25 | Three independent changes deployed as one unit, no per-change verification |
| RULE 26 | Net-negative: each iteration made LCP worse. Not rolled back promptly. |

## The Deeper Lesson: Wrong Bottleneck

The font hosting experiment targeted the wrong problem. Pagespeed.dev (Google's infrastructure, Lighthouse 13.4, Moto G Power, Slow 4G) consistently shows:
- **TBT: 110-160ms** — essentially zero. CLI Lighthouse TBT (7,000-30,000ms) is an artifact of weak emulation hardware.
- **LCP: 15-22s with 97% render delay** — the real bottleneck is 30+ CSS files loaded with `media='all'` (render-blocking).

The real problem: `optimize_css_delivery: 1` is **inert** without RUCSS (`remove_unused_css: 0`). Without RUCSS to generate critical CSS, the feature falls back to loading all CSS normally — render-blocking. ADR 0007 disabled RUCSS because it was stripping the inline LCP fix CSS. The result: 30+ CSS files block rendering.

## Correct Next Step

Enable RUCSS (`remove_unused_css: 1`) with the safelist infrastructure already in `fix-lcp-opacity.php` v0.9.0:
- `rocket_rucss_inline_content_exclusions` filter with `/*io-lcp*/` marker
- `remove_unused_css_safelist` populated with `eut-title`, `eut-description`, `eut-btn`, `eut-fade-in-right`, `eut-feature-content`, `eut-slider-item`

## Related

- ADR 0007 — LCP fix session postmortem (RUCSS was disabled here)
- ADR 0010 — performance analysis (identified font hosting opportunity, NF1)
- [[font-hosting-experiment-failure]] — memory entry
- [[async-css-mandatory-for-this-site]] — async_css is load-bearing
- [[lcp-css-fix-insufficient-97pct-render-delay]] — CSS fix necessary but insufficient
- [[rucss-enabled-css-async-works]] — RUCSS re-enabled successfully
- RULE 11, RULE 25, RULE 26

## Appendix: RUCSS Re-Enabled — Success

After the font hosting rollback, RUCSS (`remove_unused_css: 1`) was re-enabled with the safelist from `fix-lcp-opacity.php` v0.9.0. Results (Pagespeed.dev, Jul 19):

| Metric | Before RUCSS | After RUCSS |
|---|---|---|
| LCP | 27.6s | 15.9s (-42%) |
| FCP | 5.5s | 5.2s |
| TBT | 160ms | 320ms |
| CLS | 0 | 0 |
| Render-blocking CSS | 22,410ms | 0ms |

30+ CSS files now load asynchronously. Inline LCP fix CSS survives via safelist. Only jQuery (1,510ms) + Termly (4,350ms) remain render-blocking. RUCSS stays enabled permanently.
