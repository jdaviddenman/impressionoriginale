---
name: lcp-fix-session-postmortem
description: "Every mistake made during the 2026-07-16 LCP fix session — output buffer, RUCSS, delay_js, wp option patch, cache layers, wrong CSS targets. Permanent lessons."
metadata: 
  node_type: memory
  type: feedback
  originSessionId: 8fe8ee67-16da-45fb-a561-61a5d894254b
---

# LCP Fix Session Postmortem — Mistakes & Lessons

**Date:** 2026-07-16
**Session:** LCP image lazy-load fix — 10+ production changes, each worse than the last

## Mistake 1: Output buffer to strip WP Rocket lazy-load

**What O did:** Added an `ob_start` callback that stripped `rocket-lazyload` class and restored `data-bg` to inline `style="background-image"` for ALL `.eut-bg-image` elements on the page.

**Why it was wrong:** Stripped lazy-load from ALL 10 slider background images (300-400KB each), causing ~3MB of additional payload on page load. On Slow 4G, this starved the LCP image of bandwidth. LCP regressed from 4.9s → 20.4s. CLS appeared (0.32) from images loading asynchronously.

**Correct approach:** Use WP Rocket's built-in `exclude_lazyload` setting with targeted filename substrings for only the first 1-2 slides. Or use `data-skip-lazy` attribute on specific elements.

**Why**: The output buffer ran after WP Rocket's own processing but before the HTML was sent. Buffer ordering is fragile and regex-based HTML manipulation is error-prone. WP Rocket's settings-based exclusion is the documented, supported approach.

## Mistake 2: Didn't inventory WP Rocket settings before changing

**What O did:** Made changes without checking what WP Rocket features were enabled.

**What was enabled that O didn't know:**
- `remove_unused_css: 1` — strips ALL inline `<style>` content
- `delay_js: 1` — delays ALL JS until user interaction
- `lazyload_css_bg_img: 1` — converts bg images to `data-bg` + `rocket-lazyload`
- `minify_css: 1` — combines CSS

**Rule:** Before any WP Rocket change, run:
```bash
ssh ... 'wp option get wp_rocket_settings --format=json' | python3 -c "import sys,json; s=json.load(sys.stdin); [print(f'{k}: {s[k]}') for k in sorted(s) if s[k] and k in ['delay_js','remove_unused_css','lazyload_css_bg_img','minify_css','minify_html','lazyload','exclude_lazyload']]"
```

## Mistake 3: Didn't understand cache layers

**What O did:** Ran `wp cache flush` repeatedly, thinking it cleared everything. It only clears WP Engine Varnish.

**Cache layers (outer→inner):**
1. Cloudflare CDN — `WpeCommon::clear_cdn_cache()`
2. WP Engine Varnish — `wp cache flush`
3. WP Rocket page cache — `rocket_clean_home()`
4. WP Rocket RUCSS DB — `DELETE FROM wp_wpr_rucss_used_css`

**Correct purge order (inner→outer):** Rocket → Varnish → CDN. If you purge CDN before Rocket, CDN re-caches the stale Rocket page.

**Why**: Each cache layer can mask fixes. A fix deployed to origin is invisible until ALL layers above it are purged. O repeatedly checked the CDN (outermost) and saw stale content, assuming fixes didn't work.

## Mistake 4: RUCSS strips inline CSS

**What O did:** Didn't know WP Rocket's "Remove Unused CSS" removes ALL inline `<style>` elements by default. Our inline CSS was emptied: `<style id="io-lcp-first-slide"></style>`.

**The fix that didn't work:** `rocket_rucss_inline_content_exclusions` filter. The filter exists but didn't reliably protect our CSS in the WP Rocket version on this site.

**The fix that broke everything:** Deleted the RUCSS DB table (1386 rows of used CSS). When RUCSS was re-enabled later, it removed all CSS files but had no cached used CSS to inline → page had NO CSS at all → completely blank white page.

**Rule:** Disable RUCSS before making CSS changes. Only re-enable after verifying the page renders correctly. Never delete the RUCSS table without disabling RUCSS first.

## Mistake 5: `wp option patch insert` corrupts serialized arrays

**What O did:** Used `wp option patch insert wp_rocket_settings exclude_lazyload "_HOME-"` to add lazy-load exclusions.

**Result:** The command converted the array `["CadeauCalligraphie_Phedre_triocote-scaled"]` to the plain string `"_HOME-"`. Repeated calls overwrote with `"_HOME_"`.

