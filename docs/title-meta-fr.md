# French `/fr/` Title & Meta Rewrites (keyword-first)

Companion to [`title-meta-rewrites.md`](title-meta-rewrites.md) (EN). The EN keyword-first sweep (2026-07-04) wrote **only to the EN terms**; because the FR categories are **separate WPML terms**, none of it propagated. The `/fr/` side still renders 2023-era legacy titles — several never translated (still English), two leaking raw templates. This doc audits the live FR state and gives copy-paste French rewrites.

**Where to enter:** the **French** category's Yoast SEO box — wp-admin → Products → Categories → switch the language flag to **FR** → edit the French term → *Yoast SEO* box. **Not** the EN term (editing EN does not touch the FR render). Home → the FR homepage's Yoast fields.

## How this was audited

Cloudflare bot-blocks scripted `curl` on `/fr/` pages, and WebFetch drops `<head>` tags. Ground truth was read from **Yoast's own presenter over SSH**:

```bash
ssh impressionor@impressionor.ssh.wpengine.net \
  'wp eval "\$s=\$GLOBALS[\"sitepress\"]; \$s->switch_lang(\"fr\"); \$m=YoastSEO()->meta->for_term(62); echo \$m->title;"'
```

Validated against 3 external sources:
1. **Google FR SERP** (`papier cadeau de luxe fabriqué en France`) — competitors lead with *papier cadeau de luxe / haut de gamme* + *fabriqué en France / Made in France*; IO's own pages rank but on the weak *"Vente en ligne de papier cadeau design I"* title. Free-shipping *"dès 49 €"* is a live differentiator worth keeping in metas.
2. **[Furoshiki.fr](https://furoshiki.fr/) / [Maisons du Monde](https://www.maisonsdumonde.com/FR/fr/edito/article/idees-emballages-cadeaux-en-tissu)** — *furoshiki* is the rising, low-competition FR term (zéro-déchet trend), currently buried as "Carré de tissu / Furoshiki". *ruban cadeau* / *nœud cadeau* are the standard complementary terms.
3. **[WebRankInfo](https://www.webrankinfo.com/astuces/taille-meta-description) + [Google Search Central](https://developers.google.com/search/docs/appearance/title-link)** — FR best practice: title 50–65 chars, meta 120–160 chars, primary keyword in **both** title and meta.

## Live FR state today (Yoast presenter — proven, not inferred)

| FR page | FR term id | Current rendered title | Defect |
|---|---|---|---|
| `/fr/papier-cadeau/` | 62 | Vente en ligne de papier cadeau design **I** Impression Originale | "Vente en ligne de" wastes the lead; `I` sep |
| `/fr/ruban/` | 63 | Ruban cadeau, qualité et fabrication française I … | OK lead, too long, `I` sep |
| `/fr/noeud/` | 528 | Noeud cadeau, qualité et fabrication française I … | OK lead, `I` sep |
| `/fr/pochette-cadeau/` | 863 | Pochette cadeau haut-de-gamme I Made in France I … | decent; `I` sep + English "Made in France" |
| `/fr/pochette-boite/boite-cadeau/` | 862 | Vente en ligne de boîte cadeau I … | weak lead, `I` sep |
| `/fr/carre-de-tissu-furoshiki/` | 1151 | Carré de tissu / Furoshiki - Impression Originale | term-name only; buries rising "furoshiki" |
| `/fr/noel/` | 911 | **Gift wrap Christmas** I Luxury made in France… | **English on a FR page** |
| `/fr/mariage/` | 914 | **Gift Mariage** I Luxury made in France… | **broken FR/EN hybrid** |
| `/fr/amour/` | 913 | Papier Cadeau **Mariages & Amour** - … Made in France | **Valentine page mislabeled "mariages"**; English tail |
| `/fr/naissance/` | 909 | Papier Cadeau Naissance **& Petit** - … Made in France | entity-name leak; English tail |
| `/fr/anniversaire/` | 910 | Papier Cadeau Anniversaire - … Fabrication française | brand mid-title, no `\|` |
| `/fr/luxe/` | 912 | Papier Cadeau Luxe - … Fabrication française | cannibalizes papier-cadeau's "de luxe" |
| `/fr/etiquettes-cadeaux/` | 1077 | **Eshop – Gift Tag for luxury gifting** – … | **fully English on FR page** |
| `/fr/marque-places/` | 1143 | **ready-to-bow Archives** - Impression Originale | **English + raw "Archives" template leak, empty meta** |
| `/fr/ciseaux-fr/` | 1078 | **NULL** (no Yoast title) | falls to raw template |

**Verdict:** the FR side is a grade worse than pre-fix EN — not just brand-first, but language-leaking. High-value, low-risk fix (title/meta only — no layout/cart blast radius; instantly reversible; direct-to-live allowed per RULE 1).

## Recommended FR rewrites

Keyphrase = exact phrase for the Yoast **Focus keyphrase** field; meta must contain it verbatim. Titles land 51–63 chars, metas ≤160 (both verified). Native French — do **not** auto-translate the English.

| Page | URL | Focus keyphrase | New title (chars) | New meta description |
|---|---|---|---|---|
| Accueil | `/fr/` | papier cadeau de luxe | Papier Cadeau de Luxe & Rubans \| Impression Originale (53) | Papier cadeau de luxe, rubans, boîtes et nœuds dessinés par des artistes et fabriqués en France. Livraison offerte dès 49 €. Emballez vos plus beaux cadeaux. |
| Papier cadeau | `/fr/papier-cadeau/` | papier cadeau de luxe | Papier Cadeau de Luxe Fabriqué en France \| Impression Originale (63) | Papier cadeau de luxe dessiné par des artistes, imprimé en France sur papier recyclé. Feuilles et coffrets pour toutes vos occasions. Livraison dès 49 €. |
| Rubans | `/fr/ruban/` | ruban cadeau | Ruban Cadeau : Satin, Velours & Organza \| Impression Originale (62) | Ruban cadeau en satin, velours, organza, gros-grain et taffetas, fabriqué en France pour sublimer chaque emballage. Choisissez couleur et taille. |
| Nœuds | `/fr/noeud/` | nœud cadeau | Nœuds Cadeaux Faits Main, Prêts à Poser \| Impression Originale (62) | Nœuds cadeaux faits main, prêts à poser, en tailles S, M et L. La touche couture qui sublime chaque cadeau. Fabriqués en France. Livraison dès 49 €. |
| Boîtes cadeaux | `/fr/pochette-boite/boite-cadeau/` | boîte cadeau | Boîtes Cadeaux à Monter en 3 Étapes \| Impression Originale (58) | Boîtes cadeaux à monter en 3 étapes, livrées à plat, fabriquées en France en 3 tailles. L'alternative raffinée à l'emballage. Livraison dès 49 €. |
| Pochettes cadeaux | `/fr/pochette-cadeau/` | pochette cadeau | Pochettes Cadeaux Haut de Gamme \| Impression Originale (54) | Pochettes et sacs cadeaux haut de gamme, finis à la main pour un emballage élégant sans effort. Dessinés par des artistes, fabriqués en France. |
| Étiquettes | `/fr/etiquettes-cadeaux/` | étiquettes cadeaux | Étiquettes Cadeaux Illustrées & Design \| Impression Originale (61) | Étiquettes cadeaux illustrées pour personnaliser chaque présent, fabriquées en France sur carton recyclé. Découvrez la collection Impression Originale. |
| Furoshiki | `/fr/carre-de-tissu-furoshiki/` | furoshiki | Furoshiki : Tissu Cadeau Réutilisable \| Impression Originale (60) | Le furoshiki, tissu d'emballage réutilisable et zéro déchet, imprimé en France. Emballez vos cadeaux autrement. Découvrez les motifs et les tailles. |
| Noël | `/fr/noel/` | papier cadeau de noël | Papier Cadeau & Rubans de Noël de Luxe \| Impression Originale (61) | Papier cadeau, rubans et nœuds de Noël dessinés par des artistes et fabriqués en France. Emballez les fêtes dans un luxe éco-responsable. Dès 49 €. |
| Mariage | `/fr/mariage/` | papier cadeau mariage | Papier Cadeau Mariage & Célébration \| Impression Originale (58) | Papier cadeau, rubans et marque-places de mariage dessinés par des artistes et fabriqués en France. Sublimez chaque cadeau de célébration. |
| Anniversaire | `/fr/anniversaire/` | papier cadeau anniversaire | Papier Cadeau Anniversaire & Rubans \| Impression Originale (58) | Papier cadeau, rubans et nœuds d'anniversaire, colorés et dessinés par des artistes. Fabriqués en France, éco-responsables. Livraison dès 49 €. |
| Amour / Saint-Valentin | `/fr/amour/` | papier cadeau saint-valentin | Papier Cadeau Saint-Valentin \| Impression Originale (51) | Papier cadeau Saint-Valentin aux motifs tendres, fabriqué en France. Déclarez votre amour et finissez d'un nœud généreux. Découvrez la collection. |
| Naissance | `/fr/naissance/` | papier cadeau naissance | Papier Cadeau Naissance & Enfant \| Impression Originale (55) | Papier cadeau naissance et motifs enfants, fabriqué en France par des artistes. La façon tendre d'emballer pour les nouveau-nés. Livraison dès 49 €. |
| Luxe (différencié) | `/fr/luxe/` | papier cadeau métallisé | Papier Cadeau Métallisé & Glamour \| Impression Originale (56) | Papier cadeau métallisé à l'éclat scintillant et luxueux, fabriqué en France. La façon glamour d'emballer un cadeau d'exception. Découvrez la collection. |
| Marque-places | `/fr/marque-places/` | marque-places | Marque-places pour Mariages & Réceptions \| Impression Originale (63) | Marque-places pour sublimer une belle table de mariage, dîner ou réception. Dessinés et fabriqués en France. Découvrez la collection Impression Originale. |
| Ciseaux | `/fr/ciseaux-fr/` | ciseaux | Ciseaux à Papier Cadeau, Précis & Nets \| Impression Originale (61) | Ciseaux sélectionnés pour couper papier cadeau et ruban sans effort — l'outil parfait pour un emballage impeccable. Découvrez-les chez Impression Originale. |

**Anti-cannibalization (mirrors EN):** `/fr/papier-cadeau/` owns *papier cadeau de luxe*; `/fr/luxe/` is pushed to *papier cadeau métallisé* (grounded in its "scintillant / luxueux" copy) so the two don't compete.

## Priority order

1. **Broken pages first** — `noel`, `mariage`, `etiquettes-cadeaux`, `marque-places`, `ciseaux-fr` (English / NULL / raw-template leaks — actively costing FR ranking).
2. **Highest demand** — `papier-cadeau` + `furoshiki` (Sources 1 & 2).
3. The remaining occasion + accessory pages.

## Verify after each edit (RULE 5)

From the origin (Cloudflare blocks external `curl`):

```bash
ssh impressionor@impressionor.ssh.wpengine.net \
  'wp eval "\$GLOBALS[\"sitepress\"]->switch_lang(\"fr\"); \$m=YoastSEO()->meta->for_term(<FR_TID>); echo \$m->title.\"\n\".\$m->description;"'
```

Confirm the new French title/meta, then purge the CDN (RULE 15) and re-check `cf-cache-status: MISS`.

## Notes

- **`nœud`** (œ ligature) is the correct French; slug stays `noeud`, do not change it (URL stability — mirrors the EN "don't change the slug" rule).
- Keep **"Livraison offerte dès 49 €"** in metas where it fits — a live CTR asset confirmed against the current FR descriptions.
- FR occasion categories live at **root** (`/fr/noel/`, `/fr/mariage/`, …), **not** under `/fr/occasions-cadeau/` — the EN doc's earlier FR draft had the wrong paths (corrected there).
</content>
</invoke>
