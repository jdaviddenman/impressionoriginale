# ADR 0014 — IndexNow auto-submit for instant search engine indexing

**Status:** Accepted · **Date:** 2026-07-21

## Context

Bing Webmaster Tools was being onboarded. Two requirements emerged:

1. **Domain verification** — Bing needs a `msvalidate.01` meta tag in `<head>`
2. **IndexNow** — instant URL submission to IndexNow-compatible search engines (Bing, Yandex, Seznam)

Without IndexNow, new/updated content waits for crawlers to discover it via sitemap polling, which can take hours to days.

## Decision

- **Verification:** Inject `msvalidate.01` meta tag site-wide via mu-plugin (`io-bing-verify.php`) using `wp_head` action at priority 1. Tag is present on every page — harmless, zero maintenance.
- **IndexNow:** Two-pronged approach:
  1. **Key file** hosted at web root (`1479359dbca34ecabae3e080d1b96001.txt`) — required by the IndexNow protocol.
  2. **mu-plugin** (`io-indexnow.php`) that auto-submits URLs on publish/update via `wp_after_insert_post`, plus a bulk processor that parses the Yoast sitemap and submits batches of 10 URLs every 60 seconds.
- **Bulk backfill:** All 1,386 URLs from the existing sitemap submitted to `api.indexnow.org` in batches of 50.

## Consequences

- **Positive:** New content reaches search engines within hours instead of days. No manual submission needed.
- **Negative:** A fire-and-forget HTTP call on every publish — negligible overhead (non-blocking, 15s timeout).
- **Risk:** If IndexNow API is down, submissions silently fail (logged to `error_log`). No retry — acceptable for a best-effort signal.
