---
name: rucss-saas-empty-css
description: "RUCSS SaaS API returning empty CSS — all 1,197 DB entries have len=0. wpr-usedcss block missing from page output. 29 CSS files load sync. Needs wp-admin investigation."
metadata:
  type: project
  originSessionId: current
---

# RUCSS SaaS API Returning Empty CSS

**Date:** 2026-07-21

## Evidence

WP Rocket `remove_unused_css: 1` is enabled but the `wpr-usedcss` inline block is absent from page output. 29 external CSS files load synchronously with `media='all'` — render-blocking.

Database check (Jul 21):
```sql
SELECT url, CHAR_LENGTH(css) as len, modified FROM wp_wpr_rucss_used_css
WHERE url LIKE '%impressionoriginale%' LIMIT 10;
```
All 10 sampled entries: `len=0` (empty CSS). Modified timestamps are today — cron runs and overwrites entries with empty results.

The SaaS API endpoint (`saas.wp-rocket.me`) is reachable (HTTP 404 on root — expected). WP Rocket license (`1667811646`), consumer key (`343a2e8d`), and secret key are set.

## Root cause (hypothesis)

WP Rocket's SaaS API is returning empty used CSS for all URLs. Either:
1. API rate limit hit during overnight cron regeneration of 1,197 entries
2. License/API key invalidated
3. SaaS service degraded

**Why:** The RUCSS cron runs periodically, calls the SaaS API for each URL, and stores the result. If the API returns empty, the DB entries get `len=0`. WP Rocket then checks the DB on page load, finds empty CSS, and falls back to sync CSS loading (no `wpr-usedcss` block).

## Impact

- 35 render-blocking CSS files
- No `wpr-usedcss` inline block — all CSS loads sync
- `async_css: 1` without RUCSS also fails — 4 config combinations tested (see [[async-css-not-working-without-rucss]])
- Only mitigation: io-lcp critical CSS block (1,431 chars) provides H1 styles early

## Diagnostic History

- **Jul 20:** Memcached purge cleared SaaS transient → WP Rocket internally disabled RUCSS
- **Jul 21 AM:** SaaS transient restored via wp-admin visit. "Clear Used CSS" cleared 1,506 empty entries. SaaS returned hashes but no CSS content.
- **Jul 21 PM:** 142 new entries after re-clear. 112 have non-empty hashes — SaaS IS processing URLs. But ALL have `len=0` (empty CSS). SaaS returns hashes without CSS.
- **Conclusion:** WP Rocket SaaS server-side issue. Support case compiled: `docs/wp-rocket-support-rucss-saas.md`

## Resolution

Requires WP Rocket support. Plan B: optimize elsewhere (Issue #43 static hero, #102 WebP, #101 pixel dedup). RUCSS stays enabled — will work when SaaS is fixed.

## Related

- [[rucss-enabled-css-async-works]] — RUCSS was working Jul 19 (LCP -42%)
- [[async-css-not-working-without-rucss]] — async_css doesn't work standalone
- [[io-lcp-critical-css-fix-deployed]] — the mitigation keeping LCP from regressing further
- ADR 0012 — full incident report
