<?php
/**
 * Plugin Name: IO LCP — un-hide first-slide content before JS
 * Description: Forces first-slide hero content to render immediately by
 *              overriding theme CSS opacity:0 and JS translateX transforms.
 *              Uses CSS !important + JS visibility enforcement for Safari.
 * Version: 0.8.0
 *
 * v0.8.0: Complete rewrite. CSS targets every animated element in first slide.
 *         Adds inline JS that immediately forces visibility on DOM ready,
 *         working around any CSS cascade or RUCSS issues on WebKit.
 * v0.7.1: Also target .eut-fade-in-right (parent container).
 * v0.7.0: Add rocket_rucss_inline_content_exclusions filter.
 * v0.6.x: Output buffer (removed — caused regressions).
 * v0.5.0: Preload LCP image via HTTP header + link element.
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
    $needed = array('eut-title', 'eut-description', 'eut-btn', 'eut-fade-in-right', 'eut-feature-content', 'eut-slider-item');
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

// ── Inline CSS + JS in <head>
add_action('wp_head', function () {
    if (!is_front_page()) return;
    echo '<link rel="preload" as="image" href="https://www.impressionoriginale.com/wp-content/uploads/2021/03/LOGO-2020-WHITE.png" fetchpriority="high">' . "\n";
    ?>
<style id="io-lcp-first-slide">/*io-lcp*/
/* Reserve slider height before JS init */
#eut-feature-slider{min-height:400px}
@media(min-width:768px){#eut-feature-slider{min-height:600px}}
/* Force first-slide visibility — beats theme CSS opacity:0 AND JS inline styles */
#eut-feature-slider .eut-slider-item:first-child,
#eut-feature-slider .eut-slider-item:first-child .eut-feature-content,
#eut-feature-slider .eut-slider-item:first-child .eut-container,
#eut-feature-slider .eut-slider-item:first-child .eut-title,
#eut-feature-slider .eut-slider-item:first-child .eut-description,
#eut-feature-slider .eut-slider-item:first-child .eut-btn,
#eut-feature-slider .eut-slider-item:first-child .eut-fade-in-right,
#eut-feature-slider .eut-slider-item:first-child .eut-fade-in-up,
#eut-feature-slider .eut-slider-item:first-child .eut-fade-in-down,
#eut-feature-slider .eut-slider-item:first-child .eut-fade-in-left{
    opacity:1!important;
    transform:none!important;
    -webkit-transform:none!important;
    visibility:visible!important
}
#eut-feature-slider .eut-slider-item:first-child .eut-title{
    font-size:48px!important;line-height:1.2!important;color:#fff!important
}
#eut-feature-slider .eut-slider-item:first-child .eut-description{
    font-size:18px!important;color:#fff!important
}
/* Override theme's section-level opacity rule */
#eut-feature-section .eut-title{opacity:1!important}
#eut-feature-section .eut-description{opacity:1!important}
</style>
<script>
/* IO LCP — immediate visibility enforcement, fires before theme JS */
(function(){
var d=document;
function forceShow(){
    var s=d.querySelector('#eut-feature-slider .eut-slider-item:first-child');
    if(!s) return;
    var els=s.querySelectorAll('.eut-title,.eut-description,.eut-btn,.eut-fade-in-right,.eut-fade-in-up,.eut-fade-in-down,.eut-fade-in-left,.eut-feature-content,.eut-container');
    for(var i=0;i<els.length;i++){
        els[i].style.opacity='1';
        els[i].style.transform='none';
        els[i].style.webkitTransform='none';
        els[i].style.visibility='visible';
    }
}
/* Run immediately if DOM ready, otherwise on DOMContentLoaded */
if(d.readyState!=='loading') forceShow();
else d.addEventListener('DOMContentLoaded',forceShow);
/* Also run after a short delay to catch late JS init */
setTimeout(forceShow,100);
setTimeout(forceShow,500);
setTimeout(forceShow,2000);
})();
</script>
    <?php
}, -1);
