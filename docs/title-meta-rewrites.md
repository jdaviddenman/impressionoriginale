# Title & Meta Rewrites + Category Optimization Pattern (keyword-first)

The current titles/H1 lead with the brand name — which nobody searches yet — wasting the strongest SERP real estate. These rewrites lead with **demand keywords**, append the brand after a real `|`, keep titles ≤ ~60 chars and meta ≤ ~156, and weave in the "made in France" USP.

**Where to enter:** Yoast fields per page. WooCommerce categories → Products → Categories → edit → *Yoast SEO* box. Static pages → edit page → Yoast box. Home → Yoast → Settings.

**H1 note:** each page's `<h1>` should mirror its title's lead keyword (currently the H1 is the brand). A title fix without the H1 fix is half the gain.

---

## The locked per-category pattern (proven on `/wrap/` + `/ribbons/`)

Run this loop per product category. Each step is grounded in ≥2 SEO best practices and verified against the live rendered HTML, not just Yoast's saved state.

1. **Focus keyphrase** — set the exact phrase from the table below (≤4 words). This one field drives most of Yoast's checks; an over-long keyphrase (the site's old default was an 11-word string) turns six checks red at once.
2. **SEO title** — paste from the table. Front-loads the keyphrase (Google title-link weighting) + exact match at the start. Delete any trailing `%%sep%% %%sitename%%` variable, or the brand doubles.
3. **Meta description** — paste from the table. Must contain the **exact keyphrase** and stay ≤156 chars (CTR + SERP width).
4. **Category Description field** — expand to ~40–50 words, entered as **raw HTML** (see snippets below), with:
   - the **exact keyphrase once, in the first sentence** (satisfies density + "keyphrase in introduction, in one sentence");
   - **1–2 internal links** to sibling categories (authority distribution + crawl paths), reciprocated where possible.
5. **Save → verify live** — O re-fetches the URL and confirms rendered title/meta, real `<a href>` links, exact-keyphrase count, and clean markup.

### Gotchas learned live
- **Exact contiguous phrase.** Yoast counts the keyphrase only as an exact adjacent match. "Couture gift ribbon" scores **0** for keyphrase `luxury gift ribbon` — the word "luxury" must be adjacent. English plurals are fine ("ribbons" counts for "ribbon").
- **Enter descriptions as raw HTML**, not pasted from Notes/Word — rich-editor paste injects `<span class="s1">` / `Apple-converted-space` cruft, and markdown `[text](/url)` renders as literal text, not a link. Use the Text/code view.
- **Do NOT change the slug.** Yoast's "keyphrase in slug" suggestion is declined on purpose — renaming a live category URL forces 301s and risks lost ranking/links. URL stability wins.
- **One exact keyphrase use is enough** — do not repeat to raise density; Google penalizes stuffing and Yoast's minimum here is 1.
- **WPBakery/Slider pages (Home, `/collection/`) mislead Yoast.** Its content analysis reads the raw shortcode, not the rendered page, so "no images / <300 words / no links" are false there. Only the title/meta/keyphrase **fields** are reliable on builder pages — don't pad them to satisfy the meter.
- **Category description may be theme-hidden.** Confirm it renders on the live page (it does on this theme — verified). If hidden, content-depth work isn't visible to users.

### Progress

