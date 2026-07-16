<?php
/**
 * Plugin Name: IO LCP — un-hide first-slide content before JS
 * Description: Overrides theme opacity:0 AND JS translateX transforms on first
 *              slider heading/description/button so the H1 paints immediately
 *              as the LCP candidate. Adds minimal title sizing for render
 *              before external CSS loads. Reserves slider height to prevent
 *              CLS from JS initialization. Preloads LCP image via HTTP header
 *              and link element to eliminate load delay. Strips WP Rocket
 *              lazy-load from slider background images so the LCP image loads
 *              without waiting for viewport intersection (scroll).
 * Version: 0.6.0
 *
 * v0.6.0: Strip WP Rocket lazy-load from slider bg images (data-bg → inline
 *         style) so LCP image loads immediately without scroll. Self-heal
 *         corrupted WP Rocket exclude_lazyload setting (wp option patch bug).
 * v0.5.0: Add LCP image preload (HTTP Link header + link element) for the
 *         white logo, eliminating ~2s load delay from Termly parser blocking.
 * v0.4.0: Add min-height on #eut-feature-slider to reserve space and prevent
 *         CLS from slider JS initialization.
 * v0.3.0: wp_head priority -1 (fires before Termly resource blocker).
 */
if (!defined('ABSPATH')) exit;

// ── Self-heal: fix WP Rocket exclude_lazyload if corrupted to string ──
// wp option patch insert can corrupt the serialized array to a string.
// Check once per admin page load; correct automatically.
add_action('init', function () {
    if (!current_user_can('manage_options')) return;
    $s = get_option('wp_rocket_settings');
    if (!is_array($s) || !isset($s['exclude_lazyload'])) return;
    if (is_array($s['exclude_lazyload'])) return; // already correct

    // Corrupted — restore to array
    $s['exclude_lazyload'] = array(
        'CadeauCalligraphie_Phedre_triocote-scaled',
        '_HOME-',
        '_HOME_',
    );
    update_option('wp_rocket_settings', $s);
}, 999);

// ── Strip WP Rocket lazy-load from slider background images ──
// WP Rocket converts inline style="background-image: url(...)" to
// data-bg="..." + class="rocket-lazyload". On narrow viewports even
// above-the-fold bg images get lazy-loaded → LCP image doesn't load
// until scroll/intersection. Undo this for slider items.
add_action('wp_loaded', function () {
    if (is_admin() || wp_doing_ajax() || wp_doing_cron()) return;
    ob_start('io_fix_slider_lazyload');
}, PHP_INT_MAX);

// Also hook rocket_buffer for cached pages where WP Rocket uses its own buffer
add_filter('rocket_buffer', 'io_fix_slider_lazyload', PHP_INT_MAX);

function io_fix_slider_lazyload($buffer) {
    // Only process if the slider is present
    if (strpos($buffer, 'eut-feature-slider') === false) return $buffer;
    if (strpos($buffer, 'rocket-lazyload') === false) return $buffer;

    // Replace data-bg="URL" with style="background-image: url(URL);"
    // and remove rocket-lazyload class — only on eut-bg-image divs
    $pattern = '/<div\s+data-bg="([^"]*)"\s+class="([^"]*)\brocket-lazyload\b([^"]*)"\s+style="">/';
    $replacement = '<div class="$2$3" style="background-image: url($1);">';

    // Only process slider bg images (inside eut-slider-item — they have the
    // eut-bg-image class in the match, we just need to verify it's a bg-image)
    // The pattern already captures any div with data-bg + rocket-lazyload.
    // Scope it: only elements whose class contains 'eut-bg-image'.
    $buffer = preg_replace_callback(
        '/<div\s+data-bg="([^"]*)"\s+class="([^"]*)\brocket-lazyload\b([^"]*)"\s+style="">/',
        function ($m) {
            $classes = $m[2] . $m[3];
            if (strpos($classes, 'eut-bg-image') === false) {
                return $m[0]; // not a bg-image element — leave untouched
            }
            // Remove rocket-lazyload, restore inline background-image
            $clean = trim(str_replace('rocket-lazyload', '', $classes));
            return '<div class="' . $clean . '" style="background-image: url(' . $m[1] . ');">';
        },
        $buffer
    );

    return $buffer;
}

// ── HTTP Link header — arrives before any HTML, beats parser-blocking scripts
add_action('send_headers', function () {
    if (!is_front_page()) return;
    header(
        'Link: <https://www.impressionoriginale.com/wp-content/uploads/2021/03/LOGO-2020-WHITE.png>; rel=preload; as=image; fetchpriority=high',
        false
    );
});

// ── Inline CSS + preload link in <head>
add_action('wp_head', function () {
    if (!is_front_page()) return;
    echo '<link rel="preload" as="image" href="https://www.impressionoriginale.com/wp-content/uploads/2021/03/LOGO-2020-WHITE.png" fetchpriority="high">' . "\n";
    echo '<style id="io-lcp-first-slide">' .
        // CLS prevention: reserve slider height before JS init
        '#eut-feature-slider{min-height:400px;}' .
        '@media(min-width:768px){#eut-feature-slider{min-height:600px;}}' .
        // LCP: un-hide first slide content
        '#eut-feature-slider .eut-slider-item:first-child .eut-title,' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-description,' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-btn{' .
        'opacity:1!important;transform:none!important;' .
        '}' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-title{' .
        'font-size:48px!important;line-height:1.2!important;color:#fff!important;' .
        '}' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-description{' .
        'font-size:18px!important;color:#fff!important;' .
        '}' .
        '</style>' . "\n";
}, -1);
