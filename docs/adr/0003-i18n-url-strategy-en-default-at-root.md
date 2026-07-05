# ADR 0003 — i18n URL strategy: EN-default-at-root is by design (no `/en/` is not a defect)

- **Status:** Accepted
- **Date:** 2026-07-05
- **Related:** Issue #1 (hreflang not-a-defect); CLAUDE.md RULE 5, RULE 6.

## Context

WPML runs in **directory mode with EN as the default language**. The default
language is served at the **root with no path prefix**; FR is served under
`/fr/`. Consequence: **there is no `/en/` URL tree** — English *is* the bare
path. A recurring audit reflex flags "many pages have `/fr/` but no `/en/`" as
an internationalization defect. It is not; it is standard WPML behavior, the
same class of false positive as the hreflang-in-head miss (Issue #1).

Verified live and adversarially (fresh-context refute critic, RULE 6),
2026-07-05:

- `/` → 200, `<html lang="en-US">`, self-canonical.
- `/fr/` → 200, `<html lang="fr-FR">`, self-canonical.
- `/en/` → 301 (single hop) → `/e-shop/ink-gift-wrap/`.
- hreflang lives in the **XML sitemaps**, not the head (WPML SEO 2.2.2+ — same
  design as Issue #1). Empty head hreflang is expected, not missing.
- Per-language self-canonicals; no cross-language canonical bleed; FR fully
  indexable (no `noindex` found).

**Counting gotcha — corrects a live error made during this analysis.** The
Yoast/WPML sitemaps list **each language version as a separate `<url>` block**:
a bilingual product appears **twice** (an EN `<loc>` and an FR `<loc>`), each
carrying the full hreflang set. So `<url>`-entry counts are **~2× the distinct
product count**. `product-sitemap.xml` has **819 `<url>` entries ≈ 418 distinct
products** (~402 bilingual + 12 FR-only + 4 EN-only), **not 819 products**. Any
"819 products / 803 bilingual" reading is a category error — treating
URL-entries as products and double-counting every bilingual item.

## Decision

Record as a **standing fact**: the absence of `/en/` URLs is **correct WPML
directory-mode behavior, not a defect**. Do **not** re-audit or re-escalate it.
Empty head hreflang is likewise expected (sitemap-based).

When counting products from a sitemap, **dedupe by translation group** — never
treat `<url>` entries as a product count.

## Consequences

- Future sessions stop re-flagging "missing `/en/`" and "empty head hreflang."
  This ADR plus Issue #1 are the source of truth; where a fresh audit disagrees,
  reproduce against the live sitemap before escalating (RULE 5).
- Genuine, **low-priority** gaps found during verification stay open for optional
  tracking (none store-breaking):
  1. **`/fr/shop/` hreflang alternate is broken** — the EN `/shop/` archive lists
     an FR alternate `/fr/shop/` that is non-reciprocal (not a `<loc>` in the
     sitemap) and 301-chains
     (`/fr/shop/ → /fr/?page_id=10664 → /fr/produit/ciseaux-8-noir/`) to an
     unrelated product.
  2. **12 FR-only products carry no `x-default`** — "x-default → EN" is not
     universal; those 12 give Google no default fallback.
  3. **16 single-language items** — 12 FR-only + 4 EN-only (translation gaps),
     all live and indexable.
  4. **`/en/` stray 301 → `/e-shop/ink-gift-wrap/`** — junk target but harmless
     (single-hop 301 folds; `/en/` is not indexed as a duplicate).
- **Not yet machine-verified:** hreflang reciprocity of `post-`, `product_cat-`,
  `product_brand-`, `designer-` sitemaps (only `page-` and `product-` were
  checked). Do not claim those clean without a check.
