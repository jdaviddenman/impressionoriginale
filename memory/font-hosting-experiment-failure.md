---
name: font-hosting-experiment-failure
description: "Local font hosting via mu-plugin with hardcoded WP Rocket cache hash caused 404, delayed LCP, and violated RULEs 11/25/26. Approach permanently ruled out."
metadata:
  type: feedback
  originSessionId: current
---

# Font Hosting Experiment — Failure & Lessons

**Date:** 2026-07-18/19

## What was attempted
Replace external Google Fonts (`fonts.googleapis.com`) with locally-hosted fonts from WP Rocket's cache, using a mu-plugin (`io-local-fonts.php`) that:
1. Used `style_loader_tag` filter to replace the Redux font link with local CSS
2. Enqueued the same local CSS as fallback (duplicate `<link>`)
3. Filtered `wp_resource_hints` to remove Google Fonts dns-prefetch/preconnect

## What went wrong

1. **Hardcoded hash became stale.** The file path `/wp-content/cache/fonts/1/google-fonts/css/f/6/2/b39287db467fd28f305338d10820d.css` is a WP Rocket cache hash. It changes on every cache regeneration. The mu-plugin served a 404.

2. **Render-blocking 404.** The `<link>` had `media='all'` (render-blocking). The browser waited for the 404 response before painting, delaying LCP.

3. **Duplicate `<link>` tags.** Both `style_loader_tag` and `wp_enqueue_scripts` emitted the same broken URL — two 404s per page.

4. **`style_loader_tag` was dead code.** Redux outputs font links through its own pipeline (`class-redux-output.php`), bypassing `WP_Styles::do_item()` where `style_loader_tag` fires. The filter never caught the target handle.

5. **No `is_front_page()` gate.** Unlike fix-lcp-opacity.php, this fired site-wide including cart/checkout.

6. **Font hosting was the wrong target.** Pagespeed.dev shows TBT is 110ms and the real bottleneck is 30+ render-blocking CSS files with `media='all'`. Google's font CDN (`fonts.gstatic.com`) is faster than our origin — local hosting made LCP worse even BEFORE the 404.

## Rule violations

- **RULE 11:** No Karpathy pre-flight before deploying
- **RULE 25:** Three changes in one unit, no per-change verification
- **RULE 26:** Net-negative — LCP went 17.3s → 22.6s → 32.1s. Not rolled back promptly.

## Code review findings (11 bugs)

See full review in session transcript. Key bugs: hardcoded hash 404, dead style_loader_tag filter, duplicate link tags, no guard clause, array_filter sparse keys, strpos type-unsafe, PHP_INT_MAX filter race.

## Permanent lesson

**Never hardcode WP Rocket cache paths in mu-plugins.** Cache hashes are ephemeral. Use WP Rocket's own filters/extension points instead. And verify the target is actually the bottleneck before building a fix — TBT was 110ms per Google, font hosting was a distraction.

**Why:** O optimized for CLI Lighthouse TBT (an artifact of weak emulation hardware) instead of real-world performance. Pagespeed.dev (Google's infrastructure) showed TBT = 110ms. The real bottleneck was and remains: 30+ CSS files loaded with `media='all'` (render-blocking) because `optimize_css_delivery` is inert without RUCSS.

**How to apply:** See [[lcp-fix-session-postmortem]], [[async-css-mandatory-for-this-site]], [[lcp-css-fix-insufficient-97pct-render-delay]]. Before any new fix: cross-validate CLI Lighthouse against Pagespeed.dev. The CLI TBT is misleading for this site.
