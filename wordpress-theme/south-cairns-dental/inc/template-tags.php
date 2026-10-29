<?php
/**
 * Shared components: icons, buttons, image frames, breadcrumbs, hours table and map card.
 * Markup matches the static site so the shared stylesheet applies unchanged.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline HTML allowed in short text fields (headings, card text, notes).
 *
 * @return array
 */
function scd_inline_html() {
	return array(
		'a'      => array( 'href' => true, 'rel' => true, 'target' => true, 'class' => true, 'aria-label' => true ),
		'strong' => array(),
		'b'      => array(),
		'em'     => array(),
		'i'      => array(),
		'br'     => array(),
		'span'   => array( 'class' => true ),
		'abbr'   => array( 'title' => true ),
		'sup'    => array(),
	);
}

/**
 * Escape a short text field that may hold inline HTML (links, emphasis, entities).
 *
 * @param string $text Text.
 * @return string
 */
function scd_inline( $text ) {
	return wp_kses( (string) $text, scd_inline_html() );
}

/**
 * Small UI icons (phone, arrow, chevron, mail, pin), 16 or 24px artwork.
 *
 * @param string $name Icon name.
 * @return string SVG markup (static, safe).
 */
function scd_ui_icon( $name ) {
	$icons = array(
		'phone' => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/></svg>',
		'arrow' => '<svg viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M2 8h11M9 4l4 4-4 4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'chev'  => '<svg viewBox="0 0 12 12" aria-hidden="true" focusable="false"><path d="M2.5 4.5 6 8l3.5-3.5" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
		'mail'  => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M3.5 6.5h17v11h-17zM4 7l8 6 8-6"/></svg>',
		'pin'   => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path fill="none" stroke="currentColor" stroke-width="1.8" d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3" fill="currentColor"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Line icons used in cards and lists (24px, 1.7 stroke).
 *
 * @return string[] name => inner SVG paths.
 */
function scd_icon_paths() {
	return array(
		'tooth'     => '<path d="M7 3.5c2 0 3 1 5 1s3-1 5-1c2.5 0 3.5 2.2 3 5-.6 3-1.6 4-2 7-.4 3-1 5-2.3 5-1.6 0-1.1-5.5-3.7-5.5S9.9 20.5 8.3 20.5C7 20.5 6.4 18.5 6 15.5c-.4-3-1.4-4-2-7-.5-2.8.5-5 3-5z"/>',
		'family'    => '<circle cx="8" cy="7" r="3"/><circle cx="17" cy="9" r="2.3"/><path d="M2.5 20c.5-4 2.8-6.2 5.5-6.2s5 2.2 5.5 6.2M13.5 20c.3-2.8 1.7-4.4 3.5-4.4s3.2 1.6 3.5 4.4"/>',
		'heart'     => '<path d="M12 20s-7.5-4.6-7.5-10A4.2 4.2 0 0 1 12 7.6 4.2 4.2 0 0 1 19.5 10c0 5.4-7.5 10-7.5 10z"/>',
		'shield'    => '<path d="M12 3l7 3v5.5c0 4.4-3 8-7 9.5-4-1.5-7-5.1-7-9.5V6z"/><path d="M9 12l2 2 4-4"/>',
		'spark'     => '<path d="M12 3v4M12 17v4M3 12h4M17 12h4M6 6l2.5 2.5M15.5 15.5 18 18M18 6l-2.5 2.5M8.5 15.5 6 18"/>',
		'calendar'  => '<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4"/>',
		'car'       => '<path d="M5 16V12l2-5h10l2 5v4"/><path d="M3.5 16h17v3h-17zM7 19v1.5M17 19v1.5"/><circle cx="8" cy="13.5" r=".6"/><circle cx="16" cy="13.5" r=".6"/>',
		'chat'      => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9.5h8M8 12.5h5"/>',
		'clipboard' => '<rect x="5" y="4.5" width="14" height="16" rx="2"/><path d="M9 4.5V3h6v1.5M8.5 10h7M8.5 13.5h7M8.5 17h4"/>',
		'clock'     => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
		'music'     => '<path d="M9 18V6l10-2v12"/><circle cx="7" cy="18" r="2.2"/><circle cx="17" cy="16" r="2.2"/>',
		'pause'     => '<circle cx="12" cy="12" r="8.5"/><path d="M10 9v6M14 9v6"/>',
		'question'  => '<circle cx="12" cy="12" r="8.5"/><path d="M9.6 9.5a2.5 2.5 0 1 1 3.6 2.3c-.8.4-1.2.9-1.2 1.8M12 16.6v.2"/>',
		'alert'     => '<path d="M12 3.5 21 19.5H3z"/><path d="M12 10v4M12 16.8v.2"/>',
		'wallet'    => '<rect x="3" y="6" width="18" height="13" rx="2.5"/><path d="M16 12.5h2.5M3 9.5h15"/>',
		'phone'     => '<path d="M21 16.4v3a1.9 1.9 0 0 1-2.1 1.9A18.8 18.8 0 0 1 2.7 5.1 1.9 1.9 0 0 1 4.6 3h3a1.9 1.9 0 0 1 1.9 1.6c.1.9.4 1.8.7 2.7a1.9 1.9 0 0 1-.4 2L8.5 10.6a15 15 0 0 0 5 5l1.3-1.3a1.9 1.9 0 0 1 2-.4c.9.3 1.8.6 2.7.7a1.9 1.9 0 0 1 1.5 1.8z"/>',
		'map'       => '<path d="M9 4 3.5 6v14L9 18l6 2 5.5-2V4L15 6z"/><path d="M9 4v14M15 6v14"/>',
		'mail'      => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="m4 7 8 6 8-6"/>',
		// Treatment and campaign landing page icons.
		'brush'          => '<path d="M4 20 13.5 10.5"/><rect x="12.5" y="4" width="7.5" height="6.5" rx="1.5" transform="rotate(45 16.25 7.25)"/><path d="M15 5.5l3.5 3.5"/>',
		'drop'           => '<path d="M12 3.5s6 6.4 6 10.5a6 6 0 0 1-12 0c0-4.1 6-10.5 6-10.5z"/>',
		'cup'            => '<path d="M5 8h11v5a5 5 0 0 1-5 5h-1a5 5 0 0 1-5-5z"/><path d="M16 9.5h1.5a2.5 2.5 0 0 1 0 5H16M8 3.5v2M11 3.5v2"/>',
		'shield-plain'   => '<path d="M12 3.5 19 6v5.5c0 4.3-3 7.6-7 9-4-1.4-7-4.7-7-9V6z"/>',
		'camera'         => '<rect x="3.5" y="6" width="17" height="13" rx="2.5"/><circle cx="12" cy="12.5" r="3.2"/><path d="M8.5 6l1.2-2h4.6l1.2 2"/>',
		'sun'            => '<path d="M12 4v3M12 17v3M4 12h3M17 12h3M6.6 6.6l2 2M15.4 15.4l2 2M6.6 17.4l2-2M15.4 8.6l2-2"/><circle cx="12" cy="12" r="2.2"/>',
		'tooth-check'    => '<path d="M7 3.5c2 0 3 1 5 1s3-1 5-1c2.5 0 3.5 2.2 3 5-.6 3-1.6 4-2 7-.4 3-1 5-2.3 5-1.6 0-1.1-5.5-3.7-5.5S9.9 20.5 8.3 20.5C7 20.5 6.4 18.5 6 15.5c-.4-3-1.4-4-2-7-.5-2.8.5-5 3-5z"/><path d="M14.5 4.2l1.3 2.3 1.9-1"/>',
		'gap'            => '<rect x="3.5" y="5" width="6.5" height="14" rx="3"/><rect x="14" y="5" width="6.5" height="14" rx="3"/><path d="M12 9.5v5"/>',
		'wave'           => '<path d="M4 8.5c2.7-3 5.3 3 8 0s5.3-3 8 0"/><path d="M4 16h16"/>',
		'crown'          => '<path d="M4.5 9.5 8 12.5l4-6 4 6 3.5-3-1.5 9h-12z"/>',
		'info'           => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5v5.5M12 16.3v.2"/>',
		'calendar-check' => '<rect x="3.5" y="5" width="17" height="15" rx="2.5"/><path d="M3.5 10h17M8 3v4M16 3v4M9 14.5l2 2 4-4"/>',
		'moon'           => '<path d="M19 14.5A7.5 7.5 0 1 1 9.5 5a6 6 0 0 0 9.5 9.5z"/>',
		'phone-line'     => '<path d="M6.6 10.8a15.1 15.1 0 0 0 6.6 6.6l2.2-2.2c.3-.3.7-.4 1-.2 1.1.4 2.3.6 3.6.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1A17 17 0 0 1 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.5.6 3.6.1.3 0 .7-.2 1l-2.3 2.2z"/>',
		'target'         => '<circle cx="12" cy="12" r="8.5"/><path d="M12 3.5v17M3.5 12h17"/>',
		'person'         => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20c.8-3.8 3.6-6 7-6s6.2 2.2 7 6"/>',
		'arrows'         => '<path d="M4 12h16M7.5 8.5 4 12l3.5 3.5M16.5 8.5 20 12l-3.5 3.5"/>',
		'lock'           => '<rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/>',
		'implant'        => '<path d="M8.5 3.5h7l-1.2 4h-4.6z"/><path d="M10 7.5 10.5 20h3L14 7.5"/><path d="M9.8 11h4.4M10 14.5h4M10.2 18h3.6"/>',
		'box'            => '<path d="M4 8 12 4l8 4v8l-8 4-8-4z"/><path d="M4 8l8 4 8-4M12 12v8"/>',
		'pin'            => '<path d="M12 21s-6.5-6-6.5-11a6.5 6.5 0 0 1 13 0c0 5-6.5 11-6.5 11z"/><circle cx="12" cy="10" r="2.3" fill="currentColor" stroke="none"/>',
	);
}

/**
 * A line icon.
 *
 * @param string $name Icon name from scd_icon_paths().
 * @return string SVG markup (static, safe) or ''.
 */
function scd_icon( $name ) {
	$paths = scd_icon_paths();
	if ( empty( $paths[ $name ] ) ) {
		return '';
	}
	return '<svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><g fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . $paths[ $name ] . '</g></svg>';
}

/**
 * Icon choices for widget controls.
 *
 * @return string[]
 */
function scd_icon_options() {
	$labels = array(
		''          => __( 'None', 'south-cairns-dental' ),
		'tooth'     => __( 'Tooth', 'south-cairns-dental' ),
		'family'    => __( 'Family', 'south-cairns-dental' ),
		'heart'     => __( 'Heart', 'south-cairns-dental' ),
		'shield'    => __( 'Shield', 'south-cairns-dental' ),
		'spark'     => __( 'Sparkle', 'south-cairns-dental' ),
		'calendar'  => __( 'Calendar', 'south-cairns-dental' ),
		'car'       => __( 'Car', 'south-cairns-dental' ),
		'chat'      => __( 'Speech bubble', 'south-cairns-dental' ),
		'clipboard' => __( 'Clipboard', 'south-cairns-dental' ),
		'clock'     => __( 'Clock', 'south-cairns-dental' ),
		'music'     => __( 'Music', 'south-cairns-dental' ),
		'pause'     => __( 'Pause', 'south-cairns-dental' ),
		'question'  => __( 'Question', 'south-cairns-dental' ),
		'alert'     => __( 'Alert', 'south-cairns-dental' ),
		'wallet'    => __( 'Wallet', 'south-cairns-dental' ),
		'phone'     => __( 'Phone', 'south-cairns-dental' ),
		'map'       => __( 'Map', 'south-cairns-dental' ),
		'mail'      => __( 'Email', 'south-cairns-dental' ),
		'brush'          => __( 'Toothbrush', 'south-cairns-dental' ),
		'drop'           => __( 'Drop', 'south-cairns-dental' ),
		'cup'            => __( 'Cup', 'south-cairns-dental' ),
		'shield-plain'   => __( 'Shield (plain)', 'south-cairns-dental' ),
		'camera'         => __( 'Camera', 'south-cairns-dental' ),
		'sun'            => __( 'Brightness', 'south-cairns-dental' ),
		'tooth-check'    => __( 'Tooth with tick', 'south-cairns-dental' ),
		'gap'            => __( 'Gap between teeth', 'south-cairns-dental' ),
		'wave'           => __( 'Wavy line', 'south-cairns-dental' ),
		'crown'          => __( 'Crown', 'south-cairns-dental' ),
		'info'           => __( 'Information', 'south-cairns-dental' ),
		'calendar-check' => __( 'Calendar with tick', 'south-cairns-dental' ),
		'moon'           => __( 'Moon (night)', 'south-cairns-dental' ),
		'phone-line'     => __( 'Phone (outline)', 'south-cairns-dental' ),
		'target'         => __( 'Scan (crosshair)', 'south-cairns-dental' ),
		'person'         => __( 'Person', 'south-cairns-dental' ),
		'arrows'         => __( 'Arrows', 'south-cairns-dental' ),
		'lock'           => __( 'Lock', 'south-cairns-dental' ),
		'implant'        => __( 'Implant', 'south-cairns-dental' ),
		'box'            => __( 'Box', 'south-cairns-dental' ),
		'pin'            => __( 'Map pin', 'south-cairns-dental' ),
	);
	return $labels;
}

/**
 * The URL a button type points to.
 *
 * @param string $type   book | call | email | custom.
 * @param string $custom Custom URL.
 * @return string
 */
function scd_button_url( $type, $custom = '' ) {
	switch ( $type ) {
		case 'book':
			return scd_practice( 'booking_url' );
		case 'call':
			return 'tel:' . scd_practice( 'phone_tel' );
		case 'email':
			return 'mailto:' . scd_practice( 'email' );
		default:
			return (string) $custom;
	}
}

/**
 * A button.
 *
 * @param array $args {
 *     @type string $type    book | call | email | custom.
 *     @type string $label   Visible label ({phone} is replaced with the practice phone number).
 *     @type string $url     Custom URL.
 *     @type string $class   Extra classes, e.g. "btn--lime btn--lg".
 *     @type string $icon    calendar | phone | mail | '' (auto by type).
 *     @type bool   $external Open in a new tab.
 *     @type string $aria    aria-label.
 *     @type string $attrs   Extra pre-escaped attributes.
 * }
 * @return string
 */
function scd_button( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'type'     => 'custom',
			'label'    => '',
			'url'      => '',
			'class'    => '',
			'icon'     => 'auto',
			'external' => false,
			'aria'     => '',
			'attrs'    => '',
		)
	);
	$url = scd_button_url( $args['type'], $args['url'] );
	if ( '' === $url ) {
		return '';
	}
	$label = str_replace( '{phone}', scd_practice( 'phone_display' ), (string) $args['label'] );
	$icon  = $args['icon'];
	if ( 'auto' === $icon ) {
		$icon = array( 'book' => 'calendar', 'call' => 'phone', 'email' => '' )[ $args['type'] ] ?? '';
	}
	$icon_html = '';
	if ( 'phone' === $icon ) {
		$icon_html = scd_ui_icon( 'phone' );
	} elseif ( 'mail' === $icon ) {
		$icon_html = scd_ui_icon( 'mail' );
	} elseif ( $icon ) {
		$icon_html = scd_icon( $icon );
	}
	$rel  = ( 'book' === $args['type'] || preg_match( '#^https?://#', $url ) && ! scd_is_internal_url( $url ) ) ? ' rel="noopener"' : '';
	$tgt  = $args['external'] ? ' target="_blank"' : '';
	$aria = $args['aria'] ? ' aria-label="' . esc_attr( $args['aria'] ) . '"' : '';
	$text = $icon_html ? '<span>' . scd_inline( $label ) . '</span>' : scd_inline( $label );
	return sprintf(
		'<a class="%s" href="%s"%s%s%s%s>%s%s</a>',
		esc_attr( trim( 'btn ' . $args['class'] ) ),
		esc_url( $url ),
		$rel,
		$tgt,
		$aria,
		$args['attrs'],
		$icon_html,
		$text
	);
}

