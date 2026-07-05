# Impression Originale — Plugin Update & Removal Plan

**Publication note:** RULE 7 designates this content — the store's exact installed-version → vulnerability map — as private (kept out of this public repo). It is published here on **2026-07-05 by explicit operator decision** (jdaviddenman), overriding RULE 7 for this document, after being warned that a public copy exposes the store's currently-unpatched vulnerability surface permanently (cached/indexed/forkable) while the fixes are not yet applied. Risk accepted by the operator.

- **Date:** 2026-07-05
- **Site:** WordPress 7.0, WooCommerce 10.7.0, PHP 8.2, WP Engine (nginx), WPML EN/FR.
- **Provenance:** live crawl + 4 multi-source research agents (Patchstack/WPScan/Wordfence/vendor changelogs) → refute critic → NVD verification of the two load-bearing corrections.
- **Constraint:** no clone (ADR 0001) → this is a PLAN. Apply gated: RULE 3 backup → apply → RULE 4/5 verify.

---

## 1. Security-driven (installed version has a known vuln) — corrected ranking

| # | Plugin | Installed → target | Vuln (verified) | Severity | Blast radius |
|---|---|---|---|---|---|
| 1 | **Gravity Forms** | 2.10.0 → **2.10.5** | **CVE-2026-48866** unauth path-traversal / arbitrary file deletion, ≤2.10.0.1, fixed 2.10.1 (**NVD-confirmed**) + 5× stored XSS CVE-2026-5109/10/11/12/13 (7.2) | **CVSS 9.6 CRITICAL, unauthenticated** | **CONFIRMED USED + anonymously reachable** — 7 public forms (§12). Single most urgent action → **DO FIRST**. Same-minor update; backup + submit-test one form after |
| 2 | **WooCommerce Stripe Gateway** | 10.6.1 → **10.8.3** | CVE-2026-2381 missing-auth order-status manipulation, ≤10.7.0, fixed 10.8.0 (Wordfence/GHSA-xpf8-p6c2-qcp9) | 6.5 medium | **checkout** — sequence with WC→10.9.3 |
| 3 | **Enable Media Replace** | 4.1.9 → **4.2.2** | stored XSS ≤4.2.1, fixed 4.2.2 (Patchstack VDP; no CVE yet) | med, authenticated | admin-only; 4.2.x = UI redesign, test the replace screen |
| 4 | **Advanced Order Export** | 4.0.7 → **4.1.0** | CVE-2026-11360 SQLi via `sort_direction`, authenticated shop-manager+, fixed 4.1.0 | 4.9 medium (authenticated) | admin-only → **safe direct-to-live** |
| 5 | **Meta for WooCommerce** | 3.6.3 → **3.7.4** (or 3.7.1) | CVE-2026-49059 open redirect ≤3.7.0, fixed 3.7.1 | 4.7 low | tracking only |
| 6 | **WP Maps** | 4.9.3 → **4.9.5** | CVE-2026-9594 stored XSS, requires authenticated Administrator, fixed 4.9.5 | 5.9 med (admin-auth) | front-end map (live) |
| 7 | **Better Search Replace** | 1.4.10 → **1.4.11** | vendor-disclosed SQLi, **charset-gated (GBK/Big5; store is utf8mb4)**, admin-only | low real risk | admin tool |

**PixelYourSite is NOT here (corrected).** Installed free `pixelyoursite` 11.2.0.4 is **already patched** — its XSS is **CVE-2026-1841**, affects **≤11.2.0** (NVD-confirmed); CVE-2026-1844 is the **PRO** product, which you don't run. The routine update to 11.2.0.7 is fine; **no security driver, no removal-for-security**.

## 2. Do NOT blind-update — compat holds

