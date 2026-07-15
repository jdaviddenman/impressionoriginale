# PHP 8.2 → 8.4 & WordPress 7.0 → 7.1 Upgrade Analysis

**Date:** 2026-07-15
**Live stack:** PHP 8.2.31, WordPress 7.0, WooCommerce 10.9.3, WP Engine

## PHP 8.2 → 8.4

### Timeline

| Milestone | Date |
|---|---|
| PHP 8.2 active support ended | 2024-12-31 |
| PHP 8.2 security support ends (EOL) | **2026-12-31** (~5.5 months) |
| PHP 8.4 active support ends | 2026-12-31 |
| PHP 8.4 security support ends | **2028-12-31** |

After 2026-12-31, PHP 8.2 receives no patches of any kind. WP Engine will auto-upgrade remaining 8.2 sites when they drop the version.

### WP Engine availability

WP Engine currently offers PHP **7.4, 8.2, 8.4**. No 8.3 — they skipped it. The upgrade path is 8.2 → 8.4 directly. WP Engine's PHP Test Driver allows cookie-based testing of 8.4 with zero production impact.

### Performance

Tideways benchmarks (Laravel, Symfony, WordPress demo apps) show **no measurable difference** between PHP 8.2, 8.3, 8.4, and 8.5 for real framework workloads. PHP 7.4 is ~5% slower. The upgrade's value is the security timeline, not speed.

### Benefits

- **Security runway**: 2.5 years (8.4) vs 5.5 months (8.2)
- **WP Engine will force this anyway** — doing it on your schedule with testing beats a forced migration
- **WooCommerce 10.9.x** officially recommends PHP 8.3+; 10.3.6+ includes PHP 8.4 fixes
- PHP 8.4 new features: property hooks, asymmetric visibility, `#[\Deprecated]`, `mb_trim`, lazy objects

### Risks

1. **PHP 8.4 deprecations** (→ hard errors in PHP 9.0):
   - Implicitly nullable typed parameters (`function foo(string $x = null)` deprecated)
   - Passing `null` to string-typed functions
2. **Theme "The Core" (EngineThemes)**: old theme, unknown PHP 8.4 compatibility — **highest risk**
3. **WPBakery Page Builder + Slider Revolution**: old plugins, unknown PHP 8.4 compatibility
4. **No PHP 8.3 fallback**: WP Engine doesn't offer it. If 8.4 breaks, rollback is to 8.2 only
5. **Plugin surface area**: WPML suite, Yoast SEO, WP Rocket, WebP Express, GTM4WP, PixelYourSite, Termly, Stripe gateway — each a variable

### Mitigation

WP Engine PHP Test Driver (cookie-based, zero production impact). Test before switching.

---

## WordPress 7.0 → 7.1

### Timeline

| Milestone | Date |
|---|---|
| WordPress 7.0 released | 2026-05-20 |
| WordPress 7.1 beta 1 | 2026-07-15 |
| WordPress 7.1 RC1 | 2026-08-05 |
| **WordPress 7.1 final** | **2026-08-19** (~1 month) |
| WordPress 7.0 EOL | 2026-08-19 (day 7.1 ships) |

WordPress only supports the latest major version. Security backports to older versions are "courtesy only" with no SLA.

### Benefits

- **Continued security support** — 7.0 goes EOL in ~1 month
- **React 19**: core jumps from React 18 → 19; concurrent rendering perf improvements
- **Classic Block deprecated**: removed from block inserter, TinyMCE lazy-loaded → admin perf win
- **Speculative loading**: bumped to "moderate" when caching detected → faster front-end nav
- **Responsive editing**: per-breakpoint block styling, pseudo-class state styling in editor
- **Client-side media processing**: WebAssembly `wasm-vips`, resumable uploads
- **New blocks**: Tabs, Table of Contents, Playlist

### Risks

1. **React 19**: plugins/themes using React may break (WPML, Yoast, WooCommerce all use React)
2. **Old theme "The Core"**: predates block editor era; each WP major version widens the gap
3. **WooCommerce template overrides**: 10 flagged-stale overrides in the Engic theme
4. **Short release cycle**: ~5 weeks beta-to-final; less bake time than typical
5. **Classic Block deprecation**: unlikely to affect this WPBakery-based store, but note it

---

## Combined Risk Matrix

| Scenario | PHP risk | WP risk | Combined |
|---|---|---|---|
| Do nothing | 8.2 EOL Dec 2026 → forced upgrade | 7.0 EOL Aug 2026 → no patches | **Highest** |
| WP 7.1 only | Same PHP risk | Low (routine minor update) | Medium |
| PHP 8.4 only | Theme/plugin unknown | 7.0 EOL in 1 month anyway | Medium-High |
| Both together | Harder to isolate breakage | Harder to isolate breakage | **High** |
| **Staged: WP 7.1 first, then PHP 8.4** | Clean blame per change | Clean blame per change | **Lowest** |

---

## Recommended Path

1. **WP 7.1 first** (late August 2026, after release settles). Lower risk, routine minor update, buys security runway.
2. **PHP 8.4 second** (September-October 2026, well before Dec 31 deadline). Test via WP Engine PHP Test Driver first. If theme/plugins fatal, 3 months remain to fix or replace before EOL.
3. **Don't do both at once** — if something breaks, you can't tell which change caused it.

### Unknowns requiring testing

- EngineThemes "The Core" on PHP 8.4 (fatal risk)
- WPBakery Page Builder on PHP 8.4
- Slider Revolution on PHP 8.4
- WPML suite on PHP 8.4
- WooCommerce 10.9.3 with PHP 8.4 (likely fine per 10.3.6+ fixes, unverified on this install)

## Sources

- [PHP Supported Versions](https://www.php.net/supported-versions.php)
- [WP Engine PHP Guide](https://wpengine.com/support/php-guide/)
- [Tideways PHP Benchmarks (8.5 vs 8.4, 8.3, 7.4)](https://tideways.com/profiler/blog/php-benchmarks-8-5-vs-8-4-8-3-and-7-4)
- [WooCommerce: Update PHP and WordPress](https://woocommerce.com/document/update-php-wordpress/)
- [WordPress Requirements](https://wordpress.org/about/requirements/)
- [WordPress 7.1 Roadmap (WP Poland)](https://wppoland.com/en/wordpress-7-1-roadmap-2026/)
