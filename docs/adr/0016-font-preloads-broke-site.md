# ADR 0016 — CDN Font Preloads Broke the Site, Permanently Ruled Out

**Status:** Accepted
**Date:** 2026-07-22

## Context

WP Rocket's `auto_preload_fonts: 1` generates `<link rel="preload" as="font">` tags with wrong-origin URLs — it resolves `@font-face` declarations against `site_url()` (`www.impressionoriginale.com`) while the actual CSS is served from RocketCDN (`5ec66156.delivery.rocketcdn.me`). The browser fetches preloaded fonts from the wrong origin, wastes ~260KB, and discards them.

A mu-plugin `io-font-preloads.php` was deployed on 2026-07-22 with 4 hardcoded CDN-correct font preload URLs, paired with `auto_preload_fonts: 0`.

## Decision

**The font preloads mu-plugin broke the site and is permanently ruled out.** Deployed 2026-07-22, rolled back same day. `auto_preload_fonts` restored to `1`. The approach is dead — no iteration, no "fix the fix."

## Why It Broke

The user reported the site was broken after the font preloads change. The exact mechanism of breakage wasn't diagnosed in-session (the fix was immediate rollback per RULE 26), but the hardcoded CDN URLs are fragile and the benefit was marginal — preloading fonts doesn't address the actual bottleneck (30+ render-blocking CSS files).

## Consequences

- **Font preload mu-plugin approach permanently ruled out.** RULE 26 applies — net-negative, not iterable.
- **`auto_preload_fonts: 1` stays enabled** despite the wrong-origin URL issue. The wasted 260KB is the lesser problem.
- **The wrong-origin preload issue is acknowledged but unfixed.** The correct fix would require WP Rocket to resolve font URLs against the CDN origin, not `site_url()` — a WP Rocket plugin fix, not a custom mu-plugin.
- Mirrors ADR 0011 (font hosting experiment — hardcoded paths are fragile) and ADR 0015 (jQuery defer — net-negative, permanently ruled out).
- **Lesson:** CDN-aware font preloading requires WP Rocket internals integration, not a standalone mu-plugin with hardcoded URLs. The risk/benefit ratio of hardcoded CDN URLs is always negative.

## Rollback (applied 2026-07-22)

```bash
ssh impressionor@impressionor.ssh.wpengine.net 'wp option patch update wp_rocket_settings auto_preload_fonts 1'
ssh impressionor@impressionor.ssh.wpengine.net 'rm /nas/content/live/impressionor/wp-content/mu-plugins/io-font-preloads.php'
# Full cache purge (RULE 20: Rocket → Varnish → CDN)
```
