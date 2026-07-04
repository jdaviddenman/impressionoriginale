# Agentic / AI Search Readiness

Workstream to make the store legible and citable to AI answer engines and agents (ChatGPT Search, Perplexity, Claude, Google AI Overviews) and to coding/shopping agents. Grounded in the live state, not assumption.

## Reality check on `llms.txt` (do it, but don't overvalue it)

As of mid-2026, `llms.txt` is **not consumed by any major AI provider** for search/answers:

- Google **officially does not use it** (confirmed, re-clarified June 2026) — **zero SEO/ranking value**.
- OpenAI, Anthropic, Meta, Mistral have made **no production commitment**; measured AI-bot interest is negligible (~408 of ~500M AI-bot visits in one 90-day study).
- It genuinely works for **coding agents on documentation sites** (Cursor, Claude Code, Copilot) — not an ecommerce catalog.

**Decision:** ship a minimal `llms.txt` anyway — it is cheap, harmless, and gives ownership/coverage — but treat it as the *lowest*-value item here. The real agentic-search levers are crawl access and structured data (below).

Sources: Google no-SEO-value (digitalapplied, 2026); State of llms.txt 2026 (Presenc AI); Ahrefs "What is llms.txt".

## Verified live state (2026-07-04)

### AI crawler policy — `robots.txt` (already coherent)

Verbatim, the site's `robots.txt` already makes deliberate AI-bot choices:

```
ChatGPT-User:   Allow: /     # agent fetch when a user asks ChatGPT — ALLOWED
OAI-SearchBot:  Allow: /     # ChatGPT Search crawler — ALLOWED
GPTBot:         Disallow: /  # OpenAI TRAINING crawler — BLOCKED (deliberate)
Googlebot / Bingbot / TermlyBot: Allow: /
User-agent: *   Disallow: /wp-admin/, /?s*, */feed/, /*?add-to-cart, /*?orderby
Sitemap: https://www.impressionoriginale.com/sitemap_index.xml
```

Read: the **answer + agent bots are allowed**; only the **training** bot is blocked. That is a reasonable, even sophisticated stance (be citable in ChatGPT/Perplexity answers without feeding model training). Not a problem — but confirm it's intentional. Perplexity / Claude / Google-Extended are **not named**, so they fall under `*` and can crawl content (only wp-admin/search/feed/cart are blocked).

**Decisions to make (minor):**
- Keep `GPTBot: Disallow` (block training) or open it? Trade-off: training exposure vs. brand presence in future model knowledge. Current block is defensible.
- Optionally add **explicit** `Allow` rules for `PerplexityBot`, `ClaudeBot`, `Google-Extended` to make intent explicit rather than implicit-via-`*`.
- Add a `Sitemap`/reference line for `llms.txt` once shipped (optional).

### Structured data — already strong, two gaps

Product pages already emit: `Product` + `Offer` + `priceCurrency: EUR` + `availability: InStock` + `UnitPriceSpecification`, plus site-wide `Organization` / `WebSite` / `BreadcrumbList`. This is exactly what answer engines parse — good foundation.

**Missing (the real schema lever):**
- **`aggregateRating` / `review`** — 0 present. Requires WooCommerce reviews enabled (ties to audit finding #4). Star ratings are among the most-cited signals in AI product answers.
- **`sku` / `gtin` / product identifiers** — 0 present. Yoast WooCommerce SEO adds these; they help agents match products across sources.

## Proposed workstream (ranked by real impact)

1. **[low value, ship anyway] `llms.txt`** — publish the file at `https://www.impressionoriginale.com/llms.txt` (see repo root `llms.txt`). Deployment options below.
2. **[high value] Confirm the AI-crawler policy is intentional** — decide on GPTBot; optionally add explicit allows for Perplexity/Claude/Google-Extended.
3. **[high value] Enable reviews + extend Product schema** — turn on WooCommerce reviews and install Yoast WooCommerce SEO → adds `aggregateRating` + product identifiers. Overlaps audit finding #4.
4. **[ongoing] Clear titles/meta/descriptions** — already in progress; helps AI extraction and citation.

## Deploying `llms.txt` on WordPress / WP Engine

The file must be served at the site root: `https://www.impressionoriginale.com/llms.txt`. Options:

- **Static upload (simplest):** SFTP / WPE file manager → drop `llms.txt` in the web root (`/nas/content/live/impressionor/`). nginx serves it directly.
- **Plugin:** a WordPress "llms.txt" generator plugin (keeps it in sync with content).
- **Verify:** `curl -s https://www.impressionoriginale.com/llms.txt | head` returns the file (HTTP 200, not the WordPress 404 page).

Keep it small and link-only (the spec favours a concise map, not full content). Update when top categories change.

## Acceptance (done-when)

- [ ] `curl -s https://www.impressionoriginale.com/llms.txt` returns the file (HTTP 200).
- [ ] AI-crawler policy reviewed and confirmed intentional (GPTBot decision recorded; optional explicit allows added).
- [ ] WooCommerce reviews enabled; a product page emits `aggregateRating` (verify in Rich Results Test).
- [ ] Product identifiers (`sku`/`gtin`) present in Product schema.

## Honest caveat

`llms.txt` will not measurably move GPT/agentic visibility on its own — the evidence is clear. The crawl-access + structured-data items are what actually determine whether AI engines can reach and cite the store. Prioritise accordingly.
