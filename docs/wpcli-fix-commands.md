# WP-CLI Fix Commands — Ready to Run

Run these on a machine with SSH access to the WP Engine server, or directly on the server.

**⚠️ Backup first:** `wp db export` on the server before running any search-replace.

## Site-wide spelling fixes (13 patterns)

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
