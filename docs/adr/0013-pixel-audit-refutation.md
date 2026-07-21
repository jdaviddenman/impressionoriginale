# ADR 0013 — Pixel Duplication Audit: Four False Positives

**Date:** 2026-07-21
**Status:** Complete. All four "duplicate" pairs are false positives. No plugin disables needed. PYS dead modules already disabled.

## Context

ADR 0010 (Jul 18) flagged 3 potential pixel duplications:
- NF3: Facebook pixel ×2 (PixelYourSite + Facebook-for-WooCommerce)
- NF3: Pinterest pixel ×2 (PixelYourSite + Pinterest-for-WooCommerce)
- NF2: GA4 ×2 (GTM4WP + Google Site Kit) — already ruled FALSE POSITIVE

Additionally, the plugin list showed:
- Two consent mechanisms: uk-cookie-consent + Termly

Issue #101 was filed: "A3: Deduplicate Facebook + Pinterest pixels."

## Method

Two-pass audit via 6 agents: source code analysis (SSH), live HTML inspection, browser firing check, consent audit, blast radius assessment. Then adversarial refutation of all findings.

## Findings

### 1. Facebook — FALSE POSITIVE

| | PixelYourSite 11.2.1 | Facebook-for-WooCommerce 3.7.4 |
|---|---|---|
| Pixel ID | `1011540012316296` | `1011540012316296` |
| Browser pixel | Yes (sole injector via public.js) | No (signals.js manages consent, no fbq()) |
| Server API | Yes | Yes (merchant access token) |
| Product catalog sync | No | Yes (enable_product_sync: yes) |
| Consent gate | facebook_prior_consent_enabled: true | signals.js held state |

**No browser-side duplication.** Same pixel ID. PYS owns browser-side firing. FB-for-WooCommerce handles catalog sync + server CAPI. Both are load-bearing for different functions.

**Meta CAPI risk:** Both have server API enabled — potential double-send on Purchase/AddToCart. Already tracked as Issue #91 (Month 3+, verify in Meta Events Manager when Ads launch).

**Action:** None. Both plugins needed.

### 2. Pinterest — FALSE POSITIVE

PYS has NO Pinterest module. `pysOptions.staticEvents` contains only `"facebook"`. No `pys_pinterest` option in DB. All `pintrk()` calls originate from Pinterest-for-WooCommerce with `np: "woocommerce"` namespace marker.

The two calls on each page — `pintrk('page')` and `pintrk('track', 'PageVisit')` — are different Pinterest event types (page beacon vs conversion tracking), not duplicates even from a single source.

**Action:** None. Pinterest-for-WooCommerce is the sole Pinterest tag injector.

### 3. GA4 — FALSE POSITIVE (confirmed ADR 0010 NF2)

| | GTM4WP 1.22.3 | Google Site Kit 1.183.0 |
|---|---|---|
| Container | GTM-MT7G7Z3C | — |
| GA4 injection | via GTM | useSnippet: false |
| Active modules | — | pagespeed-insights, analytics-4 (dashboard-only) |

GA4 delivery path: GTM4WP → GTM-MT7G7Z3C → G-Y88VQHFDBV. Single path. Site Kit is wp-admin dashboard layer only (reads GA4 data for reports, zero frontend tags).

**Action:** None. Both serve different purposes.

### 4. Consent — FALSE POSITIVE (slug rebrand)

`uk-cookie-consent` v3.3.1 has display title "Termly - GDPR/CCPA Cookie Consent Banner." It IS the Termly plugin. The slug is legacy from a rebrand.

Evidence:
- Zero `uk_cookie*` options in DB
- Zero `uk-cookie` output in homepage HTML
- Only Termly resource-blocker in HTML
- fix-consent-defaults.php references Termly as the CMP
- io-termly-preconnect-async.php optimizes Termly script
- GTM4WP consent mode set to "termly"

**Action:** None. Sole consent manager.

### 5. PYS dead modules — already disabled

PYS GTM module (`pys_gtm.enabled: false`) and GA module (`pys_ga.enabled: false`) were already disabled. Both had empty tracking IDs — zero functional impact.

**Action:** None. Already done.

## Decision

**No plugin disables, deactivations, or configuration changes needed.** All four "duplicate" findings were false positives. The one legitimate risk (Meta CAPI double-send) is already tracked as Issue #91.

Issues #101 (pixel dedup) can be closed. Issue #91 (Meta CAPI) stays open for Month 3+ verification.

## Related

- ADR 0010 — performance analysis (NF2, NF3)
- Issue #91 — Meta CAPI double-send risk
- Issue #101 — pixel deduplication (closed)
- [[cli-lighthouse-tbt-misleading]] — TBT was never the bottleneck
