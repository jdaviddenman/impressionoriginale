---
name: no-product-reviews-by-design
description: The store does not do product reviews — a business decision. Never flag missing aggregateRating / review schema as an SEO defect; it is intentional, not a gap.
metadata:
  node_type: memory
  type: project
---

Impression Originale **does not do product reviews** — deliberate business decision (operator, 2026-07-08). `woocommerce_enable_reviews = no`, 0 approved reviews by design.

**How to apply:** Do NOT flag missing `aggregateRating` / `review` structured data as a defect or SEO opportunity. It is out of scope permanently, not an oversight. This closed the last open criterion of Issue #15 (agentic-search readiness) as won't-do — the llms.txt + crawler-policy + `sku` schema deliverables shipped; reviews/aggregateRating is N/A.

Product JSON-LD correctly emits `Product` + `Offer` + `sku` (e.g. `IOR-0002FRUS1C2016`); `gtin`/`mpn` absent (bespoke products, often no GTIN). That is the expected complete state — no rating node.

Related: [[yoast-titlemeta-write-mechanism]].
