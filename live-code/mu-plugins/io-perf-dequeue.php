<?php
/**
 * Plugin Name: IO Performance — conditional asset dequeue
 * Description: Dequeues front-end assets not used on the current page. See GH jdaviddenman/impressionoriginale #45 (§B) + #56 (icon-font dedup). Reversible: delete this file.
 * Version: 0.6.1
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_enqueue_scripts', function () {

    /**
     * WP Google Maps (wp-google-map-plugin): enqueues its front-end JS/CSS + the
     * Google Maps API (~650 KB) on EVERY page, but the site renders no map anywhere
     * (verified 2026-07-06: no [put_wpgm] shortcode in any post content, widget, or
     * postmeta; /where-to-find-us/ + homepage render 0 gm-style maps). Dequeue
     * everywhere except a page embedding [put_wpgm] (future-proofs a real map).
     */
    $keep_map = false;
    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && has_shortcode((string) $post->post_content, 'put_wpgm')) {
            $keep_map = true;
        }
    }
    if (!$keep_map) {
        wp_dequeue_style('wpgmp-frontend');
        wp_dequeue_script('wpgmp-frontend');
        wp_dequeue_script('wpgmp-google-api');
        wp_dequeue_script('wpgmp-google-map-main');
    }

    /**
     * Dashicons: WP core admin-bar icon font (~35 KB, render-blocking). Loaded on the
     * front end even for logged-out visitors, but no front-end element renders a
     * dashicons glyph (verified 2026-07-06: 0 rendered glyphs incl. ::before/::after
     * on home/shop/product). Keep it only when the admin bar shows (logged-in users
     * need it); dequeue for everyone else.
     */
    if (!is_admin_bar_showing()) {
        wp_dequeue_style('dashicons');
        wp_deregister_style('dashicons'); // sticky via a dependency; deregister removes it. Safe: 0 glyphs render.
    }

    /**
     * Mailchimp WP (mailchimp-wp): enqueues its own bundled Font Awesome v4.1.0
     * (handle 'fca-eoi-font-awesome' -> fontawesome-webfont.woff, ~96 KB) — a full
     * third copy of Font Awesome on top of the theme's canonical FA6 (fa-solid-900 +
     * fa-brands-400) + v4-shims. Verified 2026-07-06 (live headless): the only
     * homepage element resolving to this v4 "FontAwesome" family is the .fa-angle-up
     * back-to-top button; disabling this stylesheet in the DOM falls it back to the
     * theme's "Font Awesome 6 Free" and the glyph still renders (v4-shims maps
     * .fa-angle-up). No mailchimp-form FA glyph renders; theme FA6+shims covers any
     * .fa/.fa-* the plugin uses. Leaves the plugin's form styling
     * (tooltipster/featherlight/style-new) intact.
     *
     * NOTE: the plugin enqueues this handle inside its SHORTCODE handler
     * (eoi-shortcode.php EasyOptInsShortcodes::enqueue_assets, called during content
     * rendering) — i.e. AFTER wp_enqueue_scripts has fired. So wp_dequeue_style() on
     * wp_enqueue_scripts is a no-op (the style isn't queued yet). Suppress the printed
     * <link> instead via style_loader_tag, which fires whenever/wherever the tag is
     * emitted (header or footer). No CSS -> no @font-face -> the 96 KB woff is never
     * fetched. Registered here (front-end only) so the wp-admin Optin editor, which
     * also enqueues this handle, is untouched.
     */
    add_filter('style_loader_tag', function ($tag, $handle) {
        return $handle === 'fca-eoi-font-awesome' ? '' : $tag;
    }, 10, 2);

}, PHP_INT_MAX);
