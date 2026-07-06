# Obsolete Universal Analytics tag still firing — remove it (GA4 already live)

## Summary

The site still loads the obsolete Universal Analytics tag `UA-85910237-1` (UA retired 2023-07-01; the property is deleted). **But GA4 is already collecting** — the GA4 property `Impression Originale - GA4` (`375621420`, Measurement ID `G-Y88VQHFDBV`) shows live traffic and revenue.

GA4 fires **inside the GTM container `GTM-MT7G7Z3C`**, injected by GTM's JavaScript at runtime — so it does **not** appear in a static HTML fetch (which is why an external `curl` saw only UA). This is **not** a migration. GA4 works. The task is **cleanup**: stop the dead UA tag, and optionally confirm GA4 ecommerce tracking is complete.

## Status — 2026-07-06 (UA removed) · 2026-07-04 (GA4 consolidated)

**UA removal — DONE (2026-07-06).** The obsolete `UA-85910237-1` no longer loads on the front end. Removed via mu-plugin `io-remove-ua.php` (`remove_action('wp_head','io_analytics',20)`), not a source edit — reversible by deleting the file. External fetch (normal + cache-buster): UA **2 → 0**; `GTM-MT7G7Z3C` unchanged (1); `GT-5TPLSSZ` unchanged. See `live-code/mu-plugins/README.md`.

> **Correction (2026-07-06):** the section below said the UA tag was "hardcoded in the **theme** PHP." **Wrong.** It was emitted by `io_analytics()` in the bespoke plugin `wp-content/plugins/impression_originale/impression_originale.php`, hooked `add_action('wp_head','io_analytics',20)`. Better Search Replace's "0 DB rows" was right (it's in a PHP file, not the DB) — but the *theme* inference was not. Located by grepping live wp-content over SSH.

> **Aside (2026-07-06, not UA):** the 2026-07-04 note below claims `GT-5TPLSSZ` was driven `2 → 0`. As of 2026-07-06 it is back to **2** in the static fetch (pre-existing, unrelated to the UA removal). Whether that is a re-introduced duplicate is a separate GA4 question — not tracked here.

**GA4 consolidated to a single, healthy path — done (2026-07-04).** During cleanup the WooCommerce/PixelYourSite/GTM4WP plugin tags were briefly pointed at GA4 (`GT-5TPLSSZ`), which **double-fired**; those were removed. GA4 now fires via the GTM container `GTM-MT7G7Z3C` → `G-Y88VQHFDBV`, and **GA4 Realtime confirms live collection** (active users while browsing). At the time, external fetch showed `GT-5TPLSSZ` **2 → 0** (but see the 2026-07-06 aside above). The Google-tag "install `GT-5TPLSSZ`" wizard was correctly skipped — it doesn't see GA4-via-GTM, and installing would have re-created the duplicate.

## Evidence

```
GA4 Reports (property 375621420 = G-Y88VQHFDBV), ~June 2026:
  Active users 1.9K · New users 1.8K · Avg engagement 16s · Revenue €432.96   → GA4 IS collecting

Static homepage fetch:
  gtag/js?id=UA-85910237-1   ← obsolete UA tag still loading (via GTM4WP + PixelYourSite)
  G-Y88VQHFDBV / GT-5TPLSSZ  → 0 occurrences (GA4 fires via GTM at runtime, invisible to curl)
```

An earlier Realtime **0** was the **Termly consent banner** (Google Consent Mode gates `analytics_storage` until consent) plus the static-HTML check — not a data gap. The Reports data supersedes it: GA4 is live.

## Why it still matters (low severity)

- **No data loss** — GA4 is fine. The dead UA tag is wasted overhead + legacy cruft that clutters the tag setup and makes future changes error-prone.
- While in there, worth confirming GA4 ecommerce events (add_to_cart, begin_checkout, purchase) are complete — revenue already tracks (€432.96 shown).

## Where the obsolete UA tag lives (in WordPress, not Google)

The UA property is deleted, so there's nothing in Google to find. A Better Search Replace dry-run for `UA-85910237-1` across 158 tables returned **0 database rows** — correct, because it is emitted from **PHP, not the DB**. **Located 2026-07-06 (SSH grep of live wp-content):** the bespoke plugin `wp-content/plugins/impression_originale/impression_originale.php`, function `io_analytics()` (lines ~535–549), hooked:

```php
add_action('wp_head','io_analytics', 20);
function io_analytics() { ?>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-85910237-1"></script>
...
<?php }
```

`io_analytics()` outputs **only** the dead UA block, so unhooking the whole action removes exactly the UA tag. The earlier "hardcoded in the **theme**" claim was wrong (see the 2026-07-06 correction above). Removed via mu-plugin `io-remove-ua.php` — `remove_action('wp_head','io_analytics',20)` — rather than editing the plugin source.

## Proposed path

1. In **GTM4WP** → Google Analytics settings, clear/remove `UA-85910237-1`.
2. In **PixelYourSite**, remove the UA reference.
3. **Dry-run search** to confirm no `UA-85910237-1` remains anywhere.
4. Confirm the GA4 tag inside GTM container `GTM-MT7G7Z3C` remains intact (`G-Y88VQHFDBV`) and still fires.
5. *(Optional)* Audit GA4 ecommerce events for completeness.

## Acceptance (done-when)

- [x] GA4 collecting via a **single** path (GTM → `G-Y88VQHFDBV`); no double-count (`GT-5TPLSSZ` 2 → 0). — **done**
- [x] GA4 Realtime for `G-Y88VQHFDBV` registers hits (active users confirmed while browsing).
- [x] Front end no longer loads `gtag/js?id=UA-85910237-1` — **done 2026-07-06** (mu-plugin `io-remove-ua.php`; external fetch UA 2 → 0).

## Notes

- IDs (`G-Y88VQHFDBV`, `GT-5TPLSSZ`, account `85910237`, property `375621420`) are public identifiers, not secrets.
- Independent of the hreflang / plugin-update workstream.
- **Do NOT re-run the GA4 Setup Assistant** ([support.google.com/analytics/answer/9744165](https://support.google.com/analytics/answer/9744165)) to "create" GA4 — the property already exists and collects data. Re-running risks a **duplicate** property or enabling the deprecated **"connected site tags"** mode. The UA property is deleted, so there are no UA settings left to migrate either.
