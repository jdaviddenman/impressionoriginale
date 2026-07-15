# Ad Strategy: impressionoriginale.com

**Date:** 2026-07-14
**Status:** Research complete — strategy ready for operator review
**Budget range:** $540–1,250/month (phased, months 1–3)

---

## 1. Executive Summary

**Start with Google Standard Shopping at $15/day ($450/month), free Merchant Center listings, and an immediate review-collection system. Fix hero-image LCP before ad dollar #1. First profitable campaign expected by Day 60–90. Second platform (Pinterest, $500/month) added at Day 90+ if Shopping shows positive unit economics.**

Google Shopping captures existing purchase intent — someone already searching for "luxury gift wrap." No creative production needed (product feed only). Standard Shopping (not PMax) gives negative keyword control, critical for a niche store. PMax is contraindicated at this budget: it needs 30–60 conversions/month to optimize, and at $15/day this store will generate ~15–20/month. PMax also front-loads brand/remarketing traffic, producing fake-high month-1 ROAS that collapses when warm audiences exhaust.

Pinterest is the stronger strategic fit for the niche (visual, planning-oriented, gift-giving), but adversarial evidence shows a 50%+ failure rate for small stores in months 1–3. Shopping is the safer on-ramp: lower failure rate, intent-capture rather than demand-creation, faster feedback loop.

---

## 2. Current State Assessment

### Store Context

- **URL:** https://www.impressionoriginale.com/
- **Niche:** Luxury gift wrapping — paper, ribbons, bows, boxes, bags, furoshiki, scissors, tags, place cards
- **USP:** Designed by independent artists, printed on recycled stock, made in France
- **Languages:** English (default at root) + French (`/fr/`) via WPML
- **Markets:** Canada primarily, EU (FR audience)
- **Products:** 410 products (artisan-designed gift wrap)
- **AOV:** $30–100 estimated
- **Current organic:** ~1.9K users/month, ~€433 revenue (June 2026)
- **Platform:** WordPress 7.0 + WooCommerce 10.9.4 on WP Engine, Cloudflare CDN

### Working

- GA4 live via GTM (Measurement ID `G-Y88VQHFDBV`); obsolete UA tag (`UA-85910237-1`) removed
- Yoast SEO active; valid `sitemap_index.xml`
- Valid hreflang via XML sitemap (WPML SEO 2.2.2+ design)
- Product schema on pages (Offer, price, availability)
- Google Business Profile exists
- Stripe payment processing (Merchant Center-compatible)
- WPML bilingual (EN default at root, FR at `/fr/`)
- EN+FR category title rewrites applied — keyword-first with `|` separator on 31/36 categories (86%). 5 stragglers remain: `christmas-capsule` (EN, 20 products — `I` sep), `capsule-noel` (FR, 20 products — `I` sep + English title on FR category), `instinct-animal-fr` (FR, 14 products — `–` sep), `wraps-x3` and `kit-x-3-feuilles` (0 products — `I` sep). 32 template strings still carry hardcoded `I` instead of `%%sep%%` (2026-07-15)
- Breadcrumbs rendering on product pages (EN+FR) — `BreadcrumbList` with 3 items (2026-07-14)
- og:image site-wide Yoast default set (verified 2026-07-04)
- 126 legacy `I`-sep product titles cleared; separator flipped `–` → `|` site-wide (2026-07-08)

### Broken — Must Fix Before Ad Dollar #1

