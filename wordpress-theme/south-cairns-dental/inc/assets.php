<?php
/**
 * Styles, scripts, font preloads and the fallback favicon.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Asset version: the theme version, or the file time while developing.
 *
 * @param string $rel Path relative to the theme.
 * @return string
 */
function scd_asset_version( $rel ) {
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && file_exists( SCD_THEME_DIR . '/' . $rel ) ) {
		return SCD_THEME_VERSION . '.' . filemtime( SCD_THEME_DIR . '/' . $rel );
	}
	return SCD_THEME_VERSION;
}

/**
 * Front-end styles and scripts.
 */
function scd_enqueue_assets() {
	wp_enqueue_style( 'scd-site', SCD_THEME_URI . '/assets/css/site.css', array(), scd_asset_version( 'assets/css/site.css' ) );
	wp_enqueue_style( 'scd-wp', SCD_THEME_URI . '/assets/css/wp.css', array( 'scd-site' ), scd_asset_version( 'assets/css/wp.css' ) );

	wp_enqueue_script(
		'scd-main',
		SCD_THEME_URI . '/assets/js/main.js',
		array(),
		scd_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);
	// Opening hours for the live "open now" status, from Appearance > Customize > Practice details.
	wp_add_inline_script(
		'scd-main',
		'window.SCD = ' . wp_json_encode(
			array(
				'hours' => scd_hours(),
				'phone' => scd_practice( 'phone_tel' ),
				'phoneDisplay' => scd_practice( 'phone_display' ),
			)
		) . ';',
		'before'
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
// After Elementor's own styles, so the design system wins over Elementor's generic resets
// (e.g. `.elementor img { height: auto }`) where specificity is equal. Settings made with a
// widget's Style tab are more specific and still apply.
add_action( 'wp_enqueue_scripts', 'scd_enqueue_assets', 30 ); // Elementor enqueues at 20.

/**
 * Preload the two variable fonts (self-hosted, no external requests).
 */
function scd_preload_fonts() {
	foreach ( array( 'archivo-var.woff2', 'figtree-var.woff2' ) as $font ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( SCD_THEME_URI . '/assets/fonts/' . $font ) );
	}
	echo '<meta name="theme-color" content="#1E4A3F">' . "\n";
}
add_action( 'wp_head', 'scd_preload_fonts', 2 );

/**
 * Practice favicon until a Site Icon is set in Appearance > Customize > Site Identity.
 */
function scd_fallback_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" type="image/png" sizes="32x32" href="%s">' . "\n", esc_url( SCD_THEME_URI . '/assets/images/brand/favicon-32.png' ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( SCD_THEME_URI . '/assets/images/brand/apple-touch-icon.png' ) );
}
add_action( 'wp_head', 'scd_fallback_favicon', 3 );
