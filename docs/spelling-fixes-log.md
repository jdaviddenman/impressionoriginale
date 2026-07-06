# Spelling & Grammar Fixes — Applied

Running log of all fixes applied to the live site. Source data: `reports/spelling-grammar-audit.md` (git commit `3601a0f`).

**Total: 2 of 348 confirmed errors fixed** (0.6%)

| # | Date | Page | Post ID | Typo | Fix | Method | Verified |
|---|---|---|---|---|---|---|---|
| 1 | 2026-07-06 | `/3d-modeling-surgeon-paper/` | 4662 | `poeple` | `people` | REST API via Dashboard nonce | curl |
| 2 | 2026-07-06 | `/faqs/` | 13090 | `papr` | `paper` | REST API via Dashboard nonce | REST API (frontend cached) |

## Method key

- **REST API + nonce** — Dashboard `wpApiSettings.nonce` → REST API `context=edit` → replace → PUT. See `docs/playwright-mcp-setup.md`.
- **Search Regex** — operator pastes regex into wp-admin → Search Regex → Replace. See `docs/spelling-fix-runbook.md`.
- **Manual** — operator edits directly in wp-admin.
