# Plugin Maintenance Program

Goal: keep plugins current **without breaking a live store**. Two opposing risks:
- **Fossilization** — outdated plugins are the #1 WordPress attack vector; the further behind, the riskier the eventual forced update.
- **Careless updates** — bulk-updating a live WooCommerce store breaks checkout/layout.

The answer is a disciplined, tiered, staging-first cadence. The full plugin/version inventory (attack surface) is kept in the **private note**, not this public file; this doc holds the strategy + the specific landmines.

## Environment reality (matters for testing)
- **WP Engine staging** is required for **functional/checkout testing** (a real, renderable front end).
- The **UpdraftPlus clone** can confirm "updates apply without a fatal error" via WP-CLI, but its **front end hangs** (won't render) — so it **cannot** test checkout, layout, or forms. Use WPE staging for those.
- Backup before every round (DB + plugins is sufficient for plugin updates — skip the media library; see the backup note).

## Risk tiers (from the live update inventory, ~30 plugins behind)

### Tier A — High blast radius (checkout / payment) → isolate, staging, full checkout matrix
- **WooCommerce core 10.7.0 → 10.9.3** and **WooCommerce Stripe Gateway 10.6.1 → 10.8.3**: **update these two together.** WooCommerce 10.9.0 changed an internal `FeedInterface` that **breaks older Stripe gateway versions** — your Stripe is old, so this is a real break if updated alone. Target **10.9.3** (patched; 10.9.0/.1 had update-time fatals, fixed in 10.9.2/.3). Run the full checkout matrix (add-to-cart → cart → checkout → test payment → order-received → emails) on staging before live. Update in **isolation** — nothing else in the same round.
- Sources: WooCommerce 10.9.3 release notes; woocommerce/woocommerce issue #66031 (fatal after 10.8→10.9).

### Tier B — Layout / infra risk → staging + visual check
- **WP Rocket 3.21.1 → 3.22.0.3**: cache/optimisation; can shift layout/CWV. Update alone so any regression is attributable; clear both WP Rocket + WPE caches after.
- (WPBakery, Slider Revolution, the `engic` theme are the layout break-zone — not in the current update list, but the same rule applies if they ever are.)

### Tier C — Investigate BEFORE updating (anomalies)
- **Advanced Google reCAPTCHA 1.34 → 5.39**: the jump is a **version renumber** aligning the free plugin to the PRO 5.x line (not a different plugin), but current reviews report it became bloated/upsell-heavy and its captcha behaviour changed. **Test the form captcha on staging**, or better, **evaluate replacing it** (e.g. Cloudflare Turnstile) — it guards Gravity Forms / contact / login. Do not bulk-update blind.
- **YITH WooCommerce Social Login 1.58.0 → "1.40.0"**: the offered "update" is a **lower** version (numbering anomaly). Investigate; ensure a bulk-updater doesn't downgrade it.

### Tier D — Low-risk, batch on staging → live
Utility/tracking/SEO plugins: Additional Custom Emails, Advanced Order Export, Back In Stock Notifier, Classic Editor, Duplicate Page, Enable Media Replace, Google Analytics for WooCommerce, Gravity Forms, Meta/Facebook for WooCommerce, **PDF Invoices (5.9→5.15 — big jump, test invoice generation)**, PixelYourSite, Product Feed PRO, Redirection, Simple Page Ordering, UpdraftPlus, WebP Express, Weight Based Shipping, WPCode, WP Maps, **Yoast SEO (27.9)**, **WPML family + WooCommerce ML**. Batch these, test, ship.
- **Clone already proved:** Yoast 27.9 and WooCommerce Multilingual 5.5.6 update cleanly. WPML premium (core/String/Media) only downloads on the **registered** domain (fails on the clone) — so update those on staging/live, not the clone.

### Security-first override (jumps the queue)
Run a vulnerability scan — **Patchstack / Wordfence / WPScan** — against the plugin set. **Any plugin with an active CVE is patched ASAP (within 24–48h)**, regardless of tier. Half of high-impact WP vulnerabilities are exploited within 24h of disclosure.

## The cadence (ongoing, so it doesn't fossilize again)
1. **Weekly:** apply Tier D minor/patch updates on staging → quick smoke test → live. Clear caches.
2. **On disclosure:** security patches within 24–48h (may skip the weekly batch cadence — but still staging-smoke-test first unless the vuln is actively exploited).
3. **Monthly / as needed:** Tier B, and Tier A majors with a **1–2 week soak** after release (let others surface bugs) + isolation + full checkout matrix.
4. **Before each round:** backup (DB + plugins). **After each round:** functional checklist + cache clear.
5. **Document** each round (what updated, date) — reuse `reports/tracking.md`'s change-log pattern.

## Functional checklist (staging, after each round)
Home renders (slider/layout) · category + product render · **add-to-cart → cart → checkout → payment step → order emails** · language switch EN↔FR · currency switch · Gravity/contact form submits (+ captcha) · PDF invoice generates.

## First recommended action
1. Run a **Patchstack/Wordfence scan** to find any active CVEs → those first.
2. On **WPE staging**: batch **Tier D** (backup → update → checklist → cache clear) — highest safety, clears most of the backlog, builds confidence.
3. Then **Tier A** (WC core 10.9.3 + Stripe 10.8.3 together, isolated, checkout matrix).
4. **Tier C** anomalies only after individual investigation.
