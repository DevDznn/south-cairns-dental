<?php
/**
 * Blog post card.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

$scd_i        = isset( $args['i'] ) ? (int) $args['i'] : 0;
$scd_featured = ! empty( $args['featured'] );
$scd_cats     = get_the_category();
?>
<li class="post-card<?php echo $scd_featured ? ' post-card--featured' : ''; ?> reveal" style="--i:<?php echo esc_attr( $scd_i % 3 ); ?>">
	<a href="<?php the_permalink(); ?>">
		<span class="post-card__media"><?php the_post_thumbnail( 'scd-card', array_merge( array( 'alt' => '' ), scd_loading_attrs() ) ); ?></span>
		<span class="post-card__body">
			<span class="post-card__meta">
				<?php if ( $scd_cats ) : ?>
					<span class="tag"><?php echo esc_html( $scd_cats[0]->name ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'Y-m-d' ) ); ?>"><?php echo esc_html( get_the_date( 'j F Y' ) ); ?></time>
			</span>
			<strong class="post-card__title"><?php the_title(); ?></strong>
			<span class="post-card__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></span>
			<span class="post-card__more"><?php esc_html_e( 'Read the article', 'south-cairns-dental' ); ?> <?php echo scd_ui_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?></span>
		</span>
	</a>
</li>
