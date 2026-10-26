<?php
/**
 * Page not found (same layout and links as the static site's 404 page).
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

get_header();
$scd_links = array(
	'about'          => __( 'About South Cairns Dental', 'south-cairns-dental' ),
	'team'           => __( 'Meet our dentists', 'south-cairns-dental' ),
	'new-patients'   => __( 'New patients', 'south-cairns-dental' ),
	'faq'            => __( 'Frequently asked questions', 'south-cairns-dental' ),
	'blog'           => __( 'Blog', 'south-cairns-dental' ),
	'contact'        => __( 'Contact us', 'south-cairns-dental' ),
);
?>
<section class="sec notfound">
	<div class="wrap split split--6-6">
		<div>
			<p class="notfound__code" aria-hidden="true">404</p>
			<h1><?php esc_html_e( 'We can’t find that page', 'south-cairns-dental' ); ?></h1>
			<p class="lead">
				<?php esc_html_e( 'It may have moved when our website was updated. Try one of these instead, or call us on', 'south-cairns-dental' ); ?>
				<a href="tel:<?php echo esc_attr( scd_practice( 'phone_tel' ) ); ?>"><?php echo esc_html( scd_practice( 'phone_display' ) ); ?></a>.
			</p>
			<div class="btn-row mt-6">
				<a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the homepage', 'south-cairns-dental' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( scd_page_url( 'services' ) ); ?>"><?php esc_html_e( 'See our services', 'south-cairns-dental' ); ?></a>
				<a class="btn btn--ghost" href="<?php echo esc_url( scd_page_url( 'emergency-dentist' ) ); ?>"><?php esc_html_e( 'Emergency help', 'south-cairns-dental' ); ?></a>
			</div>
		</div>
		<ul class="notfound__links">
			<?php foreach ( $scd_links as $scd_slug => $scd_label ) : ?>
				<li><a href="<?php echo esc_url( 'blog' === $scd_slug && get_option( 'page_for_posts' ) ? get_permalink( (int) get_option( 'page_for_posts' ) ) : scd_page_url( $scd_slug ) ); ?>"><?php echo esc_html( $scd_label ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
<?php
get_footer();
