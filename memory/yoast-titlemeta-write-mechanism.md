---
name: yoast-titlemeta-write-mechanism
description: "How to write/clear Yoast title & meta on live over WP-CLI — the render source is the indexable, and the naive save API fatals. Also: empty metadesc emits NO tag (Yoast >=14, no auto-generate)."
metadata: 
  node_type: memory
  type: reference
  originSessionId: 897559b3-e15c-4b00-a5c2-2b28b3dcddb4
---

Live Yoast title/meta edits on impressionoriginale.com (WP-CLI over SSH, [[wpe-ssh-slow-handshake]]).

**Render source = the Yoast indexable (`wp_yoast_indexable`), NOT postmeta.** Setting postmeta alone leaves a stale indexable rendering the old value. Must update/rebuild both.

**SET a value (title or metadesc)** — works:
```php
update_post_meta($id,"_yoast_wpseo_metadesc",$v);   // or _yoast_wpseo_title
$repo=YoastSEO()->classes->get(Yoast\WP\SEO\Repositories\Indexable_Repository::class);
$ind=$repo->find_by_id_and_type($id,"post"); if($ind){ $ind->description=$v; $ind->save(); }
```

**CLEAR a value (fall back to template)** — the naive `$ind->title=null; $ind->save();` **FATALS** ("critical error", uncatchable, bypasses try/catch). Correct path = delete postmeta + delete the indexable row, let `for_post` auto-rebuild from template:
```php
delete_post_meta($id,"_yoast_wpseo_title");
$GLOBALS["wpdb"]->delete($GLOBALS["wpdb"]->prefix."yoast_indexable",["object_id"=>(int)$id,"object_type"=>"post"]);
YoastSEO()->meta->for_post($id);   // triggers rebuild; read ->title to verify
```

**Empty `metadesc-product` / `metadesc-post` templates emit NO `<meta name=description>` tag at all** — Yoast >=14 dropped content-based auto-generation. The earlier assumption "empty => auto-generates from excerpt" is WRONG. A product/post with empty metadesc has no description tag (Google then synthesizes).

**Yoast title templates are a single shared value across EN+FR** — WPML String Translation is NOT configured for `wpseo_titles` (`icl_strings` has no title-product rows). Editing `wpseo_titles` (e.g. `separator`, `title-product`) hits BOTH languages. Per-object overrides (postmeta+indexable) are per-post, so safe to edit one language without touching the other.

**Verify externally via Playwright, not curl** — Cloudflare 403s scripted curl on `/fr/` and product pages ([[sandbox-kills-headless-chrome]]). Presenter `for_post()->title/description` is the origin truth; Playwright confirms the CDN-served HTML. Purge after (RULE 15, [[wpe-cdn-purge-after-change]]).

Applied 2026-07-08: 20 FR metas written; separator flipped sc-dash->sc-pipe; 126 legacy 'I'-separator product titles + 1 FR blog English-leak title cleared. Rollback ids in session scratchpad `step5_rollback.json`.
