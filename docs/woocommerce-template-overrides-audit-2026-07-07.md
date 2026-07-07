# WooCommerce "outdated templates" warning — audit & decision

**Date:** 2026-07-07
**Trigger:** wp-admin notice — *"Your theme (Engic Child) contains outdated copies of some WooCommerce template files."*
**Decision:** **Option A — do nothing.** The warning is cosmetic. No change to live.
**Status:** Closed as *not-a-defect* (working as designed; version-header lag on intentionally-customized theme templates).

---

## TL;DR

The Engic theme (parent `engic` + child `engic-child`) overrides **52 WooCommerce templates**. WooCommerce's System Status flags **10** as "outdated" because the override's `@version` header is behind the current core template's.

A read-only audit (full `diff` of every override against the installed core template) shows these are **not stale copies of core** — they are **heavily rewritten bespoke templates** (custom `eut-` markup, designer tabs, gallery logic, custom cart/grid layouts). WooCommerce's actual changes across every version gap are **benign** (guard hardening, accessibility spans, perf micro-optimizations, a duplicated-`<tfoot>` fix). **Zero security/escaping fixes and zero breaking hook removals** were introduced core-side. The overrides preserve the WooCommerce hook surface, so third-party integrations, coupons, tracking, and AJAX add-to-cart continue to work.

**Recopying the core templates (the "official" fix) would destroy the store's layout.** The standard "recopy + re-apply your changes" remedy assumes light drift; the reality here is deep customization. The correct action is to **leave the templates as-is and accept the notice.**

---

## Why it matters

- The notice implies a compatibility problem. In practice it is version-header lag, not a functional defect — the store renders and checks out correctly.
- The instinctive fix (copy the new core template over the override) is **actively dangerous** here: it would wipe the theme's custom markup and break shop/cart/product/checkout layouts. Recording the audit prevents a future operator from "fixing" it destructively.
- Establishes the baseline for the real maintenance concern: these templates will **never** track core, so the `@version` warning is permanent unless headers are bumped (Option C, declined).

---

## Evidence

Method: read-only SSH, `diff -u <core> <override>` for every `*.php` under both theme `woocommerce/` directories against the installed WooCommerce plugin templates. Raw output archived at `scratchpad/wc-override-audit.txt` (not committed — 221 KB of commercial EngineThemes source; RULE 7).

### Verified core-vs-core deltas for the 10 flagged templates (benign)

Each core change was confirmed against GitHub-tagged WooCommerce source, not guessed:

| Template | Layer | What core changed | Severity |
|---|---|---|---|
| `content-product.php` | child | `empty($product)` → `is_a($product, WC_Product)` guard (PHP-8 type safety) | med (low-prob WSOD) |
| `archive-product.php` | child | Inline title block replaced by new `woocommerce_shop_loop_header` hook | med (inert hook) |
| `loop/add-to-cart.php` | child | Added `aria-describedby` screen-reader span | low (a11y) |
| `single-product/add-to-cart/variable.php` | parent | Added VariationGallery snapshot block (feature gated, ~unused here) | med (feature) |
| `cart/cart.php` | parent | Hook set byte-identical; i18n/a11y refinements only | low |
| `single-product/meta.php` | child | Comment + `ProductType::VARIABLE` enum refactor (behaviour identical) | low |
| `single-product/product-image.php` | parent | One-line perf micro-optimization | low |
| `single-product/tabs/tabs.php` | child | ARIA `role`/`aria-controls` moved `<li>` → `<a>` | low (a11y) |
| `order/order-details.php` | parent | De-duplicated a `<tfoot>` in the totals table | low (cosmetic) |
| `order/tracking.php` | parent | `@version` header bump only; body unchanged | none |

### Scope-of-customization: all 52 overrides (diff magnitude)

Large diffs = deep theme rewrites, **not** staleness. This is the core reason recopy is unsafe.

> **Note:** the *Override* column is a best-effort `grep` of the first `@version` match in each theme file and disagrees with WooCommerce System Status on a few rows (e.g. `order/tracking.php` grep=10.6.0 vs System-Status theme=10.1.0). **WooCommerce System Status governs the flagged set;** the diff-magnitude columns are the reliable evidence here. The 10 flagged templates are those the System Status listed with a "Version X is out of date" note.