| Category | Title/meta | Description + links | Verified live | Status |
|----------|-----------|---------------------|---------------|--------|
| `/wrap/` | ✅ | ✅ 48w, 2 links | ✅ 2026-07-04 | **done** |
| `/ribbons/` | ✅ | ✅ 45w, 2 links | ✅ 2026-07-04 | **done** |
| `/bows/` | ✅ | ✅ 43w, 2 links | ✅ 2026-07-04 | **done** |
| `/gift-bags/` | ✅ | ✅ 39w, 2 links | ✅ 2026-07-04 | **done** |
| `/gift-tags/` | ✅ | ✅ 33w, 2 links | ✅ 2026-07-04 | **done** |
| `/bags-boxes/` (parent, term-617) | ✅ | ✅ 35w, 2 links | ✅ 2026-07-04 | **done** — kp `luxury gift boxes` |
| `/bags-boxes/gift-boxes/` (child, term-861) | ✅ | ✅ 60w, 3 links (→parent) | ✅ 2026-07-04 | **done** — kp `easy to build gift boxes` |
| `/occasions-to-gift/christmas/` | ✅ | ✅ 38w, 2 links | ✅ 2026-07-04 | **done** |
| `/gift-fabric-furoshiki/` | ✅ | ✅ 54w, 2 links | ✅ 2026-07-04 | **done** |
| `/occasions-to-gift/wedding-celebration/` | ✅ | ✅ 39w, 2 links | ✅ 2026-07-04 | **done** |
| `/occasions-to-gift/birthday-party/` | ✅ | ✅ 37w, 2 links | ✅ 2026-07-04 | **done** |
| `/occasions-to-gift/baby-shower-kid/` | ✅ | ✅ 41w, 2 links | ✅ 2026-07-04 | **done** (title 69 — optional trim) |
| `/occasions-to-gift/valentine/` | — | — | — | **ready** (block below) — NEXT |
| `/scissors/` | — | — | — | **ready** (block below) |
| `/table-name-cards/` | — | — | — | **ready** (block below) |
| `/occasions-to-gift/luxury/` | — | — | — | **ready** — differentiate, kp `metallic gift wrap` (block below) |
| `/size-l/m/s-ribbons/` · `/size-xl/` · `/size-xs/` · `/ribbons/tuxedo/` | n/a | n/a | n/a | **noindex** (decided) — Yoast Advanced → not in search results |
| `/masterclass/` | — | — | — | out of scope (service page, not a product category) |

