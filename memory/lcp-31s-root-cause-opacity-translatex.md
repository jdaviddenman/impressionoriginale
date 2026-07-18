---
name: lcp-31s-root-cause-opacity-translatex
description: "H1 hidden by CSS opacity:0 + JS translateX(200px), not image bytes. LCP 31.3s with 98% render delay. Fix: mu-plugin fix-lcp-opacity.php."
metadata:
  type: project
  originSessionId: 8fe8ee67-16da-45fb-a561-61a5d894254b
---

# LCP 31.3s Root Cause — opacity + translateX

**Date:** 2026-07-16
**Status:** Fix deployed (mu-plugin fix-lcp-opacity.php v0.9.0). CSS fix necessary but insufficient — 97% render delay persists ([[lcp-css-fix-insufficient-97pct-render-delay]]).

## Root Cause

Homepage LCP 31.3s. The LCP element is `h1.eut-title.eut-light` — the slider heading TEXT, not an image. Zero bytes to load, but 98% render delay (30.6s).

Three mechanisms conspire to prevent the browser from painting the H1:

1. **Theme CSS `opacity: 0`:** `#eut-feature-section .eut-title { opacity: 0 }` — hidden by default, waiting for JS fade-in animation
2. **JS `initPos` `translateX(200px)`:** `EUTHEM.featureAnim.initPos()` applies `transform: translateX(200px)` inline — pushes the H1 200px right, partially outside the 360px Moto G4 viewport. Off-screen element cannot be LCP candidate.
3. **JS `initPos` inline `opacity: 0`:** Same function sets `element.style.opacity = 0` inline

JS executes after jQuery (90KB) loads synchronously + ~40 deferred scripts download + theme's `plugins.js` + `main.js` parse — ~30 seconds on Moto G4 + Slow 4G.

## False Lead: Background Image Lazy-Load

Slide 0 had `data-bg` + `rocket-lazyload` (WP Rocket background lazy-load). Fixing this (adding image URL to `exclude_lazyload`) produced zero improvement — LCP unchanged at 31.3s. The image was never the problem.

## Fix

Mu-plugin `fix-lcp-opacity.php` overrides theme CSS with `opacity:1!important; transform:none!important; visibility:visible!important` targeting first slider child + all animation class variants. Current version: v0.9.0 (JS forceShow removed — caused CLS 0.317).

**CSS fix is necessary but insufficient.** Three Lighthouse runs (2026-07-18) show constant 97% render delay regardless of CDN cache state or CSS delivery method. JS execution time is the bottleneck.

## Related

- ADR 0005 — full investigation
- [[lcp-image-lazy-load-scroll-fix]] — the follow-on fix (LCP image still lazy-loaded after this fix)
- [[lcp-fix-session-postmortem]] — 10 mistakes from the fix session
- [[lcp-css-fix-insufficient-97pct-render-delay]] — CSS fix alone not enough
- [[original-baseline-was-better]] — pre-O LCP was 3.9s
- `mu-plugins/fix-lcp-opacity.php` v0.9.0
