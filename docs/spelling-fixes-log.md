# Spelling & Grammar Fixes — Applied

Running log of all fixes applied to the live site. Source data: `reports/spelling-grammar-audit.md` (git commit `3601a0f`).

**Total: 24 of 348 confirmed errors fixed** (6.9%) — 14 pages fixed.

| # | Date | Page | Post ID | Typo | Fix | Method | Verified |
|---|---|---|---|---|---|---|---|
| 1 | 2026-07-06 | `/3d-modeling-surgeon-paper/` | 4662 | `poeple` | `people` | REST API + nonce | curl |
| 2 | 2026-07-06 | `/faqs/` | 13090 | `papr` | `paper` | REST API + nonce | REST API (cached) |
| 3 | 2026-07-06 | `/3d-modeling-surgeon-paper/` | 4662 | `RABIT` | `RABBIT` | REST API + nonce | REST API |
| 4 | 2026-07-06 | `/3d-modeling-surgeon-paper/` | 4662 | `modelisation` | `modelling` | REST API + nonce | REST API |
| 5 | 2026-07-06 | `/3d-modeling-surgeon-paper/` | 4662 | `modelised` | `modelled` | REST API + nonce | REST API |
| 6 | 2026-07-06 | `/bespoke-services/` | 13287 | `embroisery` | `embroidery` | REST API + nonce | REST API |
| 7 | 2026-07-06 | `/bespoke-services/` | 13287 | `DESING` | `DESIGN` | REST API + nonce | REST API |
| 8 | 2026-07-06 | `/decipher-quadrichromia-printing-process/` | 3656 | `Ethymology` | `Etymology` | REST API + nonce | REST API |
| 9 | 2026-07-06 | `/decipher-quadrichromia-printing-process/` | 3656 | `reffered` | `referred` | REST API + nonce | REST API |
| 10 | 2026-07-06 | `/decipher-quadrichromia-printing-process/` | 3656 | `supperposing` | `superposing` | REST API + nonce | REST API |
| 11 | 2026-07-06 | `/diary/` | 3366 | `developping` | `developing` | REST API + nonce | REST API |
| 12 | 2026-07-06 | `/diary/` | 3366 | `dehibd` | `behind` | REST API + nonce | REST API |
| 13 | 2026-07-06 | `/historical-reissue-gift-wrap-of-the-bnf/` | 10349 | `gratuded` | `graduated` | REST API + nonce | REST API |
| 14 | 2026-07-06 | `/historical-reissue-gift-wrap-of-the-bnf/` | 10349 | `faciliting` | `facilitating` | REST API + nonce | REST API |
| 15 | 2026-07-06 | `/how-to-a-commission-for-musee-rodin/` | 6084 | `freetime` | `free time` | REST API + nonce | REST API |
| 16 | 2026-07-06 | `/how-to-a-commission-for-musee-rodin/` | 6084 | `Somedays` | `Some days` | REST API + nonce | REST API |
| 17 | 2026-07-06 | `/illustrated-interview-kim-heeguym-aka-mr-fox/` | 5924 | `LATERN ON` | `LANTERN ON` | REST API + nonce | REST API |
| 18 | 2026-07-06 | `/know-how-the-perfect-gift/inspirations/` | 8777 | `KEEPSAFE` | `KEEPSAKE` | REST API + nonce | REST API |
| 19 | 2026-07-06 | `/know-how-the-perfect-gift/wrapping-service/` | 10687 | `the hear of the stationnary` | `the heart of the stationery` | REST API + nonce | REST API |
| 20 | 2026-07-06 | `/meet-an-expert-the-art-of-colours/` | 6271 | `behing` | `behind` | REST API + nonce | REST API |
| 21 | 2026-07-06 | `/furoshiki-8-ways-to-master-it/` | 12858 | `Do-it-Youself` | `Do-it-Yourself` | REST API + nonce | REST API |
| 22 | 2026-07-06 | `/talk-with-our-founder-the-wrapping-ceremony/` | 9395 | `do it yourself` | `do-it-yourself` | REST API + nonce | REST API |

## Method key

- **REST API + nonce** — Dashboard `wpApiSettings.nonce` → REST API `context=edit` → replace → PUT. See `docs/playwright-mcp-setup.md`.
- **Search Regex** — operator pastes regex into wp-admin → Search Regex → Replace. See `docs/spelling-fix-runbook.md`.
- **Manual** — operator edits directly in wp-admin.
