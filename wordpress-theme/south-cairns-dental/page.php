<?php
/**
 * Pages. Pages built in Elementor (every page the importer creates) render their Elementor
 * layout as is; other pages get the site's standard page hero and prose layout.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	if ( scd_is_elementor_page() ) {
		the_content();
	} else {
		get_template_part( 'template-parts/content/page' );
	}
endwhile;

get_footer();
