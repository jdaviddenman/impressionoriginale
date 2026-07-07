# Hero / full-bleed image inventory — built from live homepage (2026-07-07)

> **CORRECTIONS after adversarial review (2026-07-07, all reproduced on live):**
> 1. **NOT one pathological file — TWO.** The inventory parsed EN `home.html` only. The **/fr/**
>    homepage serves a separate WPML media copy `IMPRESSION-ORIGINALE_Wraps_HOME_Web-1.jpg`
>    (`-1` suffix) with **identical pathology**: 11069×7379, 549,680 bytes, `webp_bigger`. Fix set is
>    **both** files. (Reproduced: `fr.html` `eut-bg-image url(...Wraps_HOME_Web-1.jpg)`; live SOF parse
>    confirms 81.7 MP.)
> 2. **Wraps is NOT the LCP hero.** LCP is the WP-Rocket-preloaded `Phedre_triocote-1440x1920.jpg`
>    (`<link rel=preload as=image fetchpriority=high>`, section 1 @ home.html:553), already a
>    correctly-sized 2.8 MP derivative. Wraps is section 2 (home.html:568). Resizing Wraps will **not**
>    move LCP. The decode-RAM win is real (81.7 MP ≈ 326 MB RGBA/decode, brutal on mobile) but the
>    "matters more for the 668 ms long-tasks" line is an **unproven hypothesis** — Chrome crashed, no
>    long-task attribution captured.
> 3. **SSH swap is NOT "instantly reversible."** Origin is fronted by RocketCDN/Cloudflare with
>    `cache-control: max-age≈31919000` (~369 days), currently `cf-cache-status: HIT`. A same-URL swap
>    is invisible — and un-revertible — until a **two-cache purge** (Cloudflare/RocketCDN + WP Rocket).
>    WebP Express `.webp` sidecars also go stale (low impact — `webp_bigger` proves origin JPEG is
>    served). Verification/rollback both require the purge; not instant.

Issue **#44**. Source: `reports/perf-baseline-2026-07-06/raw/home.html` (rendered-HTML URL
extraction) + live `curl -I` (bytes / cf-polished) + JPEG SOF-marker parse (actual pixel dims,
not filenames — RULE 12/13). All 13 return HTTP 200, all `cf-polished: webp_bigger`.

Full-bleed set = the two CSS `background-image:` heroes + the RevSlider `*_HOME*` slides. Product-grid
thumbnails (`-350x435`, `-600x600`, etc.) excluded — not full-bleed, not the payload story.

| file | actual dims | MP | bytes | name-dims match | bucket |
|---|---|---|---|---|---|
| `IMPRESSION-ORIGINALE_Wraps_HOME_Web.jpg` (EN CSS bg, §2) | **11069×7379** | **81.7** | 549,680 | no-suffix (full-size) | **RESIZE** |
| `IMPRESSION-ORIGINALE_Wraps_HOME_Web-1.jpg` (FR CSS bg, WPML copy) | **11069×7379** | **81.7** | 549,680 | no-suffix (full-size) | **RESIZE** |
| `IMPRESSION_ORIGINALE_Montage_Tryptique_pochette_HOME-1920x1080.jpg` | 1920×1080 | 2.1 | 370,450 | ✓ | recompress? |
| `IMPRESSION_ORIGINALE_Noeuds_Pirouettes_plateau_HOME-1920x1281.jpg` | 1920×1281 | 2.5 | 310,416 | ✓ | recompress? |
| `IMPRESSION-ORIGINALE_CadeauCalligraphie_HOME-1440x1920.jpg` | 1440×1920 | 2.8 | 293,664 | ✓ | recompress? |
| `IMPRESSION-ORIGINALE_CoeursOrigami_HOME-1440x1920.jpg` | 1440×1920 | 2.8 | 293,387 | ✓ | recompress? |
| `Impression-Originale_Gift_Woman-in-Black-scaled.jpg` | 1920×2560 | 4.9 | 284,825 | no-suffix (WP 2560 cap) | leave |
| `IMPRESSION-ORIGINALE_Ciseaux_HOME-1920x1440.jpg` | 1920×1440 | 2.8 | 282,255 | ✓ | recompress? |
| `IMPRESSION-ORIGINALE_GiftTags_HOME-1920x1280.jpg` | 1920×1280 | 2.5 | 274,879 | ✓ | recompress? |
| `IMPRESSION-ORIGINALE_CadeauCalligraphie_Phedre_triocote-1440x1920.jpg` (CSS bg) | 1440×1920 | 2.8 | 231,230 | ✓ | recompress? |
| `IMPRESSION-ORIGINALE_Bobine_HOME-1920x1280.jpg` | 1920×1280 | 2.5 | 221,906 | ✓ | leave |
| `IMPRESSION_ORIGINALE_Cadeau_fleur_rond-1920x1280.jpg` | 1920×1280 | 2.5 | 191,375 | ✓ | leave |
| `IMPRESSION-ORIGINALE_Furoshiki_fondliedevin_HOME_Web-1920x1280.jpg` | 1920×1280 | 2.5 | 161,726 | ✓ | leave |
| `Gift_ceremony.jpg` | 1335×2000 | 2.7 | 329,689 | no-suffix (full-size) | recompress? |

## Correction to the investigation's "mixed batch" hypothesis

The prior spot-check (`hero-image-resize-2026-07-06.md`) guessed a broad mix of resize/recompress/leave.
The **full** inventory shows it is not broad: **exactly one file is pathological** —
`Wraps_HOME_Web.jpg` at 81.7 MP. Every other full-bleed image is already a correctly-sized
2.1–4.9 MP derivative. The win is concentrated, not distributed.

12/12 files with a `-WxH` suffix matched their real SOF dims → WP's derivative naming is trustworthy
here; the pathology is only the one full-size CSS background that was never downsized.

## Buckets

- **RESIZE (the fix):** `Wraps_HOME_Web.jpg` only. 81.7 MP → **2560px q82 ≈ 224 KB (−59% bytes)**, and
  decode **81.7 MP → ~4 MP (~95% less decode CPU/RAM per load)**. The decode win likely matters more
  than the byte win for the 668 ms main-thread long-tasks — worse on mobile. Visually lossless at
  display size (PSNR 45.7 dB — proof in `AB_crop_wraps_now-vs-2560q82.png`).
- **RECOMPRESS (optional, small):** the ~270–370 KB derivatives (Montage, Noeuds, both CadeauCalligraphie,
  CoeursOrigami, Ciseaux, GiftTags, Gift_ceremony) could go q82 for ~−20–40% each. Not the pathology;
  defer unless chasing the last KB.
- **LEAVE:** the ≤222 KB derivatives (Bobine, Cadeau_fleur_rond, Furoshiki, Gift_Woman-in-Black) are lean.

## Method notes

- Chrome (Playwright MCP) crashed on load (`SkFontMgr…Not implemented` Skia fatal) — sandbox instability
  (see memory `sandbox-kills-headless-chrome`). Pivoted to static HTML URL extraction + `curl` headers +
  pure-stdlib JPEG SOF parse. No rendered `transferSize` captured; byte sizes are origin `content-length`
  (CDN, pre-Polish). Good enough to bucket; a rendered perf-timing pass belongs in the before/after verify.
- Dims parsed from JPEG SOF0/SOF2 markers (`0xFFC0`–`0xFFCF`), not filenames — satisfies "never assume."
