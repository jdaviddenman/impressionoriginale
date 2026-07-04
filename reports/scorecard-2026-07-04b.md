# On-Page SEO Scorecard — 2026-07-04 (checkpoint after EN core categories)

Re-run of the Tier-1 harness across the same representative page set as the baseline, after optimizing 7 EN category pages + the site-wide og:image flip.

**Methodology note:** the original baseline (`baseline-scorecard-2026-07-04.md`) hand-scored 35/65, but that scoring had minor internal inconsistencies (e.g. title-length applied unevenly). Both snapshots are **recomputed here under one consistent rubric** so the delta is apples-to-apples. This supersedes the baseline's 35/65 figure for comparison purposes.

**Rubric (per page, applicable checks):** HTTP=200 · Title ≤65 · Meta ≤156 · hreflang ≥2 · og:image=Y · H1=1 · Cat-desc ≥30w (categories only).

## Aggregate

| Snapshot | Score | % |
|----------|-------|---|
| Baseline 2026-07-04 (recomputed) | 27/65 | 42% |
| **Checkpoint 2026-07-04b** | **43/65** | **66%** |
| **Delta** | **+16** | **+24pp** |

## Per-page (recomputed baseline → now)

| Page | HTTP | Title | Meta | hreflang | og | H1 | desc | Base | Now |
|------|------|-------|------|----------|----|----|------|------|-----|
| EN home | 200 | 73 | 150 | **0** | Y | 1 | — | 3/6 | 4/6 |
| FR home | 200 | 81 | 177 | **0** | Y | 1 | — | 2/6 | 3/6 |
| Gift Wrap | 200 | 65 | 142 | **0** | Y | 2 | 47 | 4/7 | 5/7 |
| Ribbons | 200 | 70 | 146 | **0** | Y | 2 | 45 | 2/7 | 4/7 |
| Bows | 200 | 61 | 140 | **0** | Y | 2 | 43 | 1/7 | 5/7 |
| Gift Bags | 200 | 69 | 140 | **0** | Y | 2 | 39 | 2/7 | 4/7 |
| Gift Tags | 200 | 62 | 138 | **0** | Y | 2 | 33 | 2/7 | 5/7 |
| Christmas | 200 | 67 | 151 | **0** | Y | 1 | 25 | 3/7 | 4/7 |
| Collection | 200 | 33 | 142 | **0** | Y | 1 | — | 4/6 | 5/6 |
| Product (FR) | 200 | 40 | 191 | **0** | Y | 1 | — | 4/6 | 4/6 |

## What drove the +16
- **og:image** default set site-wide → all 10 pages N→Y (was the single largest lever measured).
- **Meta length** fixed on Ribbons (192→146), Bows (173→140), Gift Bags (157→140), Gift Tags (177→138).
- **Description depth** brought ≥30w on Gift Wrap (47), Ribbons (45), Bows (43), Gift Bags (39), Gift Tags (33).
- **Title length** improved on Bows (68→61).

## Remaining on-page gaps (next levers)
1. **hreflang = 0 on all 10** — biggest single remaining lever (+10 checks). Gated on the clone (Issue #1).
2. **Category H1 = 2** — theme emits a second H1 on category archives; a double-H1 dilutes the heading signal. Investigate the theme template. Consistent across snapshots, so it doesn't distort the delta.
3. **Christmas desc 25w**, **Product meta 191**, **FR home title 81 / meta 177**, **EN home title 73** — not yet optimized.
4. **Titles 66–70** on Ribbons (70), Gift Bags (69) — slightly over the ≤65 target; optional trim.

## Caveat
Tier-1 measures **on-page quality landing**, not search outcomes. Traffic/ranking proof requires **Tier 2 (Search Console)** — pending admin access — and **Tier 3 (GA4 organic)**. See `tracking.md`.
