# ADR 0006 — LCP image lazy-load on scroll: WP Rocket `lazyload_css_bg_img` blocks slider background

**Date:** 2026-07-16
**Status:** Fix deployed (mu-plugin v0.6.0), CDN purge pending

## Problem

After ADR 0005 (opacity + translateX fix), LCP dropped from 31.3s to 4.9s but the LCP background image still didn't render until the user scrolled. Lighthouse on Moto G4 + Slow 4G showed the LCP element with `rocket-lazyload entered lazyloaded` — WP Rocket's JS lazy-load had intercepted the image.

LCP 4.9s meant the text (H1) was painting immediately, but the hero background image was invisible until the user triggered viewport intersection by scrolling.

## Investigation

### Background image lazy-load mechanism

WP Rocket's `lazyload_css_bg_img` feature (enabled) converts inline `style="background-image: url(...)"` to `data-bg="..."` + class `rocket-lazyload`. This happens at the PHP level (server-side HTML rewrite) before the page is served. WP Rocket's client-side JS then monitors viewport intersection and swaps `data-bg` back to inline `style` when the element enters the viewport.

### Current state (before fix)

`exclude_lazyload` in WP Rocket settings contained only one entry: `CadeauCalligraphie_Phedre_triocote-scaled` (slide 1's image). Server-side HTML:

```
Slide 1: style="background-image: url(...CadeauCalligraphie_Phedre_triocote-scaled.jpg);"  ← excluded ✓
Slide 2: style="background-image: url(...Wraps_HOME_Web.jpg);"  ← not lazy-loaded (desktop viewport)
Slides 3-9: data-bg="..." class="...rocket-lazyload"  ← ALL lazy-loaded ✗
```

On the narrow Moto G4 viewport (360px), WP Rocket's server-side detection may flag even above-the-fold slides as "below the fold" due to layout differences, causing the LCP image to be lazy-loaded.

### WP-CLI serialization bug

`wp option patch insert wp_rocket_settings exclude_lazyload "value"` corrupts the serialized PHP array to a plain string. The `exclude_lazyload` key (originally `["CadeauCalligraphie_Phedre_triocote-scaled"]`) was overwritten to the string `"_HOME-"`. Root cause: `wp option patch` with `--format=plaintext` (default) treats nested arrays as scalar values.

**Workaround:** Use `wp eval-file` with piped PHP, or avoid `wp option patch` for array-valued keys entirely.

## Fix (Two-Pronged)

### 1. Mu-plugin output buffer (`fix-lcp-opacity.php` v0.6.0)

Added an output buffer that strips WP Rocket's lazy-load transformation from slider background images:

```php
// Hook wp_loaded at PHP_INT_MAX — ensures buffer starts AFTER WP Rocket
add_action('wp_loaded', function () {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;
    ob_start('io_fix_slider_lazyload');
}, PHP_INT_MAX);

// Also hook rocket_buffer for cached pages
add_filter('rocket_buffer', 'io_fix_slider_lazyload', PHP_INT_MAX);

function io_fix_slider_lazyload($buffer) {
    // Scoped to .eut-bg-image elements only
    // Replaces data-bg="URL" with style="background-image: url(URL);"
    // Removes rocket-lazyload class
}
```

**Buffer ordering is critical.** Using `wp_loaded` at PHP_INT_MAX ensures the ob_start fires before WP Rocket's processing at `template_redirect`. This makes our buffer OUTER (starts first), so WP Rocket's INNER buffer processes first (adds lazy-load), then our outer buffer processes second (undoes it for slider elements). If the buffer order were reversed, WP Rocket would lazy-load after our fix, undoing it.

Also hooks `rocket_buffer` filter as a secondary path for WP Rocket's own caching pipeline.

### 2. WP Rocket `exclude_lazyload` substrings

Added `_HOME-` and `_HOME_` to the `exclude_lazyload` array. All slider background images (slides 2-9) contain these substrings. Verified zero matches on product/category pages. Slide 1 already covered by `CadeauCalligraphie_Phedre_triocote-scaled`.

The mu-plugin includes a self-heal that restores the array if it's corrupted to a string (`init` hook, admin-only).

### 3. Why both fixes

The output buffer is the primary defense — it works regardless of WP Rocket settings. The `exclude_lazyload` entries are a belt-and-suspenders backup: if the output buffer ever fails (e.g., buffer conflict with another plugin), the WP Rocket exclusion prevents the lazy-load conversion at the PHP level.

## Verification

```bash
# Origin bypass — confirms fix works (bypasses Cloudflare CDN):
curl -s 'https://www.impressionoriginale.com/?nocache=verify' \
  | grep -oP '<div[^>]*eut-bg-image[^>]*>' | grep -c 'rocket-lazyload'
# Expected: 0

# Live check (after CDN purge):
curl -s 'https://www.impressionoriginale.com/' \
  | grep -oP '<div[^>]*eut-bg-image[^>]*>' | grep -c 'rocket-lazyload'
# Expected: 0

# CDN cache status:
curl -sI 'https://www.impressionoriginale.com/' | grep cf-cache-status
# Should show MISS after purge
```

## CDN Purge

Full purge sequence (RULE 15):

```bash
ssh impressionor@impressionor.ssh.wpengine.net 'wp cache flush && wp eval "
if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
}
"'
```

`wp cache flush` alone is insufficient — it only clears WP Engine's Varnish origin cache. Cloudflare front-ends the site with 28-day edge-cache TTLs. `WpeCommon::clear_cdn_cache()` purges Cloudflare.

## Rollback

```bash
# Revert mu-plugin to v0.5.0
ssh impressionor@impressionor.ssh.wpengine.net \
  'cp /sites/impressionor/wp-content/mu-plugins/fix-lcp-opacity.php.v0.5.0 \
      /sites/impressionor/wp-content/mu-plugins/fix-lcp-opacity.php'
# Remove exclude_lazyload additions
ssh impressionor@impressionor.ssh.wpengine.net \
  'wp option patch delete wp_rocket_settings exclude_lazyload "_HOME-" \
   && wp option patch delete wp_rocket_settings exclude_lazyload "_HOME_"'
# Purge caches
```

## Related

- [[lcp-31s-root-cause-opacity-translatex]] — the prior LCP fix (ADR 0005)
- [[wpe-cdn-purge-after-change]] — full purge sequence
- `mu-plugins/fix-lcp-opacity.php` v0.6.0
- [[lcp-image-lazy-load-scroll-fix]] — memory entry
