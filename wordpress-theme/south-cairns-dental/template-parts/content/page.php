<?php
/**
 * A page written in the block editor: page hero (breadcrumbs from the page's parents,
 * title, excerpt as the introduction) and the content in the site's prose style.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

$scd_trail = array();
foreach ( array_reverse( get_post_ancestors( get_the_ID() ) ) as $scd_ancestor ) {
	$scd_trail[] = array( get_the_title( $scd_ancestor ), get_permalink( $scd_ancestor ) );
}
?>
<section class="phero phero--plain">
	<div class="wrap wrap-text">
		<?php echo scd_crumbs( $scd_trail, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
		<?php the_title( '<h1>', '</h1>' ); ?>
		<?php if ( has_excerpt() ) : ?>
			<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>
</section>
<section class="sec sec--flush">
	<div class="wrap wrap-text prose entry-content">
		<?php
		the_content();
		wp_link_pages();
		?>
	</div>
</section>
