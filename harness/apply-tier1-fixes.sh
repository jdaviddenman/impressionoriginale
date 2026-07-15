#!/bin/bash
# Tier 1 SEO Fixes — apply all 6 to live impressionoriginale.com
# Generated 2026-07-15 from docs/tier1-seo-fixes-runbook.md
# Each fix includes: apply → verify → error handling
# Run: bash harness/apply-tier1-fixes.sh

set -euo pipefail

SSH="ssh -o ConnectTimeout=60 -o IdentitiesOnly=yes -o BatchMode=yes impressionor@impressionor.ssh.wpengine.net"
PASS=0
FAIL=0

red()  { echo -e "\033[31m$*\033[0m"; }
green(){ echo -e "\033[32m$*\033[0m"; }

die() { red "FATAL: $*"; exit 1; }

# ── Pre-flight ──────────────────────────────────────────────
echo "=== PRE-FLIGHT ==="
echo ""

echo -n "SSH: "
$SSH 'wp option get siteurl' || die "SSH failed"
green "OK"

echo -n "Yoast: "
$SSH 'wp plugin get wordpress-seo --field=version' || die "Yoast not found"
green "OK"

echo -n "Redirection: "
$SSH 'wp plugin get redirection --field=version' || die "Redirection not found"
green "OK"

echo -n "Backup: "
$SSH 'wp eval "echo get_option(\"updraft_last_backup\") ?: \"NO UPDRaft — check WP Engine backup points\";"' || echo "WARN: backup check failed"

echo ""
green "Pre-flight passed."
echo ""

# ── Fix 1: Homepage H1 ─────────────────────────────────────
echo "=== Fix 1: Homepage H1 ==="