**Correct approach:** Use `wp eval-file` with piped PHP:
```bash
echo '<?php $s=get_option("wp_rocket_settings"); $s["exclude_lazyload"][]="value"; update_option("wp_rocket_settings",$s);' | ssh ... 'cat > /tmp/fix.php && wp eval-file /tmp/fix.php'
```

**Why**: `wp option patch` with `--format=plaintext` (default) treats nested serialized arrays as scalar values. It cannot append to arrays within serialized option values.

## Mistake 6: CSS targeted `.eut-title` but JS translates `.eut-fade-in-right`

**What O did:** Set `transform:none!important` on `.eut-title` (the H1). But `EUTHEM.featureAnim.initPos()` sets `translateX(200px)` on the `.eut-fade-in-right` PARENT CONTAINER via jQuery `.transition()`.

**Why**: O read the ADR (which said "H1") but didn't verify against the actual HTML structure. The `eut-fade-in-right` class is on `.eut-feature-content`, not on `.eut-title`. JS targets ALL content items (title, description, buttons) inside the animated section, not just the H1.

**Lesson:** Never trust a prior diagnosis. Inspect the actual DOM structure and JS source before writing CSS selectors.

## Mistake 7: Delay JS was the ROOT CAUSE of blank page

**What O didn't find for hours:** `delay_js: 1` — WP Rocket's "Delay JavaScript Execution" prevents ALL JavaScript from running until user interaction (scroll/click/tap).

**Effect:** No JS runs on page load → `woocommerce-no-js` class remains on `<body>` → theme CSS hides content → COMPLETELY BLANK WHITE PAGE. The user must scroll to trigger JS, which then shows content. This also explains the original complaint: "LCP image doesn't load until I scroll."

**Why O missed it:** O checked the HTML and CSS via curl but couldn't see the rendered page. O didn't systematically check ALL WP Rocket settings before making changes.

**Rule:** Check `delay_js` FIRST when a site appears blank. It's the most common cause of "invisible content" with WP Rocket.

## Mistake 8: Claimed "fixed" without verification

**What O did:** Deployed changes, ran curl, saw expected HTML, and claimed "fixed" — 4+ times. Each time the user said "still broken."

**Why**: curl shows server HTML. It doesn't show:
- What a browser renders
- Whether JS executes
- CDN cache status
- Whether CSS actually applies

**Rule:** Never claim "fixed" without at minimum:
- `cf-cache-status: MISS` confirmation
- Origin bypass verification (`?nocache=test`)
- If possible, a browser screenshot

## Mistake 9: `_HOME-` and `_HOME_` excluded ALL slider images

**What O did:** Added filename substrings `_HOME-` and `_HOME_` to `exclude_lazyload` to prevent lazy-loading of slider images that were LCP candidates.

**Why it was wrong:** ALL slider images (slides 2-9) contain `HOME` in their filenames. These substrings would exclude ALL of them from lazy loading, causing the same bandwidth starvation as Mistake 1 (3MB+ eager-loaded images).

**Correct approach:** Only exclude the SPECIFIC image that's the LCP candidate (`CadeauCalligraphie_Phedre_triocote-scaled`). WP Rocket already skips above-the-fold bg images automatically — the exclusion is only needed as a belt-and-suspenders for narrow viewports.

## Mistake 10: 10+ production changes without proper rollback

**What O did:** Made sequential changes to live production (output buffer, exclude_lazyload, RUCSS enable/disable, CSS selectors, JS injection) without a rollback plan or verification step between each.

**Correct approach:**
1. State the single change
2. State the verification method
3. Deploy
4. Verify
5. If fail → rollback immediately
6. If pass → next change

**Why**: Each change compounded the previous problems. The site went from "LCP image loads on scroll" (original issue) to "completely blank white page" because O kept adding fixes on top of broken fixes.

## Permanent Rules

1. **Inventory WP Rocket settings before any change** — `delay_js`, `remove_unused_css`, `lazyload_css_bg_img` especially
2. **Never use output buffers to modify WP Rocket's HTML** — use WP Rocket's settings and filters
3. **Never use `wp option patch insert` for array-valued settings** — use `wp eval-file`
4. **Purge caches inner→outer: Rocket → Varnish → CDN**
5. **Disable RUCSS before making CSS changes** — re-enable only after verification
6. **Check `delay_js` FIRST for any "blank page" report**
7. **Verify with `?nocache=` origin bypass AND CDN `cf-cache-status: MISS` before claiming fixed**
8. **Inspect actual DOM/JS before writing CSS selectors** — don't trust prior diagnoses
9. **One change at a time with explicit verification between**
10. **If you can't use a browser, state "unverified — cannot render"**
