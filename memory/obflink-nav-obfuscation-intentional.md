---
name: obflink-nav-obfuscation-intentional
description: "The \"uncrawlable links\" Lighthouse flag is deliberate obflink obfuscation + noindex (crawl-budget/PageRank sculpting), not a defect; don't strip it. Issue"
metadata: 
  node_type: memory
  type: project
  originSessionId: d6688229-d90d-4f6e-a36a-97625b89e965
---

The homepage/main-nav "Links are not crawlable" Lighthouse SEO flag is **working-as-intended**, not a bug. Cause: licensed plugin **`obflink`** (`wp-content/plugins/obflink/`, `WpRank\ObfLink` — French SEO agency WP Rank/CreaNico) strips `href` on ~72 hand-selected menu items and base64-stores the URL in `data-obflink-url` (JS decodes on click). It's a **crawl-budget / PageRank-sculpting** tool, hand-configured via per-item `_obflink` post-meta (`''`/`all`/`not-home`).

The obfuscated product-category targets are **also `noindex`** — two coherent deliberate signals on the same pages (verified live: `/wrap/geometric/`, `/wrap/velvet/`, `/collection/` = `noindex,follow`; `/our-products/`, `/know-how-the-perfect-gift/`, `/inspirations/` = `index,follow`). So for the noindex categories, "not crawlable" is desired.

**Do NOT propose restoring nav hrefs site-wide** — it re-exposes ~48 deliberately-noindexed pages to crawl, fighting the site's own noindex setup (worse across WPML EN+FR duplicated category permutations). Only obfuscated-AND-`index` items (Our Products, Know-How, Inspirations, a few more) are even arguably a defect, and they're likely already discoverable via the Yoast sitemap, so not worth touching.

Reached via a refute-charter critic + independent live re-verification that overturned an initial "self-harm, strip it all" read ([[feedback-verify-ground-state]] / RULE 6/13). A WebFetch sitemap summary lied (claimed noindex cats were in the sitemap); live robots meta was authoritative. Issue #48 closed not-planned with full evidence.
