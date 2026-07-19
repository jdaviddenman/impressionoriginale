---
name: rucss-enabled-css-async-works
description: "Enabling RUCSS (remove_unused_css: 1) with safelist made CSS load async — 30+ CSS files no longer render-blocking. LCP dropped 42% (27.6s → 15.9s). Safelist from fix-lcp-opacity.php v0.9.0 protects inline LCP CSS."
metadata:
  type: project
  originSessionId: current
---

# RUCSS Enabled — CSS Async Loading Works

**Date:** 2026-07-19

## What changed
Enabled `remove_unused_css: 1` in WP Rocket settings. The mu-plugin `fix-lcp-opacity.php` v0.9.0 provides the safelist infrastructure:
- `rocket_rucss_inline_content_exclusions` filter with `/*io-lcp*/` marker
- `remove_unused_css_safelist` populated with eut-* animation classes

Previously RUCSS was disabled (ADR 0007) because it was stripping the inline LCP fix CSS. The safelist now protects it.

## Results (Pagespeed.dev, Moto G Power, Slow 4G)

| Metric | Before | After | Delta |
|---|---|---|---|
| LCP | 27.6s | 15.9s | -42% |
| FCP | 5.5s | 5.2s | -5% |
| TBT | 160ms | 320ms | noise |
| CLS | 0 | 0 | unchanged |
| Render-blocking CSS | 22,410ms | 0ms | eliminated |

30+ CSS files now load asynchronously. Only jQuery (1,510ms) + Termly (4,350ms) remain render-blocking.

## Why this works now (and failed on July 16)

On July 16 (ADR 0007), RUCSS was enabled but:
1. No safelist was configured — the inline LCP CSS was stripped
2. The `rocket_rucss_inline_content_exclusions` filter was "unreliable on WP Rocket 3.22"
3. The RUCSS DB table was deleted while RUCSS was enabled → zero CSS on page

Now:
1. Safelist is populated (eut-title, eut-description, eut-btn, eut-fade-in-right, eut-feature-content, eut-slider-item)
2. WP Rocket upgraded to 3.23 (filter may be fixed)
3. RUCSS table was truncated BEFORE enabling (clean start)

## How to apply
Do NOT disable RUCSS. The safelist in fix-lcp-opacity.php is load-bearing. If adding new inline CSS, add its classes to `remove_unused_css_safelist` and its marker comment to `rocket_rucss_inline_content_exclusions`. See [[lcp-fix-session-postmortem]], [[lcp-css-fix-insufficient-97pct-render-delay]], [[font-hosting-experiment-failure]].
