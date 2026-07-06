# where-to-find-us: misaligned WPBakery grid + 'Tapei' typo

**Issue:** [#62](https://github.com/jdaviddenman/impressionoriginale/issues/62) · **Page:** https://www.impressionoriginale.com/where-to-find-us/ · **Status:** open, fix pending in wp-admin

## Problem

`/where-to-find-us/` renders "weirdly formatted" (operator report). Two authoring defects, both content/page-builder — not code, plugin, or cache:

1. **Misaligned grid.** WPBakery layout uses **four hardcoded `vc_empty_space` 83px spacers** to fake vertical column alignment, plus **inconsistent column widths** (FRANCE row = 3× `1/3`; every other row = 2× `1/2`), and pairs two different countries per row (USA│SWITZERLAND, NEW CALEDONIA│TAIWAN). Columns hold unequal entry counts, so the fixed 83px offset over/undershoots → headings and rows don't line up.
2. **"Taipei" misspelled "Tapei"** in the Taiwan block (city line for Eslite).

(Separate, out of scope here — tracked verbally: stockists are text-only, no per-place photos. That's an asset-sourcing change, filed later.)

## Why it matters

Retail-stockist page is brand/discovery surface — lists Musée Rodin, Fortnum & Mason, Le Bon Marché, La Samaritaine, Centre Pompidou, Selfridges. Ragged layout + a visible city typo read as unmaintained.

## Evidence

Live fetch `https://www.impressionoriginale.com/where-to-find-us/` (HTTP 200), newline-flattened:

```
$ grep -oiE 'vc_empty_space"[^>]*height: 83px'  → 4 hits
$ grep -oiE 'eut-column-[0-9-]+' | sort | uniq -c
   4 eut-column-1
   7 eut-column-1-2
   3 eut-column-1-3
   4 eut-column-1-4
```

Grid reconstruction (token order): FRANCE row = 3×`1/3`, only col-1 labeled, cols 2–3 carry France stores unlabeled behind an 83px spacer each; subsequent rows = 2×`1/2` pairing two countries. "Tapei" appears in the Taiwan/Eslite city line.

Current stockists (12 / 6 countries): FRANCE — Maison Paon (Angers), La Samaritaine, Flammarion/Centre Pompidou, Le Bon Marché, Musée Rodin, Plume & Bille (Paris); UNITED KINGDOM — Fortnum & Mason, Selfridges (London); USA — The Give Store (Los Angeles); SWITZERLAND — Brachard (Geneva); NEW CALEDONIA — Bonnie & Bonnie (Nouméa); TAIWAN — Eslite (**Tapei→Taipei**).

## Ruled out

- Not caching (structure is in server HTML, deterministic across fetches).
- Not a plugin/theme regression (no shortcode leakage, no PHP errors; markup is hand-authored WPBakery).
- Not a rendering-engine fault (defect is in the DOM structure itself).

## Proposed path (wp-admin, WPBakery backend editor)

Blast radius: this one CMS page. Does **not** touch homepage / cart / checkout. Reversible via WP page revisions. Content-only, single page → per RULE 1 may go direct to live (no clone; ADR 0001). Snapshot first.

1. Take a page revision / UpdraftPlus snapshot (revert guard).
2. Delete all four `Empty Space` (83px) elements.
3. Rebuild as **two rows of three equal `1/3` columns**, one country per column, each column self-contained (its own `<h3>` heading + its stores stacked inside). Unequal store counts then only make a column taller — they can't misalign siblings, so no spacers are needed.
   - Row A: FRANCE · UNITED KINGDOM · USA
   - Row B: SWITZERLAND · NEW CALEDONIA · TAIWAN
4. Remove `text-align: justify` on the FRANCE heading (only heading with it — inconsistent).
5. **Fix city typo: "Tapei" → "Taipei"** (Taiwan / Eslite block).
6. Preview → Publish → clear WP Rocket **and** WP Engine cache → re-fetch.

## Acceptance criteria (RULE 5)

Run after publish + cache clear:

```bash
# spacers gone
curl -sL https://www.impressionoriginale.com/where-to-find-us/ | tr '\n' ' ' \
  | grep -oiE 'vc_empty_space"[^>]*height: 83px' | wc -l        # expect 0 (was 4)

# uniform grid — only 1/3 columns in content
curl -sL https://www.impressionoriginale.com/where-to-find-us/ | tr '\n' ' ' \
  | grep -oiE 'eut-column-1-[234]' | sort | uniq -c             # expect only eut-column-1-3

# typo fixed
curl -sL https://www.impressionoriginale.com/where-to-find-us/ | grep -c 'Tapei'   # expect 0
curl -sL https://www.impressionoriginale.com/where-to-find-us/ | grep -c 'Taipei'  # expect ≥1

# no regression
./harness/fingerprint.sh https://www.impressionoriginale.com after-wtf-fix   # HTTP 200, no PHP errors / mojibake
```