| Issue | Impact on Ads | Fix Effort |
|---|---|---|
| **LCP 9.1s** (Google "Poor" bucket: >4.0s) | +33% CPC penalty, ~50% conversion collapse vs 2.5s | **Partial fix applied 2026-07-14:** hero images compressed -68% (781→248KB). LCP re-measurement pending. See `docs/lcp-hero-image-fix.md`. CSS/JS deferral gated behind LCP measurement. |
| **No product reviews** (by business design — store does not do reviews) | Cold ad traffic lands on pages with zero social proof; luxury purchase without trust signals = conversion risk. Note: this is an intentional business decision, not a defect — but it still impacts ad conversion rates. | If operator decides to add: install free review plugin, set up post-purchase email at 48h, 1-hour setup |
| **6-plugin tracking stack** (PixelYourSite + Meta-for-WC + Google Site Kit + Pinterest-for-WC + GTM4WP + Mailchimp for WP) — *verified 2026-07-15* | **Double-counting confirmed.** Google Site Kit (1.182.0) loads `gtag.js?id=GT-5TPLSSZ`; GTM4WP loads `gtm.js?id=GTM-MT7G7Z3C` — two Google containers on the same page. Pinterest pixel double-fires (PageVisit x2). Meta pixel managed by two plugins (PixelYourSite + Facebook-for-WC, same pixel ID `1011540012316296`). Broken attribution poisons algorithm optimization. | Audit + deduplicate: 2–4 hours. Keep GTM4WP as single source of truth for GA4 ecommerce events. Remove or disable redundant pixels in Site Kit, PixelYourSite, Pinterest-for-WC, Meta-for-WC. |
| **Termly consent banner is non-functional** — *verified 2026-07-15* | **GDPR non-compliant.** Zero Termly JavaScript loads; only HTML footer links remain. No `gtag('consent', ...)` calls exist. All tracking fires unconditionally before any consent interaction. 30–50% of EU traffic would lose tracking if consent were ever enforced. | Reinstall/reconfigure Termly with Consent Mode v2: default all `denied`, upgrade to `granted` only after user consent. 1–2 hours. |
| **`/shop/` and `/fr/shop/` are `noindex,follow`** — *verified 2026-07-15* | Primary product listing pages blocked from Google index. Either intentional (duplicate-content avoidance vs category pages) or a defect — either way, paid traffic lands on pages that can't rank organically. | 2 minutes: investigate Yoast setting. If intentional: document as ADR. If defect: flip to `index`. |
| **Stale blog** (last post Aug 2025 — confirmed 2026-07-14) | Zero top-of-funnel content for gift-idea searches; no Google Discover eligibility | Low priority for ad launch; fix after Shopping is live |
| **4 plugins behind** (verified 2026-07-15 via WP-CLI: PDF Invoices 5.15.1→5.15.2, PixelYourSite 11.2.0.7→11.2.1, Site Kit 1.182.0→1.183.0, Stripe 10.8.3→10.8.4) — all minor/patch, not a security concern | Negligible — all patch bumps | Deferred; update in next maintenance window |
| **Homepage H1 still `IMPRESSION ORIGINALE`** (not keyword-optimized — documented fix was never applied) | Weakens landing page relevance for branded search; missed opportunity for "Luxury Gift Wrap, Made in France" in H1 | 1 minute: edit page H1 in WordPress |
| **`/shop/` (EN+FR) missing meta description** — no `<meta name="description">`, no `og:description` on either language | No SERP snippet control on the primary product listing page; FR title is English `Shop` not French `Boutique` | 5 minutes: set meta descriptions in Yoast; localize FR title |
| **`/bespoke-services/` (EN+FR) missing meta description** — no `<meta name="description">`, no `og:description` | No SERP snippet control on the services page | 5 minutes: set meta descriptions in Yoast |
| **`/portfolio/furoshiki/` missing meta + H1 + og:description** — portfolio post type may not have Yoast fields configured | No SERP snippet control; no heading for accessibility/SEO | 10 minutes: enable Yoast on portfolio post type, set meta + H1 |
| **5 static pages with ALL-CAPS title prefixes** — `/our-philosophy/`, `/our-products/`, `/where-to-find-us/`, `/bespoke-services/`, `/corporate-gifts-order-form-online/` | Keyword-first but uppercase reads as shouting in SERPs → lower CTR | 5 minutes: convert to title case or sentence case in Yoast |
| **`/our-philosophy/` meta typos** — `"optimazing"` → `"optimising"`, `"minimizes"` → `"minimises"` (UK English) | Typos in SERP snippet = amateur signal, lower CTR | 1 minute: fix in Yoast meta field |
| **`/fr/notre-savoir-faire/` returns 404** | Broken link if referenced anywhere; missing FR landing page | 2 minutes: investigate + either create page or add redirect |
| **`/fr/shop/` title is English `Shop`, not French `Boutique`** | FR shop page has English title in SERPs — confusing for French-language searchers | 1 minute: localize title in Yoast |

**Gates before spend:** LCP < 4s, tracking deduplicated, Termly consent reinstalled with Consent Mode v2, `/shop/` noindex resolved, review collection live (if operator decides to add reviews). Missing meta descriptions + H1 + typos are quick fixes (~30 min total) and should be resolved before any paid landing pages go live.

---

## 3. Platform Recommendations (Ranked)

### Rank 1: Google Standard Shopping

**Rationale:** Captures existing purchase intent. Someone types "luxury wrapping paper" into Google — product image + price appears. No creative production needed. Negative keyword control prevents budget bleed on "cheap gift wrap," "cellophane," "dollar store wrap."

Standard Shopping (not PMax) because:
- PMax needs 30–60 conversions/month to optimize; this store will generate ~15–20 at $15/day
- PMax has no native negative keywords
- PMax front-loads brand/remarketing traffic producing fake-high ROAS that collapses by month 3
- 62% of advertisers report PMax made performance worse
- Real case studies show +35% to +200% revenue gains switching from PMax to Standard Shopping

| Parameter | Value |
|---|---|
| Starting daily budget | $15/day ($450/month) |
| Expected CPC | $0.30–0.80 (Shopping, home/gift niche) |
| Expected ROAS (first 90 days) | 1.5–3x months 1–2, 3–5x month 3+ |
| Setup complexity | Medium (Merchant Center + feed plugin + feed optimization + negative keyword list) |
| Time to launch | 1–2 weeks (including feed approval) |

