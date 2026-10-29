<?php
/**
 * Elementor integration: the "South Cairns Dental" widget category and widgets, Elementor Pro
 * theme locations (header/footer can be replaced in Theme Builder), self-hosted brand fonts in
 * Elementor's typography controls, and editor-only styles.
 *
 * Works with Elementor (free). Nothing here requires Elementor Pro.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget files and classes.
 *
 * @return string[] file slug => class name
 */
function scd_elementor_widgets() {
	return array(
		'home-hero'         => 'Home_Hero',
		'page-hero'         => 'Page_Hero',
		'service-hero'      => 'Service_Hero',
		'info-strip'        => 'Info_Strip',
		'cards'             => 'Cards',
		'media-text'        => 'Media_Text',
		'services-grid'     => 'Services_Grid',
		'service-directory' => 'Service_Directory',
		'related'           => 'Related',
		'team'              => 'Team',
		'profiles'          => 'Profiles',
		'faq'               => 'Faq',
		'faq-page'          => 'Faq_Page',
		'emergency'         => 'Emergency',
		'cta'               => 'Cta',
		'text'              => 'Text',
		'callout'           => 'Callout',
		'offer'             => 'Offer',
		'contact'           => 'Contact',
		'map'               => 'Map',
		'section'           => 'Section',
	);
}

/**
 * Register the widget category.
 *
 * @param \Elementor\Elements_Manager $manager Elements manager.
 */
function scd_elementor_category( $manager ) {
	$manager->add_category(
		'south-cairns-dental',
		array(
			'title' => __( 'South Cairns Dental', 'south-cairns-dental' ),
			'icon'  => 'eicon-site-identity',
		)
	);
}
add_action( 'elementor/elements/categories_registered', 'scd_elementor_category' );

/**
 * Register the widgets.
 *
 * @param \Elementor\Widgets_Manager $manager Widgets manager.
 */
function scd_elementor_register_widgets( $manager ) {
	require_once __DIR__ . '/base.php';
	foreach ( scd_elementor_widgets() as $file => $class ) {
		require_once __DIR__ . '/widgets/' . $file . '.php';
		$fqcn = '\\SCD\\Elementor\\' . $class;
		$manager->register( new $fqcn() );
	}
}
add_action( 'elementor/widgets/register', 'scd_elementor_register_widgets' );

/**
 * Elementor Pro Theme Builder locations (header, footer, single, archive).
 *
 * @param object $manager Elementor Pro locations manager.
 */
function scd_elementor_locations( $manager ) {
	$manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'scd_elementor_locations' );

/**
 * List the theme's self-hosted fonts in Elementor's typography controls without loading them from Google.
 *
 * @param array $fonts Additional fonts.
 * @return array
 */
function scd_elementor_fonts( $fonts ) {
	$fonts['Archivo'] = 'system';
	$fonts['Figtree'] = 'system';
	return $fonts;
}
add_filter( 'elementor/fonts/additional_fonts', 'scd_elementor_fonts' );

/**
 * Editor preview: show content immediately (scroll reveals off) and outline hidden helpers.
 */
function scd_elementor_preview_styles() {
	wp_add_inline_style( 'scd-wp', '.elementor-editor-active .reveal{opacity:1!important;transform:none!important}' );
}
add_action( 'elementor/preview/enqueue_styles', 'scd_elementor_preview_styles' );

/**
 * Hours table shortcode for text inside widgets (e.g. an FAQ answer): [scd_hours_table].
 * It prints the opening hours from Customize > Practice details so they stay in one place.
 *
 * @return string
 */
function scd_hours_table_shortcode() {
	return scd_hours_table();
}
add_shortcode( 'scd_hours_table', 'scd_hours_table_shortcode' );