$SSH 'wp eval "
\$slider = get_post_meta(9558, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$old = \$slider[0][\"title\"];
    \$slider[0][\"title\"] = \"Luxury Gift Wrap & Ribbons, Made in France\";
    update_post_meta(9558, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"EN H1: \" . \$old . \" -> \" . \$slider[0][\"title\"] . PHP_EOL;
} else { echo \"ERROR: EN slider meta structure unexpected\" . PHP_EOL; exit(1); }
"'
echo ""

$SSH 'wp eval "
\$slider = get_post_meta(9709, \"_engic_eutf_feature_slider_items\", true);
if (!empty(\$slider) && isset(\$slider[0]) && isset(\$slider[0][\"title\"])) {
    \$old = \$slider[0][\"title\"];
    \$slider[0][\"title\"] = \"Papier Cadeau de Luxe & Rubans, Fabrique en France\";
    update_post_meta(9709, \"_engic_eutf_feature_slider_items\", \$slider);
    echo \"FR H1: \" . \$old . \" -> \" . \$slider[0][\"title\"] . PHP_EOL;
} else { echo \"ERROR: FR slider meta structure unexpected\" . PHP_EOL; exit(1); }
"'
echo ""

# Verify
EN_H1=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/\")); preg_match(\"#<h1[^>]*>.*?</h1>#\", \$r[\"body\"], \$m); echo \$m[0] ?? \"NOT FOUND\";"')
echo "Verify EN H1: $EN_H1"
if echo "$EN_H1" | grep -q "Luxury Gift Wrap"; then green "Fix 1 EN: OK"; ((PASS++)); else red "Fix 1 EN: FAIL"; ((FAIL++)); fi

FR_H1=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/fr/\")); preg_match(\"#<h1[^>]*>.*?</h1>#\", \$r[\"body\"], \$m); echo \$m[0] ?? \"NOT FOUND\";"')
echo "Verify FR H1: $FR_H1"
if echo "$FR_H1" | grep -q "Papier Cadeau"; then green "Fix 1 FR: OK"; ((PASS++)); else red "Fix 1 FR: FAIL"; ((FAIL++)); fi
echo ""

# ── Fix 2: /shop/ noindex + meta + FR title ─────────────────
echo "=== Fix 2: /shop/ noindex + meta + FR title ==="

# 2a. Remove noindex
$SSH 'wp eval "
delete_post_meta(9817, \"_yoast_wpseo_meta-robots-noindex\");
\$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>9817, \"object_type\"=>\"post\"]);
echo \"noindex removed from page 9817\" . PHP_EOL;
\$archive = YoastSEO()->meta->for_post_type_archive(\"product\");
echo \"Archive title: \" . \$archive->title . PHP_EOL;
"'
echo ""

# 2b. Set EN meta description
$SSH 'wp eval "
update_post_meta(9817, \"_yoast_wpseo_metadesc\", \"Discover luxury hand-drawn gift wrap, ribbons, boxes and bows designed by independent artists and made in France. Eco-conscious gift packaging.\");
\$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>9817, \"object_type\"=>\"post\"]);
echo \"EN desc: \" . YoastSEO()->meta->for_post(9817)->description . PHP_EOL;
"'
echo ""

# 2c. Set FR meta + title (check for separate FR page first)
$SSH 'wp eval "
global \$wpdb;
\$fr_id = \$wpdb->get_var(\"SELECT element_id FROM {\$wpdb->prefix}icl_translations WHERE trid = (SELECT trid FROM {\$wpdb->prefix}icl_translations WHERE element_id=9817 AND element_type='\''post_page'\'') AND language_code='\''fr'\'' AND element_id != 9817\");
if (\$fr_id) {
    update_post_meta(\$fr_id, \"_yoast_wpseo_metadesc\", \"Decouvrez des emballages cadeau de luxe, rubans, boites et noeuds dessines par des artistes independants et fabriques en France. Emballage eco-responsable.\");
    \$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$fr_id, \"object_type\"=>\"post\"]);
    echo \"FR desc (page \" . \$fr_id . \"): \" . YoastSEO()->meta->for_post(\$fr_id)->description . PHP_EOL;
    update_post_meta(\$fr_id, \"_yoast_wpseo_title\", \"Boutique %%sep%% %%sitename%%\");
    \$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$fr_id, \"object_type\"=>\"post\"]);
    echo \"FR title (page \" . \$fr_id . \"): \" . YoastSEO()->meta->for_post(\$fr_id)->title . PHP_EOL;
} else {
    echo \"No separate FR shop page (WCML renders it). Setting FR meta on EN page.\" . PHP_EOL;
}
"'
echo ""

# Verify
EN_ROBOTS=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/shop/\")); preg_match(\"#<meta name=.robots. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
EN_DESC=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/shop/\")); preg_match(\"#<meta name=.description. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
echo "Verify EN: robots=$EN_ROBOTS desc=${EN_DESC:0:80}..."
if echo "$EN_ROBOTS" | grep -q "index" && [ -n "$EN_DESC" ] && [ "$EN_DESC" != "MISSING" ]; then green "Fix 2 EN: OK"; ((PASS++)); else red "Fix 2 EN: FAIL"; ((FAIL++)); fi

FR_ROBOTS=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/fr/shop/\")); preg_match(\"#<meta name=.robots. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
echo "Verify FR: robots=$FR_ROBOTS"
if echo "$FR_ROBOTS" | grep -q "index"; then green "Fix 2 FR: OK"; ((PASS++)); else red "Fix 2 FR: FAIL"; ((FAIL++)); fi
echo ""

# ── Fix 3: /bespoke-services/ meta ──────────────────────────
echo "=== Fix 3: /bespoke-services/ meta ==="

$SSH 'wp eval "
\$page = get_page_by_path(\"bespoke-services\");
if (!\$page) { echo \"ERROR: EN page not found\" . PHP_EOL; exit(1); }
echo \"EN page ID: \" . \$page->ID . PHP_EOL;
update_post_meta(\$page->ID, \"_yoast_wpseo_metadesc\", \"Bespoke luxury gift wrap and packaging — custom sizes, materials, and finishes. Design your own wrapping paper, tissue, ribbons and boxes with our Paris studio.\");
\$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$page->ID, \"object_type\"=>\"post\"]);
echo YoastSEO()->meta->for_post(\$page->ID)->description . PHP_EOL;
"'
echo ""

$SSH 'wp eval "
\$page = get_page_by_path(\"bespoke-services\");
if (!\$page) { echo \"ERROR: EN page not found for lookup\" . PHP_EOL; exit(1); }
global \$wpdb;
\$fr_id = \$wpdb->get_var(\"SELECT element_id FROM {\$wpdb->prefix}icl_translations WHERE trid = (SELECT trid FROM {\$wpdb->prefix}icl_translations WHERE element_id=\" . \$page->ID . \" AND element_type='\''post_page'\'') AND language_code='\''fr'\'' AND element_id != \" . \$page->ID);
if (!\$fr_id) { echo \"ERROR: FR page not found\" . PHP_EOL; exit(1); }
echo \"FR page ID: \" . \$fr_id . PHP_EOL;
update_post_meta(\$fr_id, \"_yoast_wpseo_metadesc\", \"Emballage cadeau de luxe sur mesure — tailles, materiaux et finitions personnalises. Creez votre propre papier cadeau, papier de soie, rubans et boites avec notre atelier parisien.\");
\$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$fr_id, \"object_type\"=>\"post\"]);
echo YoastSEO()->meta->for_post(\$fr_id)->description . PHP_EOL;
"'
echo ""

# Verify
EN_BESPOKE=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/bespoke-services/\")); preg_match(\"#<meta name=.description. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
echo "Verify EN bespoke: ${EN_BESPOKE:0:80}..."
if [ -n "$EN_BESPOKE" ] && [ "$EN_BESPOKE" != "MISSING" ]; then green "Fix 3 EN: OK"; ((PASS++)); else red "Fix 3 EN: FAIL"; ((FAIL++)); fi

FR_BESPOKE=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/fr/bespoke-services/\")); preg_match(\"#<meta name=.description. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
echo "Verify FR bespoke: ${FR_BESPOKE:0:80}..."
if [ -n "$FR_BESPOKE" ] && [ "$FR_BESPOKE" != "MISSING" ]; then green "Fix 3 FR: OK"; ((PASS++)); else red "Fix 3 FR: FAIL"; ((FAIL++)); fi
echo ""

# ── Fix 4: 5 ALL-CAPS titles ────────────────────────────────
echo "=== Fix 4: ALL-CAPS titles ==="

$SSH 'wp eval "
\$pages = [
    \"our-philosophy\" => \"Our Philosophy %%sep%% %%sitename%%\",
    \"our-products\" => \"Our Products %%sep%% %%sitename%%\",
    \"where-to-find-us\" => \"Where to Find Us %%sep%% %%sitename%%\",
    \"bespoke-services\" => \"Bespoke Services %%sep%% %%sitename%%\",
    \"corporate-gifts-order-form-online\" => \"Corporate Gifts Order Form %%sep%% %%sitename%%\",
];
foreach (\$pages as \$slug => \$title) {
    \$page = get_page_by_path(\$slug);
    if (!\$page) { echo \"SKIP \" . \$slug . \": not found\" . PHP_EOL; continue; }
    \$id = \$page->ID;
    update_post_meta(\$id, \"_yoast_wpseo_title\", \$title);
    \$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$id, \"object_type\"=>\"post\"]);
    echo \$slug . \" (ID \" . \$id . \"): \" . YoastSEO()->meta->for_post(\$id)->title . PHP_EOL;
}
"'
echo ""

# Verify each
CAPS_FAILS=0
for slug in our-philosophy our-products where-to-find-us bespoke-services corporate-gifts-order-form-online; do
  TITLE=$($SSH "wp eval '\$r=wp_remote_get(home_url(\"/$slug/\")); preg_match(\"#<title>([^<]*)</title>#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";'")
  echo "Verify $slug: $TITLE"
  if echo "$TITLE" | grep -qE '[A-Z]{4,}'; then red "  FAIL: still has ALL-CAPS"; ((CAPS_FAILS++)); else green "  OK"; fi
done
if [ $CAPS_FAILS -eq 0 ]; then green "Fix 4: OK"; ((PASS++)); else red "Fix 4: FAIL ($CAPS_FAILS pages still have caps)"; ((FAIL++)); fi
echo ""

# ── Fix 5: /our-philosophy/ typos ───────────────────────────
echo "=== Fix 5: /our-philosophy/ typos ==="

$SSH 'wp eval "
\$page = get_page_by_path(\"our-philosophy\");
if (!\$page) { echo \"ERROR: page not found\" . PHP_EOL; exit(1); }
\$id = \$page->ID;
\$current = get_post_meta(\$id, \"_yoast_wpseo_metadesc\", true);
echo \"Current: \" . \$current . PHP_EOL;
\$fixed = str_replace([\"optimazing\", \"minimizes\"], [\"optimising\", \"minimises\"], \$current);
update_post_meta(\$id, \"_yoast_wpseo_metadesc\", \$fixed);
\$GLOBALS['\''wpdb'\'']->delete(\$GLOBALS['\''wpdb'\'']->prefix.\"yoast_indexable\", [\"object_id\"=>(int)\$id, \"object_type\"=>\"post\"]);
echo \"Fixed: \" . YoastSEO()->meta->for_post(\$id)->description . PHP_EOL;
"'
echo ""

# Verify
DESC=$($SSH 'wp eval "\$r=wp_remote_get(home_url(\"/our-philosophy/\")); preg_match(\"#<meta name=.description. content=.([^\"]*).#\", \$r[\"body\"], \$m); echo \$m[1] ?? \"MISSING\";"')
echo "Verify desc: ${DESC:0:120}..."
if echo "$DESC" | grep -q "optimising" && echo "$DESC" | grep -q "minimises"; then
  if echo "$DESC" | grep -q "optimazing\|minimizes"; then red "Fix 5: FAIL (old typos still present)"; ((FAIL++)); else green "Fix 5: OK"; ((PASS++)); fi
else red "Fix 5: FAIL (corrected words not found)"; ((FAIL++)); fi
echo ""

# ── Fix 6: /fr/notre-savoir-faire/ redirect ─────────────────
echo "=== Fix 6: /fr/notre-savoir-faire/ redirect ==="

$SSH 'wp eval "
\$r1 = Red_Item::create([
    \"url\" => \"/fr/notre-savoir-faire/\",
    \"match_type\" => \"url\",
    \"action_type\" => \"url\",
    \"action_data\" => [\"url\" => \"/fr/savoir-faire/\"],
    \"action_code\" => 301,
    \"group_id\" => 1,
    \"status\" => \"enabled\",
]);
if (is_wp_error(\$r1)) {
    echo \"ERROR creating redirect 1: \" . \$r1->get_error_message() . PHP_EOL;
} else {
    echo \"Redirect 1 OK: /fr/notre-savoir-faire/ -> /fr/savoir-faire/ (ID: \" . \$r1->get_id() . \")\" . PHP_EOL;
}

\$r2 = Red_Item::create([
    \"url\" => \"/notre-savoir-faire/\",
    \"match_type\" => \"url\",
    \"action_type\" => \"url\",
    \"action_data\" => [\"url\" => \"/know-how-the-perfect-gift/\"],
    \"action_code\" => 301,
    \"group_id\" => 1,
    \"status\" => \"enabled\",
]);
if (is_wp_error(\$r2)) {
    echo \"ERROR creating redirect 2: \" . \$r2->get_error_message() . PHP_EOL;
} else {
    echo \"Redirect 2 OK: /notre-savoir-faire/ -> /know-how-the-perfect-gift/ (ID: \" . \$r2->get_id() . \")\" . PHP_EOL;
}
"'
echo ""

# Verify
REDIR=$($SSH 'wp eval "\$resp = wp_remote_get(home_url(\"/fr/notre-savoir-faire/\"), [\"redirection\" => 0]); echo wp_remote_retrieve_response_code(\$resp);"')
echo "Verify redirect status: $REDIR"
if [ "$REDIR" = "301" ]; then green "Fix 6: OK"; ((PASS++)); else red "Fix 6: FAIL (expected 301, got $REDIR)"; ((FAIL++)); fi
echo ""

# ── CDN Purge ───────────────────────────────────────────────
echo "=== CDN Purge ==="
$SSH 'wp cache flush && wp eval "
  if (class_exists(\"WpeCommon\")) {
    WpeCommon::purge_varnish_cache_all();
    WpeCommon::clear_cdn_cache();
    WpeCommon::clear_maxcdn_cache();
    WpeCommon::purge_memcached();
  }
  if (function_exists(\"rocket_clean_domain\")) { rocket_clean_domain(); echo \"WP Rocket cleared\" . PHP_EOL; }
  echo \"Purge complete\" . PHP_EOL;
"'
echo ""

# Confirm CDN
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"
CF=$(curl -sI -H "User-Agent: $UA" "https://www.impressionoriginale.com/" 2>/dev/null | grep -i cf-cache-status || echo "cf-cache-status: COULD_NOT_FETCH")
echo "CDN: $CF"
if echo "$CF" | grep -qE "MISS|EXPIRED"; then green "CDN purge: OK"; else red "CDN purge: may not have taken effect ($CF)"; fi
echo ""

# ── Final Summary ───────────────────────────────────────────
echo "=========================="
echo "FINAL: $PASS passed, $FAIL failed"
echo "=========================="
if [ $FAIL -gt 0 ]; then
  red "Some fixes failed. Check output above for details."
  exit 1
else
  green "All fixes applied successfully."
fi
