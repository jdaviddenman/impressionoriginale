# Runbook — bulk EN spelling fixes via Search Regex (wp-admin only)

**Issue #37 · payload = PR #30** (348 EN errors / 521 pages). This is the working method after the dry run proved **REST + HTTP-MCP are edge-blocked on this host** (the `Authorization` header is stripped before it reaches PHP — see [`rest-content-edits.md`](rest-content-edits.md)). Search Regex runs **inside your logged-in wp-admin session** (cookie auth, which the edge always forwards), so it sidesteps the header block entirely. No SSH, no host access needed — only the wp-admin Administrator you already have.

Scope of this runbook: **only the repeated-string misspellings** that are safe to fix mechanically. Explicitly **out of scope** (handled elsewhere): the CSS/substring traps (`COLOR`, `colors`), the WooCommerce-default `Description Description` (a non-bug — see PR #30 correction), the doubled byline (a single Display-Name edit), the `en-US→en-GB` setting, and the ~150 unique grammar/punctuation/untranslated-French items (manual, per page).

---

## ⚠️ Two hazards — read before running anything (plain sentences)

**1. WPML cross-language contamination.** French translations live as separate posts in the **same** database. Search Regex has no language filter, so a global replace will also rewrite French content. Several of the target strings are **correct French words** — `favorite` (fem. of *favori*), `gros grain`, `personnalis…`. Replacing those globally **corrupts the French site**. Those rules are marked 🟠 below and must be applied **per-match (EN rows only)**, never globally.

**2. No undo.** Search Regex writes to the database with no rollback. Your backup is the only revert. Take a **full WP Engine restore point + an UpdraftPlus backup to Google Drive before installing the plugin or running a single replace** (RULE 3). Restore first, diagnose second if anything degrades.

---

## Preconditions (RULE 4)

1. Backup taken (both WPE + UpdraftPlus), verified present off-server.
2. Install **Search Regex** (Plugins → Add New → search "Search Regex" by *John Godley* → Install → Activate). New plugin = low blast radius, reversible by deactivate; the backup covers the DB writes it will make.
3. Know how to purge **both** caches after (WPE object/page cache **and** WP Rocket) — the re-check reads stale HTML otherwise.

## Per-rule procedure (every rule, no exceptions)

1. **Tools → Search Regex.**
2. **Search pattern** = the regex from the table. Toggle **Regex = ON**. **Source = Post content** (if a rule returns 0 matches, also try *Post excerpt* — WooCommerce short descriptions live there).
3. Click **Search**. This is the **dry run** — it lists every match with context. Confirm the count is in the expected range **and every row is the intended word in the intended language.**
4. 🟢 rule + list is clean → **Replace → Replace & Save** (global).
   🟠 rule → **replace only the EN rows individually** from the results list; skip any `/fr/` / French-context row.
5. **Verify** (RULE 5): purge both caches → fetch the example URL → old string gone, correction present, page still 200. *(Product `/e-shop/…` pages 403 to `curl` behind Cloudflare — verify those via the logged-in front-end preview or WebFetch; category pages like `/wrap/` pass `curl`.)*

---

## 🟢 Global-safe rules (English-only strings — will not appear in French)

Regex ON. Whole-word `\b` where the string could be a substring of another word.

| # | Search (Regex ON) | Replace | ~Pages | Verify URL |
|---|---|---|---|---|
| 1 | `\bcurrated\b` | `curated` | 20 | `/e-shop/gift-tags-black/` |
| 2 | `\bbeautifuly\b` | `beautifully` | 20 | `/e-shop/gift-tags-black/` |
| 3 | `\bRecylced\b` | `Recycled` | 12 | `/e-shop/gift-tags-watercolour-blue/` |
| 4 | `\bVelvelt\b` | `Velvet` | 10 | `/e-shop/almond-green-velvet-tuxedo-bow-n436-l/` |
| 5 | `\bornates\b` | `adorns` | 31 | `/e-shop/black-tuxedo-bow-n233-20mm/` |
| 6 | `\bArtic\b` | `Arctic` | 8 | `/gift-bags/` |
| 7 | `\btraveling\b` | `travelling` | 10 | `/wrap/` |
| 8 | `\bcolorful\b` | `colourful` | 14 | `/e-shop/deep-dive-coral-gift-wrap/` |
| 9 | `g raduated` | `graduated` | 12 | `/e-shop/24-december-gift-bag-m/` |

- **#6 `Artic`** — the `\b` and capital `A` are mandatory: `artic` is a substring of *particular*, *article*, *articulate*. Do not drop the boundary.
- **#8 `colorful`** — match the whole word; never shorten to `color` (that's a trap, below).
- **#9 `g raduated`** — a literal broken-space artifact; the exact string won't appear anywhere legitimate.
- **Case:** these appear in one dominant case. If the dry run shows a capitalised variant too (e.g. sentence-start `Currated`), add a second rule for that form — the replacement is literal and won't fix case for you.

## 🟠 Per-row rules (language-ambiguous — replace EN rows ONLY)

Dry-run, then replace individual EN matches from the results list. **Do not global-replace — these are valid French.**

| Search (Regex ON) | Replace | ~Pages | Why per-row |
|---|---|---|---|
| `personnalis` | `personalis` | 20 | Fixes `personnalise`/`personnalised` family, but French `personnalisé/personnalisation` are **correct** — skip FR rows. |
| `\bgros grain\b` | `grosgrain` | 21 | Two-words→one (the batch file's `gros → grosgrain` was wrong — `gros` alone corrupts every word containing it and French `gros`=*big*). Skip FR rows; leave product **titles/slugs** alone (SEO). |
| `quadri-color` | `four-colour` | 41 | English-only in the audit, but confirm no FR page carries it before replacing; skip FR rows. |
| `\bfavorite\b` / `\bFAVORITE\b` | `favourite` / `FAVOURITE` | 51 | Br/Am, but French `favorite` (fem. of *favori*) is correct — skip FR rows; two cases seen. |

## ⛔ Deferred / not this runbook

- **`COLOR` (364) / `colors` (79)** — **do not global-replace.** `color` matches inline CSS `color:` / `background-color`, and the `COLOR` spec label may be theme-rendered, not content. Confirm where it lives (dry-run Post content vs. it's a template label) and handle narrowly, with per-change risk-acceptance. Likely a theme/attribute-label fix, not a content replace.
- **`Description Description` (66)** — WooCommerce-default tab-label + panel-heading render, **not a DB string** (find→replace = 0 rows). Cosmetic-only; one filter if unwanted: `add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );`. See PR #30 correction.
- **`Mathilde Habert ×2` byline (24)** — one fix: Users → the author → **Display name**. Not a replace.
- **`lang="en-US"` → en-GB** — Settings → General → **Site Language** (one change).
- **Grammar (37) / punctuation (19) / unique typos / untranslated-French pages (17)** — non-mechanical; manual block-editor edits, per page. Do untranslated-French first (worst UX/SEO).

---

## Acceptance criteria (per rule)

**Done when**, after purging both caches, for the rule's verify URL:

- `<old string>` no longer present, `<correction>` present (fetch the page — `curl` for category pages, WebFetch/logged-in preview for `/e-shop/` product pages).
- Page still returns `200`; `harness/fingerprint.sh` diff shows no new PHP errors / shortcode leakage, headings intact.
- **French unaffected:** for every 🟠 rule, spot-check the FR equivalent page (`/fr/…`) still reads the correct French — the replace must not have touched it.

A green "N rows replaced" in Search Regex is **not** acceptance — only the external re-fetch is (RULE 5).

## Rollback (RULE 3)

Search Regex has no undo. Revert = restore the pre-run backup (WPE restore point or UpdraftPlus). If a bad replace is caught immediately, the inverse replace (`correction → old`) can undo a clean global rule, but the backup is authoritative — use it for anything ambiguous or serialized.
