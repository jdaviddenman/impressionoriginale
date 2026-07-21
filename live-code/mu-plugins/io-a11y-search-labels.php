<?php
/**
 * Plugin Name: IO A11y — discernible text on search elements
 * Description: Adds aria-label attributes to the search submit button (WooCommerce
 *   Product Search widget) and the search icon link (theme topbar). Fixes AI-agent
 *   accessibility audit failures: "Buttons must have discernible text" and "Links
 *   must have discernible text." Reversible: delete this file.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_footer', function () {
    ?>
<script id="io-a11y-search-labels">
(function(){
var b=document.querySelectorAll('button.eut-search-btn');
for(var i=0;i<b.length;i++){b[i].setAttribute('aria-label','Search');}
var a=document.querySelectorAll('a.eut-icon-search');
for(var i=0;i<a.length;i++){a[i].setAttribute('aria-label','Open search');}
})();
</script>
    <?php
}, 9999);
