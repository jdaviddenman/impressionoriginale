---
name: i18n-en-default-at-root
description: "EN is WPML default served at site root (no /en/ prefix); absence of /en/ URLs is by design, not a defect — don't re-audit. ADR 0003."
metadata: 
  node_type: memory
  type: project
  originSessionId: aa36b243-0561-4b58-a0a4-e6abfdbc44e8
---

impressionoriginale.com runs WPML **directory mode, EN default at root (no prefix)**, FR under `/fr/`. There are no `/en/` URLs — English *is* the bare path. Flagging "missing /en/" as an i18n defect is wrong (verified live + refute-critic 2026-07-05); same false-positive class as hreflang [[no-clone-test-bench]]-era Issue #1. hreflang is sitemap-based, so an empty `<head>` is expected.

Counting gotcha: sitemaps list each language as a **separate `<url>` block**, so bilingual products are double-counted — `<url>` entries ≈ 2× distinct products (819 entries ≈ 418 products). Minor real gaps only: `/fr/shop/` broken/non-reciprocal hreflang alternate that 301-chains to an unrelated product; 12 FR-only products lack `x-default`; stray `/en/`→gift-wrap 301 (harmless). Source of truth: `docs/adr/0003-i18n-url-strategy-en-default-at-root.md`.
