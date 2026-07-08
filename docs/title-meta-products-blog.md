# Product & Blog Title/Meta Cleanup (EN + FR)

Companion to [`title-meta-rewrites.md`](title-meta-rewrites.md) (EN categories) and [`title-meta-fr.md`](title-meta-fr.md) (FR categories). Those two swept the **category + homepage** titles/meta (keyword-first, `#71`). **Products and blog posts were never touched on either language** — this doc is that greenfield workstream.

Ground truth read live over SSH via the Yoast presenter (Cloudflare bot-blocks scripted `curl` on `/fr/` and product pages), both languages via WPML `switch_lang`. Verified 2026-07-08.

## Live state at audit

| Segment | Count | Finding |
|---|---|---|
| EN products | 410 | 101 custom titles (69 legacy `I`-sep boilerplate + 32 size-prefix), 5 missing meta, 309 on template |
| FR products | 413 | 81 custom titles (55 legacy `I`-sep + 26 size-prefix), 3 missing meta, 332 on template |
| EN blog | 23 | all 23 metas present (0 missing); titles fine, no leak |
| FR blog | 23 | 17 missing meta; titles inconsistent + **English leak** (`The diary of` on a FR post) |

Key mechanics (see memory `[[yoast-titlemeta-write-mechanism]]`):
- **Render source is the Yoast indexable, not postmeta** — both must be updated/rebuilt.
- **Empty `metadesc-product` / `metadesc-post` emit NO description tag** (Yoast ≥14 dropped content auto-generation). Earlier "auto-generates from excerpt" assumption was wrong; scope stayed small only because coverage was already 815/823 products.
- **Yoast title templates are a single shared value across EN+FR** (WPML String Translation not configured for `wpseo_titles`). Editing a template hits both languages; per-object overrides are per-post and language-safe.

## Method

Recommendations were generated across 7 decision dimensions, then each was adversarially reviewed in fresh context by two refuting critics (an SEO auditor + a WPML implementation engineer), then synthesised. Critics corrected several claims (see below). No change shipped until externally verified.

## The 7 decisions (reviewed)

1. **Legacy `I`-separator product titles (124)** → **CLEAR** to template. They are non-unique boilerplate (`%%title%% %%page%% I Luxury gifting by Impression Originale`), use a capital-`I` fake pipe that reads as a SERP typo, and duplicate the brand tail. Product name is already front-loaded; clearing loses nothing of value. *(Critic correction: the FR-cannibalization claim was overstated — the FR tail `emballage cadeau de luxe` only partially overlaps the FR category head term `papier cadeau de luxe`. Case stands on boilerplate/broken-pipe grounds.)*
2. **Size-prefix titles (58: `Luxurious Large` EN / `Grands` FR)** → **DEFER**. Identical literal on all 32/26 = boilerplate; no evidence these items are large-format. Keeping `Large` on a non-large product is misleading. `Grands` also has FR gender-agreement risk on feminine nouns (`pince`, `presse`). Verify large-format reality first, then branch keep/clear.
3. **Missing product meta (8: 5 EN + 3 FR)** → **hand-write** unique in-language metas; leave the template empty. Blast radius genuinely 8 objects.
4. **FR blog missing meta (17)** → **hand-write** 17 FR metas per-post; spot-audit the 6 existing for English leak. Template left empty.
5. **FR blog title leaks** → **clear** the English-leak override (`9396` = `The diary of`). *(Critic correction: build the work-list by SQL, don't reuse the missing-meta IDs. Result: only 9396 is an actual English-on-French leak; the `Le journal d'` / `Journal d'` FR tails are valid French and were left untouched — surgical.)*
6. **Separator house-style** → **flip** `wpseo_titles.separator` sc-dash → sc-pipe (`|`) to match the category/home pages, run **before** the clears so cleared titles inherit `|`. *(Critic correction: this does NOT yield full site-wide consistency by itself — the legacy `I` literals need separate clearing; blast radius is every templated title both languages, not just products.)*
7. **Meta template** → **no action**. A global template under the shared EN+FR constraint is at best redundant, at worst leaks one language onto the other. Per-item fills own the gaps.

## What shipped (2026-07-08, live-verified)

Executed in review-sorted order; each with indexable rebuild → full CDN purge (RULE 15) → external verify (Playwright for `/fr/` + product pages; presenter as origin truth).

- **Step 1-2 — 20 FR metas written** (3 FR products `8513/3768/2183` + 17 FR blog posts). All were empty before (no clobber); presenter 20/20 render; live-confirmed on product `8513` and post `3714`.
- **Step 3 — separator flipped** sc-dash → sc-pipe. Live: `Ensemble Boréal | Impression Originale` (FR), `Look Boreal | Impression Originale` (EN).
- **Step 4 — FR blog English leak cleared** (`9396`). Live: `Conversation avec notre fondatrice : la cérémonie cadeau | Impression Originale`.
- **Step 5 — 126 legacy `I` titles cleared** (69 EN + 57 FR incl. 2 drafts) → template. Leftover 0, live `I`-sep 0. Live-confirmed EN `13164`: `By Design 24 December | Impression Originale`. Only "duplicate" is the cross-language pair `Mexicana Furoshiki` (11805 EN / 11806 FR — separate hreflang URLs, benign).

## Deferred / out of scope

- **Step 6 size-prefix** (≤58) — pending large-format verification + `Grands` agreement.
- **5 mis-bucketed "EN" tools** (`9937/9923/9915/9887/9877`) — WPML-tagged `en` but 100% French name+body (untranslated single-language products, likely Issue #33). Writing English meta onto a French page is wrong → held for the #33 translation decision, not a meta band-aid.
- **FR blog missing meta (17)** — shipped here, but tracked as a **second language track under Issue #50** (blog SEO audit).
- Theme-level H1 (`h1=0` in `the_content` on all 46 posts) → **#50** (RULE 12 — theme may emit page H1 at template level).
- Social/OG description audit; product tag/attribute archive titles; obscure-name product long-tail rewrites.

## Rollback

Full payload in the session scratchpad `step5_rollback.json`:
- Step 3: `wpseo_titles.separator` = `sc-dash`.
- Step 4: re-add `9396` title `%%title%% %%page%% %%sep%% The diary of %%sitename%%`.
- Step 5: re-add the two exact literals to the listed 126 ids' `_yoast_wpseo_title`, then delete each `wp_yoast_indexable` row to force rebuild.

## Verify commands

```bash
# origin truth (Yoast presenter, both langs)
ssh impressionor@impressionor.ssh.wpengine.net \
  'wp eval "\$GLOBALS[\"sitepress\"]->switch_lang(\"fr\"); \$m=YoastSEO()->meta->for_post(<ID>); echo \$m->title.\"\n\".\$m->description;"'
# external (Cloudflare blocks curl on /fr/ + products → use Playwright MCP)
```