| Template | Layer | Override* | Core | Δ (+/−) | Note |
|---|---|---|---|---|---|
| `archive-product.php` | child | 3.4.0 | 8.6.0 | +112/−97 | flagged; double-override with parent |
| `content-product.php` | child | 3.6.0 | 9.4.0 | +109/−67 | flagged; **child wins**; carries old `empty()` guard |
| `loop/add-to-cart.php` | child | 3.3.0 | 9.2.0 | +21/−31 | flagged |
| `single-product.php` | child | 3.0.0 | — | +90/−62 | designer content, `the_content` echo |
| `single-product/meta.php` | child | 3.0.0 | 9.7.0 | +55/−42 | flagged |
| `single-product/tabs/tabs.php` | child | 3.8.0 | 9.8.0 | +41/−19 | flagged; designer tab (see security note) |
| `single-product/title.php` | child | 4.4.0 | — | +5/−13 | |
| `cart/cart-empty.php` | parent | 7.0.1 | 7.0.1 | +17/−28 | |
| `cart/cart-item-data.php` | parent | 2.4.0 | 2.4.0 | +7/−15 | |
| `cart/cart-totals.php` | parent | 2.3.6 | 2.3.6 | +46/−39 | |
| `cart/cart.php` | parent | 10.1.0 | 10.8.0 | +138/−190 | flagged; full bespoke grid rewrite |
| `cart/cross-sells.php` | parent | 9.6.0 | 9.6.0 | +67/−28 | renders `content-product.php` |
| `cart/mini-cart.php` | parent | 10.0.0 | 10.0.0 | +66/−69 | |
| `cart/proceed-to-checkout-button.php` | parent | 7.0.1 | 7.0.1 | +9/−15 | |
| `checkout/cart-errors.php` | parent | 3.5.0 | 3.5.0 | +14/−13 | |
| `checkout/form-billing.php` | parent | 3.6.0 | 3.6.0 | +72/−21 | |
| `checkout/form-checkout.php` | parent | 9.4.0 | 9.4.0 | +47/−36 | |
| `checkout/form-coupon.php` | parent | 9.8.0 | 9.8.0 | +7/−33 | |
| `checkout/form-shipping.php` | parent | 3.6.0 | 3.6.0 | +91/−26 | |
| `checkout/payment-method.php` | parent | 3.5.0 | 3.5.0 | +4/−12 | |
| `checkout/payment.php` | parent | 9.8.0 | 9.8.0 | +9/−18 | Stripe path — XSS-clean |
| `checkout/review-order.php` | parent | 5.2.0 | 5.2.0 | +47/−42 | XSS-clean |
| `checkout/thankyou.php` | parent | 8.1.0 | 8.1.0 | +52/−66 | |
| `content-product.php` | parent | 9.4.0 | 9.4.0 | +70/−49 | dead weight — child copy wins |
| `content-single-product.php` | parent | 3.6.0 | 3.6.0 | +66/−51 | |
| `loop/add-to-cart.php` | parent | 9.2.0 | 9.2.0 | +12/−29 | |
| `loop/pagination.php` | parent | 9.3.0 | 9.3.0 | +48/−30 | |
| `loop/price.php` | parent | 1.6.4 | 1.6.4 | +3/−11 | |
| `loop/sale-flash.php` | parent | 1.6.4 | 1.6.4 | +4/−15 | |
| `loop/title.php` | parent | 2.4.0 | n/a | +0/−0 | pure theme template, not a WC override |
| `notices/error.php` | parent | 8.6.0 | 8.6.0 | +27/−20 | |
| `notices/notice.php` | parent | 10.4.0 | 10.4.0 | +23/−19 | |
| `notices/success.php` | parent | 8.6.0 | 8.6.0 | +22/−18 | |
| `order/order-details-customer.php` | parent | 8.7.0 | 8.7.0 | +31/−66 | |
| `order/order-details.php` | parent | 10.1.0 | 10.9.0 | +26/−80 | flagged |
| `order/order-downloads.php` | parent | 3.3.0 | 3.3.0 | +23/−35 | |
| `order/tracking.php` | parent | 10.1.0 | 10.6.0 | +21/−42 | flagged (per System Status) |
| `single-product-reviews.php` | parent | 9.7.0 | 9.7.0 | +59/−99 | |
| `single-product.php` | parent | 3.0.0 | — | +38/−19 | |
| `single-product/add-to-cart/simple.php` | parent | 10.2.0 | 10.2.0 | +23/−21 | |
| `single-product/add-to-cart/variable.php` | parent | 9.6.0 | 10.9.0 | +57/−86 | flagged |
| `single-product/meta.php` | parent | 9.7.0 | 9.7.0 | +33/−20 | |
| `single-product/price.php` | parent | 3.0.0 | 3.0.0 | +4/−12 | |
| `single-product/product-image.php` | parent | 9.7.0 | 10.5.0 | +55/−45 | flagged (per System Status) |
| `single-product/product-thumbnails.php` | parent | 9.8.0 | 9.8.0 | +61/−30 | |
| `single-product/rating.php` | parent | 3.6.0 | 3.6.0 | +18/−21 | |
| `single-product/related.php` | parent | 10.3.0 | 10.3.0 | +62/−41 | renders `content-product.php` |
| `single-product/sale-flash.php` | parent | 1.6.4 | 1.6.4 | +4/−15 | |
| `single-product/short-description.php` | parent | 3.3.0 | 3.3.0 | +6/−14 | |
| `single-product/tabs/additional-information.php` | parent | 3.0.0 | 3.0.0 | +15/−15 | |
| `single-product/tabs/description.php` | parent | 2.0.0 | 2.0.0 | +10/−16 | |
| `single-product/tabs/tabs.php` | parent | 9.8.0 | 9.8.0 | +10/−18 | |

