# ADR 0006 — Shop page noindex: intentional or accidental?

- **Status:** Proposed (operator decision pending)
- **Date:** 2026-07-15
- **Supersedes:** the audit finding that flagged `/shop/` and `/fr/shop/` noindex as a defect requiring a fix.

## Context

A Tier 1 SEO audit flagged the primary product listing pages — `/shop/` (EN) and
`/fr/shop/` (FR) — as blocked from Google with `noindex,follow`. The shop page
is the store's main product archive; if noindex is accidental, it blocks the
single highest-value product listing from search results.

Research against the live pages (2026-07-15) found:

**Both EN and FR render `<meta name='robots' content='noindex, follow' />`.**

**Root cause — individual page-level setting, not global:**
- Global Yoast setting: `noindex-ptarchive-product = false` — the product post
  type archive is NOT globally noindexed.
- Per-page setting (post ID 9817, the EN Shop page in wp-admin):
  `_yoast_wpseo_meta-robots-noindex = 1` — explicitly set at the individual page
  level on the Shop page edit screen.
- FR shop page has no separate post in `icl_translations` — it renders via
  WooCommerce + WCML from the same Shop endpoint, not a WPML duplicate.

**Interpretation:** Someone went to Pages -> Shop -> Yoast advanced settings and
toggled "Allow search engines to show this Page in search results?" to "No."
This is a deliberate action on a specific page, not a default or inherited
setting.

**What the review phase found:**
1. Using `$sitepress->make_duplicate()` to create a FR shop page is
   architecturally wrong — the WooCommerce shop endpoint renders its archive
   without needing a translated page, and creating a duplicate would conflict
   with WC's routing.
2. The FR shop page renders `<meta property="og:url" content="https://www.impressionoriginale.com/shop/"/>`
   (the EN URL), not `/fr/shop/`. This is a separate og:url misconfiguration,
   not caused by the noindex setting.

**What the refute phase found:**
1. Cloudflare blocks `curl` verification of the shop page from external hosts.
   Origin-side verification (`wp_remote_get()` or SSH-based fetch) is required
   to confirm any change.
2. Yoast maintains a separate `wp_yoast_indexable` row for the product archive
   (`object_type='post-type-archive'`, `object_sub_type='product'`). Flipping
   the page-level noindex on post 9817 may not update this archive-level
   indexable — both must be checked after any change.
3. The FR shop page `<h1>` is "Shop" (English), not "Boutique" (French). This
   is a separate localization issue — the FR shop title was never translated.
4. Any `make_duplicate()` approach would fail inside `wp eval` context due to
   WPML's bootstrap dependencies.
5. The og:url bug on the FR page is a separate defect — fix it independently,
   don't bundle it with the noindex question.

## Decision

**Deferred to operator.** The noindex is set at the individual page level — it
was placed there deliberately through the wp-admin UI. Two paths:

**Path A — noindex is intentional (keep as-is):**
- The shop page was intentionally blocked, most likely to avoid duplicate
  content with product category pages (`/categorie-produit/...`), which serve
  the same product listings segmented by category.
- No change. Document the rationale and close.

**Path B — noindex is accidental (flip to index):**
- Flip the page-level setting on post 9817: set
  `_yoast_wpseo_meta-robots-noindex` to `2` (or delete the row — Yoast treats
  absent as default/index).
- Verify the `wp_yoast_indexable` row for the product post-type-archive also
  updates (check `is_robots_noindex`).
- Add SEO titles and meta descriptions for both EN and FR shop pages.
- Fix the FR shop `<h1>` from "Shop" to "Boutique."
- Fix the FR og:url pointing to the EN URL (separate defect — file a distinct
  issue).

**Critical architectural question for the operator:** Should product category
pages OR the shop page be the primary product listing in search results?
Both cannot rank for the same queries without a careful canonical structure. If
category pages are the intended SEO landing pages, Path A is correct. If the
shop page should rank, Path B is needed and category canonicalization must be
reviewed.

## Consequences

- **No code changes until the operator decides.** The noindex is a configuration
  choice, not a technical defect — flipping it without confirming intent risks
  creating a duplicate-content problem with category pages.
- If Path B (flip to index): the fix on post 9817 is a single wp-admin toggle
  (~30 seconds), but the archive-level indexable and FR localization must be
  verified separately.
- Cloudflare blocks external verification of `/shop/` — any post-change
  confirmation must use origin-side fetch (SSH + `wp_remote_get()` or
  `wp eval`), not `curl`.
- The FR og:url bug and FR H1 "Shop" localization gap are separate defects
  tracked independently of this ADR.
- The `wp_yoast_indexable` dual-row risk (page-level vs archive-level) is now
  recorded — future Yoast noindex changes on the shop page must verify both
  rows.
