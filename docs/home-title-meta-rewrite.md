# Homepage Title / Meta / H1 Rewrite (keyword-first)

Companion to [title-meta-rewrites.md](title-meta-rewrites.md). The category pages
are done; the **homepage** — the site's highest-authority page — still leads with
the brand and wastes its SERP real estate.

## Evidence (live, 2026-07-04)

| Field | Current (live) | Problem |
|-------|----------------|---------|
| Title | `Impression Originale I Eshop I Luxe gift wraps and ribbons made in France` (73 chars) | Brand-first; nobody searches the brand yet. 73 chars → truncates. Separators are capital-`I` letters, not real `\|`. |
| Meta | `Impression Originale eshop for beautiful gifting. Discover our Collection of luxe gift wraps, ribbons, gift boxes, gift bags and bows. Made in France.` | Opens on the brand; buries the demand keyword. |
| H1 | `IMPRESSION ORIGINALE` (`<h1 class="eut-title eut-light">`) | Brand-only. The strongest on-page heading carries zero keyword. |

## The rewrite (copy-paste each value)

**Focus keyphrase** (Yoast — drives the checks):

```
luxury gift wrap
```

**SEO title** — 55 chars, keyword-first, real `|`, brand after, USP kept:

```
Luxury Gift Wrap, Made in France | Impression Originale
```

**Meta description** — 154 chars, exact keyphrase in the first sentence:

```
Luxury gift wrap, ribbons, boxes and bows — hand-drawn by artists and made in France from recycled paper. Eco-conscious luxury gifting, shipped worldwide.
```

**H1** — 32 chars, mirrors the title's lead keyphrase:

```
Luxury Gift Wrap, Made in France
```

### Alt title (if you want "ribbons" in the title — 65 chars, risks truncation)

```
Luxury Gift Wrap & Ribbons, Made in France | Impression Originale
```

## Where to enter

- **Title + meta + focus keyphrase** → WordPress admin → **Yoast SEO → Settings → Homepage** (or edit the front page → Yoast box). Delete any trailing `%%sep%% %%sitename%%` variable or the brand doubles.
- **H1** → *not* a Yoast field. It renders from the theme title element (`eut-title`, the Enfold/`eut` theme). Set it in the **page/theme title area or hero module**, not Yoast. A title fix without the H1 fix is half the gain.

## Caveats (from the category work, apply here too)

- Home is a **WPBakery/slider builder page** → Yoast's content meter reads raw shortcode, not the rendered page, so "no images / <300 words / no links" are **false alarms**. Trust only the title/meta/keyphrase fields.
- **Do not change the slug** (`/`). URL stability wins.
- Exact contiguous phrase: `luxury gift wrap` must appear adjacent to score.

## Verify live (Done when)

After saving, re-fetch and confirm the rendered head + heading:

```
curl -s https://www.impressionoriginale.com/ | grep -oiE '<title>[^<]*</title>|<h1[^>]*>[^<]*</h1>'
```

Done when the title returns `Luxury Gift Wrap, Made in France | Impression Originale`
and the H1 returns `Luxury Gift Wrap, Made in France` (not `IMPRESSION ORIGINALE`).
