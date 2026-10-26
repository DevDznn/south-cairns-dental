<?php
/**
 * Blog listing (the Posts page set in Settings > Reading), categories, tags, dates and search results.
 * The Posts page's own title, excerpt and content supply the hero heading, introduction and side note.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

get_header();

$scd_posts_page = (int) get_option( 'page_for_posts' );
if ( is_home() && $scd_posts_page ) {
	$scd_title = get_the_title( $scd_posts_page );
	$scd_lead  = has_excerpt( $scd_posts_page ) ? get_the_excerpt( $scd_posts_page ) : '';
	$scd_aside = get_post_field( 'post_content', $scd_posts_page );
	$scd_crumb = $scd_title;
	$scd_trail = array();
} elseif ( is_search() ) {
	/* translators: %s: search terms */
	$scd_title = sprintf( __( 'Search results for “%s”', 'south-cairns-dental' ), get_search_query() );
	$scd_lead  = '';
	$scd_aside = '';
	$scd_crumb = __( 'Search', 'south-cairns-dental' );
	$scd_trail = array();
} else {
	$scd_title = wp_strip_all_tags( get_the_archive_title() );
	$scd_lead  = wp_strip_all_tags( get_the_archive_description() );
	$scd_aside = '';
	$scd_crumb = $scd_title;
	$scd_trail = $scd_posts_page ? array( array( get_the_title( $scd_posts_page ), get_permalink( $scd_posts_page ) ) ) : array();
}
?>
<section class="phero">
	<div class="wrap">
		<?php echo scd_crumbs( $scd_trail, $scd_crumb ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<div class="split split--7-5">
			<div>
				<h1><?php echo esc_html( $scd_title ); ?></h1>
				<?php if ( $scd_lead ) : ?>
					<p class="lead mb-0"><?php echo esc_html( $scd_lead ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( trim( wp_strip_all_tags( (string) $scd_aside ) ) ) : ?>
				<div class="phero__aside mb-0"><?php echo wp_kses_post( wpautop( $scd_aside ) ); ?></div>
			<?php endif; ?>
		</div>
	</div>
</section>

<section class="sec" aria-labelledby="articles-h">
	<div class="wrap">
		<h2 id="articles-h" class="visually-hidden"><?php esc_html_e( 'Articles', 'south-cairns-dental' ); ?></h2>
		<?php if ( have_posts() ) : ?>
			<ul class="post-grid">
				<?php
				$scd_i = 0;
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/content/post-card', null, array( 'i' => $scd_i, 'featured' => ( 0 === $scd_i && is_home() && ! is_paged() ) ) );
					++$scd_i;
				endwhile;
				?>
			</ul>
			<?php
			$scd_pager = paginate_links(
				array(
					'type'      => 'array',
					'prev_text' => __( 'Newer articles', 'south-cairns-dental' ),
					'next_text' => __( 'Older articles', 'south-cairns-dental' ),
				)
			);
			if ( $scd_pager ) {
				echo '<nav class="pager" aria-label="' . esc_attr__( 'Blog pages', 'south-cairns-dental' ) . '"><ol><li>' . implode( '</li><li>', array_map( 'wp_kses_post', $scd_pager ) ) . '</li></ol></nav>';
			}
			?>
		<?php else : ?>
			<div class="empty-state">
				<h2 class="h3-size"><?php esc_html_e( 'No articles yet', 'south-cairns-dental' ); ?></h2>
				<p><?php esc_html_e( 'We haven’t published any articles yet. In the meantime, our treatment pages explain what each treatment involves.', 'south-cairns-dental' ); ?></p>
				<a class="btn" href="<?php echo esc_url( scd_page_url( 'services' ) ); ?>"><?php esc_html_e( 'Browse our services', 'south-cairns-dental' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php if ( is_home() ) : ?>
<section class="sec sec--mist" aria-labelledby="guides-h">
	<div class="wrap">
		<div class="section-head section-head--row">
			<div>
				<h2 id="guides-h"><?php esc_html_e( 'Patient guides', 'south-cairns-dental' ); ?></h2>
				<p class="muted mb-0"><?php esc_html_e( 'Each treatment page explains what is involved, what to consider, how to prepare and what recovery looks like.', 'south-cairns-dental' ); ?></p>
			</div>
			<a class="btn btn--ghost" href="<?php echo esc_url( scd_page_url( 'services' ) ); ?>"><?php esc_html_e( 'Services overview', 'south-cairns-dental' ); ?></a>
		</div>
		<?php echo scd_service_directory(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
	</div>
</section>
<?php endif; ?>

<?php
echo scd_cta_band(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function.
get_footer();
