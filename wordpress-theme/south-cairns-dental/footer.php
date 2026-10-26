<?php
/**
 * Site footer and closing tags.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;
?>
</main>
<?php
if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' ) ) {
	get_template_part( 'template-parts/footer/site-footer' );
}
wp_footer();
?>
</body>
</html>
