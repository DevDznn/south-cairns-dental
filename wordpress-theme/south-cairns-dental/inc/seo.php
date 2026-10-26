<?php
/**
 * SEO: ThinkRank is the site's SEO plugin and owns titles, meta descriptions, canonical URLs,
 * robots directives, Open Graph and X (Twitter) tags, sitemaps and schema (including the
 * practice's LocalBusiness/Dentist entity, set up in ThinkRank > Local SEO).
 *
 * The theme therefore prints none of those while ThinkRank (or another SEO plugin) is active.
 * The <title> comes from WordPress core via add_theme_support( 'title-tag' ), which ThinkRank
 * overrides through pre_get_document_title.
 *
 * Without an SEO plugin the theme adds only a fallback meta description and, on the homepage
 * and Contact page, the practice's Dentist structured data from the static site. The theme
 * never prints robots directives; search visibility is controlled by Settings > Reading and ThinkRank.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Is an SEO plugin handling metadata and schema?
 *
 * @return bool
 */
function scd_seo_plugin_active() {
	$active = defined( 'THINKRANK_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) || defined( 'SLIM_SEO_VER' );
	return (bool) apply_filters( 'scd_seo_plugin_active', $active );
}

/**
 * Fallback meta description when no SEO plugin is active.
 */
function scd_fallback_meta_description() {
	if ( scd_seo_plugin_active() || is_404() || is_search() ) {
		return;
	}
	$desc = '';
	if ( is_singular() ) {
		$post = get_queried_object();
		$desc = has_excerpt( $post ) ? get_the_excerpt( $post ) : '';
	} elseif ( is_home() && get_option( 'page_for_posts' ) ) {
		$desc = get_the_excerpt( (int) get_option( 'page_for_posts' ) );
	}
	if ( '' === trim( $desc ) && is_front_page() ) {
		$desc = get_bloginfo( 'description' );
	}
	$desc = trim( wp_strip_all_tags( $desc ) );
	if ( '' !== $desc ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $desc ) );
	}
}
add_action( 'wp_head', 'scd_fallback_meta_description', 1 );

/**
 * The practice as a schema.org Dentist (only without an SEO plugin; ThinkRank's Local SEO replaces it).
 */
function scd_fallback_schema() {
	if ( scd_seo_plugin_active() || ! apply_filters( 'scd_output_schema', true ) ) {
		return;
	}
	$contact = get_page_by_path( 'contact' );
	if ( ! is_front_page() && ! ( $contact && is_page( $contact->ID ) ) ) {
		return;
	}
	$spec = array();
	foreach ( scd_hours() as $d ) {
		if ( $d['open'] ) {
			$spec[] = array(
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $d['day'],
				'opens'     => $d['open'],
				'closes'    => $d['close'],
			);
		}
	}
	$logo_id = get_theme_mod( 'custom_logo' );
	$data    = array(
		'@context'                  => 'https://schema.org',
		'@type'                     => 'Dentist',
		'name'                      => scd_practice( 'name' ),
		'url'                       => home_url( '/' ),
		'telephone'                 => scd_practice( 'phone_tel' ),
		'email'                     => scd_practice( 'email' ),
		'logo'                      => $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : scd_theme_image( 'brand/scd-logo.png' ),
		'image'                     => scd_theme_image( 'brand/og-image.jpg' ),
		'address'                   => array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => scd_practice( 'address_line1' ),
			'addressLocality' => scd_practice( 'suburb' ),
			'addressRegion'   => scd_practice( 'state' ),
			'postalCode'      => scd_practice( 'postcode' ),
			'addressCountry'  => 'AU',
		),
		'hasMap'                    => scd_maps_link(),
		'areaServed'                => array( 'Edmonton', 'Bentley Park', 'Mount Sheridan', 'Gordonvale', 'Cairns' ),
		'openingHoursSpecification' => $spec,
	);
	if ( is_numeric( scd_practice( 'lat' ) ) && is_numeric( scd_practice( 'lng' ) ) ) {
		$data['geo'] = array(
			'@type'     => 'GeoCoordinates',
			'latitude'  => (float) scd_practice( 'lat' ),
			'longitude' => (float) scd_practice( 'lng' ),
		);
	}
	if ( scd_practice( 'facebook_url' ) ) {
		$data['sameAs'] = array( scd_practice( 'facebook_url' ) );
	}
	echo '<script type="application/ld+json">' . wp_json_encode( apply_filters( 'scd_schema', $data ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
}
add_action( 'wp_head', 'scd_fallback_schema', 30 );

/**
 * Admin notice: point to ThinkRank if no SEO plugin is active.
 */
function scd_seo_notice() {
	if ( scd_seo_plugin_active() || ! current_user_can( 'install_plugins' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes', 'appearance_page_scd-setup' ), true ) ) {
		return;
	}
	echo '<div class="notice notice-info"><p>' . esc_html__( 'South Cairns Dental theme: install and activate the ThinkRank plugin to manage SEO titles, descriptions, sitemaps and Local SEO. Until then the theme adds only a basic meta description and the practice’s structured data.', 'south-cairns-dental' ) . '</p></div>';
}
add_action( 'admin_notices', 'scd_seo_notice' );