**Key configuration:**
- Set `identifier_exists = false` on all products (no GTINs — artist-designed)
- Separate feed labels for EN (Canada) vs FR (EU) — do not dump both markets into one campaign
- Negative keywords day 1: "cheap," "dollar store," "cellophane," "DIY," "bulk," "wholesale," "plastic," "kids," "christmas" (add Christmas back in Q4)
- Manual CPC bidding for first 30 days, not automated. Switch to "Maximize Conversions" only after 30+ conversions in 30 days
- Feed attributes: fill material, size, color, pattern, occasion, artist — WooCommerce has no native GTIN field; feed plugin must explicitly handle this
- Shipping + returns + privacy pages must exist and match feed settings exactly (misrepresentation = #1 suspension cause)
- Feed plugin: Google Listings & Ads (WooCommerce official, free) or CTX Feed (free tier)
- Product titles in feed: front-load keyword + material + use case. "Luxury Washi Gift Wrap Sheet — Gold Foil Pattern — 50x70cm" not "Gift Wrap Set"
- Images: 1000×1000px minimum, no watermarks (upload clean versions as primary; watermarks on-site only)

### Rank 2: Pinterest Catalog/Shopping Ads (Month 3+, gated)

**Rationale:** Best strategic fit for the niche (visual, planning-oriented, gift-giving, 96% unbranded searches). But adversarial evidence shows 50%+ small-store failure rate in months 1–3. The "Inspiration Browser" problem is real: pinners save to "Someday" boards with zero purchase intent. Pinterest's 30-day click attribution inflates ROAS by 20–50% vs normalized 7-day windows — platform-reported 5–15x is actually ~2.9x normalized.

**Gates:** (a) Google Shopping showing positive unit economics for 60+ days, (b) 50+ product reviews live, (c) LCP < 4s.

| Parameter | Value |
|---|---|
| Starting daily budget | $17/day ($500/month) |
| Expected CPC | $0.20–0.75 |
| Expected ROAS (first 90 days) | 1.5–3x (normalize to 7-day click window) |
| Setup complexity | Medium–Hard (Pinterest Tag + catalog feed + Pin creation + 4–8 week learning phase) |
| Time to launch | 2–3 weeks (catalog approval + initial Pin creation) |

**Key configuration:**
- Pinterest's WooCommerce integration for catalog sync
- Separate catalogs for EN and FR audiences
- 5–10 Pin variations ready before launch (creative fatigue hits at 2–4 weeks per Pin)
- Target: women 25–55, interests: gift wrapping, home decor, DIY crafts, wedding planning, luxury lifestyle
- Do not judge performance before week 6–8 (60–90 day learning phase)
- Watch for bot traffic: gap between billed "Pin clicks" and actual outbound clicks is a red flag; ~5.2% of Pinterest ad requests are blocked as invalid

### Rank 3: Google Branded Search (Month 1, alongside Shopping)

**Rationale:** Bid on "impression originale," "impression originale gift wrap," "impression originale wrapping paper." CPC near $0.10–0.50. Near-certain conversions. Protects brand from competitors bidding on it.

| Parameter | Value |
|---|---|
| Starting daily budget | $3–5/day ($90–150/month) |
| Expected CPC | $0.10–0.50 |
| Expected ROAS | 5–15x (branded = highest-intent traffic) |
| Setup complexity | Easy (one Search campaign, exact match keywords) |
| Time to launch | 1 day after Google Ads account is live |

### Rank 4: Meta Retargeting (Month 3+, gated)

**Rationale:** Captures the 96–98% of visitors who don't convert on first visit. Dynamic catalog ads for viewed products, cart-abandonment sequences. Do NOT use Meta for cold prospecting at this budget — cold Home & Garden median ROAS is 2.18x, below breakeven for many margins under 50%. Retargeting ROAS is typically 3–8x.

| Parameter | Value |
|---|---|
| Starting daily budget | $5/day ($150/month) |
| Expected ROAS | 3–8x (retargeting) |
| Setup complexity | Easy (Meta pixel via GTM + catalog feed + one retargeting campaign) |
| Gate | 1,000+ monthly site visitors |

**Key configuration:** Exclude purchasers from retargeting audiences. Frequency cap at 3 impressions/day. Advantage+ catalog ads format.

### NOT RECOMMENDED

- **Google PMax:** Needs 30–60 conversions/month. This store at $15–30/day will never hit that. 62% of advertisers say PMax made performance worse. Do not touch until 60+ conversions/month.
- **TikTok:** Median ROAS 1.41x. Creative lifespan 1–2 weeks. Audience 18–35 with less disposable income. Not for luxury AOV products.
- **Bing/Microsoft Ads:** Search volume for "luxury gift wrap" in French/Canadian markets completely unverified — likely near-zero. Worth a 30-day $5/day test ONLY after Google Shopping is stable and keyword research shows volume.

---

## 4. Phased Budget — Months 1–3

### Month 1: Foundation (Total: $540–600)

| Channel | Daily | Monthly | Expected Outcome |
|---|---|---|---|
| Google Standard Shopping | $15 | $450 | 15–20 conversions. Data gathering. ROAS likely 0.8–1.5x — negative or break-even |
| Google Branded Search | $3–5 | $90–150 | Near-certain conversions. ROAS 5–15x. Small volume |

**Go/no-go at Day 30:**
- **GO if:** (a) tracking verified — GA4 purchase events match actual orders within 10%, (b) Shopping has generated 15+ conversions, (c) no Merchant Center suspension
- **NO-GO if:** tracking broken, zero Shopping conversions, or Merchant Center disapproved
- If GO: increase Shopping to $20/day. If NO-GO: fix the blocker, do not increase spend.

### Month 2: Optimization (Total: $690–750)

| Channel | Daily | Monthly | Expected Outcome |
|---|---|---|---|
| Google Standard Shopping | $20 | $600 | ROAS 2–3x. Negative keywords refined. First winning products identified |
| Google Branded Search | $3–5 | $90–150 | Maintained as defensive spend |

**Go/no-go at Day 60:**
- **GO if:** (a) Shopping ROAS > 2x (break-even or better at estimated margins), (b) 50+ reviews live on site, (c) LCP < 4s
- **NO-GO if:** Shopping ROAS consistently < 1.5x with no upward trend, or reviews still at zero
- If GO: add Pinterest at $500/month starting Day 90. If NO-GO: pause Shopping, fix conversion rate (reviews + LCP), retest with smaller budget.

### Month 3: Expansion (Total: $1,190–1,250)

| Channel | Daily | Monthly | Expected Outcome |
|---|---|---|---|
| Google Standard Shopping | $20 | $600 | ROAS 3–5x. Scaled to 20% budget increase |
| Google Branded Search | $3–5 | $90–150 | Maintained |
| Pinterest Catalog Ads | $17 | $500 | Data gathering. ROAS likely 0.5–1.5x in learning phase. Do not judge before Day 150 |
| Meta Retargeting (conditional) | $5 | $150 | Only if 1,000+ monthly visitors. Expected ROAS 3–8x |

**Go/no-go at Day 90:**
- **GO if:** combined ROAS across all channels > 2.5x, tracking consistent, no platform suspensions
- **SCALE if:** any single channel consistently > 4x ROAS for 30+ days — increase that channel's budget 20%/week
- **PIVOT if:** combined ROAS < 1.5x with no channel > 2x. Stop all spend. Fix conversion rate. Retest with one channel only.

---

## 5. Pre-Flight Checklist

All items are gates — must be complete before ad dollar #1.

### SEO Content Fixes — Quick Wins for Ad Landing Pages (30 min, new)

Found in the 2026-07-14 live re-audit. All are low-effort, high-impact for SERP snippet quality on landing pages that paid traffic will hit.

- [ ] **Fix homepage H1:** change `IMPRESSION ORIGINALE` → `Luxury Gift Wrap, Made in France` (per `docs/home-title-meta-rewrite.md`)
- [ ] **Set `/shop/` meta description** (EN): keyword-rich, 155–160 chars. Set `og:description` (auto from Yoast)
- [ ] **Set `/fr/shop/` meta description** (FR): French-language. Localize title from `Shop` → `Boutique`
- [ ] **Set `/bespoke-services/` meta description** (EN+FR): both languages
- [ ] **Set `/portfolio/furoshiki/` meta + H1 + og:description**: enable Yoast on portfolio post type if needed
- [ ] **Fix 5 ALL-CAPS page titles**: `/our-philosophy/`, `/our-products/`, `/where-to-find-us/`, `/bespoke-services/`, `/corporate-gifts-order-form-online/` → title case or sentence case
- [ ] **Fix `/our-philosophy/` meta typos**: `"optimazing"` → `"optimising"`, `"minimizes"` → `"minimises"`
- [ ] **Investigate `/fr/notre-savoir-faire/` 404**: create page or add redirect
- [ ] **Verify with live fetch**: `curl` each page, confirm changes render in CDN (`cf-cache-status: MISS`)

### Conversion Tracking (2–4 hours)

- [ ] Audit 5-pixel stack: identify which tags fire on which events
- [ ] Deduplicate: keep GTM4WP as single source of truth for GA4 ecommerce events. Disable redundant pixels in PixelYourSite, Meta-for-WC, GA-for-WC, Pinterest-for-WC (or remove plugins entirely if they serve no other function)
- [ ] Verify: place a test order, confirm exactly ONE purchase event fires in GA4 DebugView
- [ ] Add UTM parameters: auto-tagging for Google Ads + manual UTM template for Pinterest (`utm_source=pinterest&utm_medium=cpc&utm_campaign={campaign}&utm_content={ad}`)
- [ ] Set up Google Ads conversion tracking: import GA4 purchase event as primary conversion action. Attribution model: data-driven (default)
- [ ] Verify Google Ads conversion tag fires post-purchase on thank-you page

### Landing Page Quality (1–2 hours)

- [x] Compress all hero/product images to WebP, target < 100KB each — **hero done (118KB+130KB), product images not yet**
- [x] Add `fetchpriority="high"` to above-fold hero image — **already present (WP Rocket auto-preload)**
- [x] Remove autoplay hero video/slider if any — **no video found; slider autoplay preserved (11 CTAs, business decision)**
- [ ] Verify LCP drops below 4s (Google "Needs Improvement" bucket — minimum for paid traffic). Target: < 2.5s ("Good")
- [ ] Test 3 product pages on mobile (Lighthouse): LCP, CLS, INP all in "Good" or "Needs Improvement" range
- [ ] Checkout and cart pages: confirm they load and function correctly

### Merchant Center Feed (3–5 hours)

- [ ] Create Google Merchant Center account (use same Google account as GA4)
- [ ] Verify + claim domain via GA4 tag (instant — tag already on site)
- [ ] Install Google Listings & Ads plugin (WooCommerce official, free)
- [ ] Configure feed: set `identifier_exists = false` globally (no GTINs)
- [ ] Fill ALL product attributes: material, size, color, pattern, occasion, artist, paper weight
- [ ] Create separate feed labels for EN (shipping to Canada) and FR (shipping to EU)
- [ ] Verify tax + shipping settings in GMC match the site exactly
- [ ] Confirm Returns, Shipping, Privacy, Terms pages exist, are accessible, and match feed
- [ ] Upload clean (no watermark) primary product images for feed — watermarks cause disapproval
- [ ] Submit feed for review. Approval typically 3–5 business days
- [ ] Fix all diagnostics before launching ads — every warning becomes a suspension risk

### Cookie Consent for Ad Tracking (2–4 hours — Termly is broken, needs reinstallation)

- [ ] **Termly consent banner is BROKEN.** Zero Termly JavaScript loads; only HTML footer links remain. No `gtag('consent', ...)` calls exist. All tracking fires unconditionally — GDPR non-compliant for EU traffic. Reinstall/reconfigure Termly with Consent Mode v2 (default all `denied`, upgrade to `granted` only after user consent). Verify: clear cookies, reject all → zero ad tags fire; accept all → tags fire.
- [ ] Google Ads + GA4: confirm tags fire only after consent. If Consent Mode is not implemented, tracking breaks for ~30–50% of EU traffic
- [ ] Pinterest Tag + Meta pixel: same — must respect consent
- [ ] Test: clear cookies, visit site, reject all — confirm no ad tags fire. Accept all — confirm tags fire

### Ad Account Setup (2 hours)

- [ ] Create Google Ads account. Link to existing GA4 property
- [ ] Link Google Ads to Merchant Center
- [ ] Set up billing
- [ ] Create conversion action: import GA4 purchase event
- [ ] Create audiences: all visitors (30-day), purchasers (180-day), cart abandoners (7-day) — for future retargeting
- [ ] Install Pinterest Tag via GTM (for future Pinterest campaigns, even if not launching month 1)
- [ ] Set up Pinterest business account + claim website + create product catalog (setup now, use later)

### Creative Assets (2–4 hours)

- [ ] Select 10–20 best product images (lifestyle shots: wrapped gift on styled surface > plain product shot). 1000×1000px minimum, no watermarks
- [ ] Write product titles in feed-optimized format: "Luxury [Material] Gift Wrap — [Pattern/Color] — [Size] by [Artist]"
- [ ] Build negative keyword list for Shopping (minimum 50 terms): cheap, dollar store, cellophane, DIY, bulk, wholesale, plastic, kids, wrapping paper roll (if selling sheets), craft paper, butcher paper, tissue paper, newsprint
- [ ] Draft branded Search ad copy (3 headlines, 2 descriptions): "French Luxury Gift Wrap | Designed by Independent Artists | Recycled Stock, Made in France"
- [ ] Define audiences: Canada (EN), France/Belgium/Switzerland (FR), women 25–55, interests: gift giving, home decor, DIY, weddings, luxury lifestyle

---

## 6. Measurement Plan

### Key Metrics

| Metric | Target (Day 30) | Target (Day 60) | Target (Day 90) |
|---|---|---|---|
| Blended ROAS (total rev / total ad spend) | > 1.5x | > 2.5x | > 3x |
| Shopping ROAS | > 1.5x | > 2x | > 3x |
| CPA (cost per purchase) | < $20 | < $15 | < $12 |
| CTR (Shopping) | > 0.5% | > 0.8% | > 1% |
| Conversion rate (product page) | > 1.5% | > 2% | > 2.5% |
| Impressions share (Shopping) | Track only | > 10% | > 20% |

### Tools

- **GA4:** Primary source of truth for revenue, conversions, user behavior
- **Google Ads dashboard:** Campaign-level performance. Do NOT trust platform-reported ROAS alone — compare against GA4 revenue
- **Google Search Console:** Organic performance baseline. Track whether ads cannibalize organic branded traffic
- **No Triple Whale/Northbeam/Rockerbox:** $400–500/month attribution tools unjustified at $450–1,250/month ad spend. GA4 UTM-based attribution is sufficient
- **Manual blended ROAS calculation weekly:** Total GA4 ecommerce revenue (all channels) / total ad spend. This is the single number that matters.

### Attribution Rules

- **GA4 purchase event is canonical.** If Google Ads reports 15 conversions but GA4 shows 12, GA4 wins
- **UTM parameters on all ad URLs.** No UTM = invisible in GA4, even if Google Ads auto-tagging is on
- **Attribution window:** 7-day click, 1-day view (standard for ecommerce). Do NOT use 30-day click — it inflates Pinterest ROAS by 20–50% and makes cross-platform comparison impossible
- **Conversion deduplication:** GA4 purchase event fires once per transaction. If other pixels fire additional purchase events, those are duplicates — ignore for blended ROAS

### Milestone Criteria

**Day 30:**
- Success: tracking verified, 15+ Shopping conversions, no Merchant Center suspension, blended ROAS > 1x
- Failure: tracking broken OR zero Shopping conversions OR Merchant Center suspended
- Action on failure: fix blocker, do not increase spend

**Day 60:**
- Success: blended ROAS > 2x, 50+ reviews live, LCP < 4s, CPA trending down
- Failure: blended ROAS < 1.5x with no upward trend, OR reviews still at zero
- Action on failure: pause Shopping, fix conversion rate, retest with smaller budget

**Day 90:**
- Success: blended ROAS > 2.5x, two channels profitable, Pinterest data accumulating
- Failure: blended ROAS < 1.5x across all channels
- Action on failure: stop all spend. Reassess product-market fit, conversion rate, and whether paid ads are the right channel. The store may need to fix fundamentals (reviews, LCP, product page design) before ads can work.

---

## 7. Risks and Pitfalls

### Seasonal Demand (Critical)

Gift wrap is **hyper-seasonal**: ~58% of annual consumption at Christmas, 31% of sales in December alone. Launching ads in July 2026 means advertising when demand is at its annual trough.

**Impact:** Low search volume, low conversion rates, and potential false-negative signals ("ads don't work" when the real problem is timing).

**Mitigation:** Launch Shopping now to gather data and train the algorithm cheaply (summer CPCs are lower). By Q4, the algorithm has 3–4 months of conversion history and is ready for the holiday spike. Do NOT judge ROAS against annual benchmarks until Q4 data is in.

**Seasonal content calendar to align with ad campaigns:**

| Season | Ad Push Timing | Products to Feature |
|---|---|---|
| Christmas/Hanukkah | Oct 15 – Dec 15 | All gift wrap, ribbons, bows, boxes, tags, place cards |
| Valentine's Day | Jan 15 – Feb 10 | Romantic patterns, red/pink, silk ribbons |
| Mother's Day (France: May 25) | May 1–24 | Floral patterns, luxury boxes, furoshiki |
| Wedding season | May–Oct | Elegant/neutral gift wrap, place cards, ribbon |
| Back to school / corporate | Aug–Sep | Bulk-friendly bundles, corporate gifting boxes |

### Learning Phase Budget Burn

At $15/day, Shopping will generate ~40–60 clicks/day at $0.30–0.50 CPC, ~15–20 conversions/month at 1.5% CVR. That is below the 30 conversions/month Smart Bidding threshold. Manual CPC avoids this problem entirely — do not switch to automated bidding until hitting 30+ conversions/month consistently.

### Conversion Tracking Contamination

The 5-pixel stack is the most likely source of silent failure. If GA4 fires 3 purchase events per transaction (one per pixel plugin), Google Ads will see 3x the real conversion volume. The algorithm will optimize toward phantom conversions and ROAS will collapse when fixed. Verify deduplication before launch; do not "fix it later."

### LCP 9.1s Kills Ad ROI

Google Quality Score assigns "Below Average" landing page experience to 9s LCP pages. This increases CPC by up to 33%. Combined with conversion collapse (~50% lower CVR vs a 2.5s page), the effective CPA is roughly **2x what it would be on a fast page.** At $15/day, that difference is the gap between break-even and losing money.

### French-Language Ad Complexity

Two markets (Canada EN default + France/Belgium/Switzerland FR) with different currencies, shipping expectations, and search behavior. Dumping both into one campaign = algorithm confusion.

**Rule:** separate campaigns or feed labels per market. EN Shopping campaign for Canada. FR Shopping campaign for EU. Separate budgets. Branded Search in both languages. This means two of everything, but it is the only way to get clean data.

### Margin Sensitivity

The store's actual gross margins are unstated. This changes everything:

| Gross Margin | Break-even ROAS | Viability at $0.50 CPC, 1.5% CVR, $50 AOV |
|---|---|---|
| 30% | 3.33x | Unlikely — Shopping ROAS for new stores typically 1.5–3x. Ads will lose money. Stick to organic. |
| 40% | 2.50x | Marginal. May break even with optimization. High risk. |
| 50% | 2.00x | Viable. 2x ROAS is achievable in months 2–3. |
| 60% | 1.67x | Solid. Nearly any positive ROAS is profitable. |

**Pre-flight gate:** calculate actual gross margin on top 10 products. If average is below 40%, the paid ad path is high-risk. If below 30%, paid ads are not viable at this AOV — invest in organic instead.

### Merchant Center Suspension

The #1 killer for new advertisers. Misrepresentation (site claims vs feed attributes mismatch, missing policy pages, watermarked images, "Made in France" claims unverifiable on landing pages) triggers instant suspension that takes weeks to appeal. Every claim on the site must be verifiable on the landing page the ad points to.

### WooCommerce "Hidden Feed Tax"

Unlike Shopify (native GTIN field, automatic feed sync), WooCommerce has no native GTIN field, no automatic feed sync, and requires deliberate plugin investment. A WooCommerce store launching Shopping ads without proper feed optimization is paying 10–25% more per conversion:

| Missing Attribute | CPA Increase |
|---|---|
| Size / Color | 15–25% |
| GTIN / identifier | 10–20% |
| Material / Custom Labels | 10–15% |

---

## 8. Free/Low-Cost Alternatives (Parallel Tracks)

Run alongside paid ads. They compound — organic trust signals improve paid conversion rates.

### 1. Google Free Shopping Listings (Week 1)

Setup overlaps entirely with paid Shopping feed. Once Merchant Center is approved and feed is live, opt into "Surfaces across Google." Products appear in Shopping tab, Images, Lens — no ad spend. Free listings convert 18% higher than paid Shopping (2.07% vs 1.70% CVR). Realistic volume: 50–500 clicks/month once feed matures. Pure margin.

### 2. Review Collection System (Week 1)

Install a free review plugin (Customer Reviews for WooCommerce or similar). Automated post-purchase email at 48 hours after delivery. One-click review process. Incentive: 10% off next purchase. Target: 50 reviews within 8 weeks.

This is the single highest-ROI activity — 270% increase in purchase likelihood from 0 to 5 reviews. Reviews feed seller ratings into Shopping ads (star ratings = 17% higher CTR, 26% more conversions).

> **Note (2026-07-14):** The store currently does not do product reviews by business decision. This section assumes a decision to add reviews before running ads — the operator must decide. Without reviews, Shopping ads will not carry seller ratings, and cold traffic will land on pages with zero social proof.

### 3. Google Business Profile Optimization (Low effort, ongoing)

Owner already has a GBP. Add product listings with 720×720px images, keyword-rich names, direct product links — these don't expire. Post weekly: new collections, seasonal gift guides, artist features. Answer Q&A with FAQ content. Upload fresh lifestyle photos monthly.

**Risk:** GBP is for businesses with face-to-face customer contact. Online-only stores risk suspension if flagged. Keep the profile but don't invest heavily — free Shopping listings are higher priority.

### 4. Blog Revival (Month 2+, ongoing)

Revive the stale blog (last post Aug 2025). 1–2 posts/month targeting gift-giving occasions and top-of-funnel searches. Topics:
- "How to Wrap a Gift Like a French Artisan"
- "12 Luxury Gift Wrap Ideas for Wedding Season"
- "The Ultimate Guide to Furoshiki Gift Wrapping"
- "Sustainable Luxury: Why Recycled Gift Wrap Matters"

Feeds Google Discover eligibility and provides landing pages for seasonal ad campaigns.

### 5. Pinterest Organic (Month 1, alongside ad setup)

Create business account, claim website, start pinning product photos. 5–10 pins/week. Rich Pins enabled via Yoast schema (already on site). Organic pins compound for months/years. Builds audience before paid Pinterest launches in month 3. Zero cost.

### 6. Email Marketing (Month 2+)

Mailchimp already installed. Set up:
- Abandoned cart sequence (1h, 24h, 72h)
- Post-purchase thank-you with cross-sell
- New arrivals monthly
- Seasonal gift guide emails (Oct, Jan, Apr)

Email ROAS: $36–42 return per $1 spent (industry benchmark). At current organic traffic (~1.9K users/month), email capture rate of 2–3% = 38–57 new subscribers/month. Small list but zero marginal cost per send.

### 7. Image SEO Audit (Week 1–2)

Descriptive filenames, alt text on every product image, image sitemap submitted to Search Console, WebP format. Images drive 22.6% of all web searches. Brands investing in image metadata see up to 37% increase in organic image clicks within 60 days. Also improves Google Shopping feed quality (image is a ranking factor).

---

## 9. Research Basis

### Benchmarks Used

| Platform | ROAS (Home/Gift) | CPC | Source |
|---|---|---|---|
| Google Shopping | 3.0–8.0x (median 4.2x) | $0.30–2.50 | Ryze AI 2026 (15K advertisers) |
| Meta (FB/IG) | 2.18x (median) | $0.50–1.50 | Triple Whale 2025 (35K brands) |
| Pinterest | 2.5–15x (normalized: ~2.9x) | $0.10–1.50 | PAS (343+ projects); Stella 2025 (225 geo-tests) |
| TikTok | 1.41x (median) | $0.50–1.00 | Triple Whale 2025 |

### Key Cross-Reference Findings

**Confirmed by real-user evidence:**
- PMax front-loads brand/remarketing then ROAS collapses — heavily confirmed (62% of advertisers agree)
- CPMs rising across Meta (+20% YoY)
- Creative is the #1 performance lever, not targeting
- Fix tracking before spending — documented $60K loss from silent pixel failure
- Pinterest attribution inflates ROAS by 20–50% (30-day click window vs normalized 7-day)

**Challenged by real-user evidence:**
- "PMax at $20–30/day" → refuted. Standard Shopping + manual CPC is correct at low budgets
- "Pinterest minimum $500/month" → likely too low. $1,000–1,500/month realistic for meaningful data
- "Meta for retargeting only" → too absolute. Cold prospecting CAN work with right creative (multiple 9–27x ROAS cases)
- "Free Shopping listings: 600–6,000 clicks/year" → aspirational, not evidence-based. Volume depends on feed quality + competition

### Sources (68 URLs across 6 research dimensions)

1. [Google Shopping Ads Benchmarks Per Industry 2025 — AdBacklog](https://adbacklog.com/blog/google-shopping-ads-benchmarks-per-industry-2025)
2. [Average Ecommerce ROAS by Vertical 2026 — Eightx](https://eightx.co/blog/average-ecommerce-roas-by-vertical-2026)
3. [What Is a Good ROAS? Industry Benchmarks 2026 — Visionary Marketing](https://visionary-marketing.co.uk/blog/what-is-a-good-roas-2026)
4. [Pinterest vs Meta vs Google Ads for Home Decor 2025–2026 — PAS](https://pinterestadvertisingstuff.com/pinterest-vs-meta-vs-google-home-decor)
5. [Performance Max vs Standard Shopping — BigFlare](https://www.bigflare.com/blog/performance-max-vs-standard-shopping-which-one-should-you-use-and-why)
6. [When To Say No To PMax — Search Engine Journal](https://www.searchenginejournal.com/when-to-say-no-to-pmax-strategic-use-cases-for-standard-shopping-campaigns/561257/)
7. [How To Test Google Ads PMax at ANY Budget Level — RobTronic Media](https://robtronicmedia.com/library/how-to-test-google-ads-performance-max-at-any-budget-level/)
8. [A Guide to Misrepresentation in Google Merchant Center — ProductHero](https://www.producthero.com/post/a-guide-to-misrepresentation-in-google-merchant-center)
9. [Google Shopping Feed Errors: How to Fix All GMC Issues 2025 — Simprosys](https://simprosys.com/simprotips/google-merchant-center-errors-and-fixes/)
10. [Google Merchant Center Setup Guide 2025 — Datamain](https://datamain.io/the-ultimate-guide-to-google-merchant-center-setup-benefits-best-practices-for-2025/)
11. [Google Ads Exposed 2025: Costs, Benchmarks, ROAS — 3R SEO Consultants](https://www.3r.ie/google-ads-exposed-2025-costs-benchmarks-roas-landing-page-factor/)
12. [Google Ads Ecommerce Strategy Guide 2026 — Involve Digital](https://www.involvedigital.com/insights/google-ads-ecommerce-strategy-guide)
13. [Pinterest Ads: Everything You Need To Get Started — Hootsuite](https://blog.hootsuite.com/pinterest-ads/)
14. [Pinterest Ads Are Changing the Game — JeffBullas](https://www.jeffbullas.com/pinterest-ads/)
15. [Pinterest Ads for Shopify Stores — Tenten](https://tenten.co/shopify/pinterest-ads-shopify-ecommerce/)
16. [Pinterest Ads Case Studies — PAS](https://pinterestadvertisingstuff.com/pinterest-ads-case-studies)
17. [Facebook Ad Benchmarks by Industry — Triple Whale](https://www.triplewhale.com/blog/facebook-ads-benchmarks)
18. [Why Brands Are Getting Higher ROI With Bing Ads in 2025 — Comms8](https://www.comms8.com/blog/bing-ads-higher-roi-microsoft-advertising)
19. [Are Bing Ads Worth It? — Seventy Seven Collective](https://seventyseven.co/are-bing-ads-worth-it-a-guide-for-smart-marketers/)
20. [Microsoft Ads for Shopify — Tenten](https://tenten.co/shopify/microsoft-ads-bing-shopify/)
21. [TikTok Benchmarks 2025 — Triple Whale](https://www.triplewhale.com/reports-guides/tiktok-benchmarks)
22. [Facebook Ads vs TikTok Ads for E-commerce — Mad Social](https://madsocial.co.uk/blog/facebook-ads-vs-tiktok-ads-ecommerce/)
23. [Meta vs TikTok Ads for E-Commerce in 2026 — Coinis](https://coinis.com/blog/meta-vs-tiktok-ecommerce-ads-2026)
24. [TrueProfit ROAS Benchmarks](https://trueprofit.io/blog/what-is-a-good-roas)
25. [TerraHQ Google Ads Benchmarks 2026](https://terrahq.com/en/blog/google-ads-benchmarks-2025)
26. [Top Growth Marketing CPC Benchmarks](https://topgrowthmarketing.com/ecommerce-ads-cpc-benchmarks/)
27. [Pinterest Ads Cost for Ecommerce — AI Advantage Agency](https://aiadvantageagency.com/pinterest-ads-cost-for-ecommerce)
28. [Paid Media Benchmarks 2026 — Sert Media](https://sertmedia.com/paid-media-benchmarks/)
29. [Shopify Marketing Budget for Small Stores 2026 — DigitalSMB](https://digitalsmb.org/shopify-marketing-budget-small-stores-2026)
30. [How Much Should Ecommerce Spend on Ads — GrowWithBA](https://growwithba.com/blog/how-much-ecommerce-spend-ads)
31. [Ecommerce Advertising Costs — Cropink](https://cropink.com/ecommerce-advertising-costs)
32. [Top 10 Ecommerce Ad Mistakes — CompleteGurus](https://completegurus.com/top-10-mistakes-ecommerce-businesses-make-with-google-and-meta-ads-and-how-to-avoid-them/)
33. [Why Most Brands Waste 40% of Ad Budget — SQRoot](https://sqroot.in/blog/why-most-brands-waste-40-of-their-ad-budget-and-how-to-fix-it/)
34. [Biggest Ad Mistakes 2025 — Ecommerce Coach](https://ecommercecoach.beehiiv.com/p/the-biggest-mistakes-i-ve-seen-this-year)
35. [Meta Ad Testing Mistakes — ChannelLife](https://channellife.com.au/story/meta-ad-testing-mistakes-costing-ecommerce-brands-profit-expert-warns)
36. [Gift Wrapping Products Market Report — Straits Research](https://straitsresearch.com/report/gift-wrapping-products-market/research-methodology)
37. [State of the Industry: Gift Wrap in the U.S. — Research and Markets](https://www.researchandmarkets.com/reports/5440136/state-of-the-industry-gift-wrap-in-the-u-s)
38. [Gift Wrapping Products Market — DataIntelo](https://dataintelo.com/report/gift-wrapping-products-market)
39. [Christmas Gift Guide That Converts — GrowthSuite](https://www.growthsuite.net/resources/shopify-holiday-campaigns/christmas-holiday-season/gift-guide-that-converts)
40. [Custom POD Gift Wrap — StickersAndPosters](https://stickersandposters.com/custom-pod-gift-wrap-a-year-round-opportunity-for-brands-and-retailers/)
41. [Google Shopping SEO — Digital Commerce](https://digitalcommerce.com/google-shopping/)
42. [Google Shopping Free Listings Guide — FeedOps](https://feedops.com/feedops/google-shopping-free-listings/)
43. [What Are Free Product Listings — SEO.ai](https://seo.ai/blog/what-are-free-product-listings)
44. [GBP Features Small Ecommerce Stores Ignore — Ecommerce Fastlane](https://ecommercefastlane.com/google-business-profile-features-small-ecommerce-stores/)
45. [GBP for Online Businesses — Birdeye](https://birdeye.com/blog/google-business-profile-for-online-business/)
46. [GBP Optimization 2025 — Brandit](https://branditms.com/google-business-profile-optimization/)
47. [7 Steps to Optimize Product Images — Retouching Labs](https://retouchinglabs.com/7-steps-to-optimize-your-product-images-and-be-found-in-google-shopping/)
48. [Image SEO in 2025 — Rank Nashville](https://ranknashville.com/photo-gallery-image-seo-in-2025-a-complete-guide-to-ranking-visual-content/)
49. [Google Discover for Ecommerce — Clara Soteras (Zurich 2025)](https://speakerdeck.com/clarasoteras/from-newsrooms-to-e-commerce-the-google-discover-strategy-youre-not-using-yet-google-search-central-live-zurich-2025-clara-soteras)
50. [How to Optimize for Google Discover — Victorious](https://victorious.com/blog/how-to-optimize-for-google-discover/)
51. [40 Traffic Sources — Rise at Seven](https://riseatseven.com/blog/40-traffic-sources-resources/)
52–68. Additional sources from Reddit cross-reference (r/PPC, r/ecommerce, r/smallbusiness, r/shopify), agency case studies (Grow My Ads, Stackmatix, Hellihub, PPC.io, PinHouss, Pinwell Media), and benchmark reports (Stella 2025, JudeLuxe 2026, Channable 2026, Triple Whale 2025, Ryze AI 2026).

---

## 10. Summary Decision Matrix

| Decision | Answer | Rationale |
|---|---|---|
| First platform | Google Standard Shopping | Captures existing intent, no creative needed, lowest failure rate for small stores |
| First month budget | $540–600 ($15/day Shopping + $3–5/day Branded) | Manual CPC, data-gathering phase |
| PMax? | No. Not until 60+ conversions/month | Needs data this store does not have. 62% of advertisers say PMax made performance worse |
| Pinterest? | Yes, but month 3+ only, gated on Shopping profitability + reviews + LCP | Better strategic fit but higher small-store failure rate |
| Meta? | Retargeting only, month 3+, gated on 1,000+ monthly visitors | Cold prospecting ROAS too low for this margin/AOV |
| TikTok? | No | 1.41x median ROAS, wrong demographic, creative churn too high |
| Bing? | Not now. 30-day $5/day test after Shopping is stable | Search volume for "luxury gift wrap" unverified |
| Reviews needed before ads? | Yes. 50 minimum before scaling spend | Zero reviews + paid cold traffic = conversion suicide. Note: reviews are currently absent by business design — operator must decide whether to add them. |
| LCP threshold before ads? | < 4s before paid Shopping. Retargeting can launch at current 9.1s | LCP penalty: +33% CPC, ~50% CVR collapse |
| Fix tracking first? | Yes. Deduplicate 5-pixel stack | Broken tracking = algorithm poisoned from day 1 |
| Free Shopping? | Immediately. Same setup as paid | Zero cost, 3–8% revenue lift |
| Break-even ROAS gate? | Calculate actual margins on top 10 products before launch | If margin < 30%, ads are not viable. Pivot to organic. |
| Seasonal timing? | Launch July (now). Low summer CPCs = cheap training data. Algorithm ready by Q4 spike | September ideal but July is better than waiting |
