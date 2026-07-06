# WP-CLI Fix Commands — Ready to Run

Run these on a machine with SSH access to the WP Engine server, or directly on the server.

**⚠️ Backup first:** `wp db export` on the server before running any search-replace.

## ⚠️ WPML homograph hazard — do NOT run these blind

This is a WPML **EN+FR** database. `wp search-replace` hits every language. Several "typos" in the audit are **valid French words** in FR posts — a blind replace corrupts correct French content. Verified language breakdown of published posts (2026-07-06):

| Pattern | EN pages | FR pages | Verdict |
|---|---|---|---|
| `gros grain` | 17 | **16** | **UNSAFE blind** — French for grosgrain; FR pages correct as-is. Needs per-language scoping. |
| `personnalise` | 21 | **28** | **UNSAFE blind** — correct French; majority FR. Scope to EN only. |
| `quadri-color` | 41 | 2 | Mostly EN; the 2 FR pages want `quadrichromie`, not `four-colour`. Scope. |
| `personnalised` | 0 published | — | Not in published content (revisions only) — no-op. |
| `ornates` | 0 published | — | Not in published content (revisions only) — no-op. |
| `Description Description` | 0 total | — | Does not exist — audit count wrong. Skip. |
| `g raduated` | 0 total | — | Does not exist. Skip. |
| `Artic Leather` | 0 total | — | Does not exist. Skip. |

Rule: before any site-wide replace, check `wp_icl_translations.language_code` for the matching posts. Run blind **only** for strings that are non-words in both EN and FR.

## ✅ Applied 2026-07-06 (Batch 4 — English-only non-words, safe site-wide)

`currated→curated` (20), `beautifuly→beautifully` (20), `Recylced→Recycled` (12), `Velvelt→Velvet` (10), `Artic Blue→Arctic Blue` (18). Each re-verified `remaining=0`. Backup: `pre-spellfix-20260706-151354.sql`. See `docs/spelling-fixes-log.md` Batch 4.

## Site-wide spelling fixes (13 patterns) — ORIGINAL LIST (see hazard table above before running)

Dry-run each first, then run without `--dry-run`.

```bash
# 1. Description Description → Description (66 pages — template bug)
wp search-replace 'Description Description' 'Description' --dry-run

# 2. quadri-color → four-colour (41 pages — franglais)
wp search-replace 'quadri-color' 'four-colour' --dry-run

# 3. ornates → adorns (31 pages — franglais)
wp search-replace 'ornates' 'adorns' --dry-run

# 4. gros → grosgrain (21 pages — truncation, careful: "gros" is a French word)
# Only replace in product descriptions/specs context
wp search-replace 'gros grain' 'grosgrain' --dry-run

# 5. personnalised → personalised (20 pages — spelling)
wp search-replace 'personnalised' 'personalised' --dry-run

# 6. currated → curated (20 pages — spelling)
wp search-replace 'currated' 'curated' --dry-run

# 7. beautifuly → beautifully (20 pages — spelling)
wp search-replace 'beautifuly' 'beautifully' --dry-run

# 8. personnalise → personalise (20 pages — spelling)
wp search-replace 'personnalise' 'personalise' --dry-run

# 9. g raduated → graduated (12 pages — spacing)
wp search-replace 'g raduated' 'graduated' --dry-run

# 10. Recylced → Recycled (12 pages — spelling)
wp search-replace 'Recylced' 'Recycled' --dry-run

# 11. Velvelt → Velvet (10 pages — spelling)
wp search-replace 'Velvelt' 'Velvet' --dry-run

# 12. Artic → Arctic (8 pages — careful: don't match "article")
wp search-replace 'Artic Blue' 'Arctic Blue' --dry-run
wp search-replace 'Artic Leather' 'Arctic Leather' --dry-run

# 13. hard working → hard-working (8 pages — grammar)
wp search-replace 'hard working' 'hard-working' --dry-run
```

## After running

```bash
# Clear caches
wp cache flush
wp rocket clean   # if WP Rocket CLI is available
```

## Single-page fixes still remaining

These couldn't be done via REST API (custom post types, WPBakery links):

```bash
# tara-lilly: mid century → mid-century (post 7876, designer CPT)
wp post update 7876 --post_content="$(wp post get 7876 --field=post_content | sed 's/mid century/mid-century/g')"

# workshop-damien-the-leather-compagnion: needs slug lookup first
wp post list --post_type=post --name="workshop-damien-the-leather-compagnion"
```
