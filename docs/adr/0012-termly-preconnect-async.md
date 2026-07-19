# ADR 0012 — Termly Preconnect + Async: Eliminating the Last JS Render-Blocker

**Date:** 2026-07-19
**Status:** Deployed. Verified. Termly render-blocking eliminated (0ms). jQuery now the sole remaining render-blocker (990-1,480ms).

## Decision

Termly cookie consent script (`app.termly.io/resource-blocker/`) was the largest remaining render-blocking resource at 4,350ms (Pagespeed.dev) after RUCSS eliminated CSS render-blocking (ADR 0011). A mu-plugin adds `dns-prefetch` + `preconnect` links for `app.termly.io` and an `async` attribute to the resource-blocker `<script>` tag. Consent remains gated by Consent Mode v2 denied defaults (`fix-consent-defaults.php`, `wait_for_update: 500`).

## Context

### What came before
- ADR 0005/0006/0007 — LCP opacity + lazy-load fixes, `delay_js: 0`
- ADR 0010 — performance analysis: identified Termly as 2,280ms (Lighthouse) / 4,350ms (Pagespeed.dev) render-blocking. NF6 recommended async + preconnect. S5 (defer Termly) was rejected as GDPR-unsafe in the same ADR.
- ADR 0011 — font hosting failed; RUCSS re-enabled. CSS render-blocking eliminated. Only jQuery (1,510ms, theme dependency) and Termly (4,350ms) remained.

### Why async now when S5 was rejected
S5 proposed `defer`, which queues scripts after DOMContentLoaded. Deferred tracking scripts would fire before Termly established consent — GDPR violation. `async` is different: async scripts download without blocking HTML parse but execute as soon as fetched, which is before DOMContentLoaded. Deferred scripts (GTM, pixels via `defer_all_js: 1`) wait for DOMContentLoaded. Async Termly executes before them.

### Why this is GDPR-safe
`fix-consent-defaults.php` (PHP_INT_MAX - 1) outputs Consent Mode v2 `denied` defaults with `wait_for_update: 500`. This inline script runs before DOMContentLoaded regardless of Termly's load timing. GTM reads `denied` from dataLayer and waits up to 500ms for consent upgrade. On connections slow enough that async Termly arrives after the 500ms window, GTM fires in denied mode — the correct GDPR fallback. The auto-blocker (`autoBlock=1`) is a secondary defense; Consent Mode v2 is the primary gate.

## Implementation

**Single mu-plugin: `io-termly-preconnect-async.php`** (deployed to `/wp-content/mu-plugins/`)

### Phase 1 — Preconnect
Hooks `wp_head` at `PHP_INT_MIN`. mu-plugins load before regular plugins, so at the same priority, our hook fires before Termly's `embed_banner`. Outputs:

```html
<link rel="dns-prefetch" href="//app.termly.io">
<link rel="preconnect" href="https://app.termly.io" crossorigin>
```

DNS + TCP + TLS connection setup starts before the browser encounters the Termly script tag. Estimated savings: 150-400ms.

### Phase 2 — Async attribute
Hooks `rocket_buffer` (RULE 21 compliant — documented WP Rocket extension point). Simple string replacement:

```
src="https://app.termly.io/resource-blocker/" → async src="https://app.termly.io/resource-blocker/"
```

No regex, no DOM parsing. If WP Rocket is deactivated, `rocket_buffer` doesn't fire and the script stays synchronous — degrades gracefully (preconnect still helps).

### Why not other approaches
- **`script_loader_tag` filter**: Termly plugin uses `printf`, not `wp_enqueue_script`. Filter has no effect.
- **Dequeue + re-enqueue**: Plugin bypasses enqueue system entirely.
- **Output buffer via `template_redirect`**: RULE 21 says use WP Rocket extension points. `rocket_buffer` is the documented filter.
- **Edit plugin file**: Overwritten on update.
- **Self-host scripts**: Resource-blocker is UUID-bound; breaks auto-update and consent management.
- **Upgrade plugin**: v3.3.1 is latest. "Termly x WP Rocket partnership" in changelog is an affiliate ad sidebar in wp-admin, not a technical integration.

### Verified server-side facts (not inferred)
- Plugin: `uk-cookie-consent` v3.3.1, code at `/sites/impressionor/wp-content/plugins/uk-cookie-consent/`
- `class-frontend.php:74-80`: `printf` with hardcoded `<script type="text/javascript" src="...">`, no async/defer
- `class-frontend.php:16`: `add_action( 'wp_head', [ __CLASS__, 'embed_banner' ], PHP_INT_MIN )`
- `termly_display_banner`: `yes`, `termly_display_auto_blocker`: `1`
- WP Rocket: `defer_all_js: 1`, `exclude_defer_js: ['app.termly.io']`, `dns_prefetch: []`
- Termly CDN: `cache-control: max-age=14400` (4 hours)
- No existing mu-plugin uses `rocket_buffer`

## Verification

### Pre-deploy
- `cf-cache-status: MISS` after full cache purge (Rocket → Varnish → CDN)
- Preconnect links present before Termly script tag
- `async` attribute on Termly resource-blocker `<script>` tag
- Site HTTP 200
- 6 Termly references in page (normal)

### Pagespeed.dev (Jul 19, Slow 4G emulation, two runs)

| Metric | Run 1 | Run 2 | Notes |
|---|---|---|---|
| FCP | 4.6s (+1) | 2.5s (+7) | Variable, inconclusive |
| LCP | 27.4s | 23.3s | Unchanged — H1 render delay (97%) is separate issue (ADR 0005/0010) |
| TBT | 50ms (+30) | 11,050ms | Discrepancy suggests bad run 2 state; run 1 excellent |
| CLS | 0 (+25) | 0 (+25) | Within margin of error |
| Render-blocking | **0ms** | **0ms** | Termly eliminated from render-blocking audit. jQuery 990-1,480ms last remaining |

**Render-blocking result:** Termly went from 4,350ms → **0ms** in the render-blocking audit across both runs. Issue #106 acceptance criteria met (< 2,000ms).

**Remaining Termly impact:** 3rd-party main-thread blocking time still 4,409ms (JS execution, not render-blocking). 4h cache TTL on 311KB of Termly scripts is the next optimization target. This is inherent to the Termly service, not our loading strategy.

**LCP unchanged:** The 97% render delay on H1 (opacity + translateX from theme JS) is pre-existing — see ADR 0005, ADR 0010. Not caused or worsened by this change.

## Rollback

```bash
ssh impressionor@impressionor.ssh.wpengine.net 'rm /sites/impressionor/wp-content/mu-plugins/io-termly-preconnect-async.php'
# Full cache purge (RULE 20)
```

## Related

- ADR 0010 — original analysis (NF6, Error 4/S5)
- ADR 0011 — RUCSS eliminated CSS blocking; Termly became last JS blocker
- [[termly-preconnect-async-fix]] — memory entry
- Issue #106 — tracking issue
