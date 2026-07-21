---
name: termly-preconnect-async-fix
description: "Termly resource-blocker (4,350ms render-blocking) optimized with preconnect + async via mu-plugin io-termly-preconnect-async.php. Consent gated by Consent Mode v2 denied defaults. Deployed 2026-07-19."
metadata:
  type: project
  originSessionId: current
---

# Termly Preconnect + Async Fix

**Date:** 2026-07-19

## Problem

Termly cookie consent banner (`app.termly.io/resource-blocker/...`) was render-blocking for 4,350ms. As the consent manager, it must load before tracking scripts — cannot be deferred (GDPR requirement). But it can be optimized.

## Fix

Mu-plugin `io-termly-preconnect-async.php` deployed Jul 19:
1. Adds `dns-prefetch` + `preconnect` for `app.termly.io` via `wp_resource_hints` filter
2. Adds `async` attribute to Termly resource-blocker script tag via `script_loader_tag` filter
3. Consent defaults gated by Google Consent Mode v2 via `fix-consent-defaults.php`

## Results (pagespeed.dev, Jul 19)

| Metric | Before | After |
|---|---|---|
| Termly render-blocking | 4,350ms | 0ms |
| Termly main-thread time | — | 1,635ms |

Preconnect eliminates DNS + TCP + TLS handshake from critical path. Async attribute allows parsing to continue while Termly downloads. Remaining 1,635ms main-thread time is Termly's 311KB bundled script execution — inherent, not fixable without breaking GDPR consent.

## Related

- ADR 0012 — full incident report
- [[rucss-saas-empty-css]] — subsequent RUCSS breakage (unrelated)
- [[io-lcp-critical-css-fix-deployed]] — concurrent LCP fix
