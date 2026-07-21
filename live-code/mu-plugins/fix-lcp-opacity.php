<?php
/**
 * Plugin Name: IO LCP — un-hide hero content before JS
 * Description: Forces hero content (#eut-feature-section) to render immediately
 *              by overriding theme CSS opacity:0 and JS inline styles.
 *              Uses CSS !important in a tiny inline <style> block.
 * Version: 0.10.0
 *
 * v0.10.0: Removed dead #eut-feature-slider selectors (homepage uses
 *          #eut-feature-section, not a slider — ADR 0013 refutation).
 *          Added .eut-btn opacity override. Added eut-fade-in class.
 *          Removed dead min-height reservation.
 * v0.9.0:  Removed JS forceShow() — it set inline styles that caused CLS 0.317.
 * v0.8.0:  CSS + JS visibility enforcement (JS caused CLS — reverted).
 * v0.7.1:  Also target .eut-fade-in-right (parent container).
 * v0.7.0:  Add rocket_rucss_inline_content_exclusions filter.
 * v0.6.x:  Output buffer (removed — caused regressions).
 * v0.5.0:  Preload LCP image via HTTP header + link element.
 */
if (!defined('ABSPATH')) exit;

// ── Self-heal: fix WP Rocket exclude_lazyload ──
add_action('init', function () {
    if (!current_user_can('manage_options')) return;
    $s = get_option('wp_rocket_settings');
    if (!is_array($s) || !isset($s['exclude_lazyload'])) return;
    if (!is_array($s['exclude_lazyload'])) {
        $s['exclude_lazyload'] = array();
    }
    $bad = array('_HOME-', '_HOME_');
    $before = count($s['exclude_lazyload']);
    $s['exclude_lazyload'] = array_values(array_filter(
        $s['exclude_lazyload'],
        function ($v) use ($bad) { return !in_array($v, $bad, true); }
    ));
    if (!in_array('CadeauCalligraphie_Phedre_triocote-scaled', $s['exclude_lazyload'], true)) {
        $s['exclude_lazyload'][] = 'CadeauCalligraphie_Phedre_triocote-scaled';
    }
    if (count($s['exclude_lazyload']) !== $before || $before === 0) {
        update_option('wp_rocket_settings', $s);
    }
}, 999);

// ── CSS Safelist for RUCSS ──
add_filter('rocket_rucss_inline_content_exclusions', function ($exclusions) {
    $exclusions[] = '/*io-lcp*/';
    return $exclusions;
});
// Also safelist via Rocket settings
add_action('init', function () {
    if (!current_user_can('manage_options')) return;
    $s = get_option('wp_rocket_settings');
    if (!is_array($s)) return;
    $safelist = isset($s['remove_unused_css_safelist']) ? $s['remove_unused_css_safelist'] : array();
    if (!is_array($safelist)) $safelist = array();
    $needed = array('eut-title', 'eut-description', 'eut-btn', 'eut-fade-in', 'eut-feature-content');
    $changed = false;
    foreach ($needed as $class) {
        if (!in_array($class, $safelist, true)) {
            $safelist[] = $class;
            $changed = true;
        }
    }
    if ($changed) {
        $s['remove_unused_css_safelist'] = $safelist;
        update_option('wp_rocket_settings', $s);
    }
}, 1000);

// ── HTTP Link header for LCP image preload
add_action('send_headers', function () {
    if (!is_front_page()) return;
    header(
        'Link: <https://www.impressionoriginale.com/wp-content/uploads/2021/03/LOGO-2020-WHITE.png>; rel=preload; as=image; fetchpriority=high',
        false
    );
});

// ── Inline CSS in <head> (priority -1: before other wp_head output)
add_action('wp_head', function () {
    if (!is_front_page()) return;
    echo '<link rel="preload" as="image" href="https://www.impressionoriginale.com/wp-content/uploads/2021/03/LOGO-2020-WHITE.png" fetchpriority="high">' . "\n";
    ?>
<style id="io-lcp-first-slide">/*io-lcp*/
/* Force hero content visibility — beats theme CSS opacity:0 AND JS initPos() inline styles.
   The homepage uses #eut-feature-section (static image hero, data-item="image"),
   NOT #eut-feature-slider. initPos() sets opacity:0 on .eut-title, .eut-description,
   .eut-btn at DOM ready. Theme CSS sets .eut-btn{opacity:0}. Our !important overrides all. */
#eut-feature-section .eut-title,
#eut-feature-section .eut-description,
#eut-feature-section .eut-btn,
#eut-feature-section .eut-feature-content{
    opacity:1!important;
    visibility:visible!important
}
/* initPos() sets x:0 for eut-fade-in (no translateX needed).
   But eut-fade-in-right and other directional variants DO get translateX(200px).
   Override those as a defensive measure for any page that uses them. */
#eut-feature-section .eut-fade-in-right,
#eut-feature-section .eut-fade-in-left,
#eut-feature-section .eut-fade-in-up,
#eut-feature-section .eut-fade-in-down{
    opacity:1!important;
    transform:none!important;
    -webkit-transform:none!important;
    visibility:visible!important
}
#eut-feature-section .eut-title{
    font-size:48px!important;
    line-height:1.2!important;
    color:#fff!important
}
#eut-feature-section .eut-description{
    font-size:18px!important;
    color:#fff!important
}
/* Ensure button is visible and clickable immediately */
#eut-feature-section .eut-btn{
    pointer-events:auto!important
}
</style>
    <?php
}, -1);
