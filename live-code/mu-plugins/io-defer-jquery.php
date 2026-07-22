<?php
/**
 * Plugin Name: IO Defer jQuery
 * Description: Adds defer attribute to jQuery and jquery-migrate.
 *              Diagnostic probe — H1 test for LCP render delay.
 * Version: 0.1.0
 */
if (!defined('ABSPATH')) exit;

add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if (in_array($handle, ['jquery-core', 'jquery-migrate'], true)) {
        // Only add defer if not already present
        if (strpos($tag, 'defer') === false) {
            $tag = str_replace(' src=', ' defer src=', $tag);
        }
    }
    return $tag;
}, 10, 3);