> **Two box categories exist.** Parent `/bags-boxes/` (term-617, "Gift Bags & Boxes") owns the head term *luxury gift boxes* and is done. Child `/bags-boxes/gift-boxes/` (term-861, "Gift Boxes") must use a **distinct** keyphrase (*easy to build gift boxes* — the site's own phrase) and link up to the parent, or the two pages cannibalize each other. Edit the child at Products → Categories → the *Gift Boxes* row nested under *Gift Bags & Boxes*.
>
> **Claim accuracy:** every product attribute in the copy must trace to the page. These are **drawer boxes with a sheath** that **ship flat** and **assemble in 3 steps** into a **rigid (350g)** box, in 3 sizes — verified from the live category copy. (An earlier draft said "foldable"; that was an unverified inference and was removed.)
| Home + `/collection/` (WPBakery — fields only) | — | — | — | queued |
| All FR pages | — | — | — | queued |

---

## Per-category copy-paste blocks (EN)

Each category has four fenced blocks — **keyphrase**, **SEO title**, **meta description**, **description HTML** — to paste field-by-field into the Yoast box + Description field (code view). ✅ = shipped + verified live.

### `/wrap/` — Gift Wrap ✅
```text
luxury gift wrapping paper
```
```text
Luxury Gift Wrapping Paper, Made in France | Impression Originale
```
```text
Shop hand-drawn luxury gift wrapping paper by independent artists — printed in France on recycled stock. Sheets & sets for every occasion.
```
```html
<p>Discover our collection of luxury gift wrapping paper — hand-drawn by independent artists and printed in France on recycled stock. Each sheet turns an ordinary present into something memorable. Pair it with our <a href="https://www.impressionoriginale.com/ribbons/">satin and velvet ribbons</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a> to finish the wrap beautifully.</p>
```

### `/ribbons/` — Ribbons ✅
```text
luxury gift ribbon
```
```text
Luxury Gift Ribbon: Satin, Velvet & Organza | Impression Originale
```
```text
Luxury gift ribbon in satin, velvet, organza, grosgrain & taffeta. Made in France to finish every wrap beautifully. Shop by colour & size.
```
```html
<p>Discover our luxury gift ribbon in satin, velvet, organza, grosgrain and taffeta — woven to finish a gift beautifully and made in France. Choose by colour, texture and width to match your <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a> or top it with a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/bows/` — Bows ✅
```text
handmade gift bows
```
```text
Handmade Gift Bows — Ready-Tied Luxury | Impression Originale
```
```text
Ready-tied handmade gift bows in S, M & L. Add a couture finishing touch to any gift in seconds. Made in France by Impression Originale.
```
```html
<p>Add the finishing touch with our handmade gift bows, ready-tied in sizes S, M and L and made in France. Drop one onto any present in seconds for a couture finish. Pair with our <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a> and coordinating <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>.</p>
```

### `/gift-bags/` — Gift Bags
```text
luxury gift bags
```
```text
Luxury Gift Bags & Pouches, Made in France | Impression Originale
```
```text
Hand-finished luxury gift bags and pouches for effortless, beautiful gifting. Designed by artists, made in France. Shop sizes & designs.
```
```html
<p>Our luxury gift bags and pouches are hand-finished in France for effortless, beautiful gifting — no wrapping required. Choose the size and design to suit the occasion, then finish with a <a href="https://www.impressionoriginale.com/ribbons/">gift ribbon</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/gift-tags/` — Gift Tags
```text
designer gift tags
```
```text
Designer Gift Tags, Hand Made in France | Impression Originale
```
```text
Illustrated designer gift tags to personalise every present. Made in France on recycled card. Shop the collection at Impression Originale.
```
```html
<p>Personalise every present with our designer gift tags, illustrated by independent artists and printed in France on recycled card. The perfect finishing detail for your <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a> and <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>.</p>
```

### `/bags-boxes/` — Gift Bags & Boxes (parent, term-617) ✅
Head term. This is the **parent** category and the primary box page.
```text
luxury gift boxes
```
```text
Luxury Gift Boxes, Made in France | Impression Originale
```
```text
Elegant luxury gift boxes in a range of sizes, artist-designed and made in France. The refined alternative to wrapping. Shop the collection.
```
```html
<p>Discover our luxury gift boxes, artist-designed and made in France in a range of sizes — the refined, reusable alternative to wrapping. Dress them up with a <a href="https://www.impressionoriginale.com/ribbons/">gift ribbon</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/bags-boxes/gift-boxes/` — Gift Boxes (child, term-861)
**Anti-cannibalization:** deliberately targets a *different* keyphrase from the parent, using the term's own copy ("easy to build," drawer box, ships flat, 3 sizes) so the two box pages don't compete for the same query. The description links **up to the parent** `/bags-boxes/` to signal the parent is the primary hub. All attributes traceable to the live page (no invented claims).
```text
easy to build gift boxes
```
```text
Easy to Build Gift Boxes in 3 Sizes | Impression Originale
```
```text
Easy to build gift boxes in three sizes — shipped flat, assembled in 3 steps into a rigid drawer box. Designed and made in France.
```
```html
<p>Our easy to build gift boxes are designed as drawer boxes with a sheath — they ship flat to simplify mailing, then assemble in three simple steps into a rigid, high-grammage box. Available in three sizes and made in France. Explore the full <a href="https://www.impressionoriginale.com/bags-boxes/">gift boxes collection</a>, or finish yours with a <a href="https://www.impressionoriginale.com/ribbons/">gift ribbon</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/occasions-to-gift/christmas/` — Christmas
```text
christmas gift wrap
```
```text
Luxury Christmas Gift Wrap & Ribbons | Impression Originale
```
```text
Designer christmas gift wrap, ribbons & bows, made in France. Wrap the holidays in eco-conscious luxury. Shop the Christmas capsule collection.
```
```html
<p>Wrap the holidays in our luxury christmas gift wrap — designer paper, ribbons and bows made in France. Everything you need for eco-conscious festive gifting in one capsule collection. Explore the matching <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a> and <a href="https://www.impressionoriginale.com/bows/">handmade bows</a>.</p>
```

### `/gift-fabric-furoshiki/` — Furoshiki
Grounded in live copy: reusable fabric wrap, M (48×48 cm) / L (75×75 cm).
```text
furoshiki gift wrap
```
```text
Furoshiki Gift Wrap, Reusable Fabric | Impression Originale
```
```text
Reusable furoshiki gift wrap in fabric, printed in France — medium (48×48cm) and large (75×75cm). The zero-waste way to wrap. Shop the collection.
```
```html
<p>Our furoshiki gift wrap is a reusable fabric alternative to paper, printed in France in two sizes — medium (48×48 cm) for a book or perfume, and large (75×75 cm) for bigger gifts. A beautiful, zero-waste way to wrap. Pair it with our <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>, or explore our <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a>.</p>
```

### `/occasions-to-gift/wedding-celebration/` — Wedding
Grounded: delicate pastel selection for bride, groom, guests.
```text
wedding gift wrap
```
```text
Wedding Gift Wrap, Made in France | Impression Originale
```
```text
Wedding gift wrap in a delicate pastel selection, made in France — for the bride, groom and every guest's gift. Shop the collection.
```
```html
<p>Our wedding gift wrap is a delicate, pastel selection made in France for the bride, the groom and every guest's gift. Dress each celebration present beautifully, then finish it with our <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/occasions-to-gift/birthday-party/` — Birthday
Grounded: "party time," gift wraps to celebrate.
```text
birthday gift wrap
```
```text
Birthday Gift Wrap, Colourful & Fun | Impression Originale
```
```text
Birthday gift wrap in colourful, artist-designed prints, made in France. Bring celebration to every present. Shop the birthday collection.
```
```html
<p>It's party time — our birthday gift wrap brings colour and celebration to every present, hand-designed by artists and made in France. Pair it with matching <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a> and a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a> to finish the look.</p>
```

### `/occasions-to-gift/baby-shower-kid/` — Baby Shower & Kid
Grounded: gift wraps for baby showers, babies and kids.
```text
baby shower gift wrap
```
```text
Baby Shower Gift Wrap & Kids' Designs | Impression Originale
```
```text
Baby shower gift wrap and playful kids' designs, made in France by independent artists. The tender way to wrap for new arrivals. Shop now.
```
```html
<p>Our baby shower gift wrap is a tender selection for the friend who's expecting, plus playful designs for babies and kids — made in France by independent artists. Complete the gift with our <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a> or a ready-tied <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### `/occasions-to-gift/valentine/` — Valentine
Grounded: gift wrap for Valentine, express love, generous bow, hearts.
```text
valentine gift wrap
```
```text
Valentine Gift Wrap, Hearts & Love | Impression Originale
```
```text
Valentine gift wrap in heart-warming designs, made in France. Express your love and finish with a generous bow. Shop the Valentine collection.
```
```html
<p>Find the perfect valentine gift wrap to express your love — heart-warming designs made in France, ready to be finished with a generous <a href="https://www.impressionoriginale.com/bows/">handmade bow</a> and coordinating <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>.</p>
```

### `/scissors/` — Scissors
Grounded: scissors selected to cut gift wrap and ribbon effortlessly.
```text
gift wrapping scissors
```
```text
Gift Wrapping Scissors, Sharp & Precise | Impression Originale
```
```text
Gift wrapping scissors selected to cut paper and ribbon effortlessly — the right tool for a flawless wrap. Shop scissors at Impression Originale.
```
```html
<p>Our gift wrapping scissors are chosen to cut paper and ribbon effortlessly — the right tool to finish a beautiful wrap. Pair them with our <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a> and <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>.</p>
```

### `/table-name-cards/` — Table Name Cards
No existing description on the live page. Grounded: place/table name cards for events.
```text
table name cards
```
```text
Table Name Cards for Events & Weddings | Impression Originale
```
```text
Table name cards to finish a beautifully set table for weddings, dinners and celebrations. Designed and made in France. Shop the collection.
```
```html
<p>Our table name cards add the finishing touch to a beautifully set table — ideal for weddings, dinners and celebrations, designed and made in France. Coordinate them with our <a href="https://www.impressionoriginale.com/occasions-to-gift/wedding-celebration/">wedding gift wrap</a> and <a href="https://www.impressionoriginale.com/ribbons/">gift ribbons</a>.</p>
```

### `/occasions-to-gift/luxury/` — Luxury (DECIDED: differentiate)
**Decision (2026-07-04): differentiate**, not consolidate. Keep the page but move it off the head term ("luxury gift wrap," owned by Home + `/wrap/`) onto a distinct attribute keyphrase — `metallic gift wrap` — grounded in its "sparkles / luxurious feel" copy, and link up to `/wrap/`. Apply the block below.
```text
metallic gift wrap
```
```text
Metallic & Glamorous Gift Wrap | Impression Originale
```
```text
Metallic gift wrap with a sparkling, luxurious finish, made in France. The glamorous way to wrap a statement gift. Shop the collection.
```
```html
<p>Our metallic gift wrap brings a sparkling, luxurious finish to any statement gift — designed by artists and made in France. Explore the full range of <a href="https://www.impressionoriginale.com/wrap/">luxury gift wrapping paper</a> or finish yours with a <a href="https://www.impressionoriginale.com/bows/">handmade bow</a>.</p>
```

### Size / attribute archives — DECIDED: noindex
**Decision (2026-07-04): noindex** these thin/duplicate archives so they stop diluting `/ribbons/` (they slice the same ribbons by size/type):
`/size-l-ribbons/` · `/size-m-ribbons/` · `/size-s-ribbons/` · `/size-xl/` · `/size-xs/` · `/ribbons/tuxedo/`

**How (per category, in Yoast):** Products → Categories → edit the category → **Yoast SEO** box → **Advanced** tab → **"Allow search engines to show this category in search results?" → No**. Yoast then emits `robots: noindex` and drops the category from the XML sitemap automatically. Repeat for all six.

**Verify:** `curl -s https://www.impressionoriginale.com/size-xl/ | grep -i 'name="robots"'` shows `noindex`; and the URLs disappear from `product_cat-sitemap.xml`. Products themselves stay indexed and reachable via `/ribbons/` — only these filter archives are hidden.

*(Note: these are still linked in on-page size filters/nav — that's fine; noindex removes them from search without breaking navigation.)*

### Out of scope
- **`/masterclass/`**: a service/class listing, not a product category — optimize as a page if kept, separate from this category sweep.

*(All FR pages: same block structure, French keyphrases — separate workstream.)*

---

## English — home + top categories

The **Focus keyphrase** column is the exact phrase to set in Yoast; the meta must contain it verbatim.

| Page | URL | Focus keyphrase | New title | New meta description |
|------|-----|-----------------|-----------|----------------------|
| Home | `/` | luxury gift wrap | Luxury Gift Wrap & Ribbons Made in France \| Impression Originale | Hand-drawn luxury gift wrap, ribbons, boxes & bows — designed by artists and made in France. Shop eco-conscious wrapping for extraordinary gifts. |
| Gift wrap | `/wrap/` | luxury gift wrapping paper | Luxury Gift Wrapping Paper, Made in France \| Impression Originale | Shop hand-drawn luxury gift wrapping paper by independent artists — printed in France on recycled stock. Sheets & sets for every occasion. |
| Ribbons | `/ribbons/` | luxury gift ribbon | Luxury Gift Ribbon: Satin, Velvet & Organza \| Impression Originale | Luxury gift ribbon in satin, velvet, organza, grosgrain & taffeta. Made in France to finish every wrap beautifully. Shop by colour & size. |
| Bows | `/bows/` | handmade gift bows | Handmade Gift Bows — Ready-Tied Luxury \| Impression Originale | Ready-tied handmade gift bows in S, M & L. Add a couture finishing touch to any gift in seconds. Made in France by Impression Originale. |
| Gift boxes (parent) | `/bags-boxes/` | luxury gift boxes | Luxury Gift Boxes, Made in France \| Impression Originale | Elegant luxury gift boxes in a range of sizes, artist-designed and made in France. The refined alternative to wrapping. Shop the collection. |
| Gift boxes (child) | `/bags-boxes/gift-boxes/` | easy to build gift boxes | Easy to Build Gift Boxes in 3 Sizes \| Impression Originale | Easy to build gift boxes in three sizes — shipped flat, assembled in 3 steps into a rigid drawer box. Designed and made in France. |
| Gift bags | `/gift-bags/` | luxury gift bags | Luxury Gift Bags & Pouches, Made in France \| Impression Originale | Hand-finished luxury gift bags and pouches for effortless, beautiful gifting. Designed by artists, made in France. Shop sizes & designs. |
| Gift tags | `/gift-tags/` | designer gift tags | Designer Gift Tags, Illustrated & Made in France \| Impression Originale | Illustrated designer gift tags to personalise every present. Made in France on recycled card. Shop the collection at Impression Originale. |
| Furoshiki | `/gift-fabric-furoshiki/` | furoshiki fabric gift wrap | Furoshiki Fabric Gift Wrap, Reusable \| Impression Originale | Reusable furoshiki fabric gift wrap — the sustainable, zero-waste way to wrap gifts. Printed in France by Impression Originale. Shop designs & sizes. |
| Christmas | `/occasions-to-gift/christmas/` | christmas gift wrap | Luxury Christmas Gift Wrap & Ribbons \| Impression Originale | Designer christmas gift wrap, ribbons & bows, made in France. Wrap the holidays in eco-conscious luxury. Shop the Christmas capsule collection. |
| Wedding | `/occasions-to-gift/wedding-celebration/` | wedding gift wrap | Wedding Gift Wrap, Ribbons & Favors \| Impression Originale | Elegant wedding gift wrap, ribbons and place cards, designed by artists and made in France. Dress every celebration gift beautifully. Shop now. |
| Birthday | `/occasions-to-gift/birthday-party/` | birthday gift wrap | Birthday Gift Wrap & Ribbons \| Impression Originale | Colourful, artist-designed birthday gift wrap, ribbons & bows. Made in France, eco-conscious, made to delight. Shop birthday wrapping. |

## French — home + top categories (`fabriqué en France` USP)

Enter these on the **French** translation of each page/category (WPML). Type native French — do **not** auto-translate the English.

| Page | URL | New title | New meta description |
|------|-----|-----------|----------------------|
| Accueil | `/fr/` | Papier Cadeau de Luxe & Rubans \| Impression Originale | Papier cadeau dessiné à la main, rubans, boîtes et nœuds, créés par des artistes et fabriqués en France. L'emballage cadeau de luxe éco-responsable. |
| Papier cadeau | `/fr/papier-cadeau/` | Papier Cadeau de Luxe Fabriqué en France \| Impression Originale | Papier cadeau de luxe dessiné par des artistes, imprimé en France sur papier recyclé. Feuilles et coffrets pour toutes vos occasions. À découvrir. |
| Rubans | `/fr/ruban/` | Ruban Cadeau de Luxe : Satin & Velours \| Impression Originale | Ruban cadeau couture en satin, velours, organza, gros-grain et taffetas. Fabriqué en France pour sublimer chaque emballage. Choisissez couleur et taille. |
| Nœuds | `/fr/noeud/` | Nœuds Cadeaux Faits Main, Prêts à Poser \| Impression Originale | Nœuds faits main prêts à poser, tailles S, M et L. La touche couture qui sublime chaque cadeau en un geste. Fabriqués en France par Impression Originale. |
| Boîtes cadeaux | `/fr/pochette-boite/boite-cadeau/` | Boîtes Cadeaux de Luxe Fabriquées en France \| Impression Originale | Boîtes cadeaux élégantes, dessinées par des artistes et fabriquées en France, en plusieurs tailles. L'alternative raffinée à l'emballage. À découvrir. |
| Pochettes cadeaux | `/fr/pochette-cadeau/` | Pochettes & Sacs Cadeaux de Luxe \| Impression Originale | Pochettes et sacs cadeaux de luxe, finis à la main, pour un emballage élégant et sans effort. Dessinés par des artistes, fabriqués en France. |
| Étiquettes | `/fr/etiquettes-cadeaux/` | Étiquettes Cadeaux Illustrées & Design \| Impression Originale | Étiquettes cadeaux illustrées pour personnaliser chaque présent. Fabriquées en France sur carton recyclé. Découvrez la collection Impression Originale. |
| Furoshiki | `/fr/carre-de-tissu-furoshiki/` | Furoshiki : Tissu Cadeau Réutilisable \| Impression Originale | Le furoshiki, tissu d'emballage réutilisable : la façon zéro déchet d'emballer vos cadeaux. Imprimé en France. Découvrez les motifs et les tailles. |
| Noël | `/fr/occasions-cadeau/noel/` | Papier Cadeau & Rubans de Noël de Luxe \| Impression Originale | Papier cadeau, rubans et nœuds de Noël, dessinés par des artistes et fabriqués en France. Emballez les fêtes dans un luxe éco-responsable. Collection Noël. |
| Mariage | `/fr/occasions-cadeau/mariage/` | Emballage Cadeau & Rubans de Mariage \| Impression Originale | Papier cadeau, rubans et marque-places de mariage, dessinés par des artistes et fabriqués en France. Sublimez chaque cadeau de célébration. À découvrir. |
| Anniversaire | `/fr/occasions-cadeau/anniversaire/` | Papier Cadeau & Rubans d'Anniversaire \| Impression Originale | Papier cadeau, rubans et nœuds d'anniversaire, colorés et dessinés par des artistes. Fabriqués en France, éco-responsables. L'emballage festif à découvrir. |

---

**Quick wins:** `papier-cadeau` is the highest-volume French term on the site — prioritise it. **Furoshiki** (EN + FR) is low-competition and rising; the blog already ranks for it.
