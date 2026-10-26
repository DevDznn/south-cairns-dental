<?php
/**
 * Blog post: article layout from the static site (hero, featured image, body, booking panel,
 * related treatments, more articles, booking band). The post body is edited in the block editor
 * (or Elementor, if preferred). Related treatments: the "Related treatments" box on the post.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();
	$scd_posts_page = (int) get_option( 'page_for_posts' );
	$scd_trail      = $scd_posts_page ? array( array( get_the_title( $scd_posts_page ), get_permalink( $scd_posts_page ) ) ) : array();
	$scd_cats       = get_the_category();
	$scd_related    = array_filter( array_map( 'absint', (array) get_post_meta( get_the_ID(), '_scd_related', true ) ) );
	$scd_note       = get_post_meta( get_the_ID(), '_scd_note', true );
	?>
	<section class="phero phero--article">
		<div class="wrap wrap--article">
			<?php echo scd_crumbs( $scd_trail, get_the_title() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<p class="post-meta">
				<?php if ( $scd_cats ) : ?>
					<span class="tag tag--light"><?php echo esc_html( $scd_cats[0]->name ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
				<span><?php echo esc_html( get_post_meta( get_the_ID(), '_scd_author', true ) ? get_post_meta( get_the_ID(), '_scd_author', true ) : get_the_author() ); ?></span>
			</p>
			<?php the_title( '<h1>', '</h1>' ); ?>
			<?php if ( has_excerpt() ) : ?>
				<p class="lead"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<section class="sec sec--article">
		<div class="wrap article-layout">
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'article' ); ?>>
				<?php
				if ( has_post_thumbnail() ) {
					echo scd_frame( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						array(
							'id'    => get_post_thumbnail_id(),
							'class' => 'frame--corner',
							'ratio' => '16 / 9',
							'eager' => true,
						)
					);
				}
				?>
				<div class="prose article__body entry-content"><?php the_content(); ?></div>
				<p class="article__note small muted"><?php echo esc_html( $scd_note ? $scd_note : __( 'Your dentist can tell you which options suit your situation.', 'south-cairns-dental' ) ); ?></p>
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</article>
			<div class="article-aside">
				<div class="book-panel">
					<h2 class="h3-size"><?php esc_html_e( 'Talk to us about it', 'south-cairns-dental' ); ?></h2>
					<p><?php esc_html_e( 'Have a question about this topic? Book a consultation and your dentist will talk you through your options.', 'south-cairns-dental' ); ?></p>
					<?php echo scd_book_buttons( 'btn--lime', 'btn--ghost-light' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
				<?php if ( $scd_related ) : ?>
					<div class="aside-list">
						<h2 class="h4-size"><?php esc_html_e( 'Related treatments', 'south-cairns-dental' ); ?></h2>
						<ul>
							<?php
							foreach ( $scd_related as $scd_page_id ) {
								$scd_d = scd_service_data( $scd_page_id );
								if ( $scd_d ) {
									echo '<li><a href="' . esc_url( $scd_d['url'] ) . '">' . scd_service_thumb( $scd_d, 'aside-list__thumb' ) . '<span>' . esc_html( $scd_d['title'] ) . '</span></a></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								}
							}
							?>
						</ul>
					</div>
				<?php endif; ?>
				<?php if ( $scd_posts_page ) : ?>
					<a class="textlink" href="<?php echo esc_url( get_permalink( $scd_posts_page ) ); ?>"><?php esc_html_e( 'All articles', 'south-cairns-dental' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
	$scd_more = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => 3,
			'post__not_in'        => array( get_the_ID() ),
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( $scd_more->have_posts() ) :
		?>
		<section class="sec sec--mist sec--tight" aria-labelledby="more-h">
			<div class="wrap">
				<h2 id="more-h"><?php esc_html_e( 'More articles', 'south-cairns-dental' ); ?></h2>
				<ul class="post-grid post-grid--compact">
					<?php
					$scd_i = 0;
					while ( $scd_more->have_posts() ) :
						$scd_more->the_post();
						get_template_part( 'template-parts/content/post-card', null, array( 'i' => $scd_i++ ) );
					endwhile;
					wp_reset_postdata();
					?>
				</ul>
			</div>
		</section>
		<?php
	endif;
	echo scd_cta_band(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
endwhile;

get_footer();
