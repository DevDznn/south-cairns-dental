<?php
/**
 * Utility bar and main header. Logo: Appearance > Customize > Site Identity.
 * Menu: Appearance > Menus (Main navigation). Contact details: Customize > Practice details.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

$scd_tel      = scd_practice( 'phone_tel' );
$scd_phone    = scd_practice( 'phone_display' );
$scd_email    = scd_practice( 'email' );
$scd_booking  = scd_practice( 'booking_url' );
$scd_urgent   = scd_page_url( 'emergency-dentist' );
$scd_logo_id  = (int) get_theme_mod( 'custom_logo' );
$scd_logo_alt = sprintf(
	/* translators: %s: practice name */
	__( '%s home', 'south-cairns-dental' ),
	scd_practice( 'name' )
);
?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'south-cairns-dental' ); ?></a>
<aside class="utility" aria-label="<?php esc_attr_e( 'Opening hours and contact details', 'south-cairns-dental' ); ?>"><div class="wrap">
	<div class="utility__group"><span class="utility__item"><?php echo scd_hours_status(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?></span>
	<a class="utility__hide-sm utility__item" href="<?php echo esc_url( scd_maps_link() ); ?>" rel="noopener"><?php echo scd_icon( 'map' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><?php echo esc_html( scd_practice( 'address_line1' ) . ', ' . scd_practice( 'suburb' ) ); ?></a></div>
	<div class="utility__group"><a class="utility__urgent" href="<?php echo esc_url( $scd_urgent ); ?>"><?php echo scd_icon( 'alert' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><span><?php esc_html_e( 'Dental emergency?', 'south-cairns-dental' ); ?></span></a><?php if ( $scd_email ) : ?><a class="utility__hide-sm utility__email" href="mailto:<?php echo esc_attr( $scd_email ); ?>"><?php echo esc_html( $scd_email ); ?></a><?php endif; ?><a href="tel:<?php echo esc_attr( $scd_tel ); ?>"><?php echo esc_html( $scd_phone ); ?></a></div>
</div></aside>
<header class="site-header">
	<div class="wrap header-bar">
		<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php
			if ( $scd_logo_id ) {
				echo wp_get_attachment_image( $scd_logo_id, 'full', false, array_merge( array( 'alt' => $scd_logo_alt ), scd_loading_attrs( true ) ) );
			} else {
				printf( '<img src="%s" width="640" height="116" alt="%s"%s>', esc_url( scd_theme_image( 'brand/scd-logo.png' ) ), esc_attr( $scd_logo_alt ), scd_image_loading_by_elementor() ? '' : ' fetchpriority="high"' );
			}
			?>
		</a>
		<button class="menu-button" type="button" aria-expanded="false" aria-controls="site-nav"><span class="menu-button__bars" aria-hidden="true"><span></span><span></span><span></span></span><span class="menu-button__text"><?php esc_html_e( 'Menu', 'south-cairns-dental' ); ?></span></button>
		<nav class="nav" id="site-nav" aria-label="<?php esc_attr_e( 'Main', 'south-cairns-dental' ); ?>">
			<?php echo scd_primary_menu(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the function. ?>
			<div class="nav__mobile-cta">
				<?php
				if ( $scd_booking ) {
					echo scd_button( array( 'type' => 'book', 'label' => __( 'Book online', 'south-cairns-dental' ), 'class' => 'btn--lime btn--lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				echo scd_button( array( 'type' => 'call', 'label' => __( 'Call {phone}', 'south-cairns-dental' ), 'class' => 'btn--forest btn--lg' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				<a class="btn btn--alert-ghost btn--lg" href="<?php echo esc_url( $scd_urgent ); ?>"><?php esc_html_e( 'Dental emergency', 'south-cairns-dental' ); ?></a>
			</div>
		</nav>
		<div class="header-cta">
			<?php if ( $scd_booking ) : ?>
				<a class="btn btn--lime btn--sm" href="<?php echo esc_url( $scd_booking ); ?>" rel="noopener"><?php esc_html_e( 'Book online', 'south-cairns-dental' ); ?></a>
			<?php endif; ?>
			<a class="btn btn--forest btn--sm" href="tel:<?php echo esc_attr( $scd_tel ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: phone number */ __( 'Call %s', 'south-cairns-dental' ), $scd_phone ) ); ?>"><?php echo scd_ui_icon( 'phone' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG. ?><span class="btn__long" aria-hidden="true"><?php echo esc_html( $scd_phone ); ?></span></a>
		</div>
	</div>
</header>
