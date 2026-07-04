# Obsolete Universal Analytics tag still firing — remove it (GA4 already live)

## Summary

The site still loads the obsolete Universal Analytics tag `UA-85910237-1` (UA retired 2023-07-01; the property is deleted). **But GA4 is already collecting** — the GA4 property `Impression Originale - GA4` (`375621420`, Measurement ID `G-Y88VQHFDBV`) shows live traffic and revenue.

GA4 fires **inside the GTM container `GTM-MT7G7Z3C`**, injected by GTM's JavaScript at runtime — so it does **not** appear in a static HTML fetch (which is why an external `curl` saw only UA). This is **not** a migration. GA4 works. The task is **cleanup**: stop the dead UA tag, and optionally confirm GA4 ecommerce tracking is complete.

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

The UA property is deleted, so there's nothing in Google to find. `UA-85910237-1` is emitted by:

- **GTM4WP** — Settings → Google Tag Manager → **Google Analytics** tab (enqueues `gtag/js?id=UA-85910237-1`, script handle `google-tag-manager-js`).
- **PixelYourSite** — also pushes a UA gtag/config.
- Confirm every copy with **Better Search Replace** (dry run) searching `UA-85910237-1`, or WP-CLI `wp search-replace 'UA-85910237-1' 'UA-85910237-1' --dry-run --all-tables`.

## Proposed path

1. In **GTM4WP** → Google Analytics settings, clear/remove `UA-85910237-1`.
2. In **PixelYourSite**, remove the UA reference.
3. **Dry-run search** to confirm no `UA-85910237-1` remains anywhere.
4. Confirm the GA4 tag inside GTM container `GTM-MT7G7Z3C` remains intact (`G-Y88VQHFDBV`) and still fires.
5. *(Optional)* Audit GA4 ecommerce events for completeness.

## Acceptance (done-when)

- [ ] Front end no longer loads `gtag/js?id=UA-85910237-1` (verify via View Source / external fetch).
- [ ] GA4 Realtime for `G-Y88VQHFDBV` still registers hits (accept the cookie banner first) — unaffected by the UA removal.
- [ ] Google Tag Assistant shows the GA4 tag firing, and **no** UA tag.

## Notes

- IDs (`G-Y88VQHFDBV`, `GT-5TPLSSZ`, account `85910237`, property `375621420`) are public identifiers, not secrets.
- Independent of the hreflang / plugin-update workstream.
