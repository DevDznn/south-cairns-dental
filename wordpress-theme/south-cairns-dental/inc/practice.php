<?php
/**
 * Practice details: one set of settings (Appearance > Customize > Practice details)
 * used by the header, footer, booking and call buttons, maps and opening hours.
 * Defaults are the verified details from the static site's practice.json.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Setting definitions: key => [ label, default, sanitize type, section ].
 *
 * @return array
 */
function scd_practice_fields() {
	$fields = array(
		'name'           => array( __( 'Practice name', 'south-cairns-dental' ), 'South Cairns Dental', 'text', 'details' ),
		'phone_display'  => array( __( 'Phone number (as shown)', 'south-cairns-dental' ), '(07) 4055 4214', 'text', 'details' ),
		'phone_tel'      => array( __( 'Phone number for links (international format)', 'south-cairns-dental' ), '+61740554214', 'tel', 'details' ),
		'email'          => array( __( 'Email address', 'south-cairns-dental' ), 'reception@southcairnsdental.com.au', 'email', 'details' ),
		'booking_url'    => array( __( 'Online booking link', 'south-cairns-dental' ), 'https://www.centaurportal.com/d4w/org-1595/search?practiceID=1800', 'url', 'details' ),
		'facebook_url'   => array( __( 'Facebook page', 'south-cairns-dental' ), 'https://www.facebook.com/southcairnsdental', 'url', 'details' ),
		'address_line1'  => array( __( 'Street address', 'south-cairns-dental' ), 'Shop 6, 7 Stokes Street', 'text', 'location' ),
		'suburb'         => array( __( 'Suburb', 'south-cairns-dental' ), 'Edmonton', 'text', 'location' ),
		'state'          => array( __( 'State', 'south-cairns-dental' ), 'QLD', 'text', 'location' ),
		'postcode'       => array( __( 'Postcode', 'south-cairns-dental' ), '4869', 'text', 'location' ),
		'maps_query'     => array( __( 'Google Maps search text', 'south-cairns-dental' ), '7 Stokes St, Edmonton QLD 4869', 'text', 'location' ),
		'lat'            => array( __( 'Latitude (for directions)', 'south-cairns-dental' ), '-17.0195683', 'float', 'location' ),
		'lng'            => array( __( 'Longitude (for directions)', 'south-cairns-dental' ), '145.7439008', 'float', 'location' ),
		'hours_summary'  => array( __( 'Short opening hours (header bar)', 'south-cairns-dental' ), 'Mon to Thu 8 am to 5 pm, Fri 8 am to 4:30 pm', 'text', 'hours' ),
		'hours_footer'   => array( __( 'Opening hours (footer)', 'south-cairns-dental' ), "Monday to Thursday 8:00 am to 5:00 pm\nFriday 8:00 am to 4:30 pm\nSaturday and Sunday closed", 'textarea', 'hours' ),
		'footer_blurb'   => array( __( 'Footer description', 'south-cairns-dental' ), 'A modern, family-owned dental practice in Edmonton, caring for families from Bentley Park, Mount Sheridan, Gordonvale and across the southern suburbs of Cairns.', 'textarea', 'footer' ),
		'footer_note'    => array( __( 'Footer note', 'south-cairns-dental' ), 'The information on this website doesn&rsquo;t replace advice from your dentist.', 'html', 'footer' ),
		'footer_logo'    => array( __( 'Footer logo (for dark background)', 'south-cairns-dental' ), '', 'image', 'footer' ),
	);
	$days = array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' );
	foreach ( $days as $day ) {
		$weekend                                 = in_array( $day, array( 'Saturday', 'Sunday' ), true );
		$close                                   = 'Friday' === $day ? '16:30' : '17:00';
		$fields[ 'open_' . strtolower( $day ) ]  = array(
			/* translators: %s: day of the week */
			sprintf( __( '%s opens (24-hour, e.g. 08:00; blank if closed)', 'south-cairns-dental' ), $day ),
			$weekend ? '' : '08:00',
			'time',
			'hours',
		);
		$fields[ 'close_' . strtolower( $day ) ] = array(
			/* translators: %s: day of the week */
			sprintf( __( '%s closes (24-hour)', 'south-cairns-dental' ), $day ),
			$weekend ? '' : $close,
			'time',
			'hours',
		);
	}
	return apply_filters( 'scd_practice_fields', $fields );
}

/**
 * A practice detail, from the Customizer or its default.
 *
 * @param string $key Field key.
 * @return string
 */
function scd_practice( $key ) {
	$fields = scd_practice_fields();
	if ( ! isset( $fields[ $key ] ) ) {
		return '';
	}
	$value = get_theme_mod( 'scd_' . $key, $fields[ $key ][1] );
	return (string) apply_filters( 'scd_practice_' . $key, $value );
}

/** One-line address, e.g. "Shop 6, 7 Stokes Street, Edmonton QLD 4869". */
function scd_address_one_line() {
	return sprintf( '%s, %s', scd_practice( 'address_line1' ), scd_address_locality() );
}

