<?php
/**
 * MU Plugin: Termly preconnect + async loading
 *
 * Phase 1: DNS prefetch + preconnect for app.termly.io before the Termly
 * resource-blocker script tag. mu-plugins load before regular plugins, so
 * the PHP_INT_MIN hook fires before Termly's own PHP_INT_MIN embed_banner.
 *
 * Phase 2: Add async attribute to the Termly resource-blocker script via
 * WP Rocket's rocket_buffer filter (RULE 21 compliant — documented
 * extension point). Eliminates render-blocking without breaking consent:
 * Consent Mode v2 defaults (denied) from fix-consent-defaults.php gate all
 * tracking before the auto-blocker loads. wait_for_update:500 provides a
 * 500ms buffer for the async script to arrive.
 *
 * If WP Rocket is deactivated, Phase 2 degrades gracefully — the script
 * stays synchronous but preconnect still reduces connection latency.
 */

// Phase 1: Preconnect before Termly script
add_action( 'wp_head', function () {
	?>
<link rel="dns-prefetch" href="//app.termly.io">
<link rel="preconnect" href="https://app.termly.io" crossorigin>
	<?php
}, PHP_INT_MIN );

// Phase 2: Add async attribute via rocket_buffer
add_filter( 'rocket_buffer', function ( $html ) {
	return str_replace(
		'src="https://app.termly.io/resource-blocker/',
		'async src="https://app.termly.io/resource-blocker/',
		$html
	);
} );
