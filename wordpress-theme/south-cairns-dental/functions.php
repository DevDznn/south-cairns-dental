<?php
/**
 * South Cairns Dental theme bootstrap.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

define( 'SCD_THEME_VERSION', '1.0.0' );
define( 'SCD_THEME_DIR', get_template_directory() );
define( 'SCD_THEME_URI', get_template_directory_uri() );

require SCD_THEME_DIR . '/inc/practice.php';
require SCD_THEME_DIR . '/inc/setup.php';
require SCD_THEME_DIR . '/inc/assets.php';
require SCD_THEME_DIR . '/inc/template-tags.php';
require SCD_THEME_DIR . '/inc/navigation.php';
require SCD_THEME_DIR . '/inc/services.php';
require SCD_THEME_DIR . '/inc/seo.php';
require SCD_THEME_DIR . '/inc/redirects.php';
require SCD_THEME_DIR . '/inc/elementor/loader.php';

if ( is_admin() ) {
	require SCD_THEME_DIR . '/inc/import/importer.php';
}
