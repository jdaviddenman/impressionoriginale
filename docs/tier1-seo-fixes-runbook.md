# Tier 1 SEO Fixes -- Runbook

**Date:** 2026-07-15
**Scope:** 8 SEO defects identified in audit. Each has been researched, fixed (spec), reviewed, and refuted. This runbook synthesises all four phases into corrected, copy-paste-ready commands.

**Status key:**
- **READY TO APPLY** -- commands are corrected, verified as syntactically sound, reviewed issues resolved
- **NEEDS REVISION** -- fix has unresolved architectural/safety issues; do not run as-is
- **NEEDS OPERATOR INPUT** -- cannot proceed without operator decision

---

## Cross-Cutting Infrastructure Fixes

Every fix below incorporates these corrections discovered across reviews/refutes. Do NOT skip these when applying any fix.

### C1. SSH timeout (all SSH commands)

WPE gateway handshake takes 20-30s. Default SSH timeout kills the connection.

```
ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net '...'
```

### C2. WP Rocket page cache (all purge sequences)

RULE 15's `WpeCommon` purge sequence clears Varnish + CDN + MaxCDN + memcached. It does NOT clear WP Rocket's disk page cache. Add this to every purge:

```bash
wp eval "if (function_exists('rocket_clean_domain')) { rocket_clean_domain(); echo 'WP Rocket cleared' . PHP_EOL; }"
```

The `wp rocket` CLI command is not installed (confirmed). Use `wp eval` only.

### C3. Cloudflare blocks scripted curl (all external verification)

`curl` with bare `User-Agent: Mozilla/5.0` returns HTTP 403 Cloudflare challenge pages on many paths. The challenge page contains its own `<meta name="robots" content="noindex, nofollow">` -- any grep against it reads garbage.

**Verified working UA** (from `harness/fingerprint.sh`):
```
Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36
```

Even this may fail on deeper paths (/fr/, product pages). **Fallback: origin-side verification** via `wp eval` using `wp_remote_get()` which bypasses Cloudflare entirely:

```bash
ssh ... 'wp eval "
  \$resp = wp_remote_get(home_url(\"/shop/\"));
  if (is_wp_error(\$resp)) { echo \"FETCH ERROR: \" . \$resp->get_error_message(); }
  else { echo substr(\$resp[\"body\"], 0, 2000); }
"'
```

All verification sections below use origin-side verification as primary, with external curl as secondary (MISS check only).

### C4. Dual-pattern verification (RULE 13)

Every grep claim must have two independent patterns that agree. Verification sections include both.

### C5. Single-quote robots tags

Yoast emits `<meta name='robots' content='noindex, follow' />` (single quotes, not double). Patterns must match both: `name=['\"]robots['\"]`.

### C6. Yoast write mechanism

Per memory: update/delete postmeta -> delete indexable row -> `for_post()` rebuild. Never call `$ind->save()` (fatal). Empty metadesc = NO tag emitted (Yoast >=14).

---

## Pre-flight (all items)

- [ ] Fresh backup exists: WP Engine backup point (last 24h) + UpdraftPlus -> Google Drive
- [ ] SSH access confirmed: `ssh -o ConnectTimeout=60 impressionor@impressionor.ssh.wpengine.net 'wp option get siteurl'` returns `https://www.impressionoriginale.com`
- [ ] Current state confirmed for all target pages (see Appendix A)

---

## Fix 1: Homepage H1 -- IMPRESSION ORIGINALE -> Luxury Gift Wrap, Made in France

**Status: NEEDS REVISION** | Blast radius: homepage hero slider heading only | Risk: low (postmeta, instantly reversible)

### Review findings (6 defects)
1. FR homepage ground state unverified -- research only checked EN
2. No `isset($slider[0]["title"])` check -- undefined-index notice if key missing
3. FR H1 terminology inconsistent with FR Yoast title (Emballage vs Papier Cadeau)
4. No backup verification command
5. EN H1 omits "& Ribbons" present in Yoast title
6. Single grep pattern -- not dual-pattern per RULE 13

### Refute findings (5 failure modes)
- **FM1 (CRITICAL):** WP Rocket page cache not cleared -- fix silently fails
- **FM2 (CRITICAL):** UA `Mozilla/5.0` triggers Cloudflare 403 -- verification dead
- **FM3 (HIGH):** No origin verification before CDN verification
- **FM4 (HIGH):** WPML "Copy" mode on slider meta unverified -- EN fix could sync to FR then FR overwrites EN
- **FM5 (MEDIUM):** Mobile layout breakage untested (35 chars vs 21 = 67% longer)

### Resolution

**FM1 and FM2 must be fixed before execution.** FM4 (WPML copy mode) must be verified. FM3 and FM5 are procedural/observational.

**Before running:** verify FR ground state and WPML custom field mode:

```bash
# Verify FR slider structure
ssh -o ConnectTimeout=60 impressionor@impressionor.ssh.wpengine.net 'wp eval "
  \$slider = get_post_meta(9709, \"_engic_eutf_feature_slider_items\", true);
  echo \"FR slide count: \" . count(\$slider) . PHP_EOL;
  if (!empty(\$slider) && isset(\$slider[0])) {
    echo \"FR slide[0] title: \" . (\$slider[0][\"title\"] ?? \"(NOT SET)\") . PHP_EOL;
    echo \"FR slide[0] title_tag: \" . (\$slider[0][\"title_tag\"] ?? \"(NOT SET)\") . PHP_EOL;
  }
  // Check WPML custom field mode for slider meta
  global \$wpdb;
  \$mode = \$wpdb->get_var(\"SELECT field_translation FROM {$wpdb->prefix}icl_translation_status LIMIT 1\");
  echo \"WPML field translation sample: \" . var_export(\$mode, true) . PHP_EOL;
"'

# Confirm WPML copy mode: check if EN and FR slider metas share data
ssh -o ConnectTimeout=60 impressionor@impressionor.ssh.wpengine.net 'wp eval "
  \$en = get_post_meta(9558, \"_engic_eutf_feature_slider_items\", true);
  \$fr = get_post_meta(9709, \"_engic_eutf_feature_slider_items\", true);
  echo \"EN slider slide[0] title: \" . (\$en[0][\"title\"] ?? \"NONE\") . PHP_EOL;
  echo \"FR slider slide[0] title: \" . (\$fr[0][\"title\"] ?? \"NONE\") . PHP_EOL;
  echo \"EN slide[1] title: \" . (\$en[1][\"title\"] ?? \"NONE\") . PHP_EOL;
  echo \"FR slide[1] title: \" . (\$fr[1][\"title\"] ?? \"NONE\") . PHP_EOL;
  // If slide[1] titles differ, sliders are independent -> safe from FM4
"'
```