\* grep heuristic — see note above; System Status is authoritative for the flagged set.

---

## Secondary findings (not caused by the warning; recorded for later)

1. **Live WooCommerce is ≥ 10.9, not 10.7.** Core template `@version` values reach 10.9.0, which cannot exceed the installed WC version. `CLAUDE.md`'s "WooCommerce 10.7.0" is stale and should be re-verified.
2. **`content-product.php` is a double override** — both `engic/` and `engic-child/` ship it; the **child wins** (WordPress child-theme precedence), so the parent copy is dead weight. The rendering child copy still carries the pre-9.4 `empty($product)` guard. Blast radius is wider than the shop grid: `content-product.php` is also rendered by `cart/cross-sells.php` (cart page) and `single-product/related.php` (product page).
3. **Checkout templates are XSS-clean.** Every dynamic echo in `checkout/*`, `cart/*`, `review-order.php`, `payment.php` uses core-standard pre-escaped functions (`wc_get_formatted_cart_item_data`, `wc_kses_notice`, `apply_filters('woocommerce_cart_item_*')`, etc.).
4. **A few theme-custom echoes lack escaping** — but only on **admin-controlled** data, not user input:
   - `single-product/tabs/tabs.php` (child): `<?= $designer->post_title ?>` into attribute/text; `<?= $profile_src ?>` in `src`.
   - `content-product.php` (child): attachment URL in inline `background-image` without `esc_url`.
   - `single-product.php` / `archive-product.php` (child): `the_content` / `the_excerpt` filter output.

   These are defense-in-depth lapses (exploitable only with post-editor access), pre-existing, and unrelated to the staleness warning. Track separately if hardening the theme.

---

## Options considered

- **A — Do nothing (CHOSEN).** Zero risk. Templates work; core deltas are benign. The notice is noise. Revisit only if a future WooCommerce update changes a hook these overrides gate.
- **B — One targeted patch.** Swap the child `content-product.php` guard `empty($product)` → `is_a($product, WC_Product::class)` to remove the only (low-probability) PHP-8 white-screen path. Requires a server-side backup of the original first (RULE 3 — the file lives on WP Engine, not in this git repo) and operator risk-acceptance (no clone, RULE 1; blast radius includes cart cross-sells + related). **Deferred** — the guard only differs for a non-`WC_Product` *object* in the loop, a narrow path.
- **C — Silence the notice.** Bump the override `@version` headers to match core, file-by-file, after confirming (done) core changed nothing functional. Clears the warning without touching layout. **Declined** — hides the "diverged" signal for future updates; do **not** blindly bump headers.

## Acceptance criteria (for Option A)

- No template files edited. `git diff` on live theme = empty (verified: no SSH write performed; audit was read-only).
- The wp-admin notice remains (expected — it reflects header lag, which Option A intentionally leaves).
- Any future proposal to "fix outdated templates" on this store must re-read this doc before touching the theme, and must **not** recopy core over an override.

## Related

- Blast-radius / no-clone constraints: `docs/adr/0001-no-clone-test-bench.md`
- Plugin/core maintenance program: Issue #22
- Memory: `woocommerce-outdated-template-overrides`
