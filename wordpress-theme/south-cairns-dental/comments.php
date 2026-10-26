<?php
/**
 * Comments (only shown when comments are open on a post).
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

if ( post_password_required() ) {
	return;
}
?>
<section class="comments mt-7" id="comments" aria-labelledby="comments-h">
	<?php if ( have_comments() ) : ?>
		<h2 id="comments-h" class="h3-size"><?php esc_html_e( 'Comments', 'south-cairns-dental' ); ?></h2>
		<ol class="comment-list">
			<?php
			wp_list_comments(
				array(
					'style'      => 'ol',
					'short_ping' => true,
				)
			);
			?>
		</ol>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>
	<?php
	comment_form(
		array(
			'title_reply'        => __( 'Leave a comment', 'south-cairns-dental' ),
			'title_reply_before' => '<h2 id="comments-h" class="h3-size">',
			'title_reply_after'  => '</h2>',
			'class_submit'       => 'btn',
		)
	);
	?>
</section>
