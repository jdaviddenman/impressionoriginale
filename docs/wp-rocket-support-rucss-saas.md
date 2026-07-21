# WP Rocket Support — RUCSS SaaS Returning Empty CSS

**Date:** 2026-07-21
**License:** Multi 500
**WP Rocket Version:** 3.23
**Site:** https://www.impressionoriginale.com/

## Problem

RUCSS (Remove Unused CSS) stopped generating used CSS around Jul 20. The `wpr-usedcss` inline block is absent from page output. 35 CSS files load synchronously (render-blocking).

## Evidence

### RUCSS table — all entries have empty CSS
```sql
SELECT COUNT(*) FROM wp_wpr_rucss_used_css WHERE CHAR_LENGTH(css) = 0;
-- Result: ALL entries (142+) have len=0

SELECT url, CHAR_LENGTH(css) as len, hash, modified FROM wp_wpr_rucss_used_css LIMIT 5;
-- All show: len=0, hash populated but css column empty
```

### Page output — no wpr-usedcss block
```
curl -s https://www.impressionoriginale.com/ | grep -c wpr-usedcss
-- Result: 0
```

### CSS loading — 35 sync stylesheets
All CSS loads with `media='all'` (render-blocking). No async CSS.

### WP Rocket settings (verified current)
```
remove_unused_css: 1
optimize_css_delivery: 1
async_css: 1
minify_css: 0
defer_all_js: 1
remove_unused_css_safelist: [eut-title, eut-description, eut-btn, eut-fade-in-right, eut-feature-content, eut-slider-item]
```

### What we tried
1. **Clear Used CSS** from wp-admin → cleared 1,506 empty entries
2. **Clear all caches** → SaaS reprocessed, same empty results
3. **Enabled/disabled RUCSS** multiple times → same
4. **Ran SaaS cron jobs** (`rocket_saas_on_submit_jobs`, `rocket_saas_pending_jobs`) → entries created, all empty
5. **Verified SaaS accessibility** → `saas.wp-rocket.me` reachable, site loads in <1s, no Cloudflare block
6. **Verified license** → Multi 500, consumer key `343a2e8d`, email `contact@apresta.fr`
7. **Checked Action Scheduler** → 0 pending, 0 failed — no stuck jobs

### Root cause hypothesis
The SaaS API returns a hash (hash column IS populated for most entries) but the CSS content is empty. Either:
- SaaS returns `{hash: "abc123...", css: ""}` (empty CSS)
- SaaS returns hash but CSS download step fails
- Some server-side processing issue specific to this site

### Timeline
- **Jul 19:** RUCSS working — LCP 15.9s (pagespeed.dev), CSS async, 0ms render-blocking
- **Jul 20 AM:** Memcached purge (`WpeCommon::purge_memcached()`) cleared SaaS status transient
- **Jul 20 PM:** RUCSS cron ran overnight, overwrote all 1,506 entries with empty CSS
- **Jul 21:** SaaS re-validated (visited wp-admin settings), new entries created, all still empty

### No stuck jobs
```sql
SELECT COUNT(*) FROM wp_actionscheduler_actions WHERE hook LIKE '%rucss%' AND status = 'pending';
-- Result: 0

SELECT COUNT(*) FROM wp_actionscheduler_actions WHERE hook LIKE '%rucss%' AND status = 'failed';
-- Result: 0
```

### Disk cache
105 `.css.gz` files exist in `/wp-content/cache/used-css/` (25-47KB each) but hashes don't match current DB entries. These appear to be from a previous generation cycle.

## Request
Please investigate why the SaaS API returns empty CSS for all URLs on this site. The SaaS connection is validated, license is active, site is accessible, and all WP Rocket settings are correct. The feature was working on Jul 19 and broke after a memcached purge on Jul 20.
