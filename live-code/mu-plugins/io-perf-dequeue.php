<?php
/**
 * Plugin Name: IO Performance — conditional asset dequeue
 * Description: Dequeues front-end assets not used on the current page. See GH jdaviddenman/impressionoriginale #45 (§B). Reversible: delete this file.
 * Version: 0.4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * WP Google Maps (wp-google-map-plugin) enqueues its front-end JS/CSS + the Google
 * Maps API (~650 KB) on EVERY page, but the site renders no map anywhere
 * (verified 2026-07-06: no [put_wpgm] shortcode in any post content, widget, or
 * postmeta; /where-to-find-us/ and the homepage render zero gm-style maps).
 *
 * Dequeue the assets everywhere EXCEPT a page that actually embeds the [put_wpgm]
 * shortcode — so if a real map is added later, that page auto-keeps its assets.
 * Priority PHP_INT_MAX so this runs after the plugin's own enqueue.
 */
add_action('wp_enqueue_scripts', function () {
    if (is_singular()) {
        $post = get_queried_object();
        if ($post instanceof WP_Post && has_shortcode((string) $post->post_content, 'put_wpgm')) {
            return; // real map on this page — leave the assets enqueued
        }
    }
    wp_dequeue_style('wpgmp-frontend');
    wp_dequeue_script('wpgmp-frontend');
    wp_dequeue_script('wpgmp-google-api');
    wp_dequeue_script('wpgmp-google-map-main');
}, PHP_INT_MAX);
