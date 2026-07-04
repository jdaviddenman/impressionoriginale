# hreflang tags missing site-wide on bilingual EN/FR store

## Summary

The site runs in two languages — English (`/`) and French (`/fr/`) via WPML — but emits **zero `rel="alternate" hreflang"` tags** on every page tested, in **both** languages. Search engines therefore cannot connect the EN and FR versions of a page.

## Why it's a problem

Without hreflang on a multilingual site:

- Google may serve the **wrong-language** page to a searcher (French page to an English query, or vice-versa).
- The EN and FR versions can be read as **near-duplicates competing** with each other instead of as one page in two languages — diluting ranking signals.
- Language/region targeting is left to guesswork, which for a France-made brand selling internationally directly undercuts the goal of "getting found."

This is the single highest-impact technical SEO defect on the site.

## Evidence

Fetched the live pages and counted `hreflang` occurrences in the rendered `<head>`:

| Page | HTTP | Yoast head present | `hreflang` count |
|------|------|--------------------|------------------|
| `/` (EN home) | 200 | yes | **0** |
| `/wrap/` (category) | 200 | yes | **0** |
| `/fr/` (FR home) | 200 | yes | **0** |

Confirmed independently in-browser via **View Source → find "hreflang"** → 0 matches. A correct setup would render, per page, one `<link rel="alternate" hreflang="…">` for `en`, one for `fr`, and one `x-default`, each reciprocal across the language twins.

## What has been ruled out

- **Language config** — WPML → Languages is correct: codes `en`/`fr`, locales `en_US`/`fr_FR`, hreflang codes set.
- **Translation linking** — EN/FR twins exist and are linked (green pencil in the WPML box); both published.
- **Caching** — cleared WP Rocket + host cache; re-checked; still 0.
- **Glue plugin present** — the WPML ↔ Yoast integration plugin ("WPML SEO") **is** installed and active.

So this is **not** a config, linking, publish, or missing-plugin problem. It points to a version/compatibility gap in the WPML ↔ Yoast integration: Yoast renders its head via a presenter pipeline, and the WPML integration injects the hreflang presenter into that pipeline. If the integration/WPML core lags the installed Yoast, the hreflang presenter silently drops — Yoast's own tags still render (they do), but hreflang does not.

## Proposed path

Validated on the clone first, then applied to live:

1. **Back up** (done for the working copy) and work on an **isolated clone** matched to live (PHP 8.2 / WP 7.0 / WC 10.7).
2. **Update the WPML family together** (WPML core, String Translation, Media Translation, WooCommerce Multilingual, WPML SEO) to current, then **Yoast SEO** to current. These are designed to be compatible at their latest versions; the fix is bringing WPML core + Yoast up to meet the integration plugin.
3. **Clear caches** (WP Rocket + host).
4. **Verify** with the harness + View Source (see acceptance criteria).
5. If hreflang is still absent after the stack is current → open a **WPML support ticket** (they can read server-side config), attaching WPML → Support debug info. Do **not** hand-code hreflang while WPML is active — duplicate/conflicting tags are worse than none.

## Acceptance criteria (done-when)

- [ ] `harness/fingerprint.sh` reports **`hreflang_count ≥ 2`** on `/`, `/fr/`, and at least one category + one product page (currently 0).
- [ ] In-browser **View Source** on an EN page shows reciprocal `en` / `fr` / `x-default` alternates, and the FR twin shows the same three.
- [ ] Search Console → **URL Inspection** on an EN/FR twin reports no "alternate page / language" errors.
- [ ] No regressions in the same harness run (no new PHP errors, shortcode leakage, or encoding breakage; all sampled pages still HTTP 200).

## Notes for the admin

- Update on a **staging/clone first**; this stack has an old theme + page builder, so validate layouts before touching live.
- After live update, clear **both** WP Rocket **and** WP Engine caches, or the re-check will read a stale copy.
