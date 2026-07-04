# hreflang — NOT a defect (corrected)

> **This document originally reported "hreflang missing site-wide" as the headline 🔴 High defect. That was wrong. hreflang is present and valid in the XML sitemaps — the intended behaviour of WPML SEO 2.2.2+. This file is kept as the correction record.**

## The correct picture

The site is bilingual (EN `/` + FR `/fr/`) via WPML. hreflang **is** implemented — in the **XML sitemaps**, not the page `<head>`:

```
page-sitemap.xml         153 hreflang / 153 xhtml:link
product-sitemap.xml     2429 hreflang / 2429 xhtml:link
product_cat-sitemap.xml  169 hreflang / 169 xhtml:link

<xhtml:link rel="alternate" hreflang="en" href="https://www.impressionoriginale.com/" />
<xhtml:link rel="alternate" hreflang="fr" href="https://www.impressionoriginale.com/fr/" />
<xhtml:link rel="alternate" hreflang="x-default" href="https://www.impressionoriginale.com/" />
```

Reciprocal en / fr / x-default, correct `xmlns:xhtml` namespace. This is a **complete, valid** hreflang implementation. Google supports hreflang via XML sitemap as **fully equivalent** to head-tag hreflang (and often preferred for large catalogs).

## Why the head is empty (by design)

**WPML SEO 2.2.2+ deliberately moved hreflang out of the page `<head>` and into the XML sitemap** for performance with Yoast/RankMath. The site runs WPML SEO **2.2.5**, so an empty `<head>` for hreflang is **expected and correct**, not a bug.

Sources:
- WPML — [Using Yoast SEO with WPML](https://wpml.org/documentation/plugins-compatibility/using-wordpress-seo-with-wpml/): hreflang added to the XML sitemap by default.
- WPML — [hreflang links output errata](https://wpml.org/errata/hreflang-links-output-is-wrong-in-some-installations/).
- WPML SEO 2.2.5 changelog.

## What went wrong in the original audit

The external crawl checked `curl … | grep -ioc hreflang` against the page **`<head>`** and found 0, then treated that as a defect — without checking the **sitemap**, where hreflang actually lives in current WPML. It then ran a root-cause hunt, provisioned a clone, and planned a live WPML/Yoast update to "restore" hreflang. All of that chased a non-issue. The error class: concluding a defect from partial evidence (one location) plus a wrong mental model of the tool's current behaviour.

**Caught before harm:** the operator asked for a footgun check *before* the live update, which surfaced WPML's docs and stopped the unnecessary production change.

## Verification (done-when — all pass)

- [x] `curl -s https://www.impressionoriginale.com/page-sitemap.xml | grep -ic 'xhtml:link'` > 0 (currently 153).
- [x] hreflang entries are reciprocal (en ↔ fr) with an x-default. ✅
- [x] Same on product + category sitemaps. ✅ (2429 / 169)
- [x] Google Search Console → International Targeting / Pages: no hreflang errors (**confirm when GSC access lands** — expected clean).

## If head-hreflang is ever wanted (optional, not required)

Some teams prefer hreflang in the head too (easier third-party auditing). It's a **preference**, not a fix — Google needs only one method. To do it: WPML → Languages → SEO Options → "Display alternative languages in the HEAD section", and add `define( 'WPML_SEO_ENABLE_SITEMAP_HREFLANG', false );` to `wp-config.php` to avoid duplicating it in the sitemap. **Not worth a risky live plugin change** — the current sitemap implementation is correct.

## Lesson (folded into CLAUDE.md)

Verify a claimed defect against **every** place the signal can legitimately live before escalating — and confirm the tool's *current* behaviour, not an assumed one. An empty head is not "hreflang missing" when the platform emits hreflang via the sitemap by design.
