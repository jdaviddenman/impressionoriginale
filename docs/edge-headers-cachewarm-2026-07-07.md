# Edge security headers + homepage cache-warm — runbook (TODO, not yet applied)

Status: **DRAFT / TODO** — nothing here is live yet. Two low-blast-radius, edge-only
perf/hardening changes surfaced by the 3-scope perf analysis (2026-07-07). Both are applied at
Cloudflare / a scheduler, not at the WordPress origin — no theme, checkout, or layout is touched.

Verified live state that motivated this (curl, 2026-07-07):
- **No security headers present** — HSTS, CSP, X-Frame-Options, X-Content-Type-Options,
  Referrer-Policy, Permissions-Policy all absent (`curl -sI https://www.impressionoriginale.com/`).
- **Warm TTFB 65–95 ms** (Cloudflare HIT) but **cache-MISS origin render 2.6–3.5 s**. HTML TTL is
  10 min (`x-cacheable: SHORT`, `cache-control: max-age=600, must-revalidate`). Every TTL expiry
  hands the next visitor a ~3 s cold render.

---

## 1. Cloudflare Transform Rule — response security headers

Cloudflare dashboard → **Rules → Transform Rules → Modify Response Header → Create rule**.

- **Rule name:** `security-headers`
- **When incoming requests match:** `Hostname` `equals` `www.impressionoriginale.com`
  (expression: `http.host eq "www.impressionoriginale.com"`) — or "All incoming requests".
- **Then — Set static headers:**

| Header | Value |
|---|---|
| `X-Content-Type-Options` | `nosniff` |
| `X-Frame-Options` | `SAMEORIGIN` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Strict-Transport-Security` | `max-age=300` |

### HSTS — ship SHORT first (this is the only non-reversible header)

Once a browser sees an HSTS header it enforces HTTPS for `max-age` **regardless of later header
removal** — you cannot un-ring it. So:

1. First deploy: `max-age=300` (5 min), **no** `includeSubDomains`, **no** `preload`.
2. Confirm nothing breaks over a few days (all traffic is already HTTPS; apex 301→www is clean).
3. Only then ramp: `max-age=31536000`. Add `includeSubDomains` **only after** confirming every
   subdomain (mail, staging, WPE infra hostnames) is HTTPS — an HTTP-only subdomain gets stranded.
   Add `preload` last, and only if you intend to submit to the preload list.

`X-Frame-Options: SAMEORIGIN` is safe here: Stripe/YouTube/Pinterest frame **into** the page; the
page itself is not meant to be framed. If any legit partner embeds the storefront in an iframe,
switch to a `Content-Security-Policy: frame-ancestors` allowlist instead.

CSP is intentionally **omitted** — the site runs GTM, GA4, PixelYourSite, Meta, Pinterest, Termly
and inline theme scripts; a CSP needs report-only tuning first and is a separate workstream.

### Verify after applying

```bash
curl -sI https://www.impressionoriginale.com/ | grep -iE \
  'strict-transport-security|x-frame-options|x-content-type-options|referrer-policy'
# expect all four present. Re-run Lighthouse Best-Practices — the header flags clear.
```

### Rollback
Delete the Transform Rule. Instant for nosniff/XFO/Referrer-Policy. HSTS persists in already-served
browsers until its `max-age` elapses — which is why step 1 uses 300 s.

---

## 2. Homepage cache-warm — keep the 10-min TTL populated

The point: **volunteer** a scheduled request to eat the 3 s cold render right after each TTL
expiry, so a real visitor doesn't. Keep the 600 s TTL as-is — do **not** raise it: the HTML carries
WooCommerce nonces + price/stock, which is why WordPress emits `no-store` and WPE bounds it to
10 min.

Rules:
- Warm the **bare canonical URL** (no query string). A cache-buster (`?_=rand`) is a different cache
  key — it would MISS every time, always eat 3 s, and never populate the real entry. Do not add one.
- Run slightly **more often than the TTL** (every 5 min for a 600 s TTL) so the gap between expiry
  and re-warm stays small.
- Send a normal browser UA + `Accept: image/webp` is irrelevant here (HTML) but use a real UA so
  Cloudflare doesn't bot-challenge.

### URLs to warm (money pages)
```
https://www.impressionoriginale.com/
https://www.impressionoriginale.com/fr/
https://www.impressionoriginale.com/wrap/
https://www.impressionoriginale.com/ribbons/
```

### Option A — GitHub Actions (drop into `.github/workflows/cache-warm.yml` when ready)

Inert until committed to a workflows dir + Actions enabled. Kept here as a code block so this stays
a TODO, not an auto-running job.

```yaml
name: cache-warm
on:
  schedule:
    - cron: '*/5 * * * *'   # every 5 min (< 10-min HTML TTL)
  workflow_dispatch: {}
jobs:
  warm:
    runs-on: ubuntu-latest
    steps:
      - name: Warm homepage + top categories
        run: |
          UA='Mozilla/5.0 (Windows NT 10.0; Win64; x64)'
          for u in \
            https://www.impressionoriginale.com/ \
            https://www.impressionoriginale.com/fr/ \
            https://www.impressionoriginale.com/wrap/ \
            https://www.impressionoriginale.com/ribbons/ ; do
            code=$(curl -s -o /dev/null -A "$UA" -w '%{http_code} ttfb=%{time_starttransfer}' "$u")
            echo "$u -> $code"
          done
```
Note: GitHub cron is best-effort and can lag several minutes under load; if the warm gap matters,
use an external uptime monitor (UptimeRobot/Cron-job.org hitting these URLs every 5 min) or a WPE
server crontab instead.

### Option B — crontab on any always-on box
```cron
*/5 * * * * for u in https://www.impressionoriginale.com/ https://www.impressionoriginale.com/fr/ https://www.impressionoriginale.com/wrap/ https://www.impressionoriginale.com/ribbons/; do curl -s -o /dev/null -A 'Mozilla/5.0' "$u"; done
```

### Verify it's working
```bash
# After a few cycles, a cold-ish check should still be a HIT with a low age:
curl -sI https://www.impressionoriginale.com/ | grep -iE 'cf-cache-status|age|x-cache'
# TTFB should stay in the warm band (65–95 ms), not spike to 2.6–3.5 s.
```

---

## Blast radius / gate

- Both changes are **edge/scheduler only** — no origin, theme, plugin, cart, or checkout mutation.
- Not gated by the no-clone rule (ADR 0001): externally verifiable and instantly reversible
  (except the HSTS caveat above, handled by the short-max-age ramp).
- Neither addresses the real homepage LCP bottleneck (RevSlider gating the ~9 s hero paint) — that
  is the separate high-blast-radius workstream (#43), clone-first.

## Provenance
Surfaced by the 2026-07-07 3-scope perf analysis + adversarial (refute-charter) review. The review
killed the larger image-byte claims (favicon "1.29 MB" is ~41 KB via Cloudflare Polish webp; bulk
webp/recompress are not levers because Polish already serves webp where it helps). These two edge
items survived the review as genuinely safe same-day wins.
