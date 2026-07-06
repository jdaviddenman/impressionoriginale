# WP-CLI Fix Commands — Ready to Run

Run these on a machine with SSH access to the WP Engine server, or directly on the server.

**⚠️ Backup first:** `wp db export` on the server before running any search-replace.

## ⚠️ WPML homograph hazard — do NOT run these blind

This is a WPML **EN+FR** database. `wp search-replace` hits every language. Several "typos" in the audit are **valid French words** in FR posts — a blind replace corrupts correct French content. Verified language breakdown of published posts (2026-07-06):

| Pattern | EN pages | FR pages | Verdict |
|---|---|---|---|
| `gros grain` | 17 | **16** | Fixed EN-only 2026-07-06 (Batch 5): lowercase prose `gros grain`→`grosgrain` (1 row). Title-case product names + `gros-grain` category URLs are not typos — not touched. |
| `personnalise` | 21 | **28** | Fixed EN-only 2026-07-06 (Batch 5): exact phrase `personnalise your`→`personalise your` (20 rows). French `personnaliser` preserved; 28 FR pages untouched. |
| `quadri-color` | 41 | 2 | Fixed EN-only 2026-07-06 (Batch 5): `quadri-color`→`four-colour` (41 rows). |
| `personnalised` | 0 published | — | Not in published content (revisions only) — no-op. |
| `ornates` | 0 published | — | Not in published content (revisions only) — no-op. |
| `Description Description` | 0 total | — | Does not exist — audit count wrong. Skip. |
| `g raduated` | 0 total | — | Does not exist. Skip. |
| `Artic Leather` | 0 total | — | Does not exist. Skip. |

Rule: before any site-wide replace, check `wp_icl_translations.language_code` for the matching posts. Run blind **only** for strings that are non-words in both EN and FR.

## ✅ Applied 2026-07-06 (Batch 4 — English-only non-words, safe site-wide)

`currated→curated` (20), `beautifuly→beautifully` (20), `Recylced→Recycled` (12), `Velvelt→Velvet` (10), `Artic Blue→Arctic Blue` (18). Each re-verified `remaining=0`. Backup: `pre-spellfix-20260706-151354.sql`. See `docs/spelling-fixes-log.md` Batch 4.

## After running

```bash
# Clear caches
wp cache flush
wp page-cache flush   # WP Engine page cache
```
