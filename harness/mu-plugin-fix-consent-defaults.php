<?php
/**
 * MU Plugin: Google Consent Mode v2 — denied defaults
 * Overrides GTM4WP's "granted" defaults since GTM4WP doesn't support Termly CMP.
 * Termly auto-blocks GTM; consent upgrades to "granted" on user accept.
 */
add_action('wp_head', function() {
    ?>
<script data-cfasync="false" data-pagespeed-no-defer type="text/javascript">
(function() {
    if (typeof gtag === 'undefined') {
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
    }
    gtag('consent', 'default', {
        'analytics_storage': 'denied',
        'ad_storage': 'denied',
        'ad_user_data': 'denied',
        'ad_personalization': 'denied',
        'functionality_storage': 'denied',
        'personalization_storage': 'denied',
        'security_storage': 'granted',
        'wait_for_update': 500,
    });
})();
</script>
    <?php
}, PHP_INT_MAX - 1); // Guaranteed last — overrides GTM4WP regardless of priority
