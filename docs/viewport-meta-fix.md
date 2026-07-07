# Viewport meta — `user-scalable=no` + `maximum-scale=1` (accessibility + mobile-friendly violation)

## Summary

Every page emits a restrictive viewport meta that disables pinch-to-zoom:

```html
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
```

This is a WCAG 2.1 AA 1.4.4 violation (resize text without assistive technology up to 200%) and triggers a Google mobile-friendly flag. Modern browsers ignore `user-scalable=no` on desktop, but removal is the correct fix regardless.

## Evidence

```bash
curl -s https://www.impressionoriginale.com/ | grep -oP '<meta[^>]*viewport[^>]*>'
# → <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
```

Confirmed on homepage and spot-checked across inner pages — theme-wide `<head>` include.

## Fix

Remove the two offending attributes, leaving only:

```html
<meta name="viewport" content="width=device-width, initial-scale=1">
```

## Location

Theme `header.php` — exact file path TBD. Likely `wp-content/themes/the-core/header.php` or a child-theme override. Locate via:

```bash
grep -r 'user-scalable=no' wp-content/themes/
```

## Blast radius

Zero. Single attribute pair in `<head>`, identical on every page. Modern mobile browsers already override `user-scalable=no`; removing it changes no layout. Instantly reversible (re-add the two attributes).

## Risk

None. The fix removes two attributes that browsers already ignore. If any visual test shows a regression, revert by restoring the original string — one-edit rollback.

## Verification (done-when)

- [ ] `curl -s https://www.impressionoriginale.com/ | grep -oP '<meta[^>]*viewport[^>]*>'` outputs only `width=device-width, initial-scale=1`
- [ ] No `user-scalable=no` or `maximum-scale=` in the viewport meta on any sampled page (home, product, category, page)
- [ ] Homepage renders identically before/after (visual spot-check; no layout shift expected)

## Notes

- WCAG 2.1 SC 1.4.4 requires that text can be resized up to 200% without loss of content or functionality. `user-scalable=no` and `maximum-scale=1` block browser zoom on mobile, failing this criterion.
- Google Search Console may flag this under Mobile Usability. After fix, request re-validation in GSC.
- The fix is a theme-file edit, not a plugin setting. If the theme updates, it may revert — confirm after any theme update.
