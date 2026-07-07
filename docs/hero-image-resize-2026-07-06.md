# Hero image optimization — the lever is RESIZE, not "compress" (2026-07-06)

Investigation snapshot for issue **#44** (image optimization). Paused pending an operator
decision on the live-write mechanism (O held no WPE upload path in-session). Pick up from here.

## TL;DR

- The homepage hero JPGs are **dimensionally oversized**, not quality-heavy. Some are stored at
  absurd resolution (one is **11069×7379 = 81.6 MP**) yet only ~550 KB on disk (already brutally
  JPEG-compressed). They render at ≤1920 px, so the browser downscales ~81 MP every load.
- **Quality-recompression is the WRONG lever here** — re-encoding the 81 MP file at q75–85 makes it
  *bigger* (166–174 % of original), because the source is already over-compressed for its size.
- **Resize to display resolution is the fix.** 2560 px q82 = **224 KB (−59 %)**; 1920 px = 141 KB
  (−75 %). Bonus, likely bigger than the byte win: decode drops **81 MP → ~2 MP** (~98 % less
  CPU/RAM per image) — a plausible contributor to the 668 ms main-thread long-tasks and slow paint,
  worse on mobile.
- Resize at q82 is **visually lossless** at display size (proof below). True *lossless* encoding is
  not viable: JPEG has no meaningful lossless re-save, and lossless WebP/PNG of a photo is *larger*
  than the JPEG — which is exactly why Cloudflare reports `cf-polished: webp_bigger` on these.
- Inventory is **mixed** — not a uniform batch. Bucket per file: oversized→resize,
  correct-dims-but-heavy→recompress, already-fine→leave.

## Why `cf-polished: webp_bigger`

Cloudflare Polish tried a WebP and it came out larger than the source, so it served the JPG. On an
already heavily-compressed source this is expected — and it means **a Polish "lossy" toggle would
NOT fix these**; the problem is pixels (dimensions), which only resizing removes.

## Evidence

### Dimensions are the pathology (sample: `IMPRESSION-ORIGINALE_Wraps_HOME_Web.jpg`)

```
orig: 11069×7379 (81.6 MP), 549,680 bytes, cf-polished: webp_bigger

quality re-encode (same dims) — INFLATES:
  q85 → 174%   q82 → 174%   q80 → 171%   q75 → 166%   of original

resize (LANCZOS) + q82/q80 progressive optimize:
  2560px q82 → 223,669 B  (40% of orig)   ← retina-safe target
  1920px q82 → 141,482 B  (25% of orig)
  1440px q82 →  90,114 B  (16% of orig)
```

### Resize @q82 is visually lossless (worst-case content: fine line-art)

Compared what the browser shows NOW (81 MP → 1920 px display) vs the PLAN (source → 2560 px q82 →
1920 px display):

```
upload file 2560px q82 = 223,669 B   (vs orig 549,680)
visible delta @1920px:  MAE 0.58/255   max_pixel_diff 28   PSNR 45.7 dB
(PSNR > 40 dB = visually lossless; MAE < 1 = avg < 0.4% per channel)
```

Side-by-side 100 % crop (left = current 81 MP render, right = 2560 px q82):
`reports/hero-resize-2026-07-06/AB_crop_wraps_now-vs-2560q82.png` — identical to the eye.

### Inventory is mixed (spot check) — SUPERSEDED 2026-07-07

> **Update 2026-07-07:** full inventory built from the live homepage —
> `reports/hero-resize-2026-07-06/inventory-2026-07-07.md`. It is NOT a broad mix: **exactly one
> file is pathological** (`Wraps_HOME_Web.jpg`, 81.7 MP). All 12 other full-bleed images are already
> correctly-sized 2.1–4.9 MP derivatives. The RESIZE win is concentrated in that single CSS
> background. Dims verified via JPEG SOF markers (12/12 named-suffix files matched). Spot-check below
> kept for history.

```
11069×7379  536KB  IMPRESSION-ORIGINALE_Wraps_HOME_Web.jpg        → RESIZE (huge win)
 1440×1920  492KB  Impression-Originale_BestPaired_Boreal_...jpg  → RECOMPRESS (right dims, heavy q)
 1440×1920  286KB  IMPRESSION-ORIGINALE_CadeauCalligraphie_...jpg → likely fine
```

Two other guessed URLs 404'd → **the full hero list must be built from the live homepage**, not
guessed. Use the actual resource list (Playwright headless load, or `reports/perf-*/`), download
each, record dims + bytes, then bucket.

## Proposed fix (unchanged from investigation)

1. Build the real hero/background inventory from the live homepage (URL, dims, transferSize).
2. Per file: oversized → resize to **≤2560 px** (retina-safe) @ **q82**; correct-dims-but-heavy →
   recompress @ q82; already-lean → leave. Progressive + optimize, strip metadata.
3. Write to live via the chosen mechanism (see open decisions). Keep originals + a WPE restore point
   (RULE 3). Regenerate WP thumbnail derivatives + WebP Express variants.
4. Purge caches (Cloudflare + WP Engine + WP Rocket) — all three, or the re-check reads stale.

## Verification (acceptance)

- `curl -sI <hero-url>` → `content-length` down ~50–75 %, still HTTP 200, `content-type: image/jpeg`.
- `./harness/perf-timing.mjs https://www.impressionoriginale.com perf-after` → hero transfer down,
  long-tasks/TBT no worse (expect better); diff vs a `perf-before` baseline.
- Visual: EN + FR homepage hero renders crisp desktop + mobile; no layout shift (intrinsic dims in
  HTML unchanged for same-name files; CSS controls layout).
- `./harness/fingerprint.sh` diff vs `reports/perf-baseline-2026-07-06` — 200s, headings intact,
  no new PHP errors.

## Open decisions (blockers for pickup)

1. **Live-write mechanism** — O holds no WPE upload path in-session (no ssh-config host, no wp-cli
   alias). Options:
   - **Bulk optimizer plugin (ShortPixel/Imagify)** — one server-side run: resize large images +
     q82, keep originals, auto-regenerate thumbnails + WebP. Broadest coverage, reversible, the #44
     recommendation. Operator installs/authorizes.
   - **Manual SSH swap** — O resizes confirmed-oversized heroes locally, replaces over SSH.
     Narrowest blast radius; needs WPE SSH host/user from the private note (RULE 7).
   - **Playwright wp-admin** — drive the existing authenticated session (Media replace / plugin UI).
     No SSH; per-file; depends on installed plugins.
2. **Target width** — recommend **2560 px** (retina-safe, −59 %); 1920 px is −75 % but softer on
   large retina full-bleed.
3. **Quality** — **q82** is proven visually lossless; q88 (~+40 KB, PSNR ~50 dB) if zero-doubt
   headroom is wanted.

## Repro (local, no live write)

```
python3 -m venv venv && venv/bin/pip install Pillow numpy
# download a hero, then:
venv/bin/python - <<'PY'
from PIL import Image; im=Image.open("wraps.jpg"); print(im.size, )
im.resize((2560, round(im.height*2560/im.width))).save("out.jpg","JPEG",quality=82,optimize=True,progressive=True)
PY
```

## Relation to other work

- Blocked-on / part of **#44** (image optimization). Also relieves payload the RevSlider hero ships
  (**#43**); does not replace #43 (RevSlider JS still blocks LCP separately).
- Measure before/after with `harness/perf-timing.mjs` (PR #66).
