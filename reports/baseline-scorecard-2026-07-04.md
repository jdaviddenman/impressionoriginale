# On-Page SEO Baseline Scorecard — 2026-07-04

Frozen day-zero snapshot of on-page/technical SEO quality, measured externally from the live site (server HTML). Re-run `harness/fingerprint.sh` + this rubric after each batch and diff against these numbers. This is **Tier 1** (leading indicator — proves the work landed). Tier 2 (Search Console) and Tier 3 (GA4 organic) are the outcome metrics, tracked in `tracking.md`.

**Intervention marker:** optimization work began 2026-07-04. `/wrap/` (Gift Wrap) was the first category optimized before this snapshot — included here as the "after" reference; every other page is still at baseline.

## Raw measurements

| Page | HTTP | Title len | Meta len | hreflang | H1 # | og:image | Cat-desc words |
|------|------|-----------|----------|----------|------|----------|----------------|
| EN home | 200 | 73 | 150 | **0** | 1 | **N** | — |
| FR home | 200 | 81 ⚠ | 177 ⚠ | **0** | 1 | **N** | — |
| Cat: Gift Wrap *(optimized)* | 200 | 65 | 142 | **0** | 2 | **N** | **47** ✅ |
| Cat: Ribbons | 200 | 62 | 192 ⚠ | **0** | 2 | **N** | 26 ⚠ |
| Cat: Bows | 200 | 68 ⚠ | 173 ⚠ | **0** | 2 | **N** | 17 ⚠ |
| Cat: Gift Bags | 200 | 58 | 157 | **0** | 2 | **N** | 29 ⚠ |
| Cat: Gift Tags | 200 | 58 | 177 ⚠ | **0** | 2 | **N** | 25 ⚠ |
| Cat: Christmas | 200 | 67 ⚠ | 151 | **0** | 1 | **N** | 25 ⚠ |
| Page: Collection | 200 | **33** ⚠ | 142 | **0** | 1 | **N** | — |
| Product (FR) | 200 | 40 | 191 ⚠ | **0** | 1 | **Y** | — |

Targets: Title ≤ ~60 · Meta ≤ 156 · hreflang ≥ 2 · H1 = 1 · og:image = Y · Cat-desc ≥ 30 words.

## Scoring rubric (per page, applicable checks only)

Each page scored on the checks that apply to it: `HTTP 200`, `Title ≤60`, `Meta ≤156`, `hreflang ≥2`, `H1 = 1`, `og:image present`, `Cat-desc ≥30w` (categories only).

| Page | Passed / applicable | Notes |
|------|--------------------|-------|
| EN home | 4/6 | fails hreflang, og:image |
| FR home | 2/6 | fails title-len, meta-len, hreflang, og:image |
| Gift Wrap *(optimized)* | 5/7 | fails hreflang, og:image (content + title + meta all pass) |
| Ribbons | 3/7 | fails meta-len, hreflang, og:image, desc-len |
| Bows | 2/7 | fails title-len, meta-len, hreflang, og:image, desc-len |
| Gift Bags | 4/7 | fails hreflang, og:image, desc-len |
| Gift Tags | 3/7 | fails meta-len, hreflang, og:image, desc-len |
| Christmas | 4/7 | fails title-len, hreflang, og:image |
| Collection | 4/6 | fails hreflang, og:image (title too short at 33) |
| Product (FR) | 4/6 | fails meta-len, hreflang |

**Baseline aggregate: 35 / 65 applicable checks pass (54%).**

> **Correction (2026-07-04):** item 1 below ("hreflang = 0") was a **measurement error** — it checked the page `<head>`, but WPML SEO 2.2.2+ emits hreflang in the **XML sitemap** (valid, present). hreflang is **not** a gap. See `docs/hreflang-fix.md` and the corrected aggregates in `scorecard-2026-07-04b.md`. Item 1 struck through below.

## The dominant defects (site-wide, systemic)

1. ~~**hreflang = 0 on every page**~~ — **struck: measurement error, not a defect** (hreflang is in the sitemap; see correction above).
2. **og:image missing on every page but the product** — quick Yoast default-image fix; flips 9/10.
3. **Thin category descriptions** — 5 of 6 unoptimized categories under 30 words (Bows 17, Gift Tags 25, Christmas 25, Ribbons 26, Gift Bags 29). The per-category work already applied to Gift Wrap (47) fixes these one at a time.
4. **Over-long metas** (>156, truncated in SERP): FR home 177, Ribbons 192, Bows 173, Gift Tags 177, Product 191.
5. **Long/brand-first titles**: FR home 81, plus the category rewrites in progress. Collection is the opposite problem — 33 chars, under-using the space.

## How to re-measure

Re-run the same page set after each batch; update a dated copy of this table (`scorecard-YYYY-MM-DD.md`) and compute the new aggregate. Rising aggregate = on-page quality improving. Pair with the Tier 2/3 outcome data in `tracking.md` to connect quality → visibility → conversions.
