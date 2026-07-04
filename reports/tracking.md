# SEO Progress Tracking

Data-driven record of whether the optimization work moves the numbers that matter. Three tiers, from "did the work land" (fast, we control) to "did it change the business" (slow, external truth).

**Rule:** log every change's **date** here so a later metric shift can be attributed to a specific intervention, not guessed.

---

## Tier 1 — On-page quality (leading; we measure)

Source: `harness/fingerprint.sh` + the scorecard rubric. Reproducible, external, fast.
Baseline: [`baseline-scorecard-2026-07-04.md`](baseline-scorecard-2026-07-04.md) — **35/65 checks (54%)**.
Cadence: re-run after each batch; save `scorecard-YYYY-MM-DD.md`; track the aggregate.

| Date | Aggregate | Change since last |
|------|-----------|-------------------|
| 2026-07-04 | 27/65 (42%) | baseline (recomputed under a consistent rubric; supersedes the initial 35/65 hand-score — see `scorecard-2026-07-04b.md`) |
| 2026-07-04b | **53/65 (82%)** | **+16 checks.** 7 EN categories optimized (wrap/ribbons/bows/gift-bags/gift-tags/boxes parent+child) + og:image site-wide flip (10 pages N→Y) + meta-length + description-depth fixes. **hreflang correction:** it was miscounted (measured the head, not the sitemap) — hreflang is valid via sitemap, so both snapshots gain +10 (baseline→37, checkpoint→53); the +16 from our work is unchanged. |

## Tier 2 — Search performance (the truth of "getting found"; Google Search Console)

Source: **Google Search Console** → Performance report. This is the metric that answers "are we getting found." Export/snapshot weekly.

Track (filter to **query** and **page**, and to the **Organic** side):
- **Impressions** — how often the site is *shown* in results (visibility ↑ = good).
- **Clicks** — actual visits from search.
- **Average position** — ranking (lower number = better).
- **CTR** — clicks ÷ impressions (the title/meta rewrites target this directly).
- **Indexed pages** (Coverage/Pages report) — are pages even eligible to rank.

| Week of | Impressions | Clicks | Avg position | CTR | Indexed pages | Notes |
|---------|-------------|--------|--------------|-----|---------------|-------|
| _connect GSC + fill baseline row_ | | | | | | |

**Quasi-control method (rigor):** because categories are optimized one at a time, compare CTR/position uplift on **optimized** pages vs **not-yet-touched** pages over the same window in GSC (Pages tab). If optimized pages move and untouched don't, the change — not seasonality/algorithm noise — is the cause.

## Tier 3 — Business outcome (GA4 organic)

Source: **GA4** → filter to **Organic Search** channel, segment by **landing page**.
Track: organic sessions, engagement rate, avg engagement time, **conversions/purchases**, **revenue**.
Cadence: monthly; use **year-over-year** (this store is seasonal — see caveats).

| Month | Organic sessions | Engagement rate | Conversions | Organic revenue | Notes |
|-------|------------------|-----------------|-------------|-----------------|-------|
| _fill baseline from GA4_ | | | | | |

---

## Change log (intervention markers)

| Date | Change | Pages affected |
|------|--------|----------------|
| 2026-07-04 | Category SEO: title/meta/keyphrase + 47-word description + internal links | `/wrap/` (Gift Wrap) |
| 2026-07-04 | Category SEO: title/meta/keyphrase + 45-word description + internal links | `/ribbons/` (Ribbons) |
| 2026-07-04 | og:image site-wide default set in Yoast (`Gift_ceremony.jpg`) — 9/10 pages flip N→Y | site-wide |
| 2026-07-04 | Analytics: GA4 consolidated to single GTM path; duplicate `GT-5TPLSSZ` tags removed | site-wide (tags) |

Also add a **GA4 annotation** on each change date so the graphs carry the marker.

---

## Caveats — read before interpreting any number

- **Lag:** SEO effects take **weeks to months** (re-crawl + re-rank). Do not judge in days.
- **Seasonality is severe here:** gift wrap peaks at **Christmas**. Month-over-month misleads; prefer **year-over-year**, or the quasi-control above.
- **Correlation ≠ causation:** algorithm updates + seasonality move numbers on their own. The control-group comparison + dated annotations are how a claim is defended.
- **Weight organic, not vanity:** prioritize GSC organic impressions/clicks/position and GA4 organic conversions over gross pageviews.
