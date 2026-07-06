<?php
/**
 * Plugin Name: IO — remove obsolete UA gtag
 * Description: Strips the hardcoded Universal Analytics tag (UA-85910237-1) from
 *   front-end output. The UA property is deleted; the tag is baked into the theme
 *   PHP (0 DB rows — Better Search Replace, 158 tables), so no admin setting or
 *   search-replace removes it. GA4 (G-Y88VQHFDBV, via GTM-MT7G7Z3C) is unaffected —
 *   it fires from GTM at runtime, not from this block. See GH #3.
 *   Reversible: delete this file.
 * Version: 0.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// template_redirect fires front-end only. On a WP Rocket cache hit PHP is bypassed,
// so this runs only when the page cache is (re)generated — near-zero runtime cost.
add_action('template_redirect', function () {
    ob_start(function ($html) {
        if (strpos($html, 'UA-85910237-1') === false) {
            return $html; // nothing to strip
        }
        // 1. the gtag.js loader for the dead UA property (~125 KB fetch)
        $html = preg_replace(
            '#\s*<script[^>]*\bsrc="https://www\.googletagmanager\.com/gtag/js\?id=UA-85910237-1"[^>]*></script>#i',
            '',
            $html
        );
        // 2. the UA config call — leave the dataLayer/gtag() scaffolding intact
        //    (other inline gtag() callers may depend on it).
        $html = preg_replace(
            "#\s*gtag\\('config',\\s*'UA-85910237-1'\\);#",
            '',
            $html
        );
        return $html;
    });
});
