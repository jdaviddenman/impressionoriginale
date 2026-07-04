# Impression Originale — SEO Audit & Remediation

**Site:** <https://www.impressionoriginale.com/>
**Audit date:** 2026-07-04
**Status:** 🟡 In progress — fixes are being validated on an isolated staging clone **before** any change touches production.
**Purpose:** a shared workspace so the site admin can see what was tested, what was found, and the exact steps to apply on live.

---

## How this was audited

- **External, evidence-based crawl** of the live site — HTML `<head>`, `robots.txt`, XML sitemaps, RSS feeds, and structured data. Every claim below is backed by what the live site actually returns, not assumption.
- **Isolated clone testing** — changes are trialled on a throwaway UpdraftPlus clone whose environment is matched to live (**PHP 8.2**, **WordPress 7.0**, **WooCommerce 10.7**), then validated with an automated before/after harness. Nothing changes on production until it is proven on the clone.

## Verdict

The site is technically well-built (Yoast, caching, product schema, rich product copy) but has several discovery-limiting issues. The headline defect is **hreflang tags missing site-wide on a bilingual EN/FR store** — see **[Issue #1](../../issues/1)**. None of the fixes require a rebuild.

## What's already working

- Yoast SEO active; valid `sitemap_index.xml` (6 child sitemaps); product sitemap fresh.
- Home meta description present and keyword-rich; self-referencing canonical; `lang="en-US"`.
- Structured data on home: `Organization`, `WebSite`, `WebPage`, `BreadcrumbList`.
- Product pages: rich descriptions, related products, image `alt` text, prices shown.
- Page caching + lazy-load active; `robots.txt` sane.

## Findings (ranked by impact)

| # | Finding | Impact | Fix | Tracking |
|---|---------|--------|-----|----------|
| 1 | **hreflang missing site-wide** on bilingual EN/FR site (0 tags, both languages) | 🔴 High | Restore WPML hreflang output | [Issue #1](../../issues/1) |
| 2 | Brand-first titles & H1 (waste the strongest keyword real estate) | 🔴 High | Keyword-first rewrites | [docs/title-meta-rewrites.md](docs/title-meta-rewrites.md) |
| 3 | Language architecture — products authored FR-first, EN as translation, unlinked by hreflang | 🔴 High | Pairs with Issue #1 | [Issue #1](../../issues/1) |
| 4 | No product review / rating schema (no SERP stars) | 🟠 Med | Enable reviews + WooCommerce SEO schema | _pending_ |
| 5 | Stale content (newest blog post 2025-08) | 🟠 Med | Content cadence around search demand | _pending_ |
| 6 | Heavy front-end (page builder + slider) → Core Web Vitals risk | 🟠 Med | Run PageSpeed Insights; defer/optimise | _pending_ |
| 7 | `og:image` missing on home (poor link/social previews) | 🟡 Low | Set default social image in Yoast | _pending_ |
| 8 | Breadcrumbs not rendering on product pages | 🟡 Low | Enable Yoast breadcrumbs in template | _pending_ |
| 9 | **Obsolete Universal Analytics tag still firing** (`UA-85910237-1`); GA4 (`G-Y88VQHFDBV`) **already live** via GTM | 🟡 Low | Remove the dead UA tag | [Issue #3](../../issues/3) |
| 10 | **Agentic / AI-search readiness** — no `llms.txt`; Product schema lacks reviews/identifiers | 🟠 Med | `llms.txt` + confirm AI-crawler policy + reviews/identifiers schema | [Issue #4](../../issues/4) |

## Remediation workflow (how we de-risk)

1. **Clone** live → environment matched (PHP 8.2 / WP 7.0 / WC 10.7).
2. **Parity check** clone vs live — confirm the clone is a faithful baseline before changing anything.
3. **Apply the fix** on the clone (see Issue #1).
4. **Re-run the harness** → diff before/after → confirm the fix landed and nothing regressed.
5. **Hand off** a verified, step-by-step runbook for the admin to repeat on live.

## Verification harness

[`harness/fingerprint.sh`](harness/fingerprint.sh) fetches a representative set of pages (EN + FR, category, product, content, a form) and records a diffable fingerprint: HTTP status, title/meta, **hreflang count**, headings, JSON-LD types, and regression flags (PHP errors, page-builder shortcode leakage, encoding breakage, asset drift). Run against a baseline and after each change, then diff.

> **Scope note:** the harness reads **server HTML only** — it does not execute JavaScript. Visual/JS checks (layout, slider, add-to-cart, language switch, currency) are performed manually each round.

## Docs

- **[hreflang issue & fix](docs/hreflang-fix.md)** — the headline defect (mirrored in [Issue #1](../../issues/1))
- **[Title & meta rewrites (EN + FR)](docs/title-meta-rewrites.md)** — copy-paste ready
- **[Universal Analytics → GA4 migration](docs/analytics-ga4-migration.md)** — dead UA tag (mirrored in [Issue #3](../../issues/3))
- **[Agentic / AI-search readiness](docs/agentic-search.md)** — `llms.txt` + AI-crawler policy + schema (mirrored in [Issue #4](../../issues/4)); the repo-root [`llms.txt`](llms.txt) is the deployable file

## Security note

The full wp-admin **plugin/version inventory and update plan** are kept in a **separate private note** (not in this public repo) to avoid publishing the store's exact update-status attack surface. Ask the audit owner for access.

## Status log

- **2026-07-04** — External audit complete. hreflang defect confirmed: **0** tags site-wide, both languages, while Yoast's own head tags render fine. Root cause narrowed to the WPML ↔ Yoast integration (config, translation-linking, publish status, and cache all ruled out). Clone provisioned; environment being matched to live. Baseline fingerprint + before/after diff pending clone data load.
- **2026-07-04** — Analytics workstream opened ([Issue #3](../../issues/3)). Confirmed the site still loads Universal Analytics `UA-85910237-1` (retired 2023-07-01); no GA4 measurement ID in page source. GTM container `GTM-MT7G7Z3C` present — check whether GA4 already fires inside it before assuming a full data gap.
- **2026-07-04** — Agentic/AI-search workstream opened ([Issue #4](../../issues/4)). Verified live: `robots.txt` already allows the answer/agent bots (`ChatGPT-User`, `OAI-SearchBot`) and blocks only the training bot (`GPTBot`) — a coherent policy. Product schema already emits `Offer`/price/availability but lacks `aggregateRating`/`sku`. Evidence (2026): `llms.txt` is not consumed by major AI providers and carries no SEO value — shipping a minimal one anyway (low cost), but the real levers are crawl access + review/identifier schema. Deployable `llms.txt` added at repo root.
- **2026-07-04** — GA4 property found to already exist (`G-Y88VQHFDBV`, Google Tag `GT-5TPLSSZ`); both IDs = 0 occurrences in **static** front-end HTML.
- **2026-07-04** — **Correction:** GA4 **is** collecting. GA4 Reports (property `375621420`) show ~1.9K users + €432.96 revenue in June. The GA4 tag fires **inside** GTM container `GTM-MT7G7Z3C` (JS-injected — invisible to an external HTML fetch; the flagged caveat, now confirmed). The earlier Realtime "0" was the Termly consent gate + the static check, not a data gap. Revised task: **remove the obsolete UA tag** (`UA-85910237-1`) — GA4 needs no migration. Severity downgraded High → Low.
