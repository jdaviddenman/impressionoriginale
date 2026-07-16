---
name: lcp-image-lazy-load-scroll-fix
description: WP Rocket lazyloads slider bg images → LCP image loads only on scroll. Fixed via mu-plugin output buffer (v0.6.0) + exclude_lazyload substrings.
metadata: 
  node_type: memory
  type: project
  originSessionId: 8fe8ee67-16da-45fb-a561-61a5d894254b
---

# LCP Image Lazy-Load on Scroll — Fix

**Date:** 2026-07-16
**Status:** Deployed in mu-plugin v0.6.0; CDN purge pending

## Problem

After fixing the 31.3s LCP (opacity + translateX — [[lcp-31s-root-cause-opacity-translatex]]), LCP dropped to 4.9s but the LCP background image still didn't load until the user scrolled. Lighthouse LCP element showed `rocket-lazyload entered lazyloaded`.

## Root Cause

WP Rocket `lazyload_css_bg_img` converts inline `style="background-image: url(...)"` to `data-bg="..."` + class `rocket-lazyload`. WP Rocket's JS lazy-load triggers on viewport intersection — the image doesn't load until the user scrolls.

`exclude_lazyload` setting had only `CadeauCalligraphie_Phedre_triocote-scaled` (slide 1). Slides 2-9 all had `data-bg` + `rocket-lazyload`. On narrow viewports (Moto G4 360px), WP Rocket's server-side detection flags even above-the-fold slides for lazy loading.

## Fix (Two-Pronged)

### 1. Mu-plugin output buffer (`fix-lcp-opacity.php` v0.6.0)

Hooks `wp_loaded` at PHP_INT_MAX to start output buffering AFTER WP Rocket processes the page. The buffer callback strips `rocket-lazyload` class and restores `data-bg` to `style="background-image: url(...)"` for any `.eut-bg-image` divs (scoped by class check).

Also hooks `rocket_buffer` filter as a secondary path (for WP Rocket cached pages).

**Key implementation detail:** Buffer ordering matters. Must start ob_start AFTER WP Rocket's own processing. Using `wp_loaded` at PHP_INT_MAX ensures the output buffer is outer (WP Rocket's is inner), so WP Rocket processes first, then the fix undoes it for slider elements.

### 2. WP Rocket `exclude_lazyload` substrings

Added `_HOME-` and `_HOME_` to the exclusion array. All slider background images (slides 2-9) contain these substrings. Verified zero matches on product/category pages.

**Note:** `wp option patch insert` corrupts serialized arrays to strings. Use `wp eval-file` with piped PHP instead. The mu-plugin includes a self-heal that restores the array if corrupted.

## Verification

```bash
# Origin bypass (confirms fix works):
curl -s 'https://www.impressionoriginale.com/?nocache=verify' | grep -oP '<div[^>]*eut-bg-image[^>]*>' | grep -c 'rocket-lazyload'
# Expected: 0

# CDN check (needs purge first):
curl -sI 'https://www.impressionoriginale.com/' | grep cf-cache-status
# Should show MISS after purge
```

## Related

- [[lcp-31s-root-cause-opacity-translatex]] — the prior LCP fix (H1 hidden by opacity + translateX)
- [[wpe-cdn-purge-after-change]] — full purge sequence (RULE 15)
- `docs/adr/0006-lcp-image-lazyload-fix.md` — ADR with full investigation
- `mu-plugins/fix-lcp-opacity.php` v0.6.0

## Rollback

```bash
# Revert mu-plugin to v0.5.0 (remove output buffer)
ssh impressionor@impressionor.ssh.wpengine.net 'cat > /sites/impressionor/wp-content/mu-plugins/fix-lcp-opacity.php' < mu-plugins/fix-lcp-opacity.php.v0.5.0
# Remove exclude_lazyload additions via wp eval-file
```
