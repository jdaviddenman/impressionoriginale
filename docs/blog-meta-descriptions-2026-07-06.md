# Blog meta descriptions — 17 posts (Issue #50)

**Date:** 2026-07-06 · **Applied to:** live (WP Engine `impressionor`) · **Status:** deployed + verified

## What

17 EN blog posts had an empty `<meta name="description">` (17/22 flagged in the #50 audit). Wrote a unique, keyword-first Yoast meta description (≤155 chars) for each. Blog meta-desc coverage is now 23/23.

Applied by setting `_yoast_wpseo_metadesc` post-meta via `wp eval-file` over SSH, then purging WP Rocket + WPE caches. Reversible per-post: clear the meta key.

## Correction to the #50 audit (recorded here + on the issue)

- **CRITICAL #1 "No H1 tags 22/22" is FALSE.** Dual-pattern check (RULE 12/13) across all 23 EN posts: 23/23 have exactly one `<h1>` (the post title). The "no H1" reading was a broken-extraction false positive. **Do not "add H1 tags" — it would inject duplicate H1s.**
- **CRITICAL #3 "interview content invisible" is overstated.** REST `content.rendered` shows 191–1393 real words per post (wrapped in WPBakery shortcodes), not "~4 words." Needs its own check before any transcription work.

## The 17 descriptions

| post ID | slug | meta description |
|---|---|---|
| 5984 | illustrated-interview-aiko-fukawa | Illustrated interview with illustrator Aiko Fukawa — her totem animal, silent storytelling and drawing, in Impression Originale's paper-artist series. |
| 4699 | illustrated-interview-black-lamb-studio | Illustrated interview with Isabel Serna of Black Lamb Studio — self-portrait, illustration and creative life, in Impression Originale's artist series. |
| 4318 | workshop-eva-magill-oliver | Meet US mixed-media artist Eva Magill-Oliver — her arty notebooks, studio practice and creative journey, in Impression Originale's workshop series. |
| 4177 | workshop-damien-the-leather-compagnion | Meet Damien, a French leather craftsman — the passion, training and artistry behind his handmade leather work, in Impression Originale's workshop series. |
| 4034 | illustrated-interview-sarah-betz | Illustrated interview with illustrator Sarah Betz (Little Cube) — self-portrait, drawing and inspiration, in Impression Originale's artist series. |
| 3842 | illustrated-interview-jeannie-phan | Illustrated interview with Toronto illustrator Jeannie Phan — home, identity and storytelling through drawing, in Impression Originale's artist series. |
| 6146 | workshop-annyen-lam | Meet Toronto artist Annyen Lam — intricate paper lace, lithography and printmaking, in Impression Originale's paper-artist workshop series. |
| 6788 | workshop-the-pineapple-chef | Meet Elise, the Pineapple Chef — food styling, photography and visual merchandising, in Impression Originale's workshop series. |
| 3368 | workshop-judith-rolfe | Meet JUDiTH+ROLFE, a US designer-architect duo — their paper architecture and shared creative life, in Impression Originale's workshop series. |
| 4662 | 3d-modeling-surgeon-paper | Meet Remi of Surgeon Paper — intricate 3D paper modeling made in France, curiosity and craft, in Impression Originale's workshop series. |
| 5924 | illustrated-interview-kim-heeguym-aka-mr-fox | Illustrated interview with illustrator Kim Heeguym (Mr. Fox) — self-portrait, drawing and storytelling, in Impression Originale's artist series. |
| 6271 | meet-an-expert-the-art-of-colours | Meet colour expert Dorte of the Danish Colour Board — textiles, trends and the art of colour, in Impression Originale's expert series. |
| 6084 | how-to-a-commission-for-musee-rodin | Behind the scenes with artist Emily on a commission for the Musée Rodin — an artist's process and craft, in Impression Originale's series. |
| 6448 | workshop-miriam-fitzgerald-juskova | Meet self-taught paper artist Miriam Fitzgerald Juskova — intuitive paper art from Slovakia to Ireland, in Impression Originale's workshop series. |
| 6351 | workshop-pippa-dyrlaga | Meet Yorkshire papercut artist Pippa Dyrlaga — contemporary artworks cut from single sheets of paper, in Impression Originale's workshop series. |
| 5695 | workshop-sarah-matthews | Meet UK paper engineer Sarah Matthews — pop-up paper engineering, craft and inspiration, in Impression Originale's workshop series. |
| 3656 | decipher-quadrichromia-printing-process | Decipher quadrichromia (CMYK) — the origin and four-colour process behind fine printing, in Impression Originale's behind-the-scenes series. |

## Apply / rollback

```php
// wp eval-file - (piped over SSH). $m = [ id => description, ... ] from the table above.
foreach ($m as $id => $desc) { update_post_meta($id, '_yoast_wpseo_metadesc', $desc); }
// rollback: delete_post_meta($id, '_yoast_wpseo_metadesc'); for the 17 IDs.
// then: wp cache flush; wp page-cache flush; rm -rf wp-content/cache/wp-rocket/*
```

## Verification (deterministic)

- `wp eval-file` reported `update_post_meta` success (new meta_id) for all 17; all lengths 127–153 (0 over 155).
- Live headless re-check (cache-buster, post-purge) on IDs 5984 / 4177 / 3656 / 6271 / 5695: `<meta name="description">` present and matching `og:description` on all sampled.
