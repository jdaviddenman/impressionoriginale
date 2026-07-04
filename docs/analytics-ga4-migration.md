# Universal Analytics still active — migrate to GA4

## Summary

The site loads `gtag/js?id=UA-85910237-1` — a **Universal Analytics** property. Google retired UA on **2023-07-01**; it processes no data.

A **GA4 property already exists** — Measurement ID **`G-Y88VQHFDBV`** (property "Impression Originale - GA4"), with Google Tag **`GT-5TPLSSZ`**. But neither appears in the site's front-end tags: the site still loads **only** the dead UA tag, so GA4 receives nothing from page-level tags. A Google Tag Manager container (`GTM-MT7G7Z3C`) is also present. The task is therefore **not** to create GA4 — it's to **route the site to `G-Y88VQHFDBV` and remove UA**.

## Why it's a problem

- If UA was the only analytics path, the site has collected **no analytics data for ~3 years** (July 2023 → now).
- Every other fix in this audit (title/meta rewrites, hreflang) needs GA4 to **measure** its impact. Without analytics, we're improving blind.

## Evidence

```
loaded gtag:  https://www.googletagmanager.com/gtag/js?id=UA-85910237-1   ← Universal Analytics (dead since 2023-07-01)
GTM-MT7G7Z3C  ← Tag Manager container present
G-Y88VQHFDBV  → 0 occurrences in front-end HTML   (GA4 property exists, but site doesn't send to it)
GT-5TPLSSZ    → 0 occurrences in front-end HTML   (Google Tag not installed on site)
```

The `g-*` strings elsewhere on the page are CSS classes / designer names — **not** GA4 IDs.

## Caveat to check first

A GA4 tag could already be firing **inside** the GTM container `GTM-MT7G7Z3C`, which is invisible from the page source. Before assuming a 3-year gap, open the container at tagmanager.google.com and check for a GA4 tag. If GA4 is already live via GTM, the remaining task is just removing the dead UA tag.

## Complication: multiple tag sources

Analytics can be injected from any of **four** places on this site. Fixing one while another still holds UA — or adding GA4 in two of them — causes UA persistence or GA4 double-counting. Consolidate to **one** GA4 path.

- **GTM4WP** (Settings → GTM4WP → Google Analytics; has a legacy Universal Analytics field)
- **Google Analytics for WooCommerce** plugin (v2.1.23; historically supported UA)
- **PixelYourSite** (GA settings)
- **GTM container** `GTM-MT7G7Z3C` (may hold a UA and/or GA4 tag)

## Proposed path (admin — needs GA account + wp-admin)

1. **GA4 property already exists — use `G-Y88VQHFDBV`.** No setup-assistant/create step needed. (First confirm the property is receiving data in GA4 → Admin → Data Streams.)
2. **Find every `UA-85910237-1` reference** across the four sources above.
3. **Pick ONE canonical GA4 path** and put the ID there — recommended: the *Google Analytics for WooCommerce* plugin (enter `G-Y88VQHFDBV`; it handles GA4 ecommerce events) **or** a GA4 config tag inside the GTM container `GTM-MT7G7Z3C`, not both. (Alternatively, load the unified Google Tag `GT-5TPLSSZ`, which routes to `G-Y88VQHFDBV`.)
4. **Remove/disable the UA tag `UA-85910237-1` everywhere else** — GTM4WP, PixelYourSite, GTM container, and any other injector — to prevent both UA persistence and GA4 double-counting.
5. **Verify** (see acceptance).

## Acceptance (done-when)

- [ ] Page source loads `gtag/js?id=G-Y88VQHFDBV` (or `GT-5TPLSSZ`), and **no** `UA-85910237-1` tag remains.
- [ ] Google Tag Assistant shows exactly **one** GA4 tag firing (`G-Y88VQHFDBV`) — no UA, no duplicate GA4.
- [ ] GA4 **Realtime** for `G-Y88VQHFDBV` registers a test visit **and** an add-to-cart / purchase event.

## Notes

- `UA-85910237-1` and `GTM-MT7G7Z3C` are already public in the site source — not sensitive.
- Independent of the hreflang / plugin-update workstream; can proceed in parallel.
- Where feasible, trial the plugin-side change on the clone first; the account/GA4-property step is Google-side and applies to live.
