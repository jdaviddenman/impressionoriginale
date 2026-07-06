<?php
/**
 * Plugin Name: IO — remove obsolete UA gtag
 * Description: Unhooks the obsolete Universal Analytics tag (UA-85910237-1). The UA
 *   property is deleted; the tag is emitted by io_analytics() in the bespoke plugin
 *   wp-content/plugins/impression_originale/impression_originale.php via
 *   add_action('wp_head','io_analytics',20). That function outputs ONLY the dead UA
 *   gtag block, so removing the action removes exactly the UA loader + config and
 *   nothing else. GA4 (G-Y88VQHFDBV via GTM-MT7G7Z3C) fires from GTM at runtime and
 *   is untouched. See GH #3. Reversible: delete this file.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Remove at wp_head priority 1, before io_analytics runs at 20. The bespoke plugin
// registers its hook after mu-plugins load, so unhook during the wp_head pass itself.
add_action('wp_head', function () {
    remove_action('wp_head', 'io_analytics', 20);
}, 1);
