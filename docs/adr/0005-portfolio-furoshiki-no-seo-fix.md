# ADR 0005 — Portfolio Furoshiki: noindex is correct until operator decides whether portfolio pages are SEO landing pages

- **Status:** Proposed (operator decision pending)
- **Date:** 2026-07-15
- **Supersedes:** the audit finding that flagged `/portfolio/furoshiki/` as a "missing meta + H1 + og:description" defect requiring a fix.

## Context

A Tier 1 SEO audit flagged `/portfolio/furoshiki/` (post ID 9515 EN, 9641 FR) as
missing meta description, og:description, and H1, proposing to enable Yoast on
the portfolio post type as the fix.

Research against the live pages (2026-07-15) found:

**EN page (ID 9515):**
- No `<h1>` element in HTML. Title is `Furoshiki | Impression Originale` (Yoast
  auto-generated from the post-type title template — not manually set).
- No meta description tag. No og:description.
- Yoast metabox is **disabled** for the portfolio post type
  (`display-metabox-pt-portfolio` not in the `wpseo_titles` option).
- `noindex-portfolio: true` is set in Yoast — portfolio pages are intentionally
  excluded from search indexing.

**FR page (ID 9641):**
- **Has** `<h1>Furoshiki</h1>` — the EN page lacks one. The original audit
  premise "no H1 on either" is partially wrong.
- Same noindex / no meta description / no og:description as EN.
- **Has** social metadata tags (og:image, og:locale, twitter:card) that EN does
  not — a half-configured state suggesting an incomplete prior attempt.

**What portfolio pages actually are:**
- Navigation shells that list links to product pages in the same category. They
  serve a UX purpose (browsing by wrapping style) but are not designed as SEO
  landing pages — the product pages they link to are the SEO targets.

**What the review and refute phases found:**
1. `wp option get --format=json` does not exist for Yoast options — the
   `wpseo_titles` option is a PHP serialized array, not JSON. Any automated
   read/write of Yoast settings must use `wp option pluck` or direct PHP
   unserialization.
2. FR page has `<h1>Furoshiki</h1>` while EN lacks one — the original premise
   that "both pages have no H1" is wrong.
3. The half-configured state (FR has social metadata tags, EN doesn't) suggests
   someone started configuring portfolio pages for SEO and stopped partway
   through, or that FR was configured independently of EN.
4. The operator was **never asked** whether portfolio pages should be SEO
   landing pages at all. The audit assumed "no H1 + no meta" is a defect, but if
   the pages are navigation shells with intentional noindex, that state is
   correct.

## Decision

**Deferred to operator.** Two paths, both valid depending on intent:

**Path A — Portfolio pages are navigation shells (keep as-is):**
- `noindex` is correct. No fix needed. Close as "by design."
- Optionally clean up the half-configured FR social tags for consistency (low
  priority — the page is noindex so the inconsistency has no SEO impact).

**Path B — Portfolio pages should be SEO landing pages:**
- Enable the Yoast metabox on the portfolio post type
  (`wp option pluck wpseo_titles display-metabox-pt-portfolio` → set to `portfolio`).
- Set `noindex-portfolio: false` in Yoast titles settings.
- Add an H1 to the EN page (FR already has one).
- Write SEO titles and meta descriptions for both EN and FR pages.
- Existing portfolio content (navigation links to products) may need enrichment
  to be useful as a landing page — a page that is only links has nothing to rank
  on.

Until the operator decides, **no changes should be made to the portfolio post
type's Yoast configuration or to the furoshiki pages.**

## Consequences

- The original audit finding ("missing meta + H1") is **not actionable** without
  the operator's decision on portfolio page purpose.
- If Path A (navigation shells): noindex is already set, no work required.
  Optionally remove FR social tags for config consistency.
- If Path B (SEO landing pages): the fix is ~30 minutes of wp-admin
  configuration + content writing, but the portfolio pages may need content
  enrichment beyond the current navigation-link structure to justify indexing.
- The `wp option get --format=json` footgun is now recorded — future Yoast
  setting reads/writes must use serialized-PHP-aware methods.
- The half-configured FR state (social tags present, EN absent) is a loose end
  regardless of path — fix it for consistency or document why the asymmetry is
  intentional.
