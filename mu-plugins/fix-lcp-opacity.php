<?php
/**
 * Plugin Name: IO LCP — un-hide first-slide content before JS
 * Description: Overrides theme opacity:0 AND JS translateX transforms on first
 *              slider heading/description/button so the H1 paints immediately
 *              as the LCP candidate. Adds minimal title sizing for render
 *              before external CSS loads. Reserves slider height to prevent
 *              CLS from JS initialization. Preloads LCP image via HTTP header
 *              and link element to eliminate load delay.
 * Version: 0.7.1
 *
 * v0.7.1: Also target .eut-fade-in-right (parent container) with
 *         transform:none!important — JS sets translateX on the container,
 *         not on .eut-title directly. Without this the H1 is still off-screen.
 * v0.7.0: Add rocket_rucss_inline_content_exclusions filter so WP Rocket RUCSS
 *         does NOT strip our inline CSS (RUCSS removes all inline styles by
 *         default). Re-enable RUCSS after deployment.
 * v0.6.2: Self-heal also removes over-broad _HOME-_ and _HOME_ substrings
 *         (v0.6.0 leftovers that excluded ALL slider images from lazy-load).
 * v0.6.1: REMOVED output buffer (v0.6.0 regression — stripped lazy-load from
 *         ALL slider bg images, causing 5MB payload and LCP regression to
 *         20.4s). Self-heal retained for corrupted WP Rocket settings.
 * v0.5.0: Add LCP image preload (HTTP Link header + link element) for the
 *         white logo, eliminating ~2s load delay from Termly parser blocking.
 * v0.4.0: Add min-height on #eut-feature-slider to reserve space and prevent
 *         CLS from slider JS initialization.
 * v0.3.0: wp_head priority -1 (fires before Termly resource blocker).
 */
if (!defined('ABSPATH')) exit;

// ── Self-heal: fix WP Rocket exclude_lazyload if corrupted or over-broad ──
// wp option patch insert corrupts serialized arrays to strings.
// v0.6.0 added _HOME-_ and _HOME_ substrings which excluded all slider images.
// Check once per admin page load; correct automatically.
add_action('init', function () {
    if (!current_user_can('manage_options')) return;
    $s = get_option('wp_rocket_settings');
    if (!is_array($s) || !isset($s['exclude_lazyload'])) return;

    // Fix corruption: string → array
    if (!is_array($s['exclude_lazyload'])) {
        $s['exclude_lazyload'] = array();
    }

    // Remove over-broad substrings added in v0.6.0 (excluded ALL slider images)
    $bad = array('_HOME-', '_HOME_');
    $before = count($s['exclude_lazyload']);
    $s['exclude_lazyload'] = array_values(array_filter(
        $s['exclude_lazyload'],
        function ($v) use ($bad) { return !in_array($v, $bad, true); }
    ));

    // Ensure baseline exclusion exists
    if (!in_array('CadeauCalligraphie_Phedre_triocote-scaled', $s['exclude_lazyload'], true)) {
        $s['exclude_lazyload'][] = 'CadeauCalligraphie_Phedre_triocote-scaled';
    }

    if (count($s['exclude_lazyload']) !== $before || $before === 0) {
        update_option('wp_rocket_settings', $s);
    }
}, 999);

// ── Protect inline CSS from WP Rocket RUCSS ──
// RUCSS removes ALL inline <style> elements by default. The
// rocket_rucss_inline_content_exclusions filter marks our style block
// as excluded by matching the unique marker comment inside it.
add_filter('rocket_rucss_inline_content_exclusions', function ($exclusions) {
    $exclusions[] = '/*io-lcp-fix*/';
    return $exclusions;
});

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
        '/*io-lcp-fix*/' .
        // CLS prevention: reserve slider height before JS init
        '#eut-feature-slider{min-height:400px;}' .
        '@media(min-width:768px){#eut-feature-slider{min-height:600px;}}' .
        // LCP: un-hide first slide content — target both the H1 and its
        // animated container (.eut-fade-in-right gets translateX via JS)
        '#eut-feature-slider .eut-slider-item:first-child .eut-title,' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-description,' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-btn,' .
        '#eut-feature-slider .eut-slider-item:first-child .eut-fade-in-right{' .
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
