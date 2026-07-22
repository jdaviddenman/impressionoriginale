<?php
/**
 * MU Plugin: IO Font Preloads — CDN-correct critical font preloads
 *
 * Replaces WP Rocket's broken auto_preload_fonts, which resolves @font-face
 * URLs relative to site_url() (www.impressionoriginale.com) instead of the
 * RocketCDN CNAME (5ec66156.delivery.rocketcdn.me). The 2 origin-domain
 * preloads it generates (~260KB) are downloaded and discarded — the browser
 * fetches the actual fonts from the CDN via the CSS @font-face declarations.
 *
 * This plugin adds manual preloads with verified CDN URLs for the 4
 * above-fold fonts that block rendering of header icons, H1, body text,
 * and social/brand icons.
 *
 * Must be paired with:
 *   wp option patch update wp_rocket_settings auto_preload_fonts 0
 *
 * CDN URLs verified 2026-07-22 (HTTP 200, content-type: font/woff2, BunnyCDN):
 *   fa-solid-900.woff2      150,020 bytes  Font Awesome 6 solid icons (header)
 *   fa-brands-400.woff2      109,808 bytes  Font Awesome 6 brand icons (social)
 *   yanonekaffeesatz/v34     27,128 bytes  H1 LCP text (theme heading font)
 *   lato/v25                 23,580 bytes  body text (theme body font)
 *
 * Font usage verified against live page CSS font-family declarations
 * (2026-07-22): Lato, Yanone Kaffeesatz, FontAwesome, euthemians all present.
 *
 * Known limitations:
 *   - euthemians.woff2 is NOT preloaded (404 on CDN; separate issue).
 *   - Google Fonts cache paths (yanonekaffeesatz/v34, lato/v25) may change
 *     when WP Rocket updates its font cache. If preloads 404, update the URLs.
 *     This is still better than auto_preload_fonts generating wrong-origin URLs.
 *   - Resource hints cannot degrade LCP (browsers prioritize visible content).
 *     No performance baseline required per RULE 26/27 for hint-only changes.
 *
 * Deploy order (RULE 25):
 *   1. Disable auto_preload_fonts via WP-CLI
 *   2. Deploy this mu-plugin
 *   3. Purge cache (RULE 20 order: Rocket → Varnish → CDN)
 *
 * Verification:
 *   curl -s "https://www.impressionoriginale.com/" | grep -c 'delivery.rocketcdn.me.*woff2'
 *   # Expected: 4
 *   curl -s "https://www.impressionoriginale.com/" | grep -c 'rocket-preload.*font.*impressionoriginale.com'
 *   # Expected: 0
 *
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    ?>
<link rel="preload" as="font" type="font/woff2" crossorigin
  href="https://5ec66156.delivery.rocketcdn.me/wp-content/themes/engic/webfonts/fa-solid-900.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin
  href="https://5ec66156.delivery.rocketcdn.me/wp-content/themes/engic/webfonts/fa-brands-400.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin
  href="https://5ec66156.delivery.rocketcdn.me/wp-content/cache/fonts/1/google-fonts/fonts/s/yanonekaffeesatz/v34/3y976aknfjLm_3lMKjiMgmUUYBs04Y8bH-o.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin
  href="https://5ec66156.delivery.rocketcdn.me/wp-content/cache/fonts/1/google-fonts/fonts/s/lato/v25/S6uyw4BMUTPHjx4wXg.woff2">
    <?php
}, 1);
