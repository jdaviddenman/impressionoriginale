# Bing Webmaster Tools — verification & IndexNow setup

**Date:** 2026-07-21 · **ADR:** [0014](adr/0014-indexnow-setup.md)

## What was done

### 1. Bing domain verification

- Added `<meta name="msvalidate.01" content="A410EF940B9223B98F874C7F616EAAAE" />` to every page's `<head>`
- **Mu-plugin:** `io-bing-verify.php` — hooks `wp_head` at priority 1
- **Verification:** tag confirmed in source at line 48, before `<body>` at line 335

### 2. IndexNow key file

- Hosted at `https://www.impressionoriginale.com/1479359dbca34ecabae3e080d1b96001.txt`
- Contains the IndexNow API key: `1479359dbca34ecabae3e080d1b96001`
- Placed directly at web root (`/sites/impressionor/1479359dbca34ecabae3e080d1b96001.txt`)

### 3. IndexNow auto-submit mu-plugin

- **Mu-plugin:** `io-indexnow.php`
- Submits single URL to `api.indexnow.org` on every publish/update
- Includes sitemap bulk processor (Yoast sitemap → IndexNow batches)
- Post type filter skips revisions, nav items, templates, etc.
- Non-blocking (`blocking: false`), 15s timeout, errors logged only

### 4. Bulk backfill

- All 1,386 URLs from `sitemap-full.xml` submitted to IndexNow
- 28 batches of 50 URLs, all returned HTTP 200/202

## Files on server

| File | Path |
|------|------|
| Bing verify | `/sites/impressionor/wp-content/mu-plugins/io-bing-verify.php` |
| IndexNow | `/sites/impressionor/wp-content/mu-plugins/io-indexnow.php` |
| Key file | `/sites/impressionor/1479359dbca34ecabae3e080d1b96001.txt` |

## Cache notes

After deploying both mu-plugins:
- `wp cache flush` (WP Engine object cache)
- `WpeCommon::clear_cdn_cache()` (Cloudflare via WP Engine)

If tag/key file ever disappears: check that WP Engine didn't restore from a stale backup that predates these files (mu-plugins are outside the normal backup scope — they persist unless the entire filesystem is rolled back).

## Verification commands

```bash
# Bing meta tag
curl -s 'https://www.impressionoriginale.com/' | grep -c 'msvalidate'

# IndexNow key file
curl -s 'https://www.impressionoriginale.com/1479359dbca34ecabae3e080d1b96001.txt'

# IndexNow API test
curl -s -w '\nHTTP %{http_code}' -X POST 'https://api.indexnow.org/indexnow' \
  -H 'Content-Type: application/json; charset=utf-8' \
  -d '{"host":"www.impressionoriginale.com","key":"1479359dbca34ecabae3e080d1b96001","keyLocation":"https://www.impressionoriginale.com/1479359dbca34ecabae3e080d1b96001.txt","urlList":["https://www.impressionoriginale.com/"]}'
```
