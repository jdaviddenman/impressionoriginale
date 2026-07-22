---
name: io-font-preloads-broke-site
description: "CDN font preloads mu-plugin broke the site, rolled back 2026-07-22. Approach permanently ruled out. ADR 0016."
metadata:
  type: feedback
  originSessionId: current
  modified: 2026-07-22T13:36:33.702Z
---

# CDN Font Preloads — Broke Site, Permanently Ruled Out

**Date:** 2026-07-22
**Status:** Rolled back, permanently ruled out

## What was attempted

`io-font-preloads.php` mu-plugin with 4 hardcoded RocketCDN font URLs as `<link rel="preload" as="font">` tags, paired with `auto_preload_fonts: 0`. Goal: replace WP Rocket's wrong-origin auto-generated preloads with CDN-correct ones.

## What went wrong

The site broke after deployment. User requested immediate rollback. Per RULE 26, the approach is permanently ruled out — no iteration, no "fix the fix."

## Rollback (applied same day)

```bash
ssh impressionor@impressionor.ssh.wpengine.net 'wp option patch update wp_rocket_settings auto_preload_fonts 1'
ssh impressionor@impressionor.ssh.wpengine.net 'rm /nas/content/live/impressionor/wp-content/mu-plugins/io-font-preloads.php'
# Full cache purge (RULE 20)
```

## Lessons

- **Hardcoded CDN URLs are fragile.** Same lesson as [[font-hosting-experiment-failure]].
- **Wrong-origin font preloads (260KB wasted) are the lesser problem** — better than a broken site.
- **CDN-aware font preloading requires WP Rocket internals integration**, not a standalone mu-plugin.
- **RULE 26:** Any net-negative change gets immediate rollback + approach permanently ruled out.

## Related

- [[font-hosting-experiment-failure]] — same hardcoded-URL lesson, ADR 0011
- [[jquery-deferral-permanently-ruled-out]] — same net-negative pattern, ADR 0015
- [[io-font-preloads-cdn-fix]] — original (now-superseded) memory for the rolled-back fix
- ADR 0016

**Why:** The font preloads mu-plugin broke the site. The risk/benefit ratio of hardcoded CDN URLs for font preloading is always negative — WP Rocket's wrong-origin preloads (260KB wasted) are the lesser problem.

**How to apply:** Never propose a font preload mu-plugin with hardcoded CDN URLs. The correct fix requires WP Rocket to resolve font URLs against the CDN origin — a plugin-level fix, not a custom mu-plugin.