/**
 * Is the URL on this site?
 *
 * @param string $url URL.
 * @return bool
 */
function scd_is_internal_url( $url ) {
	$home = wp_parse_url( home_url(), PHP_URL_HOST );
	$host = wp_parse_url( $url, PHP_URL_HOST );
	return ! $host || $host === $home;
}

/**
 * The standard "Book online" + "Call" pair.
 *
 * @param string $primary   Classes for the booking button.
 * @param string $secondary Classes for the call button.
 * @param string $attrs     Extra attributes for the row (pre-escaped).
 * @return string
 */
function scd_book_buttons( $primary = 'btn--lime', $secondary = 'btn--ghost', $attrs = '' ) {
	if ( ! scd_practice( 'booking_url' ) ) {
		return '<div class="btn-row"' . $attrs . '>' . scd_button( array( 'type' => 'call', 'label' => __( 'Call to book {phone}', 'south-cairns-dental' ), 'class' => $primary ) ) . '</div>';
	}
	return '<div class="btn-row"' . $attrs . '>'
		. scd_button( array( 'type' => 'book', 'label' => __( 'Book online', 'south-cairns-dental' ), 'class' => $primary ) )
		. scd_button( array( 'type' => 'call', 'label' => __( 'Call {phone}', 'south-cairns-dental' ), 'class' => $secondary ) )
		. '</div>';
}

