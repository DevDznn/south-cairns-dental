<?php
/**
 * Site footer. Columns: practice details (Customize > Practice details) and three menus
 * (Appearance > Menus: Footer Services, About us and Patients columns; the menu name is the heading).
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

$scd_logo_id = (int) scd_practice( 'footer_logo' );
$scd_hours   = scd_practice( 'hours_footer' );
$scd_legal   = scd_footer_menu( 'footer_legal' );
?>
<footer class="site-footer">
	<div class="wrap">
		<div class="footer-top">
			<div class="footer-brand">
				<?php
				if ( $scd_logo_id ) {
					echo wp_get_attachment_image( $scd_logo_id, 'full', false, array_merge( array( 'alt' => scd_practice( 'name' ) ), scd_loading_attrs() ) );
				} else {
					printf( '<img src="%s" width="640" height="116" alt="%s"%s>', esc_url( scd_theme_image( 'brand/scd-logo-reversed.png' ) ), esc_attr( scd_practice( 'name' ) ), scd_image_loading_by_elementor() ? '' : ' loading="lazy"' );
				}
				?>
				<p><?php echo esc_html( scd_practice( 'footer_blurb' ) ); ?></p>
				<div class="btn-row mt-5">
					<?php
					echo scd_button( array( 'type' => 'call', 'label' => __( 'Call {phone}', 'south-cairns-dental' ), 'class' => 'btn--lime' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo scd_button( array( 'type' => 'book', 'label' => __( 'Book online', 'south-cairns-dental' ), 'class' => 'btn--ghost-light', 'icon' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</div>
			</div>
			<div class="footer-cols">
				<div>
					<h2><?php esc_html_e( 'Visit', 'south-cairns-dental' ); ?></h2>
					<address><?php echo esc_html( scd_practice( 'address_line1' ) ); ?><br><?php echo esc_html( scd_address_locality() ); ?></address>
					<p class="mt-5 mb-0"><a href="<?php echo esc_url( scd_maps_directions() ); ?>" rel="noopener"><?php esc_html_e( 'Get directions', 'south-cairns-dental' ); ?></a></p>
					<?php if ( $scd_hours ) : ?>
						<h2 class="mt-6"><?php esc_html_e( 'Hours', 'south-cairns-dental' ); ?></h2>
						<p class="mb-0"><?php echo nl2br( esc_html( $scd_hours ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped before nl2br. ?></p>
					<?php endif; ?>
				</div>
				<div class="footer-services">
					<h2><?php echo esc_html( scd_menu_name( 'footer_services', __( 'Services', 'south-cairns-dental' ) ) ); ?></h2>
					<?php echo scd_footer_menu( 'footer_services' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
				</div>
				<div>
					<h2><?php echo esc_html( scd_menu_name( 'footer_about', __( 'About us', 'south-cairns-dental' ) ) ); ?></h2>
					<?php echo scd_footer_menu( 'footer_about' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<h2 class="mt-6"><?php echo esc_html( scd_menu_name( 'footer_patients', __( 'Patients', 'south-cairns-dental' ) ) ); ?></h2>
					<?php echo scd_footer_menu( 'footer_patients' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		</div>
		<div class="footer-bottom">
			<p class="mb-0">&copy; <span data-year><?php echo esc_html( gmdate( 'Y' ) ); ?></span> <?php echo esc_html( scd_practice( 'name' ) ); ?>. <?php echo esc_html( scd_address_one_line() ); ?>.</p>
			<p class="mb-0 footer-bottom__links"><?php echo $scd_legal ? wp_kses_post( str_replace( array( '<ul>', '</ul>', '<li>', '</li>' ), array( '<span class="footer-legal">', '</span>', '', ' &nbsp; ' ), $scd_legal ) ) : ''; ?><?php echo scd_inline( scd_practice( 'footer_note' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- kses in scd_inline. ?></p>
		</div>
	</div>
</footer>
