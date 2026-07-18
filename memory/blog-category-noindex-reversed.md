---
name: blog-category-noindex-reversed
description: Blog categories flipped from global noindex to indexed; 4 categories optimized with descriptions. Yoast 28.0 per-term meta is non-functional.
metadata: 
  node_type: memory
  type: project
  originSessionId: aef95f6f-208b-4c3b-8fb7-17ee83728931
---

Flipped Yoast `noindex-tax-category` from `True` → `False` on 2026-07-18. Blog categories are now indexed by default.

**Why:** Research showed crawl budget is irrelevant at this scale (~600 URLs), noindex long-term = nofollow (wastes link equity), and category pages are structural topic hubs that should be indexed when they have enough posts + unique descriptions.

**4 categories optimized with descriptions + indexed:**
- Collaboration (ID 400, 10 posts)
- Behind the Scenes (ID 420, 9 posts)
- Illustrated Interview (ID 439, 5 posts)
- Meet an expert (ID 645, 3 posts)

Each has a Yoast title, meta description, and unique category description. FR versions also indexed (WPML translations).

**11 thin categories attempted to noindex via per-term meta** — but Yoast 28.0 ignores `wpseo_noindex` term meta entirely. Only the global `noindex-tax-category` setting has effect. Per-term overrides don't work in either direction (tested: 'index' ignored when global=True, 'noindex' ignored when global=False). Practical impact minimal: 8 empty cats (0 posts) render no archive pages; Uncategorized (2 posts), Tutorial (1 post), and Behind the scene (0 posts, duplicate slug) are indexed but negligible.

**Verification:** Origin serves `index, follow` on all 4 categories. `noindex, follow` on uncategorized (global+sitemap). CDN catch-up was slow (~30 min via WPE cache + Cloudflare edge); full `WpeCommon::clear_cdn_cache()` needs to be run from user's terminal due to SSH eval quoting issues.

**Related:** [[obflink-nav-obfuscation-intentional]], [[wpe-cdn-purge-after-change]]