/**
 * An image in the site's frame component: fixed aspect ratio, focal point and the SCD corner.
 *
 * @param array $args {
 *     @type int    $id      Attachment ID (preferred: gives srcset and Media Library alt text).
 *     @type string $url     Image URL when there is no attachment.
 *     @type string $alt     Alt text override ('' keeps the Media Library alt; use $decorative for alt="").
 *     @type bool   $decorative Decorative image (alt="").
 *     @type string $class   Frame classes, e.g. "frame--corner".
 *     @type string $ratio   CSS aspect ratio, e.g. "4 / 3".
 *     @type string $pos     Focal point, e.g. "50% 40%".
 *     @type bool   $contain Show the whole image (labelled diagrams).
 *     @type bool   $illus   Illustration background.
 *     @type bool   $eager   Load immediately (above the fold).
 *     @type string $size    Image size for srcset.
 *     @type string $caption Caption text.
 * }
 * @return string
 */
function scd_frame( $args ) {
	$args = wp_parse_args(
		$args,
		array(
			'id'         => 0,
			'url'        => '',
			'alt'        => '',
			'decorative' => false,
			'class'      => '',
			'ratio'      => '4 / 3',
			'pos'        => '',
			'contain'    => false,
			'illus'      => false,
			'eager'      => false,
			'size'       => 'large',
			'caption'    => '',
		)
	);
	$img = scd_img_tag( $args );
	if ( '' === $img ) {
		return '';
	}
	$classes = array( 'frame', $args['class'] );
	if ( $args['contain'] ) {
		$classes[] = 'frame--contain';
	}
	if ( $args['illus'] ) {
		$classes[] = 'frame--illus';
	}
	$style = '--ratio:' . $args['ratio'];
	if ( $args['pos'] ) {
		$style .= ';--pos:' . $args['pos'];
	}
	$cap = $args['caption'] ? '<figcaption class="frame__cap">' . scd_inline( $args['caption'] ) . '</figcaption>' : '';
	return sprintf( '<figure class="%s" style="%s">%s%s</figure>', esc_attr( trim( implode( ' ', array_filter( $classes ) ) ) ), esc_attr( $style ), $img, $cap );
}