/** Suburb, state and postcode. */
function scd_address_locality() {
	return trim( sprintf( '%s %s %s', scd_practice( 'suburb' ), scd_practice( 'state' ), scd_practice( 'postcode' ) ) );
}

/** Google Maps search link for the practice. */
function scd_maps_link() {
	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( scd_practice( 'maps_query' ) );
}

/** Google Maps directions to the practice's coordinates. */
function scd_maps_directions() {
	$lat = scd_practice( 'lat' );
	$lng = scd_practice( 'lng' );
	if ( '' === $lat || '' === $lng ) {
		return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( scd_practice( 'maps_query' ) );
	}
	return 'https://www.google.com/maps/dir/?api=1&destination=' . rawurlencode( $lat . ',' . $lng );
}

/** Keyless Google Maps embed URL (no API key needed or stored). */
function scd_maps_embed() {
	return 'https://maps.google.com/maps?q=' . rawurlencode( scd_practice( 'maps_query' ) ) . '&z=16&output=embed';
}

/**
 * Opening hours as used by the live "open now" status and the hours table.
 *
 * @return array[] Each: day, short, open, close (open/close null when closed).
 */
function scd_hours() {
	$out = array();
	foreach ( array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ) as $day ) {
		$key   = strtolower( $day );
		$open  = scd_practice( 'open_' . $key );
		$close = scd_practice( 'close_' . $key );
		$out[] = array(
			'day'   => $day,
			'short' => substr( $day, 0, 3 ),
			'open'  => ( $open && $close ) ? $open : null,
			'close' => ( $open && $close ) ? $close : null,
		);
	}
	return $out;
}

/**
 * "08:00" → "8:00 am".
 *
 * @param string $hhmm 24-hour time.
 * @return string
 */
function scd_format_time( $hhmm ) {
	list( $h, $m ) = array_map( 'intval', explode( ':', $hhmm ) + array( 0, 0 ) );
	$suffix        = $h >= 12 ? 'pm' : 'am';
	$h             = $h % 12 ? $h % 12 : 12;
	return sprintf( '%d:%02d %s', $h, $m, $suffix );
}

/**
 * Sanitize a practice setting by type.
 *
 * @param mixed  $value Raw value.
 * @param string $type  Sanitize type.
 * @return string
 */
function scd_sanitize_practice_value( $value, $type ) {
	$value = (string) $value;
	switch ( $type ) {
		case 'email':
			return sanitize_email( $value );
		case 'url':
			return esc_url_raw( $value );
		case 'tel':
			return preg_replace( '/[^0-9+]/', '', $value );
		case 'float':
			return is_numeric( $value ) ? $value : '';
		case 'time':
			return preg_match( '/^([01]?\d|2[0-3]):[0-5]\d$/', $value ) ? $value : '';
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'html':
			return wp_kses( $value, scd_inline_html() );
		case 'image':
			return absint( $value ) ? (string) absint( $value ) : '';
		default:
			return sanitize_text_field( $value );
	}
}

/**
 * Register the Customizer panel.
 *
 * @param WP_Customize_Manager $wp_customize Customizer.
 */
function scd_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'scd_practice',
		array(
			'title'       => __( 'Practice details', 'south-cairns-dental' ),
			'description' => __( 'Used by the header, footer, booking and call buttons, maps and opening hours on every page.', 'south-cairns-dental' ),
			'priority'    => 30,
		)
	);
	$sections = array(
		'details'  => __( 'Contact and booking', 'south-cairns-dental' ),
		'location' => __( 'Address and map', 'south-cairns-dental' ),
		'hours'    => __( 'Opening hours', 'south-cairns-dental' ),
		'footer'   => __( 'Footer', 'south-cairns-dental' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( 'scd_' . $id, array( 'title' => $title, 'panel' => 'scd_practice' ) );
	}
	foreach ( scd_practice_fields() as $key => $field ) {
		list( $label, $default, $type, $section ) = $field;
		$wp_customize->add_setting(
			'scd_' . $key,
			array(
				'default'           => $default,
				'sanitize_callback' => function ( $value ) use ( $type ) {
					return scd_sanitize_practice_value( $value, $type );
				},
			)
		);
		if ( 'image' === $type ) {
			$wp_customize->add_control(
				new WP_Customize_Media_Control(
					$wp_customize,
					'scd_' . $key,
					array( 'label' => $label, 'section' => 'scd_' . $section, 'mime_type' => 'image' )
				)
			);
			continue;
		}
		$wp_customize->add_control(
			'scd_' . $key,
			array(
				'label'   => $label,
				'section' => 'scd_' . $section,
				'type'    => in_array( $type, array( 'textarea', 'html' ), true ) ? 'textarea' : ( 'email' === $type ? 'email' : ( 'url' === $type ? 'url' : 'text' ) ),
			)
		);
	}
}
add_action( 'customize_register', 'scd_customize_register' );
