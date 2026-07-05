#!/usr/bin/env bash
# Web-user-POV fingerprint of a set of pages. Server HTML only (no JS render).
# Usage: ./fingerprint.sh <BASE_URL> <OUTDIR>
#   BASE_URL e.g. https://www.impressionoriginale.com   (no trailing slash)
#            or   https://<clone>.updraftclone.com
#   OUTDIR   e.g. baseline   (or round1-wpml, round2-yoast, ...)
#
# Produces OUTDIR/SUMMARY.txt (diffable) + OUTDIR/raw/*.html
#          + OUTDIR/detail/*.{images,assets,versions}.txt (per-page, for drill-down on a hash change).
# NOTE: server HTML only. Catches: broken pages, PHP errors, shortcode leakage, encoding,
#       dropped/added images & CSS/JS assets, plugin/theme version drift. Does NOT catch rendered
#       visual/layout regressions (no JS render, no screenshots) — pair with an eyeball on key pages.
# Compare rounds with: diff baseline/SUMMARY.txt round1-wpml/SUMMARY.txt
# When comparing live vs clone, normalise the host first (the domains differ by design).
set -u
BASE="${1:?need BASE_URL}"; OUT="${2:?need OUTDIR}"
UA="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124 Safari/537.36"
mkdir -p "$OUT/raw" "$OUT/detail"

PATHS=(
  "/" "/fr/"
  "/wrap/" "/ribbons/"
  "/occasions-to-gift/christmas/"
  "/shop/" "/our-products/"
  "/fr/produit/ensemble-minimals/"
  "/bespoke-services/" "/masterclass/"
  "/our-philosophy/" "/where-to-find-us/"
)

SUM="$OUT/SUMMARY.txt"; : > "$SUM"
for p in "${PATHS[@]}"; do
  url="$BASE$p"
  slug=$(echo "$p" | sed 's#/#_#g; s#^_##; s#_$##'); [ -z "$slug" ] && slug="home"
  html="$OUT/raw/$slug.html"
  meta=$(curl -sL -A "$UA" -o "$html" -w '%{http_code}|%{url_effective}|%{size_download}' "$url")
  code="${meta%%|*}"; rest="${meta#*|}"; finalurl="${rest%|*}"; size="${rest##*|}"

  title=$(grep -oiE '<title[^>]*>[^<]*</title>' "$html" | head -1 | sed -E 's/<[^>]+>//g')
  desc=$(grep -oiE '<meta[^>]*name="description"[^>]*>' "$html" | head -1 | grep -oiE 'content="[^"]*"' | sed -E 's/content="//; s/"$//')
  canon=$(grep -oiE '<link[^>]*rel="canonical"[^>]*>' "$html" | head -1 | grep -oiE 'href="[^"]*"' | sed -E 's/href="//; s/"$//')
  lang=$(grep -oiE '<html[^>]*lang="[^"]*"' "$html" | head -1 | grep -oiE 'lang="[^"]*"')
  hrefl=$(grep -ioc 'hreflang' "$html")
  h1n=$(grep -oiE '<h1[^>]*>' "$html" | wc -l | tr -d ' ')
  h1txt=$(grep -oiE '<h1[^>]*>[^<]*</h1>' "$html" | sed -E 's/<[^>]+>//g' | paste -sd'~' -)
  jsonld=$(grep -oiE '"@type":"[A-Za-z]+"' "$html" | sort -u | sed -E 's/"@type"://; s/"//g' | paste -sd',' -)
  ogimg=$(grep -ioc 'og:image' "$html")
  scripts=$(grep -oiE '<script[^>]*src=' "$html" | wc -l | tr -d ' ')
  styles=$(grep -oiE '<link[^>]*rel="stylesheet"' "$html" | wc -l | tr -d ' ')
  # images incl. lazy-loaded (data-src/data-lazy-src), query stripped -> count + hash + detail file
  imglist=$(grep -oiE '(src|data-src|data-lazy-src)="[^"]+"' "$html" \
            | sed -E 's/^[a-zA-Z_-]+="//; s/"$//' \
            | grep -oiE '^https?://[^ ]+\.(jpe?g|png|gif|webp|svg|avif)' | sort -u)
  imgn=$(printf '%s\n' "$imglist" | grep -c .)
  imghash=$(printf '%s' "$imglist" | md5sum | cut -c1-12)
  printf '%s\n' "$imglist" > "$OUT/detail/$slug.images.txt"
  # enqueued plugin/theme CSS+JS manifest, query stripped (stable across cache clears)
  assetlist=$(grep -oiE '(href|src)="[^"]*wp-content/(plugins|themes)/[^"]+\.(css|js)[^"]*"' "$html" \
            | grep -oiE 'wp-content/(plugins|themes)/[^"?]+\.(css|js)' | sort -u)
  assn=$(printf '%s\n' "$assetlist" | grep -c .)
  assethash=$(printf '%s' "$assetlist" | md5sum | cut -c1-12)
  printf '%s\n' "$assetlist" > "$OUT/detail/$slug.assets.txt"
  # plugin/theme versions from ?ver= (semver only; skips pure-int cache-bust timestamps)
  grep -oiE 'wp-content/(plugins|themes)/[a-zA-Z0-9._-]+/[^" ]*ver=[0-9]+\.[0-9][0-9.]*' "$html" \
    | sed -E 's#.*wp-content/(plugins|themes)/([a-zA-Z0-9._-]+)/.*ver=#\2=#' | sort -u > "$OUT/detail/$slug.versions.txt"
  # regression markers
  errs=$(grep -icE 'fatal error|there has been a critical error|parse error' "$html")
  warns=$(grep -icE 'warning:|notice:|deprecated:' "$html")
  shortc=$(grep -icE '\[vc_|\[/vc_|\[caption|\[rev_slider|\]\]>' "$html")
  moji=$(grep -icE 'Ã©|Ã¨|Ã |Ã§|Ã´|Ã»|Â ' "$html")

  {
    echo "=== $p"
    echo "http=$code  final=$finalurl  bytes=$size"
    echo "title: $title"
    echo "desc : $desc"
    echo "canon: $canon"
    echo "lang : $lang   hreflang_count=$hrefl   og:image=$ogimg"
    echo "h1(#$h1n): $h1txt"
    echo "jsonld: $jsonld"
    echo "assets: scripts=$scripts styles=$styles  files=$assn assethash=$assethash"
    echo "images: count=$imgn imghash=$imghash"
    echo "FLAGS: errors=$errs warns=$warns shortcode_leak=$shortc mojibake=$moji"
    echo
  } >> "$SUM"
done
echo "Wrote $SUM and raw HTML to $OUT/raw/"
echo "--- SUMMARY ---"; cat "$SUM"
