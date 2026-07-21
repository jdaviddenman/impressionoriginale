<?php
/**
 * Plugin Name: IO — Bing Webmaster Tools verification
 * Description: Inject msvalidate.01 meta tag in head for Bing Webmaster Tools ownership verification.
 * Version:     1.0.0
 */
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    echo '<meta name="msvalidate.01" content="A410EF940B9223B98F874C7F616EAAAE" />' . "\n";
}, 1);
