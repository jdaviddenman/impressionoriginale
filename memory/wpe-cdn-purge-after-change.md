---
name: wpe-cdn-purge-after-change
description: "After any HTML/content/CSS/JS change on live, purge the full CDN stack — not just wp cache flush. Produced by 7-hour stale-cache incident."
metadata:
  type: feedback
---

After every code or content mutation on the live site, purge the full cache stack. `wp cache flush` clears WP Engine's Varnish origin only — Cloudflare edge nodes hold stale HTML for up to 28 days (`cache-control: max-age=2419200`).

**Why:** A viewport meta fix was applied to 3 theme files on 2026-07-07. `wp cache flush` ran, `curl` verification passed. But the verification used `?nocache=X` which bypassed Cloudflare. Real visitors continued receiving the old viewport for 7+ hours (`cf-cache-status: HIT`, `age: 25725`). The fix was code-complete but invisible. An adversarial review caught it and purged the CDN.

**How to apply:** After any HTML/content/CSS/JS change on live, run:

```bash
ssh impressionor@impressionor.ssh.wpengine.net '
  wp cache flush
  wp eval "
    if (class_exists(\"WpeCommon\")) {
      WpeCommon::purge_varnish_cache_all();
      WpeCommon::clear_cdn_cache();
      WpeCommon::clear_maxcdn_cache();
      WpeCommon::purge_memcached();
    }
  "
'
```

Then verify: `curl -sI "https://www.impressionoriginale.com/" | grep cf-cache-status` must show `MISS` or `EXPIRED`. Never use `?nocache=X` for post-change verification — it reads the origin, not what visitors receive.

Encoded as CLAUDE.md RULE 15. Related: [[wpe-ssh-slow-handshake]] for SSH credentials, [[feedback-verify-ground-state]] for the verify-before-claiming principle.
