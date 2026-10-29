<?php
/**
 * Theme supports, menus and body classes.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Theme supports and menu locations.
 */
function scd_setup() {
	load_theme_textdomain( 'south-cairns-dental', SCD_THEME_DIR . '/languages' );

	// WordPress (or ThinkRank, when active) prints the <title> tag.
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 116,
			'width'       => 640,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_theme_support( 'editor-styles' );
	add_editor_style( array( 'assets/css/site.css', 'assets/css/editor.css' ) );

	// Pages use the excerpt as the card description where one is needed (treatment pages, search results).
	add_post_type_support( 'page', 'excerpt' );

	register_nav_menus(
		array(
			'primary'         => __( 'Main navigation', 'south-cairns-dental' ),
			'footer_services' => __( 'Footer: Services column', 'south-cairns-dental' ),
			'footer_about'    => __( 'Footer: About us column', 'south-cairns-dental' ),
			'footer_patients' => __( 'Footer: Patients column', 'south-cairns-dental' ),
			'footer_legal'    => __( 'Footer: bottom links', 'south-cairns-dental' ),
		)
	);

	add_image_size( 'scd-card', 800, 600, true );
	add_image_size( 'scd-portrait', 900, 1125, true );
}
add_action( 'after_setup_theme', 'scd_setup' );

/**
 * Content width for embeds.
 */
function scd_content_width() {
	$GLOBALS['content_width'] = 1260; // phpcs:ignore WordPress.WordPress.GlobalVariablesOverride.Prohibited
}
add_action( 'after_setup_theme', 'scd_content_width', 0 );

/**
 * Body classes the design system relies on: page-home (homepage carousels and phone action bar),
 * page-service and page-emergency (treatment pages).
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function scd_body_classes( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'page-home';
	}
	// Campaign landing pages (Page options box): page-lp and page-lp--{style} scope landing.css.
	if ( is_page() && '1' === get_post_meta( get_queried_object_id(), '_scd_landing', true ) ) {
		$classes[] = 'page-lp';
		$style     = sanitize_html_class( (string) get_post_meta( get_queried_object_id(), '_scd_landing_style', true ) );
		if ( $style ) {
			$classes[] = 'page-lp--' . $style;
		}
	}
	if ( is_page() && scd_is_service( get_queried_object_id() ) ) {
		$classes[] = 'page-service';
		if ( 'urgent' === get_post_meta( get_queried_object_id(), '_scd_service_tone', true ) ) {
			$classes[] = 'page-emergency';
		}
	}
	return $classes;
}
add_filter( 'body_class', 'scd_body_classes' );

/**
 * The language attribute: Australian English for the practice's site.
 *
 * @param string $output Attributes.
 * @return string
 */
function scd_language_attributes( $output ) {
	return 'en-US' === get_bloginfo( 'language' ) ? str_replace( 'en-US', 'en-AU', $output ) : $output;
}
add_filter( 'language_attributes', 'scd_language_attributes' );

/**
 * Is this post built with Elementor?
 *
 * @param int $post_id Post ID (defaults to the current post).
 * @return bool
 */
function scd_is_elementor_page( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_the_ID();
	if ( ! $post_id || ! did_action( 'elementor/loaded' ) ) {
		return false;
	}
	$document = \Elementor\Plugin::$instance->documents->get( $post_id );
	return $document && $document->is_built_with_elementor();
}