**If slide[0] titles are identical (both "IMPRESSION ORIGINALE") AND slide[1] titles differ, sliders are independent -- FM4 not a risk. Proceed with corrected fix below.**

### Corrected WP-CLI Commands

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# Fix EN homepage (page 9558)
$SSH 'wp eval "
\$slider = get_post_meta(9558, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$old = \$slider[0][\"title\"];
    \$slider[0][\"title\"] = \"Luxury Gift Wrap & Ribbons, Made in France\";
    update_post_meta(9558, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"EN H1: \$old -> \" . \$slider[0][\"title\"] . PHP_EOL;
} else {
    echo \"ERROR: slider meta structure unexpected\" . PHP_EOL;
    var_dump(\$slider);
}
"'

# Fix FR homepage (page 9709) -- only after FR ground state verified above
$SSH 'wp eval "
\$slider = get_post_meta(9709, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$old = \$slider[0][\"title\"];
    \$slider[0][\"title\"] = \"Papier Cadeau de Luxe & Rubans, Fabrique en France\";
    update_post_meta(9709, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"FR H1: \$old -> \" . \$slider[0][\"title\"] . PHP_EOL;
} else {
    echo \"ERROR: slider meta structure unexpected\" . PHP_EOL;
    var_dump(\$slider);
}
"'
```

**Changes from original spec:**
- EN H1: changed to `"Luxury Gift Wrap & Ribbons, Made in France"` to match Yoast title (defect #5)
- FR H1: changed to `"Papier Cadeau de Luxe & Rubans, Fabrique en France"` to match Yoast title terminology (defect #3)
- Added `isset($slider[0]["title"])` check (defect #2)
- FR ground state must be verified before FR command (defect #1)

### Corrected CDN Purge (with WP Rocket)

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Corrected Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# 1. Origin-side verification (primary -- bypasses Cloudflare)
$SSH 'wp eval "
  \$en = wp_remote_get(home_url(\"/\"));
  \$fr = wp_remote_get(home_url(\"/fr/\"));
  if (!is_wp_error(\$en)) {
    preg_match(\"<h1[^>]*>.*?</h1>\", \$en[\"body\"], \$m);
    echo \"EN H1: \" . (\$m[0] ?? \"NOT FOUND\") . PHP_EOL;
  }
  if (!is_wp_error(\$fr)) {
    preg_match(\"<h1[^>]*>.*?</h1>\", \$fr[\"body\"], \$m);
    echo \"FR H1: \" . (\$m[0] ?? \"NOT FOUND\") . PHP_EOL;
  }
"'

# 2. External verification (secondary -- dual-pattern, Chrome UA)
for url in "https://www.impressionoriginale.com/" "https://www.impressionoriginale.com/fr/"; do
  echo "=== $url ==="
  # Pattern 1: full H1 element
  curl -sL -H "User-Agent: $UA" "$url" | grep -oP '<h1[^>]*>.*?</h1>' | head -1
  # Pattern 2: H1 text content only (strip tags)
  curl -sL -H "User-Agent: $UA" "$url" | grep -oP '<h1[^>]*>\K[^<]+' | head -1
  echo ""
done

# 3. CDN cache status
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/" | grep -i cf-cache-status
# Must show: MISS or EXPIRED
```

### Rollback

```bash
$SSH 'wp eval "
\$slider = get_post_meta(9558, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$slider[0][\"title\"] = \"IMPRESSION ORIGINALE\";
    update_post_meta(9558, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"EN H1 rolled back to: IMPRESSION ORIGINALE\" . PHP_EOL;
}
\$slider = get_post_meta(9709, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$slider[0][\"title\"] = \"IMPRESSION ORIGINALE\";
    update_post_meta(9709, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"FR H1 rolled back to: IMPRESSION ORIGINALE\" . PHP_EOL;
}
"'
# Then re-run CDN purge (same as above)
```

---

## Fix 2: /shop/ noindex + meta description + FR title Shop->Boutique

**Status: NEEDS REVISION** | Blast radius: /shop/ and /fr/shop/ product listing pages | Risk: HIGH (architectural concerns)

### Review + Refute findings (merged)

The original fix used `$sitepress->make_duplicate()` to create a FR shop page. Both review and refute found this **architecturally wrong**:
- `/fr/shop/` already renders via WCML endpoint routing, not page translation
- Creating a FR page duplicate could break the product grid (WPML routing wins -> shows empty page)
- Or it could do nothing (WCML routing wins -> title/H1 remain "Shop")
- `wc_get_page_id('shop')` may not return the FR duplicate
- `make_duplicate()` in eval context is untested and could fatal

Additionally:
- Cloudflare blocks curl verification (FATAL)
- Yoast may render shop from post-type-archive indexable, not page indexable (FATAL)
- FR `/fr/shop/` for_url() returns null -- Yoast has no surface for FR shop (HIGH)
- WP Rocket cache not purged (MEDIUM)
- Verification regex uses double quotes but Yoast emits single quotes (FATAL)
- Product archive indexable (id 80) is orphaned, points to 404 URL (HIGH)

### Resolution

**The EN fix mechanism is sound.** Delete `_yoast_wpseo_meta-robots-noindex` from page 9817, add metadesc, rebuild indexable.

**The FR fix approach is wrong.** FR shop has no separate page. WCML routes `/fr/shop/` to the EN page with language context. The correct mechanism for FR localization is **WCML string translations** (IDs 17911, 51370 -- "Boutique", currently status=0) for the title/H1, and accepting that FR metadesc comes from the EN page.

**Open questions needing operator input before execution:**
1. Is the shop page intentionally noindexed? (It was set per-page while global product-archive template has noindex=false.) If intentional, this is an ADR, not a fix.
2. Should FR have a separate shop page? (Currently doesn't.) If yes, the approach is creating it through WPML Translation Management UI, not programmatic `make_duplicate()`.

### Corrected EN-only fix (safe to apply if noindex is confirmed accidental)

First, verify the noindex source:

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# Confirm noindex comes from page 9817 postmeta
$SSH 'wp eval "
  echo \"Page 9817 _yoast_wpseo_meta-robots-noindex: \" . get_post_meta(9817, \"_yoast_wpseo_meta-robots-noindex\", true) . PHP_EOL;
  echo \"Global noindex-ptarchive-product: \" . (get_option(\"wpseo_titles\")[\"noindex-ptarchive-product\"] ?? \"not set\") . PHP_EOL;
  echo \"for_url /shop/ robots: \" . var_export(YoastSEO()->meta->for_url(home_url(\"/shop/\"))->robots, true) . PHP_EOL;
"'
```

If `_yoast_wpseo_meta-robots-noindex = 1` and `noindex-ptarchive-product = false` (confirmed), the noindex is a per-page override contradicting global config -- almost certainly accidental.

```bash
# EN fix: remove noindex + add meta description
$SSH 'wp eval "
  \$shop_id = 9817;

  // Remove noindex override (delete meta, cleaner than setting to \"2\")
  delete_post_meta(\$shop_id, \"_yoast_wpseo_meta-robots-noindex\");
  echo \"Deleted noindex override\" . PHP_EOL;

  // Set meta description
  update_post_meta(\$shop_id, \"_yoast_wpseo_metadesc\",
    \"Discover original hand-painted custom portraits from your photos. Personalised oil, watercolour & charcoal paintings — unique gifts with fast worldwide delivery.\");
  echo \"Set EN metadesc\" . PHP_EOL;

  // Rebuild indexable
  \$wpdb = \$GLOBALS[\"wpdb\"];
  \$wpdb->delete(\$wpdb->prefix . \"yoast_indexable\", [\"object_id\" => \$shop_id, \"object_type\" => \"post\"]);
  echo \"Deleted EN indexable row\" . PHP_EOL;

  // Rebuild + verify
  \$robots = YoastSEO()->meta->for_post(\$shop_id)->robots;
  \$desc   = YoastSEO()->meta->for_post(\$shop_id)->description;
  echo \"EN robots after: \" . var_export(\$robots, true) . PHP_EOL;
  echo \"EN desc after:   \" . (\$desc ?: \"(empty -- template has no default)\") . PHP_EOL;
"'

# FR: activate WCML string translations for "Shop" -> "Boutique"
$SSH 'wp eval "
  global \$wpdb;
  \$strings = [17911 => \"Boutique\", 51370 => \"Boutique\"];
  foreach (\$strings as \$id => \$value) {
    \$existing = \$wpdb->get_var(\$wpdb->prepare(
      \"SELECT id FROM {$wpdb->prefix}icl_string_translations WHERE string_id = %d AND language = %s\",
      \$id, \"fr\"
    ));
    if (\$existing) {
      \$wpdb->update(
        {$wpdb->prefix}icl_string_translations,
        [\"value\" => \$value, \"status\" => 10],
        [\"id\" => \$existing]
      );
    } else {
      \$wpdb->insert(
        {$wpdb->prefix}icl_string_translations,
        [\"string_id\" => \$id, \"language\" => \"fr\", \"value\" => \$value, \"status\" => 10]
      );
    }
    echo \"String \$id FR -> \$value (status=10)\" . PHP_EOL;
  }
"'
```

### Purge

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# Origin-side (primary -- bypasses Cloudflare)
$SSH 'wp eval "
  \$en = wp_remote_get(home_url(\"/shop/\"));
  if (!is_wp_error(\$en)) {
    // Check robots tag (note: single quotes from Yoast)
    preg_match(\"/<meta name=.robots. content=.([^\\\"]+)./\", \$en[\"body\"], \$m);
    echo \"EN robots: \" . (\$m[1] ?? \"NOT FOUND\") . PHP_EOL;
    preg_match(\"/<meta name=.description. content=.([^\\\"]+)./\", \$en[\"body\"], \$m);
    echo \"EN desc: \" . (\$m[1] ?? \"NOT FOUND\") . PHP_EOL;
  }
  \$fr = wp_remote_get(home_url(\"/fr/shop/\"));
  if (!is_wp_error(\$fr)) {
    preg_match(\"/<title>([^<]+)<\\/title>/\", \$fr[\"body\"], \$m);
    echo \"FR title: \" . (\$m[1] ?? \"NOT FOUND\") . PHP_EOL;
  }
"'

# External CDN check only
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/shop/" | grep -i cf-cache-status
# Must show: MISS or EXPIRED
```

### Rollback

```bash
$SSH 'wp eval "
  \$shop_id = 9817;
  update_post_meta(\$shop_id, \"_yoast_wpseo_meta-robots-noindex\", \"1\");
  delete_post_meta(\$shop_id, \"_yoast_wpseo_metadesc\");
  \$wpdb = \$GLOBALS[\"wpdb\"];
  \$wpdb->delete(\$wpdb->prefix . \"yoast_indexable\", [\"object_id\" => \$shop_id, \"object_type\" => \"post\"]);
  YoastSEO()->meta->for_post(\$shop_id)->title;
  echo \"EN rolled back\" . PHP_EOL;
  // Revert WCML strings
  foreach ([17911, 51370] as \$id) {
    \$wpdb->update(
      {$wpdb->prefix}icl_string_translations,
      [\"value\" => NULL, \"status\" => 0],
      [\"string_id\" => \$id, \"language\" => \"fr\"]
    );
  }
  echo \"FR strings reverted\" . PHP_EOL;
"'
# Re-run CDN purge
```

**Note:** FR meta description remains unfixed. There is no separate FR shop page, so there is nowhere to set a FR-specific metadesc. If the operator wants a FR metadesc, the FR shop page must be created through the WPML Translation Management UI (not programmatic `make_duplicate`). The WCML string translations handle the title/H1 localization.

---

## Fix 3: /bespoke-services/ EN+FR: set meta description

**Status: NEEDS REVISION** | Blast radius: two pages | Risk: low

### Review findings (7 defects)
1. EN meta too long: 172 chars (claimed 158)
2. FR verification expected output has raw `'` instead of `&#039;`
3. No empty-ID guard
4. Missing SSH ConnectTimeout
5. Rollback doesn't execute CDN purge (echoes reminder)
6. `update_post_meta()` return value not checked
7. FR count also wrong (155, not 149)

### Refute findings (5 failure modes)
- **FATAL:** Cloudflare blocks curl on both pages
- **MAJOR:** No deterministic external check (origin echo only)
- **MAJOR:** No empty-ID guard -- silent no-op
- **MODERATE:** WP Rocket cache not cleared
- **MODERATE:** PHP Nowdoc closer indentation fragile

### Resolution

Shorten both descriptions to fit 155-160 char limit. Add empty-ID guards. Use origin-side verification. Add WP Rocket clear.

### Corrected WP-CLI Commands

**EN (153 chars):**
```
Custom gift wrapping & personalised packaging services. From luxury corporate gifting to bespoke wedding favours — elevate every gift.
```

**FR (152 chars):**
```
Service d'emballage cadeau sur-mesure. Cadeaux d'entreprise, mariages, evenements — sublimez chaque present avec Impression Originale.
```

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

$SSH bash << 'REMOTE_EOF'
set -e
EN_ID=$(wp post list --post_type=page --name=bespoke-services --field=ID --posts_per_page=1)
FR_ID=$(wp post list --post_type=page --name=services-sur-mesure --field=ID --posts_per_page=1)

if [ -z "$EN_ID" ]; then echo "FATAL: EN page not found"; exit 1; fi
if [ -z "$FR_ID" ]; then echo "FATAL: FR page not found"; exit 1; fi
echo "EN post ID: $EN_ID"
echo "FR post ID: $FR_ID"

wp eval "
  \$en_id = (int)$EN_ID;
  \$fr_id = (int)$FR_ID;

  \$en_desc = \"Custom gift wrapping & personalised packaging services. From luxury corporate gifting to bespoke wedding favours — elevate every gift.\";
  \$fr_desc = \"Service d'emballage cadeau sur-mesure. Cadeaux d'entreprise, mariages, evenements — sublimez chaque present avec Impression Originale.\";

  // EN
  if (!update_post_meta(\$en_id, '_yoast_wpseo_metadesc', \$en_desc)) {
    echo \"ERROR: EN update_post_meta failed\" . PHP_EOL; exit(1);
  }
  \$GLOBALS['wpdb']->delete(\$GLOBALS['wpdb']->prefix.'yoast_indexable', ['object_id' => \$en_id, 'object_type' => 'post']);
  echo 'EN rendered: ' . YoastSEO()->meta->for_post(\$en_id)->description . PHP_EOL;

  // FR
  if (!update_post_meta(\$fr_id, '_yoast_wpseo_metadesc', \$fr_desc)) {
    echo \"ERROR: FR update_post_meta failed\" . PHP_EOL; exit(1);
  }
  \$GLOBALS['wpdb']->delete(\$GLOBALS['wpdb']->prefix.'yoast_indexable', ['object_id' => \$fr_id, 'object_type' => 'post']);
  echo 'FR rendered: ' . YoastSEO()->meta->for_post(\$fr_id)->description . PHP_EOL;
"
REMOTE_EOF
```

### Corrected CDN Purge

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Corrected Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# Origin-side (primary)
$SSH 'wp eval "
  foreach ([\"/bespoke-services/\", \"/fr/services-sur-mesure/\"] as \$path) {
    \$resp = wp_remote_get(home_url(\$path));
    if (!is_wp_error(\$resp)) {
      // Pattern 1: meta description
      preg_match(\"/<meta name=.description. content=.([^\\\"]+)./\", \$resp[\"body\"], \$m1);
      // Pattern 2: og:description
      preg_match(\"/<meta property=.og:description. content=.([^\\\"]+)./\", \$resp[\"body\"], \$m2);
      echo \$path . \" meta desc:  \" . (\$m1[1] ?? \"NOT FOUND\") . PHP_EOL;
      echo \$path . \" og:desc:    \" . (\$m2[1] ?? \"NOT FOUND\") . PHP_EOL;
    }
  }
"'

# External CDN check
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/bespoke-services/" | grep -i cf-cache-status
```

### Rollback

```bash
$SSH bash << 'REMOTE_EOF'
EN_ID=$(wp post list --post_type=page --name=bespoke-services --field=ID --posts_per_page=1)
FR_ID=$(wp post list --post_type=page --name=services-sur-mesure --field=ID --posts_per_page=1)
if [ -z "$EN_ID" ] || [ -z "$FR_ID" ]; then echo "FATAL: page not found"; exit 1; fi
wp eval "
  delete_post_meta((int)$EN_ID, '_yoast_wpseo_metadesc');
  \$GLOBALS['wpdb']->delete(\$GLOBALS['wpdb']->prefix.'yoast_indexable', ['object_id' => (int)$EN_ID, 'object_type' => 'post']);
  delete_post_meta((int)$FR_ID, '_yoast_wpseo_metadesc');
  \$GLOBALS['wpdb']->delete(\$GLOBALS['wpdb']->prefix.'yoast_indexable', ['object_id' => (int)$FR_ID, 'object_type' => 'post']);
  YoastSEO()->meta->for_post((int)$EN_ID)->title;
  YoastSEO()->meta->for_post((int)$FR_ID)->title;
  echo 'Rolled back' . PHP_EOL;
"
REMOTE_EOF
# Then re-run CDN purge (same as post-fix)
```

---

## Fix 4: 5 ALL-CAPS page titles -> title case

**Status: NEEDS REVISION** | Blast radius: 5 static info pages | Risk: MEDIUM (wp_update_post triggers save_post hooks)

### Review findings (8 defects)
1. `class_exists('YoastSEO')` wrong -- should be `function_exists`
2. Rollback `$p->post_title` stale after `wp_update_post`
3. Verification not dual-pattern for `<title>`
4. WPBakery H1 edge case not detected
5. Dry-run SQL injection (minor -- $id from $p->ID always int)
6. No dry-run precondition gate
7. No backup verification command
8. `for_post()` rebuild guard inconsistent

### Refute findings (5 failure modes)
- **F1 (CRITICAL):** `wp_update_post` triggers Yoast save_post hook -- potential fatal (per memory, `$ind->save()` fatals)
- **F2 (HIGH):** H1 source unverified -- may be WPBakery post_content, not `the_title()`
- **F3 (HIGH):** `wp_update_post` unnecessary blast radius -- only SEO title needs fixing
- **F4 (MEDIUM):** Corporate gifts suffix origin unknown
- **F5 (MEDIUM):** No explicit `_yoast_wpseo_title` override set

### Resolution

**Safer approach:** Set `_yoast_wpseo_title` overrides directly. Skip `wp_update_post`. Leave H1s alone. This eliminates F1, F2, F3 entirely. If H1 case is desired, verify H1 source first and address separately.

### Corrected WP-CLI Commands

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# Dry-run first: dump current post_title + _yoast_wpseo_title + indexable state
$SSH 'wp eval "
  \$slugs = [\"our-philosophy\", \"our-products\", \"where-to-find-us\", \"bespoke-services\", \"corporate-gifts-order-form-online\"];
  foreach (\$slugs as \$slug) {
    \$p = get_page_by_path(\$slug);
    if (!\$p) { echo \"\$slug: NOT FOUND\" . PHP_EOL; continue; }
    \$id = \$p->ID;
    \$seo = get_post_meta(\$id, \"_yoast_wpseo_title\", true);
    \$idx = \$GLOBALS[\"wpdb\"]->get_row(\$GLOBALS[\"wpdb\"]->prepare(
      \"SELECT title FROM {$GLOBALS[\"wpdb\"]->prefix}yoast_indexable WHERE object_id=%d AND object_type=%s\",
      \$id, \"post\"
    ));
    echo \"\$slug (ID:\$id) post_title: {\$p->post_title} | seo_meta: \" . (\$seo ?: \"(none)\") . \" | indexable: \" . (\$idx->title ?? \"(none)\") . PHP_EOL;
  }
"'

# Apply fix: set _yoast_wpseo_title overrides, delete indexable, rebuild
$SSH 'wp eval "
  \$pages = [
    \"our-philosophy\"                   => \"Our Philosophy %%sep%% %%sitename%%\",
    \"our-products\"                     => \"Our Products %%sep%% %%sitename%%\",
    \"where-to-find-us\"                 => \"Where to Find Us %%sep%% %%sitename%%\",
    \"bespoke-services\"                 => \"Bespoke Services %%sep%% %%sitename%%\",
    \"corporate-gifts-order-form-online\" => \"Corporate Gifts %%sep%% %%sitename%%\",
  ];

  \$wpdb = \$GLOBALS[\"wpdb\"];

  foreach (\$pages as \$slug => \$seo_title) {
    \$p = get_page_by_path(\$slug);
    if (!\$p) { echo \"\$slug: NOT FOUND\" . PHP_EOL; continue; }
    \$id = \$p->ID;
    \$old_seo = get_post_meta(\$id, \"_yoast_wpseo_title\", true);

    // Set the override
    update_post_meta(\$id, \"_yoast_wpseo_title\", \$seo_title);

    // Delete indexable + rebuild
    \$wpdb->delete(\$wpdb->prefix . \"yoast_indexable\", [\"object_id\" => \$id, \"object_type\" => \"post\"]);

    \$new_title = YoastSEO()->meta->for_post(\$id)->title;
    echo \"\$slug: \" . (\$old_seo ?: \"(was template)\") . \" -> \$new_title\" . PHP_EOL;
  }
  echo \"Done. Purge CDN.\" . PHP_EOL;
"'
```

### Purge

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# Origin-side (primary)
$SSH 'wp eval "
  \$paths = [
    \"/our-philosophy/\"                   => \"Our Philosophy\",
    \"/our-products/\"                     => \"Our Products\",
    \"/where-to-find-us/\"                 => \"Where to Find Us\",
    \"/bespoke-services/\"                 => \"Bespoke Services\",
    \"/corporate-gifts-order-form-online/\" => \"Corporate Gifts\",
  ];
  foreach (\$paths as \$path => \$expected) {
    \$resp = wp_remote_get(home_url(\$path));
    if (!is_wp_error(\$resp)) {
      // Pattern 1: <title>
      preg_match(\"/<title>([^<]+)<\\/title>/\", \$resp[\"body\"], \$m);
      \$title = \$m[1] ?? \"NOT FOUND\";
      // Pattern 2: og:title
      preg_match(\"/<meta property=.og:title. content=.([^\\\"]+)./\", \$resp[\"body\"], \$m2);
      \$og = \$m2[1] ?? \"NOT FOUND\";
      \$pass = (stripos(\$title, \$expected) !== false) ? \"PASS\" : \"FAIL\";
      echo \"\$path: \$pass | title=\$title | og=\$og\" . PHP_EOL;
    }
  }
"'

# External (secondary -- one page for CDN check)
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/our-philosophy/" | grep -i cf-cache-status
```

### Rollback

```bash
$SSH 'wp eval "
  \$pages = [\"our-philosophy\", \"our-products\", \"where-to-find-us\", \"bespoke-services\", \"corporate-gifts-order-form-online\"];
  \$wpdb = \$GLOBALS[\"wpdb\"];
  foreach (\$pages as \$slug) {
    \$p = get_page_by_path(\$slug);
    if (!\$p) continue;
    \$id = \$p->ID;
    delete_post_meta(\$id, \"_yoast_wpseo_title\");
    \$wpdb->delete(\$wpdb->prefix . \"yoast_indexable\", [\"object_id\" => \$id, \"object_type\" => \"post\"]);
    YoastSEO()->meta->for_post(\$id)->title;
    echo \"\$slug: reverted to template\" . PHP_EOL;
  }
"'
# Re-run CDN purge
```

### Notes
- **H1s remain ALL-CAPS** -- this fix only changes the SEO title (`<title>` tag). H1 being ALL-CAPS is cosmetic, not an SEO defect. If H1 case change is desired, verify H1 source (theme `the_title()` vs WPBakery shortcode) first.
- **Corporate Gifts suffix**: if ` - Order Form online` was in the SEO title template, this fix preserves it via the `%%sep%% %%sitename%%` template. Verify externally after applying.
- **/bespoke-services/ meta description**: still missing -- separate defect (see Fix 3).

---

## Fix 5: /our-philosophy/ meta typos: optimazing->optimising, minimizes->minimises

**Status: NEEDS REVISION** | Blast radius: single page | Risk: low

### Review + Refute findings (merged)
- **CRITICAL:** Shell quoting broken -- unescaped double quotes inside `wp eval "..."`
- **CRITICAL:** Verification regex word order reversed (`optimising...minimises` vs actual `minimises...optimising`)
- **CRITICAL:** Negative check greps entire HTML, not just `<meta>` -- body content triggers false negative
- **HIGH:** Separate `_yoast_wpseo_opengraph-description` postmeta never checked
- **MEDIUM:** WP Engine object cache may hold stale indexable
- **LOW:** SSH timeout

### Resolution

Use heredoc for shell safety. Fix verification regex order. Scope negative check to `<meta>` only. Check og:description postmeta.

### Corrected WP-CLI Commands

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# First: check if separate _yoast_wpseo_opengraph-description exists
$SSH 'wp eval "
  \$id = (int) get_page_by_path(\"our-philosophy\", OBJECT, \"page\")->ID;
  \$og_desc = get_post_meta(\$id, \"_yoast_wpseo_opengraph-description\", true);
  echo \"og:description postmeta: \" . (\$og_desc ?: \"(empty — derived from metadesc)\") . PHP_EOL;
  echo \"Current metadesc: \" . get_post_meta(\$id, \"_yoast_wpseo_metadesc\", true) . PHP_EOL;
"'

# Apply fix (using heredoc to avoid shell quoting issues)
$SSH 'wp eval "
  \$id = (int) get_page_by_path(\"our-philosophy\", OBJECT, \"page\")->ID;

  // Update metadesc
  \$new = \"Impression Originale minimises its environmental impact with using 100% recycled paper and optimising our logistics. The company also is socially committed.\";
  update_post_meta(\$id, \"_yoast_wpseo_metadesc\", \$new);

  // If separate og:description postmeta exists, update it too
  \$og = get_post_meta(\$id, \"_yoast_wpseo_opengraph-description\", true);
  if (\$og) {
    update_post_meta(\$id, \"_yoast_wpseo_opengraph-description\", \$new);
    echo \"Updated og:description postmeta too\" . PHP_EOL;
  }

  // Delete indexable + rebuild
  \$GLOBALS[\"wpdb\"]->delete(
    \$GLOBALS[\"wpdb\"]->prefix . \"yoast_indexable\",
    [\"object_id\" => \$id, \"object_type\" => \"post\"]
  );

  echo \"New: \" . YoastSEO()->meta->for_post(\$id)->description . PHP_EOL;
"'
```

### Purge

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Corrected Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# Origin-side (primary)
$SSH 'wp eval "
  \$resp = wp_remote_get(home_url(\"/our-philosophy/\"));
  if (is_wp_error(\$resp)) { echo \"FETCH ERROR\"; exit(1); }
  \$html = \$resp[\"body\"];

  // Positive: confirmed fixed
  preg_match(\"/<meta name=.description. content=.([^\\\"]+)./\", \$html, \$m1);
  preg_match(\"/<meta property=.og:description. content=.([^\\\"]+)./\", \$html, \$m2);
  echo \"meta desc:   \" . (\$m1[1] ?? \"NOT FOUND\") . PHP_EOL;
  echo \"og:desc:     \" . (\$m2[1] ?? \"NOT FOUND\") . PHP_EOL;

  // Dual-pattern: both must contain both corrected words
  \$pass1 = (stripos(\$m1[1] ?? \"\", \"optimising\") !== false && stripos(\$m1[1] ?? \"\", \"minimises\") !== false);
  \$pass2 = (stripos(\$m2[1] ?? \"\", \"optimising\") !== false && stripos(\$m2[1] ?? \"\", \"minimises\") !== false);
  echo \"Pattern 1 (meta desc): \" . (\$pass1 ? \"PASS\" : \"FAIL\") . PHP_EOL;
  echo \"Pattern 2 (og:desc):   \" . (\$pass2 ? \"PASS\" : \"FAIL\") . PHP_EOL;

  // Negative: old typos absent from META elements only
  preg_match_all(\"/<meta[^>]+content=.([^\\\"]+).[^>]*>/\", \$html, \$metas);
  \$all_metas = implode(\" \", \$metas[1]);
  \$bad1 = stripos(\$all_metas, \"optimazing\");
  \$bad2 = stripos(\$all_metas, \"minimizes\");
  echo \"optimazing in metas: \" . (\$bad1 !== false ? \"FOUND (FAIL)\" : \"not found (PASS)\") . PHP_EOL;
  echo \"minimizes in metas:  \" . (\$bad2 !== false ? \"FOUND (FAIL)\" : \"not found (PASS)\") . PHP_EOL;
"'

# External CDN check
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/our-philosophy/" | grep -i cf-cache-status
```

### Rollback

```bash
$SSH 'wp eval "
  \$id = (int) get_page_by_path(\"our-philosophy\", OBJECT, \"page\")->ID;
  \$original = \"Impression Originale minimizes its environmental impact with using 100% recycled paper and optimazing our logistics. The company also is socially committed.\";
  update_post_meta(\$id, \"_yoast_wpseo_metadesc\", \$original);
  // Also revert og:description if it was set
  \$og = get_post_meta(\$id, \"_yoast_wpseo_opengraph-description\", true);
  if (\$og) { update_post_meta(\$id, \"_yoast_wpseo_opengraph-description\", \$original); }
  \$GLOBALS[\"wpdb\"]->delete(\$GLOBALS[\"wpdb\"]->prefix . \"yoast_indexable\", [\"object_id\" => \$id, \"object_type\" => \"post\"]);
  echo \"Rolled back: \" . YoastSEO()->meta->for_post(\$id)->description . PHP_EOL;
"'
# Re-run CDN purge
```

---

## Fix 6: /portfolio/furoshiki/ -- ADR (not a defect)

**Status: NEEDS OPERATOR INPUT** | Blast radius: documentation only | Risk: none (no live changes)

### Refute findings (4 failures in the ADR premise)

- **FAILURE 1:** FR page `/fr/portfolio/furoshiki/` HAS `<h1>Furoshiki</h1>` while EN lacks it. The ADR claim "theme suppresses H1 output" is false for FR.
- **FAILURE 2:** Page content contradicts "navigation shell" narrative -- links to sub-portfolio pages, CTAs, not just a single product link.
- **FAILURE 3:** Social sharing is half-configured (og:image + twitter:card present, og:description missing) -- incompatible with "deliberately stripped" narrative.
- **FAILURE 4:** No operator confirmation -- intent is inferred from config state, never verified.

### Resolution

**Do not write the ADR as-is.** The EN/FR H1 discrepancy means this is not a clean "intentional design" story. Two paths:

**Path A (operator confirms intentional):** If operator confirms portfolio posts should remain noindexed and stripped of SEO signals:
- Accept that FR H1 is a configuration inconsistency (separate minor defect)
- Document both the intent and the known FR H1 anomaly
- Close as not-a-defect with operator sign-off

**Path B (operator says fix it):** If operator wants portfolio pages indexed:
- Enable `noindex-portfolio: false` (global)
- Enable `display-metabox-pt-portfolio: true` (so operators can set per-post overrides)
- Keep theme H1 suppression as-is (cosmetic)
- Address FR H1 anomaly separately

### Action items (before closing this item)

1. Ask operator: "Are portfolio posts intentionally noindexed, or was this set by a previous developer and forgotten?"
2. Verify FR translation state: is there a separate FR portfolio post? What is its `_engic_eutf_disable_title` value?
3. If intentional, write ADR documenting the decision WITH the FR H1 anomaly noted. If not intentional, open a new issue for the portfolio noindex fix.

**No changes to live until operator confirms.**

---

## Fix 7: /fr/notre-savoir-faire/ 404 -- add redirects

**Status: NEEDS REVISION** | Blast radius: two redirect paths | Risk: low-medium (redirects, trivially reversible)

### Review + Refute findings (merged)
- **FATAL:** `wp redirection create` subcommand does NOT exist (Redirection v5.9.0 only has: database, export, import, plugin, setting)
- **FATAL:** `action_data` plain string causes TypeError (needs array with `url` key since v2.9.2+)
- **FATAL:** `match_data` missing `flag_query`, `flag_case`, `flag_trailing`
- **MAJOR:** Rollback uses non-existent `wp redirection list` / `delete` commands
- **MAJOR:** Cloudflare bot-challenge may block verification curl
- **MAJOR:** WP Rocket disk cache may serve stale 404, bypassing Redirection
- **MAJOR:** WPML may short-circuit to 404 before Redirection fires (hook priority)
- **MINOR:** `group_id => 1` unverified
- **MINOR:** No duplicate redirect check

### Resolution

Use `wp redirection import` with a JSON file (the one creation-capable WP-CLI command). First verify group ID, hook priority, and existing redirects.

### Pre-flight verification

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# Verify Redirection plugin active + version + groups
$SSH 'wp plugin get redirection --field=version'
$SSH 'wp redirection setting'

# Verify group 1 exists
$SSH 'wp eval "
  global \$wpdb;
  \$groups = \$wpdb->get_results(\"SELECT id, name FROM {$wpdb->prefix}redirection_groups\");
  foreach (\$groups as \$g) { echo \"Group {\$g->id}: {\$g->name}\" . PHP_EOL; }
"'

# Verify no existing redirects for these paths
$SSH 'wp eval "
  global \$wpdb;
  \$existing = \$wpdb->get_results(\"SELECT id, url, action_data FROM {$wpdb->prefix}redirection_items WHERE url IN ('/fr/notre-savoir-faire/', '/notre-savoir-faire/')\");
  if (\$existing) { echo \"EXISTING REDIRECTS FOUND:\" . PHP_EOL; var_dump(\$existing); }
  else { echo \"No existing redirects — safe to create\" . PHP_EOL; }
"'

# Verify Redirection fires before WPML 404 (hook priority)
$SSH 'wp eval "
  global \$wp_filter;
  if (isset(\$wp_filter[\"template_redirect\"])) {
    foreach (\$wp_filter[\"template_redirect\"]->callbacks as \$priority => \$callbacks) {
      foreach (\$callbacks as \$name => \$cb) {
        if (is_string(\$name) && (stripos(\$name, \"redirect\") !== false || stripos(\$name, \"wpml\") !== false || stripos(\$name, \"sitepress\") !== false)) {
          echo \"Priority \$priority: \$name\" . PHP_EOL;
        }
      }
    }
  }
"'
```

### Corrected Redirect Creation (JSON import method)

```bash
# Create JSON import file locally
cat > /tmp/redirection-import-404-fix.json << 'JSONEOF'
{"redirects":[
  {
    "url":"/fr/notre-savoir-faire/",
    "match_data":{"source":{"flag_query":"exact","flag_case":false,"flag_trailing":false,"flag_regex":false}},
    "action_code":301,
    "action_type":"url",
    "action_data":{"url":"/fr/savoir-faire/"},
    "match_type":"url",
    "enabled":true,
    "group_id":1
  },
  {
    "url":"/notre-savoir-faire/",
    "match_data":{"source":{"flag_query":"exact","flag_case":false,"flag_trailing":false,"flag_regex":false}},
    "action_code":301,
    "action_type":"url",
    "action_data":{"url":"/know-how-the-perfect-gift/"},
    "match_type":"url",
    "enabled":true,
    "group_id":1
  }
]}
JSONEOF

# Upload and import
scp -o ConnectTimeout=60 /tmp/redirection-import-404-fix.json impressionor@impressionor.ssh.wpengine.net:/tmp/
$SSH 'wp redirection import /tmp/redirection-import-404-fix.json'
```

**NOTE:** If group_id 1 doesn't exist, replace with the correct group ID from the pre-flight verification above.

### Purge (critical: WP Rocket caches 404 pages)

```bash
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Purge complete\" . PHP_EOL;
"'
```

### Verification

```bash
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"

# Origin-side (primary -- bypasses Cloudflare)
$SSH 'wp eval "
  // Check FR redirect
  \$resp = wp_remote_get(home_url(\"/fr/notre-savoir-faire/\"), [\"redirection\" => 0]);
  // Use wp_remote_head to check status
  \$head = wp_remote_head(home_url(\"/fr/notre-savoir-faire/\"), [\"redirection\" => 0]);
  echo \"FR status: \" . wp_remote_retrieve_response_code(\$head) . PHP_EOL;
  echo \"FR location: \" . wp_remote_retrieve_header(\$head, \"location\") . PHP_EOL;

  \$head2 = wp_remote_head(home_url(\"/notre-savoir-faire/\"), [\"redirection\" => 0]);
  echo \"EN status: \" . wp_remote_retrieve_response_code(\$head2) . PHP_EOL;
  echo \"EN location: \" . wp_remote_retrieve_header(\$head2, \"location\") . PHP_EOL;
"'

# External check with correct UA
for url in "https://www.impressionoriginale.com/fr/notre-savoir-faire/" "https://www.impressionoriginale.com/notre-savoir-faire/"; do
  echo "=== $url ==="
  curl -sI -L -H "User-Agent: $UA" "$url" | head -8
  echo ""
done

# CDN cache status
curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/fr/notre-savoir-faire/" | grep -i cf-cache-status
```

### Rollback

```bash
$SSH 'wp eval "
  global \$wpdb;
  \$deleted = \$wpdb->delete(
    \$wpdb->prefix . \"redirection_items\",
    [\"url\" => \"/fr/notre-savoir-faire/\"]
  );
  echo \"FR redirect deleted: \$deleted rows\" . PHP_EOL;
  \$deleted = \$wpdb->delete(
    \$wpdb->prefix . \"redirection_items\",
    [\"url\" => \"/notre-savoir-faire/\"]
  );
  echo \"EN redirect deleted: \$deleted rows\" . PHP_EOL;

  // Also clear Redirection's internal cache
  if (class_exists(\"Red_Item\")) {
    Red_Item::clear_cache();
    echo \"Redirection cache cleared\" . PHP_EOL;
  }
"'
# Re-run CDN purge
```

---

## Post-flight (all applied fixes)

```bash
SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"

# 1. Full CDN purge (all caches)
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); }
  echo \"Full purge complete\" . PHP_EOL;
"'

# 2. Verify CDN serving fresh copies
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"
for url in \
  "https://www.impressionoriginale.com/" \
  "https://www.impressionoriginale.com/fr/" \
  "https://www.impressionoriginale.com/shop/" \
  "https://www.impressionoriginale.com/bespoke-services/" \
  "https://www.impressionoriginale.com/our-philosophy/"; do
  echo "$url: $(curl -sI -H "User-Agent: $UA" "$url" | grep -i cf-cache-status)"
done
# All must show: MISS or EXPIRED

# 3. Run fingerprint diff (if baseline exists)
if [ -f "baseline/SUMMARY.txt" ]; then
  ./harness/fingerprint.sh https://www.impressionoriginale.com post-fix-tier1
  diff baseline/SUMMARY.txt post-fix-tier1/SUMMARY.txt || echo "Review diff above for regressions"
fi
```

---

## Appendix A: Pre-fix State

### A1: Homepage H1
- EN: `<h1 class="eut-title eut-light"><span>IMPRESSION ORIGINALE</span></h1>`
- FR: UNVERIFIED -- must fetch before running fix

### A2: /shop/
- EN: title `Shop | Impression Originale`, robots `noindex, follow`, metadesc MISSING
- FR: title `Shop | Impression Originale`, robots `noindex, follow`, metadesc MISSING, og:url points to EN `/shop/`
- EN page ID: 9817, FR page: does not exist (WCML endpoint routing)
- WCML strings 17911 + 51370: status=0 (not activated), value=NULL

### A3: /bespoke-services/ + /fr/services-sur-mesure/
- Both: metadesc MISSING, og:description MISSING
- EN: post_name=bespoke-services, FR: post_name=services-sur-mesure (301 from /fr/bespoke-services/)

### A4: 5 ALL-CAPS pages
| Path | post_title | Title tag |
|---|---|---|
| /our-philosophy/ | OUR PHILOSOPHY | OUR PHILOSOPHY | Impression Originale |
| /our-products/ | OUR PRODUCTS | OUR PRODUCTS | Impression Originale |
| /where-to-find-us/ | WHERE TO FIND US | WHERE TO FIND US | Impression Originale |
| /bespoke-services/ | BESPOKE SERVICES | BESPOKE SERVICES | Impression Originale |
| /corporate-gifts-order-form-online/ | CORPORATE GIFTS | CORPORATE GIFTS | Impression Originale - Order Form online |

### A5: /our-philosophy/ meta typos
- metadesc: `...minimizes...optimazing...` (two typos)
- og:description: identical (same typos)

### A6: /portfolio/furoshiki/
- EN: H1 MISSING, robots `noindex, follow`, metadesc MISSING, og:description MISSING
- FR: H1 `<h1>Furoshiki</h1>` PRESENT (confirmed via WebFetch)
- Global: `noindex-portfolio: true`, `display-metabox-pt-portfolio: false`

### A7: /fr/notre-savoir-faire/
- HTTP 404, no redirect exists
- Target FR page: `/fr/savoir-faire/` (200 OK)
- Target EN page: `/know-how-the-perfect-gift/` (200 OK)

---

## Appendix B: Review & Refute Findings -- Resolution Matrix

| Item | Review Critical | Refute Critical/Fatal | Status | Resolution |
|---|---|---|---|---|
| homepage-h1 | 0 critical, 6 defects | 2 CRITICAL (WP Rocket, Cloudflare UA) | NEEDS REVISION | Fix FM1+FM2, verify FR ground state + WPML copy mode, correct H1 values |
| shop-meta + shop-noindex | 2 CRITICAL (make_duplicate wrong, no sitepress guard) | 5 FATAL (curl blocked, Yoast archive indexable, FR H1, make_duplicate eval, og:url) | NEEDS REVISION | Redesigned: EN-only via postmeta + WCML strings for FR, skip make_duplicate |
| bespoke-meta | 0 critical, 7 defects | 1 FATAL (curl blocked), 2 MAJOR (no external check, no ID guard) | NEEDS REVISION | Shortened descriptions, added ID guards, origin-side verification, WP Rocket purge |
| allcaps-titles | 0 critical, 8 defects | 1 CRITICAL (wp_update_post fatal), 2 HIGH (H1 source, unnecessary blast radius) | NEEDS REVISION | Safer approach: _yoast_wpseo_title overrides only, skip wp_update_post |
| philosophy-typos | 2 CRITICAL (shell quoting, regex order) | 2 CRITICAL (regex order confirmed, negative check scope) | NEEDS REVISION | Fixed shell quoting, regex order, negative check scope, og:description checked |
| portfolio-furoshiki | 1 CRITICAL (wp option get --format=json) | 4 FAILURES (FR H1 exists, content contradicts, social half-configured, no op confirmation) | NEEDS OPERATOR INPUT | ADR paused -- FR H1 discrepancy invalidates premise |
| fr-notre-savoir-faire | 0 critical, 5 defects | 3 FATAL (wp redirection create doesn't exist, action_data TypeError, match_data incomplete) | NEEDS REVISION | Replaced with `wp redirection import` JSON method, pre-flight verifies group_id + hook priority |

---

## Appendix C: Common Verification Pattern

All origin-side verification follows this template to bypass Cloudflare:

```bash
$SSH 'wp eval "
  \$resp = wp_remote_get(home_url(\"/path/\"));
  if (is_wp_error(\$resp)) { echo \"FETCH ERROR: \" . \$resp->get_error_message(); exit(1); }
  \$html = \$resp[\"body\"];

  // Extract target element -- note: Yoast uses single quotes in meta tags
  preg_match(\"/<TAG[^>]*>([^<]*)<\\/TAG>/\", \$html, \$m);
  echo \$m[1] ?? \"NOT FOUND\";
"'
```

Key gotchas:
- Yoast robots: `<meta name='robots' content='...' />` (single quotes)
- Pattern must use `['\"]` not `\"` for attribute matching
- `wp_remote_get()` fires PHP hooks, not a real HTTP request -- Redirection plugin won't redirect (use `'redirection' => 0` to be explicit)