- **WooCommerce 10.7.0 → 10.9.3. Target MUST be ≥10.9.2.** Two in-place-upgrade **fatals**: 10.7→10.8.0 (fixed 10.8.1) and 10.8.x→10.9.1 (missing `SettingsSectionRegistry`, fixed 10.9.2). Landing on 10.8.0 / 10.9.0 / 10.9.1 fatals wp-admin. No open CVE on 10.7.0 — this is a compat/parity update, not security. Expect the DB upgrade routine; backup first.
- **Google Analytics for WooCommerce → 2.2.1, NOT 2.3.0.** 2.3.0 requires WC 10.8+; you're on 10.7. 2.2.1 is WC-10.7-safe and carries a PHP 8 fatal-error fix. After WC→10.9.3, 2.3.0 becomes usable.
- **WPML ×4** (CMS 4.9.5 / String 3.5.3 / Media 3.1.2 / WC-ML 5.5.6) — no advisories, routine — but premium/**domain-locked** + high blast radius (FR URLs, hreflang, checkout). **Defer or per-change risk-accept** (no clone). Update all four together off-peak; verify FR routing + sitemap hreflang + FR add-to-cart after.

## 3. Advanced Google reCAPTCHA 1.34 → 5.39 — benign
Not a takeover. Same author (WebFactory) since 2021; the 1.x→5.x is a deliberate renumber to align free with PRO, because the two CVSS-8.8 CVEs (CVE-2026-5411/5415, file-upload + auth-bypass) are **PRO-only** and the shared slug caused scanner false-positives. Free 1.34 is clean. **Safe to update.** Gates login/checkout captcha → backup + verify a real checkout submit after (fail-closed = blocked sales). Confirm the install is the free WebFactory plugin.

## 4. Already patched — no security action (do not panic-update these for security)
WooCommerce core 10.7.0 (no open CVE) · WPCode 2.3.6 (RCE CVE-2026-8832 fixed 2.3.5→2.3.6) · Product Feed PRO 13.5.3 (CVE-2026-32443 fixed 13.5.2.2) · WebP Express 0.25.14 · WP Rocket 3.21.1 · Yoast 27.7 (XSS fixed 27.2). Update at leisure for features/compat.

## 5. Routine batch (no security pressure — backup, then apply, verify)
Yoast 27.9 · WP Rocket 3.22.0.3 · WebP Express 0.25.15 *(nginx: confirm WebP still serves post-update)* · Contact Form 7 6.1.6 · Product Feed PRO 13.5.5 · YITH Social Login 1.60.0 · WPCode 2.3.7 · Classic Editor 1.7.0 · Duplicate Page 4.5.9 · Simple Page Ordering 2.8.0 · Envato Market 2.0.14 · Redirection 5.8.1 *(re-test a 301)* · Additional Custom Emails 3.7.3 *(test order email)* · Weight-Based Shipping 6.16.0 · Back-In-Stock 7.2.2 · Product Bundles 8.5.9 · reCAPTCHA 5.39 *(test checkout)* · PixelYourSite 11.2.0.7 · Meta-for-WC / WP Maps (from §1, low/med).

## 6. Redundancy & removal

**Tracking stack is 5-deep** (crawl-confirmed installed): PixelYourSite + Meta-for-WC + GA-for-WC + Pinterest-for-WC + Mailchimp + GTM4WP/GTM + GA4. Overlap → **double/triple-counted conversions** (inflated ROAS, mis-trained ad algos).
- Meta browser Pixel: PixelYourSite **and** Meta-for-WC (± GTM4WP) → pick one; dedupe by `event_id`. (Note: free PYS = browser pixels only, **no CAPI**; server-side Meta = Meta-for-WC. So they overlap on the browser pixel, not CAPI.)
- GA4 ecommerce: GA-for-WC **and** GTM4WP→GA4 → keep one path or revenue double-counts.
- Pinterest / feeds: PYS Pinterest pixel vs Pinterest-for-WC; Product Feed PRO vs Meta/Pinterest native catalogs → audit channels.
- **Action:** Tag Assistant audit (what actually fires) BEFORE removing any tracker, then consolidate. Removing PYS is a **dedup** decision, not security; inventory its pixels first (may be sole Pinterest browser-pixel source).

**Removal tiers:**
- **DO NOT remove:** Redirection (SEO 301s), WP Maps (live map), YITH Social Login (live), WooCommerce, Stripe, WPML suite, Product Bundles, Back-In-Stock, WP Rocket, Yoast. **Envato Market** — do NOT deactivate: it's the **update channel** for Envato/ThemeForest items ("The Core" theme + Slider Revolution). **WPCode** — keep, but audit active snippets (may hold live GA4/GTM/Search-Console tags).
- **Deactivate-when-idle** (admin utilities, no front-end/SEO impact; confirm not mid-use): Better Search Replace, Duplicate Page, Simple Page Ordering.
- **Check-then-decide:** Classic Editor (content-team workflow?). *(**Gravity Forms** — resolved: **confirmed used** on 7 public forms (§12); remove-option retracted, update-only.)*

**Crawl honesty:** the crawl positively confirms front-end use; it cannot prove an admin-only plugin is unused. Every deactivate/remove above needs a wp-admin Plugins-screen + content/shortcode check.

## 7. Gated execution sequence
1. **Now — low-risk / free / admin-only:** Advanced Order Export 4.1.0, Enable Media Replace 4.2.2, WP Maps 4.9.5, Better Search Replace 1.4.11, Yoast 27.9.
2. **Gravity Forms 2.10.5** (if used) — unauth 9.6 CRITICAL. Test a form submit.
3. **Tracking decision** (Tag Assistant audit) — Meta-for-WC 3.7.4 and/or consolidate; PixelYourSite 11.2.0.7 (routine).
4. **Routine batch** (§5), backup first.
5. **Coupled, high-blast, off-peak, fresh backup:** **WooCommerce 10.9.3 + Stripe 10.8.3 together**, then **GA-for-WC 2.2.1**.
6. **Deferred/risk-accept (domain-locked):** WPML ×4 together.

## 8. Verification (RULE 4/5) after each change
- `wp plugin get <slug> --field=version` shows the target version.
- `harness/fingerprint.sh https://www.impressionoriginale.com <round>` → still 200, no new PHP errors / shortcode leakage / mojibake.
- Clear **both** WP Rocket + WP Engine caches before re-checking.
- Function-specific: checkout submit (Stripe/reCAPTCHA/WC), FR product add-to-cart + sitemap hreflang (WPML), a 301 (Redirection), an order email (Custom Emails), WebP still served (WebP Express).

## 9. Correction log (refute critic + NVD, 2026-07-05)
- **PixelYourSite — REVERSED:** was "trap / open 7.1 XSS / remove for security"; actually **already patched** (free CVE-2026-1841 ≤11.2.0, installed 11.2.0.4; agent had conflated PRO CVE-2026-1844). NVD-verified.
- **Gravity Forms — PROMOTED:** file-deletion buried as footnote; it's **CVE-2026-48866, unauth, CVSS 9.6 CRITICAL** → #1. NVD-verified.
- **WooCommerce — second fatal:** target must be ≥10.9.2 (two in-place fatals, not one).
- **Severity downgrades:** Meta-for-WC 4.7 low, WP Maps 5.9 admin-auth, Advanced Order Export 4.9 authenticated (XSS unconfirmed) — routine, not emergencies.
- **Envato Market:** removed from "deactivate-when-idle" — it gates Envato theme/Slider Revolution updates.

## 10. Open items (not yet resolved)
- **Slider Revolution 6.6.16 + WPBakery 8.7.3** — checked, see §11 (Slider Revolution is materially exposed / HIGH).
- ~~Is Gravity Forms actually used?~~ **RESOLVED 2026-07-05: yes — 7 public forms (§12). Update, do not remove.**
- Tag Assistant audit of the tracking stack (before any tracker removal).
- WPML ×4 risk-accept decision (no clone).

## 11. Theme-bundle components — vuln check (Slider Revolution 6.6.16, WPBakery 8.7.3)

Versions read from live `?ver=` asset strings (not the plugin-update screen — these update via the theme/Envato channel).

**WPBakery Page Builder (js_composer) 8.7.3 — CLEAN.** No known advisory affects 8.7.3; the most recent XSS (stored, via `vc_custom_heading` shortcode, ≤8.6.1) is fixed in 8.7. Routine/keep current. Source: Patchstack `js_composer` DB.

**Slider Revolution (revslider) 6.6.16 — MATERIALLY EXPOSED. HIGH.** Patched for the ≤6.6.15 file-upload (CVE-2023-47784) but sits below ~9 later fixes. Affecting 6.6.16 (fixed in 6.7.x/7.x), verified on Patchstack revslider DB:
- **CVE-2024-34444 — Broken Access Control, <6.7.0, UNAUTHENTICATED, CVSS 7.1** (fixed 6.7.0).
- **Missing Authorization → Arbitrary File Read (≤6.7.37)** + Arbitrary File Read via SVG/images (≤6.7.36).
- **Missing Authorization → Arbitrary plugin Deactivation (≤6.7.55).**
- Stored XSS: htmltag (≤6.7.7), SVG upload (≤6.7.18), Layer attrs (≤6.7.10), plus ≤6.6.20 / <6.7.11 / ≤6.7.13.
- (The 7.0.x-range vulns do NOT apply — 6.6.16 is below 7.0.0.)

- **Urgency:** HIGH — a stack of unauth / missing-auth issues (file read, access control, plugin deactivation) on a component dozens of releases behind (current is 7.x). Not a single 9.6 like Gravity Forms, but the worst-maintained item in the whole stack.
- **Blast radius / gotcha:** renders front-end sliders → **layout break-zone**. A 6.6→6.7→7.x jump is a large upgrade with real layout-break risk. Slider Revolution is almost certainly **bundled with the "The Core" (EngineThemes) Envato theme** → the update likely comes via the theme bundle / Envato Market license, not a standalone plugin update (this is why it's absent from your 32-item update list, and ties to §6 — do NOT deactivate Envato Market). Confirm the update channel first.
- **Recommendation:** clearest **clone-first** case (RULE 1) — high layout blast radius + big version jump. No clone (ADR 0001) → either stand up an on-demand clone (ADR 0002) to prove the 6.6→7.x jump, or defer + explicit risk-accept with a fresh backup (RULE 3) and heavy post-update layout verification (homepage + every slider). Do NOT bulk-jump blind on live.

## 12. Gravity Forms — confirmed used (crawl, 2026-07-05)

Full EN+FR page crawl (51 pages) found live Gravity Forms front-end on **7 publicly-reachable (HTTP 200) forms** — all unauthenticated and submittable, i.e. the exact attack surface for CVE-2026-48866 (unauth 9.6 file deletion) + the 5 unauth stored XSS:

- `/become_a_dealer/` — dealer / distributor signup
- `/corporate-gifts/` — corporate gifts
- `/corporate-gifts-order-form-online/` — B2B order form
- `/submit-an-inquiry/` — inquiry
- `/fr/cadeaux-daffaires/` — corporate gifts (FR)
- `/fr/devenez-distributeur/` — become distributor (FR)
- `/fr/soumettre-une-requete/` — submit request (FR)

Contact Form 7 is enqueued site-wide, but the actual forms on these pages are Gravity Forms. These are business-critical lead-gen / B2B-order forms → **update, do not remove**. Confirms §1 #1 is anonymously reachable now → highest-priority action; after updating to 2.10.5, submit one real form to confirm forms still work (fail-closed risk).