/**
 * Does Elementor's "Optimized image loading" add loading/fetchpriority attributes to images?
 * It does so for every image in the header and content and doesn't check for existing ones,
 * so the theme leaves those attributes to it to avoid duplicates.
 *
 * @return bool
 */
function scd_image_loading_by_elementor() {
	return did_action( 'elementor/loaded' ) && '1' === get_option( 'elementor_optimized_image_loading', '1' );
}

/**
 * Loading attributes for an image: none when Elementor manages them, otherwise lazy or high priority.
 *
 * @param bool $eager Above the fold.
 * @return array
 */
function scd_loading_attrs( $eager = false ) {
	if ( scd_image_loading_by_elementor() ) {
		return array( 'loading' => false );
	}
	return $eager ? array( 'fetchpriority' => 'high', 'loading' => false ) : array( 'loading' => 'lazy' );
}

/**
 * An <img> tag from an attachment or URL.
 *
 * @param array $args See scd_frame(); also 'img_class'.
 * @return string
 */
function scd_img_tag( $args ) {
	$args  = wp_parse_args( $args, array( 'id' => 0, 'url' => '', 'alt' => '', 'decorative' => false, 'eager' => false, 'size' => 'large', 'img_class' => '', 'width' => 0, 'height' => 0 ) );
	$attrs = array_merge( array( 'decoding' => 'async' ), scd_loading_attrs( $args['eager'] ) );
	if ( $args['img_class'] ) {
		$attrs['class'] = $args['img_class'];
	}
	if ( $args['decorative'] ) {
		$attrs['alt'] = '';
	} elseif ( '' !== $args['alt'] ) {
		$attrs['alt'] = $args['alt'];
	}
	$id = absint( $args['id'] );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, $args['size'], false, $attrs );
	}
	if ( '' === $args['url'] ) {
		return '';
	}
	$html = '<img src="' . esc_url( $args['url'] ) . '" alt="' . esc_attr( isset( $attrs['alt'] ) ? $attrs['alt'] : '' ) . '"';
	if ( $args['width'] && $args['height'] ) {
		$html .= ' width="' . absint( $args['width'] ) . '" height="' . absint( $args['height'] ) . '"';
	}
	if ( ! empty( $attrs['class'] ) ) {
		$html .= ' class="' . esc_attr( $attrs['class'] ) . '"';
	}
	if ( ! empty( $attrs['fetchpriority'] ) ) {
		$html .= ' fetchpriority="high"';
	}
	if ( ! empty( $attrs['loading'] ) ) {
		$html .= ' loading="lazy"';
	}
	return $html . ' decoding="async">';
}

