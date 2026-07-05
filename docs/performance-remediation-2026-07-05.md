# Performance Remediation — 2026-07-05 Lighthouse Report

Score: **37 Performance** | LCP **9.1s** | TBT **700ms** | CLS **0.079** | Payload **9,300 KiB**

Report date: Jul 5, 2026, 19:19 GMT+2 · Desktop emulation · Lighthouse 13.4.0

## Root Cause Summary

Three things dominate the low score:

1. **RevSlider JS blocks LCP.** The 9.1s LCP is almost certainly the hero slider waiting for `rs6.min.js` + `rbtools.min.js` to parse and render before the first large image paints. RevSlider is a known performance killer on every WP site it touches.
2. **Image payload is enormous.** 157 JPGs on homepage, all served as JPEG (WebP Express not converting at origin; Cloudflare Polish sees `webp_bigger` — origin images aren't compressed enough for WebP to beat them). 9,300 KiB total page weight, an estimated 3,000+ KiB of which is images.
3. **1,842 KiB unused JavaScript.** Theme bundles (`plugins.js`, `main.js`), WPBakery, jQuery UI components loaded individually (dialog, draggable, resizable, etc.), Masonry, imagesLoaded — all loading on a page that may not use them.

Secondary: no CSP/XFO/HSTS (Best Practices 73), missing accessible names/contrast (Accessibility 79), JS-generated links not crawlable (SEO 92).

## Remediation — Priority Order

### P0: LCP 9.1s → target < 2.5s

**A. Remove or replace RevSlider (highest impact, highest risk)**

RevSlider loads `rs6.min.js` (~150KB compressed) + `rbtools.min.js` + inline config. It's render-blocking by nature — it manipulates the DOM for the hero area. The theme (EngineThemes "The Core") likely embeds it as the homepage hero.

- **Option 1 (recommended):** Replace the RevSlider hero with a single optimized, preloaded `<img>` + CSS. A static WebP hero at 1200px wide, preloaded with `<link rel="preload" as="image">`, served through Cloudflare, paints in under 1s. This is a theme template change — blast radius is the homepage layout. Risk: medium (layout), instantly reversible by restoring the template.
- **Option 2 (low risk):** If RevSlider must stay, preload the LCP image (`<link rel="preload">` in `<head>`), add `fetchpriority="high"` on the first slide image, and defer RevSlider JS with WP Rocket's "Delay JavaScript" — but RevSlider may not work deferred. Test on a staging copy first. Risk: RevSlider may break if JS is deferred.

**Verification:** Lighthouse LCP < 2.5s on desktop. Re-run `harness/fingerprint.sh` diff — homepage must still 200, heading structure intact.

**B. Preload the LCP image regardless**

Even if RevSlider stays, identify the LCP element (likely the first slide's background or featured image) and preload it:

```html
<link rel="preload" as="image" href="<lcp-image-url>" fetchpriority="high">
```

This alone can cut LCP by 2-4s if the image is currently waiting for JS to request it.

### P1: Reduce image payload → target < 3,000 KiB total

**C. Enable proper WebP at origin**

WebP Express is in the stack but not converting. Cloudflare Polish's `webp_bigger` response means origin JPEGs are large enough that WebP is bigger — the source images need optimization first.

- Run all uploads through `jpegoptim` or ShortPixel/Imagify (WordPress plugin) to compress JPGs to ~80% quality at origin. Then WebP Express or Cloudflare Polish will produce smaller WebP variants.
- Alternatively, switch to Cloudflare Pro's full Polish (lossy mode) which compresses more aggressively regardless.
- **Quick win:** convert the 600x600 product thumbnails (51KB each) to properly compressed WebP. Even at 80% JPEG quality → ~30KB each → 30 images on homepage = ~900KB vs ~1,500KB.

**D. Add explicit width/height on all images**

Lighthouse flagged this. Without dimensions, the browser can't reserve space → layout shifts as images load (CLS 0.079, borderline). WP Rocket has a "Add missing image dimensions" option — enable it. Or add them via theme functions.

**E. Serve responsive images**

The homepage loads 600x600 thumbnails but may display them smaller. Ensure `<img>` tags have `srcset` with appropriate sizes. WooCommerce + WP should handle this, but the theme may override.

### P2: Reduce JavaScript bloat → target TBT < 200ms

**F. WP Rocket — enable Delay JavaScript**

Currently JS is minified but not delayed. Enable "Delay JavaScript" in WP Rocket for all scripts except:
- GTM/GTAG (must fire for analytics)
- Termly/CookieBot (consent must load before tags)
- Stripe checkout

This defers non-critical JS (RevSlider, jQuery UI, Masonry, Pinterest widget, Facebook widget, WP Google Maps) until user interaction. Estimated TBT reduction: 300-500ms.

**G. Remove unused plugins**

From the asset audit, these plugins load JS/CSS on the homepage unnecessarily:
- **WP Google Maps** — loading `maps.js` + `wpgmp_frontend.js` on homepage. Unless there's a map on the homepage, dequeue it.
- **Mailchimp WP** — loading tooltipster CSS on every page.
- **YITH Social Login** — loading frontend JS on homepage.
- **WPBakery (js_composer)** — loading `woocommerce-add-to-cart.js` via the theme.

WP Rocket's "Optimize CSS/JS per page" feature can selectively disable these.

**H. Defer non-critical CSS**

4 CSS files loading but 257 KiB unused. WP Rocket's "Optimize CSS Delivery" can inline critical CSS and defer the rest. Requires generating critical CSS — WP Rocket's "Optimize for Core Web Vitals" feature or a manual critical CSS extraction.

### P3: SEO — "Links are not crawlable"

**I. Fix uncrawlable links**

Lighthouse flagged this. Likely cause: JS-generated links in RevSlider or WPBakery that don't exist in the static HTML. Google can render JS, but Lighthouse tests against the static DOM. Verify:
1. Check the homepage source for `<a href=` — if navigation/product links are present in the static HTML, this is a false positive.
2. If RevSlider generates links via JS (e.g., slide CTAs), those may not be crawlable.

Mitigation: ensure key navigation links exist in server HTML, not just JS. Add a static fallback nav.

### P4: Best Practices & Security Headers

**J. Add security headers**

- **CSP:** `Content-Security-Policy` header — start with report-only mode, then enforce.
- **HSTS:** `Strict-Transport-Security: max-age=31536000; includeSubDomains; preload` — WP Engine supports this via their control panel or `.htaccess`.
- **XFO:** `X-Frame-Options: DENY` — prevents clickjacking.
- **COOP:** `Cross-Origin-Opener-Policy: same-origin` — process isolation.

These are server-level headers. WP Engine nginx — can be set via WP Engine's "Redirect Rules" or their support.

### P5: Accessibility

- **Buttons without accessible name:** likely icon-only buttons (search, cart, menu toggle). Add `aria-label` or screen-reader text.
- **Links without discernible name:** icon-only links. Same fix.
- **Contrast issues:** run the Lighthouse-reported contrast ratios and adjust theme CSS.
- **Heading order:** heading levels skip (e.g., H1 → H3). Fix in theme templates.

These are theme-level fixes in EngineThemes "The Core" — surgical template edits.

## Quick Wins (under 30 min, low risk)

| # | Action | Impact |
|---|--------|--------|
| 1 | WP Rocket: enable "Add missing image dimensions" | Fixes CLS image flag, zero risk |
| 2 | WP Rocket: enable "Delay JavaScript" (test homepage) | Cuts TBT, possible RevSlider breakage |
| 3 | Add `<link rel="preload">` for LCP image | Cuts LCP 2-4s, zero risk |
| 4 | Cloudflare: enable full Polish (lossy) if available | Shrinks image payload immediately |
| 5 | Dequeue WP Google Maps, Mailchimp tooltipster, YITH Social Login on homepage | Removes ~200KB unused JS/CSS |

## Risks & Dependencies

- **RevSlider removal:** theme template change. Backup first. Test that homepage still renders hero image. Revert template if broken.
- **Delay JavaScript:** may break RevSlider, Pinterest widget, Facebook widget. Test each after enabling. WP Rocket lets you exclude specific scripts.
- **Image compression:** one-time bulk operation. Run on a test copy first. Don't overwrite originals without backup.
- **Security headers:** CSP in report-only mode is zero-risk. Enforcement may break inline scripts — test with report-only first.

## Verification (post-remediation)

```bash
# Before/after fingerprint diff
./harness/fingerprint.sh https://www.impressionoriginale.com pre-fix
# ... apply fixes ...
./harness/fingerprint.sh https://www.impressionoriginale.com post-fix
diff pre-fix/SUMMARY.txt post-fix/SUMMARY.txt

# Lighthouse re-run — target scores
# Performance: 50+
# LCP: < 4s (realistic first pass); < 2.5s (ultimate)
# TBT: < 300ms
# Payload: < 5,000 KiB

# Verify crawlable links
curl -sL https://www.impressionoriginale.com/ | grep -oP '(?<=href=")[^"]*' | grep -c '^https\?://'
```

## What's Already Been Done

- On-page SEO: titles, metas, og:image, category descriptions optimized (+16 points on scorecard, 82%).
- hreflang: confirmed valid in sitemap (not a defect).
- UA cleanup: Issue #3 tracked, not yet implemented.

This performance work is the next major lever — on-page SEO content is now strong; performance is the bottleneck for both user experience and Core Web Vitals ranking signals.
