<?php
/**
 * Keep old links working after the move from the static site:
 * - /about.html, /fillings.html, /blog-why-choose-dental-implants.html etc. (static site URLs);
 * - the official site's older paths that the static site's vercel.json redirected.
 * Only requests that would otherwise be a 404 are redirected, so real pages always win.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Old path => new page path (or '' for the homepage). Filterable.
 *
 * @return string[]
 */
function scd_legacy_redirects() {
	return apply_filters(
		'scd_legacy_redirects',
		array(
			'index.html'                            => '',
			'about-us'                              => 'about',
			'payment-plans'                         => 'payments',
			'thank-you'                             => 'contact',
			'emergency-dental-care'                 => 'emergency-dentist',
			'blog/why-choose-dental-implants'       => 'why-choose-dental-implants',
			'blog-why-choose-dental-implants.html'  => 'why-choose-dental-implants',
			'blog-why-choose-dental-implants'       => 'why-choose-dental-implants',
		)
	);
}

/**
 * Redirect legacy URLs that would 404.
 */
function scd_legacy_redirect() {
	if ( ! is_404() ) {
		return;
	}
	$req  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_parse_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';
	$base = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$path = trim( substr( rawurldecode( $req ), strlen( rtrim( $base, '/' ) ) ), '/' );
	if ( '' === $path ) {
		return;
	}
	$map    = scd_legacy_redirects();
	$target = null;
	if ( isset( $map[ $path ] ) ) {
		$target = $map[ $path ];
	} elseif ( preg_match( '#^([a-z0-9-]+)\.html$#', $path, $m ) ) {
		$target = $m[1]; // e.g. fillings.html → /fillings/.
	}
	if ( null === $target ) {
		return;
	}
	$parts = explode( '#', $target, 2 );
	if ( '' === $parts[0] ) {
		$url = home_url( '/' );
	} else {
		$obj = get_page_by_path( $parts[0] );
		if ( ! $obj ) {
			$posts = get_posts( array( 'name' => $parts[0], 'post_type' => 'post', 'numberposts' => 1 ) );
			$obj   = $posts ? $posts[0] : null;
		}
		if ( ! $obj ) {
			return;
		}
		$url = get_permalink( $obj );
	}
	if ( isset( $parts[1] ) ) {
		$url .= '#' . $parts[1];
	}
	wp_safe_redirect( $url, 301 );
	exit;
}
add_action( 'template_redirect', 'scd_legacy_redirect', 1 );