/**
 * URL of an image bundled with the theme.
 *
 * @param string $rel Path under assets/images/.
 * @return string
 */
function scd_theme_image( $rel ) {
	return SCD_THEME_URI . '/assets/images/' . ltrim( $rel, '/' );
}

/**
 * Breadcrumbs.
 *
 * @param array[] $trail Middle links: each [ label, url ].
 * @param string  $current Current page label.
 * @return string
 */
function scd_crumbs( $trail, $current ) {
	$lis = '<li><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'south-cairns-dental' ) . '</a></li>';
	foreach ( $trail as $link ) {
		if ( empty( $link[0] ) ) {
			continue;
		}
		$lis .= '<li><a href="' . esc_url( $link[1] ) . '">' . scd_inline( $link[0] ) . '</a></li>';
	}
	$lis .= '<li aria-current="page">' . scd_inline( $current ) . '</li>';
	return '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'south-cairns-dental' ) . '"><ol>' . $lis . '</ol></nav>';
}

/**
 * Opening hours table (the current day is highlighted by main.js).
 *
 * @return string
 */
function scd_hours_table() {
	$rows = '';
	foreach ( scd_hours() as $d ) {
		/* translators: 1: opening time, 2: closing time */
		$val   = $d['open'] ? sprintf( __( '%1$s to %2$s', 'south-cairns-dental' ), scd_format_time( $d['open'] ), scd_format_time( $d['close'] ) ) : __( 'Closed', 'south-cairns-dental' );
		$rows .= sprintf( '<tr data-day="%1$s"><th scope="row">%1$s</th><td>%2$s</td></tr>', esc_attr( $d['day'] ), esc_html( $val ) );
	}
	return '<table class="hours"><caption class="visually-hidden">' . esc_html__( 'Opening hours', 'south-cairns-dental' ) . '</caption><tbody>' . $rows . '</tbody></table>';
}

/**
 * Live opening status (filled in by main.js; the summary shows without JavaScript).
 *
 * @return string
 */
function scd_hours_status() {
	return '<span class="status-dot" data-hours-dot aria-hidden="true"></span><span data-hours-status="text">' . esc_html( scd_practice( 'hours_summary' ) ) . '</span>';
}

/**
 * Map card: a keyless Google Maps embed with the address and directions always visible,
 * so nothing depends on the embed loading.
 *
 * @return string
 */
function scd_map_card() {
	$addr = scd_address_one_line();
	return '<figure class="mapcard"><div class="map"><div class="map__fallback" aria-hidden="true">' . scd_icon( 'map' ) . '<p><strong>' . esc_html( $addr ) . '</strong></p></div>'
		/* translators: %s: practice address */
		. '<iframe title="' . esc_attr( sprintf( __( 'Map showing %1$s at %2$s', 'south-cairns-dental' ), scd_practice( 'name' ), $addr ) ) . '" src="' . esc_url( scd_maps_embed() ) . '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe></div>'
		. '<figcaption class="mapcard__bar"><span class="mapcard__addr">' . scd_ui_icon( 'pin' ) . '<span><strong>' . esc_html( scd_practice( 'name' ) ) . '</strong><br>' . esc_html( $addr ) . '</span></span>'
		. '<span class="btn-row"><a class="btn btn--forest btn--sm" href="' . esc_url( scd_maps_directions() ) . '" rel="noopener">' . esc_html__( 'Get directions', 'south-cairns-dental' ) . '</a>'
		. '<a class="btn btn--ghost btn--sm" href="' . esc_url( scd_maps_link() ) . '" rel="noopener">' . esc_html__( 'Open in Google Maps', 'south-cairns-dental' ) . '</a></span></figcaption></figure>';
}

/**
 * Permalink of a page by slug, or the fallback.
 *
 * @param string $slug     Page path.
 * @param string $fallback Fallback URL.
 * @return string
 */
function scd_page_url( $slug, $fallback = '' ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return $fallback ? $fallback : home_url( '/' . trim( $slug, '/' ) . '/' );
}

/**
 * The standard booking band (used by templates that are not built in Elementor: blog, posts, search).
 *
 * @return string
 */
function scd_cta_band() {
	$email = scd_practice( 'email' );
	return '<section class="sec sec--tight" aria-labelledby="cta-h"><div class="wrap"><div class="cta-band"><div><h2 id="cta-h">' . esc_html__( 'Ready to book?', 'south-cairns-dental' ) . '</h2>'
		. '<p>' . esc_html__( 'Book online or call our Edmonton reception team and we’ll find a time that suits you.', 'south-cairns-dental' )
		. ( $email ? ' ' . esc_html__( 'You can also email', 'south-cairns-dental' ) . ' <a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>.' : '' ) . '</p></div>'
		. scd_book_buttons( 'btn--lime', 'btn--ghost-light' ) . '</div></div></section>';
}
