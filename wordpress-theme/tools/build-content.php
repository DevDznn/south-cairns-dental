<?php
/**
 * Build the theme's starter content from the static site.
 *
 * Reads the built HTML pages in the repository root (and _build/*.json for treatment details,
 * blog posts and image alt text) and writes Elementor page data for every page to
 * wordpress-theme/south-cairns-dental/inc/import/content/site.json. Each section becomes a
 * South Cairns Dental widget whose settings hold that section's text, images and links, so the
 * imported pages are fully editable in Elementor.
 *
 * Usage (from the repository root, PHP 7.4+ with DOM):
 *     php wordpress-theme/tools/build-content.php
 *
 * Placeholders resolved by the importer on the WordPress site:
 *     scd-asset:<path under assets/images/>   image (imported into the Media Library)
 *     scd-page:<slug>[#anchor]                link to an imported page or post
 *     scd-pageid:<slug>                       ID of an imported page (related treatments)
 *     href="<slug>.html"                      links inside text are rewritten to permalinks
 */

// phpcs:disable -- developer CLI tool, not part of the theme.

if ( PHP_SAPI !== 'cli' ) {
	exit;
}

$root  = dirname( __DIR__, 2 );
$build = $root . '/_build';
$out   = $root . '/wordpress-theme/south-cairns-dental/inc/import/content/site.json';

$P      = json_decode( file_get_contents( $build . '/practice.json' ), true );
$IMAGES = json_decode( file_get_contents( $build . '/images.json' ), true );
$BLOG   = json_decode( file_get_contents( $build . '/blog.json' ), true );

$PHONE   = $P['phone_display'];
$BOOKING = $P['booking_url'];

/* ---------------------------------------------------------------------
 * Helpers
 * ------------------------------------------------------------------- */

$GLOBALS['id_seq'] = 0;
function eid( $seed ) {
	$GLOBALS['id_seq']++;
	return substr( md5( $seed . '|' . $GLOBALS['id_seq'] ), 0, 7 );
}

function load_html( $file ) {
	$html = file_get_contents( $file );
	$dom  = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8">' . $html, LIBXML_NOERROR | LIBXML_NOWARNING );
	libxml_clear_errors();
	return $dom;
}

function xp( DOMNode $node ) {
	$doc = $node instanceof DOMDocument ? $node : $node->ownerDocument;
	return new DOMXPath( $doc );
}

/** First match (relative XPath like .//h2) or null. */
function q( DOMNode $ctx, $path ) {
	$r = xp( $ctx )->query( $path, $ctx );
	return $r && $r->length ? $r->item( 0 ) : null;
}

/** All matches as an array. */
function qa( DOMNode $ctx, $path ) {
	$r = xp( $ctx )->query( $path, $ctx );
	$a = array();
	if ( $r ) {
		foreach ( $r as $n ) {
			$a[] = $n;
		}
	}
	return $a;
}

/** XPath class test. */
function c( $class ) {
	return "contains(concat(' ', normalize-space(@class), ' '), ' $class ')";
}

function has_class( $node, $class ) {
	return $node instanceof DOMElement && in_array( $class, preg_split( '/\s+/', (string) $node->getAttribute( 'class' ) ), true );
}

/** Inner HTML without SVG icons, tidied. */
function inner( $node, $strip_svg = true ) {
	if ( ! $node ) {
		return '';
	}
	$html = '';
	foreach ( $node->childNodes as $child ) {
		if ( $strip_svg && $child instanceof DOMElement && 'svg' === $child->nodeName ) {
			continue;
		}
		$html .= $node->ownerDocument->saveHTML( $child );
	}
	return tidy_html( $html );
}

function tidy_html( $html ) {
	$html = preg_replace( '/\s+/', ' ', $html );
	$html = str_replace( array( '<br>', ' </p>', '<p> ' ), array( '<br>', '</p>', '<p>' ), $html );
	return trim( $html );
}

function text( $node ) {
	return $node ? trim( preg_replace( '/\s+/', ' ', $node->textContent ) ) : '';
}

/** Paragraph elements → textarea text (blank line between paragraphs). */
function paras_text( array $ps ) {
	$out = array();
	foreach ( $ps as $p ) {
		$out[] = inner( $p );
	}
	return implode( "\n\n", array_filter( $out, 'strlen' ) );
}

/** The phone number in labels becomes {phone} so it follows the practice settings. */
function phone_token( $label ) {
	global $PHONE;
	return str_replace( $PHONE, '{phone}', $label );
}

/** Image path → asset placeholder. */
function asset( $src ) {
	return 0 === strpos( $src, 'assets/images/' ) ? 'scd-asset:' . substr( $src, strlen( 'assets/images/' ) ) : $src;
}

/** Link → placeholder (internal pages) or the URL as is. */
function link_value( $href ) {
	if ( preg_match( '#^(?:index\.html|\./?)$#', $href ) ) {
		return array( 'url' => 'scd-page:', 'is_external' => '', 'nofollow' => '' );
	}
	if ( preg_match( '#^([a-z0-9-]+)\.html(\?[^\#]*)?(\#.*)?$#', $href, $m ) ) {
		$slug = $m[1];
		if ( 0 === strpos( $slug, 'blog-' ) && 'blog' !== $slug ) {
			$slug = substr( $slug, 5 );
		}
		return array( 'url' => 'scd-page:' . $slug . ( $m[2] ?? '' ) . ( $m[3] ?? '' ), 'is_external' => '', 'nofollow' => '' );
	}
	return array( 'url' => $href, 'is_external' => '', 'nofollow' => '' );
}

/** Icon name from an inline SVG (matched by its shapes). */
function icon_name( $svg ) {
	static $map = null;
	if ( null === $map ) {
		$map   = array();
		$theme = file_get_contents( dirname( __DIR__ ) . '/south-cairns-dental/inc/template-tags.php' );
		preg_match( "#function scd_icon_paths\(\) \{.*?return array\((.*?)\);#s", $theme, $m );
		preg_match_all( "#'([a-z-]+)'\s*=>\s*'(.*?)',#", $m[1], $all, PREG_SET_ORDER );
		foreach ( $all as $row ) {
			$doc = new DOMDocument();
			$doc->loadXML( '<svg xmlns="http://www.w3.org/2000/svg">' . $row[2] . '</svg>' );
			$map[ svg_sig( $doc->documentElement ) ] = $row[1];
		}
	}
	if ( ! $svg ) {
		return '';
	}
	$sig = svg_sig( $svg );
	return isset( $map[ $sig ] ) ? $map[ $sig ] : '';
}

function svg_sig( $node ) {
	$parts = array();
	foreach ( qa( $node, './/*' ) as $el ) {
		if ( in_array( $el->nodeName, array( 'g', 'svg' ), true ) ) {
			continue;
		}
		foreach ( array( 'd', 'cx', 'cy', 'r', 'x', 'y', 'width', 'height', 'rx' ) as $a ) {
			if ( $el->hasAttribute( $a ) ) {
				$parts[] = $a . '=' . $el->getAttribute( $a );
			}
		}
	}
	return md5( implode( '|', $parts ) );
}

/** A button (<a class="btn …">) or text link → button settings with prefix. */
function button( $a, $p ) {
	global $BOOKING;
	if ( ! $a ) {
		return array( $p . '_type' => 'none' );
	}
	$href  = $a->getAttribute( 'href' );
	$cls   = ' ' . $a->getAttribute( 'class' ) . ' ';
	$label = phone_token( text( $a ) );
	if ( $href === $BOOKING ) {
		$type = 'book';
	} elseif ( 0 === strpos( $href, 'tel:' ) ) {
		$type = 'call';
	} elseif ( 0 === strpos( $href, 'mailto:' ) ) {
		$type = 'email';
	} else {
		$type = 'custom';
	}
	$style = 'forest';
	foreach ( array( 'ghost-light', 'alert-ghost', 'lime', 'ghost', 'alert' ) as $s ) {
		if ( false !== strpos( $cls, ' btn--' . $s . ' ' ) ) {
			$style = $s;
			break;
		}
	}
	if ( false !== strpos( $cls, ' textlink ' ) ) {
		$style = 'textlink';
	}
	$size = false !== strpos( $cls, ' btn--lg ' ) ? 'lg' : ( false !== strpos( $cls, ' btn--sm ' ) ? 'sm' : '' );
	$has  = (bool) q( $a, './/*[local-name()="svg"]' );
	$out  = array(
		$p . '_type'  => $type,
		$p . '_label' => $label,
		$p . '_style' => $style,
		$p . '_size'  => $size,
		$p . '_icon'  => ( 'textlink' === $style || in_array( $type, array( 'book', 'call' ), true ) ) && $has ? 'auto' : ( $has ? 'mail' : '' ),
	);
	if ( 'custom' === $type ) {
		$out[ $p . '_url' ] = link_value( $href );
	}
	return $out;
}

/** Image (<figure class="frame …"><img></figure> or bare <img>) → media settings with prefix. */
function image( $fig, $p ) {
	if ( ! $fig ) {
		return array();
	}
	$img = 'img' === $fig->nodeName ? $fig : q( $fig, './/img' );
	if ( ! $img ) {
		return array();
	}
	$out = array( $p => array( 'url' => asset( $img->getAttribute( 'src' ) ), 'id' => '' ) );
	if ( 'figure' === $fig->nodeName ) {
		$style = $fig->getAttribute( 'style' );
		if ( preg_match( '#--ratio:\s*([^;]+)#', $style, $m ) ) {
			$out[ $p . '_ratio' ] = trim( $m[1] );
		}
		$out[ $p . '_pos' ] = preg_match( '#--pos:\s*([^;]+)#', $style, $m ) ? trim( $m[1] ) : '';
		$corner             = '';
		foreach ( array( 'corner-tr', 'corner-bl', 'corner-br', 'corner' ) as $cn ) {
			if ( has_class( $fig, 'frame--' . $cn ) ) {
				$corner = $cn;
				break;
			}
		}
		$out[ $p . '_corner' ] = $corner;
		$out[ $p . '_fit' ]    = has_class( $fig, 'frame--contain' ) ? 'yes' : '';
	}
	return $out;
}

function media_value( $img ) {
	return $img ? array( 'url' => asset( $img->getAttribute( 'src' ) ), 'id' => '' ) : array( 'url' => '', 'id' => '' );
}

function rep( array $fields, $seed ) {
	$fields['_id'] = eid( $seed );
	return $fields;
}

/** Section background, spacing and anchor from the section element. */
function section_settings( DOMElement $sec, $default_bg = '' ) {
	$s = array( 'sec_bg' => '', 'sec_size' => '' );
	foreach ( array( 'mist', 'lime', 'canopy', 'forest' ) as $bg ) {
		if ( has_class( $sec, 'sec--' . $bg ) ) {
			$s['sec_bg'] = $bg;
		}
	}
	if ( false !== strpos( $sec->getAttribute( 'style' ), 'linear-gradient' ) ) {
		$s['sec_bg'] = 'fade';
	}
	foreach ( array( 'tight', 'flush' ) as $sz ) {
		if ( has_class( $sec, 'sec--' . $sz ) ) {
			$s['sec_size'] = $sz;
		}
	}
	if ( $sec->hasAttribute( 'id' ) ) {
		$s['sec_anchor'] = $sec->getAttribute( 'id' );
	}
	return $s;
}

/** Heading block → head settings (layout, kicker, heading, intro, buttons) and the content node. */
function head( DOMElement $wrapish, $default_layout = 'stacked' ) {
	$s = array( 'head_layout' => $default_layout, 'heading' => '', 'intro' => '', 'intro_style' => 'muted', 'head_kicker' => '', 'heading_tag' => 'h2' );
	$btns = array();

	$row = q( $wrapish, './/div[' . c( 'section-head--row' ) . ']' );
	if ( $row ) {
		$s['head_layout'] = 'row';
		$div              = q( $row, './div' );
		$s['heading']     = inner( q( $div, './h2' ) );
		$ps               = qa( $div, './p' );
		$s['intro']       = paras_text( $ps );
		$s['intro_style'] = ( $ps && ! has_class( $ps[0], 'muted' ) && ! has_class( $ps[0], 'section-head__intro' ) ) ? 'normal' : 'muted';
		$btns             = qa( $row, './a' );
	} elseif ( $sh = q( $wrapish, './/div[' . c( 'section-head' ) . ']' ) ) {
		$s['head_layout'] = 'stacked';
		$s['head_kicker'] = text( q( $sh, './p[' . c( 'kicker' ) . ']' ) );
		$s['heading']     = inner( q( $sh, './h2' ) );
		$ps               = qa( $sh, './p[not(' . c( 'kicker' ) . ')]' );
		$s['intro']       = paras_text( $ps );
		$s['intro_style'] = ! $ps ? 'narrow' : ( has_class( $ps[0], 'section-head__intro' ) ? 'narrow' : ( has_class( $ps[0], 'muted' ) ? 'muted' : 'normal' ) );
		$btns             = qa( $sh, './/a[' . c( 'btn' ) . ' or ' . c( 'textlink' ) . ']' );
	} elseif ( has_class( $wrapish, 'split' ) || q( $wrapish, './div[' . c( 'split' ) . ']' ) ) {
		$split = has_class( $wrapish, 'split' ) ? $wrapish : q( $wrapish, './div[' . c( 'split' ) . ']' );
		preg_match( '#split--(\d-\d)#', $split->getAttribute( 'class' ), $m );
		$s['head_layout'] = 'split-' . ( $m ? $m[1] : '5-7' );
		$div              = q( $split, './div[1]' );
		$s['head_kicker'] = text( q( $div, './p[' . c( 'kicker' ) . ']' ) );
		$h                = q( $div, './h2' );
		$s['heading']     = inner( $h );
		$ps               = array();
		foreach ( qa( $div, './p[not(' . c( 'kicker' ) . ')]' ) as $p ) {
			$only_link = 1 === count( qa( $p, './*' ) ) && q( $p, './a[' . c( 'textlink' ) . ']' ) && text( $p ) === text( q( $p, './a' ) );
			if ( $only_link ) {
				$btns[] = q( $p, './a' );
			} else {
				$ps[] = $p;
			}
		}
		$s['intro']       = paras_text( $ps );
		$s['intro_style'] = ! $ps ? 'narrow' : ( has_class( $ps[0], 'section-head__intro' ) ? 'narrow' : ( has_class( $ps[0], 'muted' ) ? 'muted' : 'normal' ) );
		foreach ( qa( $div, './a[' . c( 'btn' ) . '] | ./div[' . c( 'btn-row' ) . ']/a' ) as $a ) {
			$btns[] = $a;
		}
	} elseif ( $h = q( $wrapish, './/h2[' . c( 'visually-hidden' ) . ']' ) ) {
		$s['head_layout'] = 'hidden';
		$s['heading']     = text( $h );
	} elseif ( $h = q( $wrapish, './h2' ) ) {
		$s['head_layout'] = 'bare';
		$s['heading']     = inner( $h );
	}
	$n = 1;
	foreach ( array_slice( $btns, 0, 2 ) as $a ) {
		$s = array_merge( $s, button( $a, 'head_btn' . $n ) );
		++$n;
	}
	return $s;
}

function widget( $type, array $settings, $seed ) {
	return array(
		'id'         => eid( $seed . $type ),
		'elType'     => 'widget',
		'widgetType' => $type,
		'settings'   => $settings,
		'elements'   => array(),
	);
}

/** One full-width, zero-padding container per section. */
function container( array $widget, $seed ) {
	$zero = array( 'unit' => 'px', 'top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0', 'isLinked' => true );
	return array(
		'id'       => eid( $seed . 'c' ),
		'elType'   => 'container',
		'settings' => array(
			'content_width' => 'full',
			'css_classes'   => 'scd-sec',
			'padding'       => $zero,
			'margin'        => $zero,
			'flex_gap'      => array( 'unit' => 'px', 'column' => '0', 'row' => '0', 'isLinked' => true, 'size' => 0 ),
			'_title'        => $widget['widgetType'],
		),
		'elements' => array( $widget ),
		'isInner'  => false,
	);
}

/* ---------------------------------------------------------------------
 * Section converters
 * ------------------------------------------------------------------- */

function conv_home_hero( DOMElement $sec, $seed ) {
	$h1   = q( $sec, './/h1' );
	$hl   = q( $h1, './span[' . c( 'hl' ) . ']' );
	$main = trim( str_replace( text( $hl ), '', text( $h1 ) ) );
	$s    = array(
		'title'           => $main,
		'title_highlight' => text( $hl ),
		'lead'            => inner( q( $sec, './/p[' . c( 'lead' ) . ']' ) ),
	);
	$row  = q( $sec, './/div[' . c( 'btn-row' ) . ']' );
	$as   = qa( $row, './a' );
	$s    = array_merge( $s, button( $as[0] ?? null, 'btn1' ), button( $as[1] ?? null, 'btn2' ) );
	$s['trust'] = array();
	foreach ( qa( $sec, './/div[' . c( 'hero-trust' ) . ']/span' ) as $i => $span ) {
		$b            = q( $span, './b' );
		$s['trust'][] = rep( array( 'icon' => icon_name( q( $span, './/*[local-name()="svg"]' ) ), 'strong' => text( $b ), 'text' => trim( str_replace( text( $b ), '', text( $span ) ) ) ), $seed . 'trust' . $i );
	}
	$s = array_merge( $s, image( q( $sec, './/div[' . c( 'hero-photo' ) . ']/figure' ), 'image' ) );
	$card = q( $sec, './/div[' . c( 'bookcard' ) . ']' );
	if ( $card ) {
		$s['card_show']  = 'yes';
		$s['card_title'] = text( q( $card, './h2' ) );
		$s['card_rows']  = array();
		foreach ( qa( $card, './dl/div' ) as $i => $d ) {
			$s['card_rows'][] = rep( array( 'label' => text( q( $d, './dt' ) ), 'value' => text( q( $d, './dd' ) ) ), $seed . 'row' . $i );
		}
		$s                    = array_merge( $s, button( q( $card, './a[' . c( 'btn' ) . ']' ), 'card_btn' ) );
		$s['card_alt_label']  = text( q( $card, './/p[' . c( 'bookcard__alt' ) . ']/a' ) );
		$s['card_alt_type']   = 'book';
	}
	return widget( 'scd-home-hero', $s, $seed );
}

function conv_info_strip( DOMElement $sec, $seed ) {
	global $PHONE;
	$items = array();
	foreach ( qa( $sec, './/div[' . c( 'infostrip__item' ) . ']' ) as $i => $it ) {
		$a    = q( $it, './/a' );
		$href = $a ? $a->getAttribute( 'href' ) : '';
		$type = ! $a ? 'none' : ( 0 === strpos( $href, 'tel:' ) ? 'call' : ( false !== strpos( $href, 'google.com/maps' ) ? 'map' : 'custom' ) );
		$row  = array(
			'icon'      => icon_name( q( $it, './*[local-name()="svg"]' ) ),
			'title'     => text( q( $it, './/strong' ) ),
			'text'      => str_replace( $PHONE, '{phone}', text( $a ? $a : q( $it, './/span' ) ) ),
			'link_type' => $type,
			'urgent'    => has_class( $it, 'infostrip__item--urgent' ) ? 'yes' : '',
		);
		if ( 'custom' === $type ) {
			$row['link'] = link_value( $href );
		}
		$items[] = rep( $row, $seed . $i );
	}
	return widget( 'scd-info-strip', array( 'items' => $items ), $seed );
}

function crumbs_trail( DOMElement $sec, $seed ) {
	$trail = array();
	$lis   = qa( $sec, './/nav[' . c( 'crumbs' ) . ']//li' );
	foreach ( array_slice( $lis, 1, -1 ) as $i => $li ) {
		$a       = q( $li, './a' );
		$trail[] = rep( array( 'label' => text( $li ), 'link' => link_value( $a ? $a->getAttribute( 'href' ) : '' ) ), $seed . 'crumb' . $i );
	}
	$current = $lis ? text( end( $lis ) ) : '';
	return array( $trail, $current );
}

function conv_page_hero( DOMElement $sec, $seed ) {
	$s = array( 'variant' => '' );
	if ( has_class( $sec, 'phero--band' ) ) {
		$s['variant'] = 'band';
	} elseif ( has_class( $sec, 'phero--plain' ) ) {
		$s['variant'] = 'plain';
	}
	list( $trail, $current ) = crumbs_trail( $sec, $seed );
	$s['crumbs_show']        = $current ? 'yes' : '';
	$s['crumbs']             = $trail;
	$s['crumb_current']      = $current;
	$wrap                    = q( $sec, './div' );
	$split                   = has_class( $wrap, 'split' ) ? $wrap : q( $wrap, './div[' . c( 'split' ) . ']' );
	$s['crumbs_inside']      = ( $split && $split === $wrap ) ? 'yes' : '';
	$s['title']              = inner( q( $sec, './/h1' ) );
	$lead                    = q( $sec, './/p[' . c( 'lead' ) . ']' );
	$s['lead']               = inner( $lead );

	if ( $split && q( $split, './h1' ) ) {
		$s['layout'] = 'title-split';
	} elseif ( $split ) {
		preg_match( '#split--(\d-\d)#', $split->getAttribute( 'class' ), $m );
		$s['layout'] = 'split-' . $m[1];
	} elseif ( has_class( $wrap, 'wrap-text' ) ) {
		$s['layout'] = 'narrow';
	} else {
		$s['layout'] = 'single';
	}
	$row = q( $sec, './/div[' . c( 'btn-row' ) . ']' );
	$as  = $row ? qa( $row, './a' ) : array();
	$s   = array_merge( $s, button( $as[0] ?? null, 'btn1' ), button( $as[1] ?? null, 'btn2' ) );
	$tl  = null;
	foreach ( qa( $sec, './/a[' . c( 'textlink' ) . ']' ) as $a ) {
		if ( ! q( $a, 'ancestor::div[' . c( 'panel' ) . ' or ' . c( 'phero__aside' ) . ']' ) && ! q( $a, 'ancestor::p[' . c( 'phero__aside' ) . ']' ) ) {
			$tl = $a;
		}
	}
	$s = array_merge( $s, button( $tl, 'link' ) );

	$s['aside'] = '';
	if ( $panel = q( $sec, './/div[' . c( 'panel' ) . ']' ) ) {
		$s['aside']      = 'panel';
		$s['aside_text'] = inner( $panel );
		if ( false === strpos( $s['aside_text'], '<p' ) ) {
			$s['aside_text'] = '<p>' . $s['aside_text'] . '</p>';
		}
		// The panel's link becomes part of its text: '<p>…</p><a class="textlink">…</a>' stays as HTML.
	} elseif ( $note = q( $sec, './/p[' . c( 'phero__aside' ) . ']' ) ) {
		$s['aside']      = 'note';
		$s['aside_text'] = '<p>' . inner( $note ) . '</p>';
	} elseif ( $jump = q( $sec, './/nav[' . c( 'jump' ) . ']' ) ) {
		$s['aside']      = 'jump';
		$s['jump_label'] = text( q( $jump, './/p' ) );
		$s['jump']       = array();
		foreach ( qa( $jump, './/a' ) as $i => $a ) {
			$href        = $a->getAttribute( 'href' );
			$s['jump'][] = rep( array( 'label' => text( $a ), 'anchor' => '#support-h' === $href ? '#support' : $href ), $seed . 'jump' . $i );
		}
	} elseif ( $split && ( $fig = q( $split, './figure' ) ) ) {
		$s['aside'] = 'image';
		$s          = array_merge( $s, image( $fig, 'image' ) );
	}
	$s['strip'] = array();
	foreach ( qa( $sec, './/ul[' . c( 'portrait-strip' ) . ']/li' ) as $i => $li ) {
		$a            = q( $li, './a' );
		$s['strip'][] = rep( array( 'image' => media_value( q( $li, './/img' ) ), 'name' => text( q( $li, './/span' ) ), 'link' => link_value( $a->getAttribute( 'href' ) ) ), $seed . 'strip' . $i );
	}
	return widget( 'scd-page-hero', $s, $seed );
}

function conv_service_hero( DOMElement $sec, $seed, $sticky ) {
	list( $trail, $current ) = crumbs_trail( $sec, $seed );
	$urgent                  = has_class( $sec, 'svc-hero--urgent' );
	$row                     = q( $sec, './/div[' . c( 'btn-row' ) . ']' );
	$btns                    = qa( $row, './a[' . c( 'btn' ) . ']' );
	$tl                      = q( $row, './a[' . c( 'textlink' ) . ']' );
	$s                       = array(
		'tone'          => $urgent ? 'urgent' : '',
		'kicker'        => text( q( $sec, './/p[' . c( 'svc-hero__kicker' ) . ']' ) ),
		'title'         => inner( q( $sec, './/h1' ) ),
		'lead'          => inner( q( $sec, './/p[' . c( 'lead' ) . ']' ) ),
		'book_label'    => $urgent ? 'Book online' : phone_token( text( $btns[0] ) ),
		'call_label'    => phone_token( text( $urgent ? $btns[0] : $btns[1] ) ),
		'link_label'    => text( $tl ),
		'link_url'      => $tl ? link_value( $tl->getAttribute( 'href' ) ) : array( 'url' => '' ),
		'status'        => q( $sec, './/p[' . c( 'svc-hero__status' ) . ']' ) ? 'yes' : '',
		'sticky'        => $sticky ? 'yes' : '',
		'crumb_parent'  => $trail ? $trail[0]['label'] : 'Services',
		'crumb_current' => $current,
	);
	return widget( 'scd-service-hero', array_merge( $s, image( q( $sec, './/div[' . c( 'svc-hero__media' ) . ']/figure' ), 'image' ) ), $seed );
}

function conv_callout( DOMElement $sec, $seed ) {
	$tone = 'brand';
	foreach ( array( 'brand', 'info', 'alert' ) as $t ) {
		if ( has_class( $sec, 'callout--' . $t ) ) {
			$tone = $t;
		}
	}
	$inner = q( $sec, './/div[' . c( 'callout__inner' ) . ']' );
	$div   = q( $inner, './div' );
	$btn   = q( $inner, './a[' . c( 'btn' ) . ']' );
	$html  = '';
	foreach ( qa( $div, './*[not(self::h2)]' ) as $el ) {
		$html .= $sec->ownerDocument->saveHTML( $el );
	}
	return widget(
		'scd-callout',
		array(
			'tone'       => $tone,
			'badge'      => text( q( $inner, './p[' . c( 'callout__badge' ) . ']' ) ),
			'title'      => inner( q( $div, './h2' ) ),
			'text'       => tidy_html( $html ),
			'call'       => $btn ? 'yes' : '',
			'call_label' => $btn ? phone_token( text( $btn ) ) : 'Call {phone}',
		),
		$seed
	);
}

function conv_media_text( DOMElement $sec, $seed ) {
	$wrap   = q( $sec, './div' );
	$comfort = has_class( $wrap, 'comfort' );
	$layout = $comfort ? 'comfort' : ( has_class( $wrap, 'editorial--about' ) ? 'about' : 'editorial' );
	$fig    = q( $wrap, './div[1]//figure' );
	$text   = q( $wrap, './div[2]' );
	$s      = array_merge(
		section_settings( $sec ),
		array(
			'layout'  => $layout,
			'kicker'  => text( q( $text, './p[' . c( 'kicker' ) . ']' ) ),
			'heading' => inner( q( $text, './h2' ) ),
		),
		image( $fig, 'image' )
	);
	if ( $prose = q( $text, './div[' . c( 'prose' ) . ']' ) ) {
		$s['body']       = inner( $prose );
		$s['body_style'] = 'prose';
	} elseif ( $lead = q( $text, './p[' . c( 'lead' ) . ']' ) ) {
		$s['body']       = '<p>' . inner( $lead ) . '</p>';
		$s['body_style'] = 'lead';
	}
	if ( $badge = q( $wrap, './/div[' . c( 'editorial__badge' ) . ']' ) ) {
		$s['badge_title'] = text( q( $badge, './strong' ) );
		$s['badge_text']  = text( q( $badge, './span' ) );
	}
	$s['list_style'] = '';
	$s['list']       = array();
	if ( $asks = q( $text, './ul[' . c( 'asks' ) . ']' ) ) {
		$s['list_style'] = 'asks';
		$s['list_label'] = $asks->getAttribute( 'aria-label' );
		foreach ( qa( $asks, './li' ) as $i => $li ) {
			$s['list'][] = rep( array( 'icon' => '', 'title' => inner( $li ), 'text' => '' ), $seed . 'ask' . $i );
		}
	} elseif ( $cl = q( $text, './ul[' . c( 'comfort__list' ) . ']' ) ) {
		$s['list_style'] = 'comfort';
		foreach ( qa( $cl, './li' ) as $i => $li ) {
			$s['list'][] = rep( array( 'icon' => icon_name( q( $li, './/*[local-name()="svg"]' ) ), 'title' => inner( q( $li, './/strong' ) ), 'text' => inner( q( $li, './div/span' ) ) ), $seed . 'li' . $i );
		}
	}
	$s['stats'] = array();
	foreach ( qa( $text, './dl[' . c( 'stats' ) . ']/div' ) as $i => $d ) {
		$s['stats'][] = rep( array( 'number' => (int) text( q( $d, './dd' ) ), 'label' => text( q( $d, './dt' ) ) ), $seed . 'stat' . $i );
	}
	$as = qa( $text, './div[' . c( 'btn-row' ) . ']/a' );
	$s  = array_merge( $s, button( $as[0] ?? null, 'btn1' ), button( $as[1] ?? null, 'btn2' ) );
	return widget( 'scd-media-text', $s, $seed );
}

/** Card/list item settings for each style. */
function conv_items( $list, $style, $seed ) {
	$items = array();
	$base  = array( 'icon' => '', 'kicker' => '', 'title' => '', 'text' => '', 'list' => '', 'flag' => '', 'link_label' => '', 'link' => array( 'url' => '' ), 'anchor' => '', 'highlight' => '', 'call' => '' );
	$nodes = 'teaser' === $style ? array( $list ) : qa( $list, './*' );
	foreach ( $nodes as $i => $el ) {
		$it = $base;
		switch ( $style ) {
			case 'concierge':
				$a                = q( $el, './a' );
				$it['icon']       = icon_name( q( $el, './/span[' . c( 'concierge__icon' ) . ']/*' ) );
				$it['title']      = inner( q( $a, './strong' ) );
				$it['text']       = inner( q( $a, './span[' . c( 'd' ) . ']' ) );
				$it['link_label'] = text( q( $a, './span[' . c( 'more' ) . ']' ) );
				$it['link']       = link_value( $a->getAttribute( 'href' ) );
				$it['highlight']  = has_class( $el, 'is-urgent' ) ? 'yes' : '';
				break;
			case 'features':
			case 'pay':
			case 'ways':
				$it['icon']  = icon_name( q( $el, './*[local-name()="svg"]' ) );
				$it['title'] = inner( q( $el, './h3' ) );
				$it['text']  = paras_text( qa( $el, './p' ) );
				if ( $a = q( $el, './a[' . c( 'textlink' ) . ']' ) ) {
					$it['link_label'] = phone_token( text( $a ) );
					$it['link']       = link_value( $a->getAttribute( 'href' ) );
				}
				break;
			case 'mention':
			case 'care':
				$it['title'] = inner( q( $el, './strong' ) );
				$it['text']  = inner( q( $el, './span' ) );
				break;
			case 'duo':
				$h            = q( $el, './h2 | ./h3' );
				$it['title']  = inner( $h );
				$it['text']   = paras_text( qa( $el, './p' ) );
				$it['anchor'] = $el->getAttribute( 'id' );
				if ( $a = q( $el, './a[' . c( 'textlink' ) . ']' ) ) {
					$it['link_label'] = text( $a );
					$it['link']       = link_value( $a->getAttribute( 'href' ) );
				}
				break;
			case 'nextlinks':
			case 'teaser':
				$a                = 'teaser' === $style ? $el : q( $el, './a' );
				$it['kicker']     = text( q( $a, './span[' . c( 'kicker' ) . ']' ) );
				$it['title']      = inner( q( $a, './strong' ) );
				$it['text']       = phone_token( inner( q( $a, './span[not(' . c( 'kicker' ) . ')]' ) ) );
				$it['link']       = link_value( $a->getAttribute( 'href' ) );
				break;
			case 'journey':
			case 'process':
			case 'steps':
				$it['title'] = inner( q( $el, './/h3' ) );
				$it['text']  = paras_text( qa( $el, './/p' ) );
				$it['flag']  = inner( q( $el, './span[' . c( 'process__note' ) . ']' ) );
				break;
			case 'factrow':
				$it['title'] = inner( q( $el, './dt' ) );
				$it['text']  = str_replace( '<br>', "\n", inner( q( $el, './dd' ) ) );
				break;
			case 'ticks':
			case 'ticks-cards':
				$it['text'] = inner( $el );
				break;
			case 'info':
				$it['kicker'] = text( q( $el, './span[' . c( 'tag' ) . ']' ) );
				$it['title']  = inner( q( $el, './h3' ) );
				$it['text']   = paras_text( qa( $el, './p[not(' . c( 'info-card__flag' ) . ')]' ) );
				$lines        = array();
				foreach ( qa( $el, './ul/li' ) as $li ) {
					$lines[] = inner( $li );
				}
				$it['list']      = implode( "\n", $lines );
				$it['flag']      = inner( q( $el, './p[' . c( 'info-card__flag' ) . ']' ) );
				$it['call']      = q( $el, './div[' . c( 'mt-auto' ) . ']' ) ? 'yes' : '';
				$it['highlight'] = ( has_class( $el, 'info-card--ask' ) && ! $it['call'] ) ? 'yes' : '';
				break;
			case 'weigh':
				$it['icon']  = icon_name( q( $el, './h3/*[local-name()="svg"]' ) );
				$it['title'] = inner( q( $el, './h3' ) );
				$lines       = array();
				foreach ( qa( $el, './ul/li' ) as $li ) {
					$lines[] = inner( $li );
				}
				$it['list'] = implode( "\n", $lines );
				break;
		}
		$items[] = rep( $it, $seed . $style . $i );
	}
	return $items;
}

/** Which card style a list element is. */
function card_style( DOMElement $list ) {
	$map = array(
		'concierge'    => 'concierge',
		'features'     => 'features',
		'pay'          => 'pay',
		'ways'         => 'ways',
		'mention'      => 'mention',
		'duo'          => 'duo',
		'nextlinks'    => 'nextlinks',
		'journey'      => 'journey',
		'process'      => 'process',
		'steps'        => 'steps',
		'factrow'      => 'factrow',
		'info-cards'   => 'info',
		'weigh'        => 'weigh',
		'care-list'    => 'care',
		'blog-teaser'  => 'teaser',
	);
	foreach ( $map as $cls => $style ) {
		if ( has_class( $list, $cls ) ) {
			return $style;
		}
	}
	if ( has_class( $list, 'ticks--cards' ) ) {
		return 'ticks-cards';
	}
	if ( has_class( $list, 'ticks--cols' ) ) {
		return 'ticks';
	}
	return '';
}

function conv_cards( DOMElement $sec, DOMElement $list, $seed ) {
	$style = card_style( $list );
	$wrap  = q( $sec, './div[' . c( 'wrap' ) . ']' );
	$s     = array_merge( section_settings( $sec ), head( $wrap ), array( 'style' => $style ) );
	$s['items'] = conv_items( $list, $style, $seed );
	if ( 'duo' === $style ) {
		$first           = q( $list, './*[1]/h2' );
		$s['title_tag']  = $first ? 'h2' : 'h3';
		$s['columns']    = has_class( $list, 'duo--3' ) ? '3' : '';
		if ( 'stacked' === $s['head_layout'] && $first && $s['heading'] === inner( $first ) ) {
			$s['heading'] = '';
		}
	}
	if ( 'info' === $style && preg_match( '#info-cards--(\d)#', $list->getAttribute( 'class' ), $m ) ) {
		$s['columns'] = $m[1];
	}
	// An image beside the heading (treatment explanations).
	if ( $fig = q( $wrap, './/figure[' . c( 'frame' ) . ']' ) ) {
		$s = array_merge( $s, array( 'side_show' => 'yes' ), image( $fig, 'side' ) );
	}
	// A small note after the cards (new patients: "More questions?").
	return widget( 'scd-cards', $s, $seed );
}

function conv_services_grid( DOMElement $sec, $seed ) {
	$s          = array_merge( section_settings( $sec ), head( q( $sec, './div' ) ), array( 'source' => 'manual', 'chips' => 'yes' ) );
	$s['chips_all']  = text( q( $sec, './/button[@data-filter="all"]' ) );
	$s['more_label'] = 'Learn more';
	$s['items']      = array();
	foreach ( qa( $sec, './/ul[' . c( 'svc-grid' ) . ']/li' ) as $i => $li ) {
		$a            = q( $li, './a' );
		$s['items'][] = rep(
			array(
				'image'    => media_value( q( $li, './/img' ) ),
				'category' => $li->getAttribute( 'data-cat' ),
				'title'    => inner( q( $a, './/strong' ) ),
				'text'     => inner( q( $a, './/span[' . c( 'svc-card__desc' ) . ']' ) ),
				'link'     => link_value( $a->getAttribute( 'href' ) ),
				'more'     => 'Learn more' === text( q( $a, './/span[' . c( 'svc-card__more' ) . ']' ) ) ? '' : text( q( $a, './/span[' . c( 'svc-card__more' ) . ']' ) ),
				'urgent'   => has_class( $li, 'svc-card--urgent' ) ? 'yes' : '',
			),
			$seed . $i
		);
	}
	return widget( 'scd-services-grid', $s, $seed );
}

function conv_team( DOMElement $sec, $seed ) {
	global $P;
	$wrap    = q( $sec, './div' );
	$support = q( $sec, './/ul[' . c( 'team--support' ) . ']' );
	$s       = array_merge( section_settings( $sec ), head( $wrap ), array( 'style' => $support ? 'support' : 'cards', 'people' => array() ) );
	foreach ( qa( $sec, $support ? './/ul[' . c( 'team--support' ) . ']/li' : './/ul[' . c( 'team-cards' ) . ']/li' ) as $i => $li ) {
		$a     = q( $li, './a' );
		$img   = q( $li, './/img' );
		$ph    = q( $li, './/figure[' . c( 'portrait--placeholder' ) . ']' );
		$roles = qa( $li, './/p' );
		$s['people'][] = rep(
			array(
				'image'  => $ph ? array( 'url' => '', 'id' => '' ) : media_value( $img ),
				'pos'    => '50% 22%',
				'name'   => text( q( $li, './/h3' ) ),
				'role'   => text( $roles[0] ?? null ),
				'quals'  => text( q( $li, './/p[' . c( 'member__quals' ) . ']' ) ),
				'link'   => $a ? link_value( $a->getAttribute( 'href' ) ) : array( 'url' => '' ),
				'anchor' => $li->getAttribute( 'id' ),
			),
			$seed . $i
		);
	}
	return widget( 'scd-team', $s, $seed );
}

function conv_profiles( DOMElement $sec, $seed ) {
	$s = array_merge( section_settings( $sec ), array( 'profiles' => array() ) );
	foreach ( qa( $sec, './/article[' . c( 'profile' ) . ']' ) as $i => $art ) {
		$h     = q( $art, './/h2' );
		$aka   = q( $h, './span' );
		$tags  = array();
		foreach ( qa( $art, './/ul[' . c( 'tags' ) . ']/li' ) as $li ) {
			$tags[] = text( $li );
		}
		$quote = q( $art, './/blockquote/p' );
		$s['profiles'][] = rep(
			array(
				'image'    => q( $art, './/figure[' . c( 'portrait--placeholder' ) . ']' ) ? array( 'url' => '', 'id' => '' ) : media_value( q( $art, './/img' ) ),
				'role'     => text( q( $art, './/p[' . c( 'profile__role' ) . ']' ) ),
				'name'     => trim( str_replace( text( $aka ), '', text( $h ) ) ),
				'known_as' => $aka ? trim( preg_replace( '#^\(known as (.*)\)$#', '$1', text( $aka ) ) ) : '',
				'quals'    => text( q( $art, './/p[' . c( 'profile__quals' ) . ']' ) ),
				'tags'     => implode( "\n", $tags ),
				'bio'      => inner( q( $art, './/div[' . c( 'prose' ) . ']' ) ),
				'quote'    => $quote ? trim( text( $quote ), "\u{201C}\u{201D} " ) : '',
				'anchor'   => $art->getAttribute( 'id' ),
			),
			$seed . $i
		);
	}
	return widget( 'scd-profiles', $s, $seed );
}

function faq_items( $faq, $seed ) {
	$items = array();
	foreach ( qa( $faq, './details' ) as $i => $d ) {
		$body    = q( $d, './/div[' . c( 'faq__body' ) . ']' );
		$answer  = q( $body, './/table[' . c( 'hours' ) . ']' ) ? '[scd_hours_table]' : inner( $body );
		$items[] = rep( array( 'question' => inner( q( $d, './summary' ) ), 'answer' => $answer ), $seed . 'q' . $i );
	}
	return $items;
}

function conv_faq( DOMElement $sec, $seed ) {
	$wrap = q( $sec, './div' );
	$faq  = q( $sec, './/div[' . c( 'faq' ) . ']' );
	$s    = section_settings( $sec );
	if ( has_class( $wrap, 'home-faq' ) ) {
		$intro = q( $wrap, './div[' . c( 'home-faq__intro' ) . ']' );
		$s     = array_merge(
			$s,
			array(
				'head_layout' => 'split-4-8',
				'heading'     => inner( q( $intro, './h2' ) ),
				'intro'       => inner( q( $intro, './p' ) ),
				'faq_style'   => 'cards',
			),
			button( q( $intro, './a' ), 'head_btn1' )
		);
		if ( $fig = q( $intro, './figure' ) ) {
			$s = array_merge( $s, array( 'side_show' => 'yes' ), image( $fig, 'side' ) );
		}
	} elseif ( q( $wrap, './/div[' . c( 'cost-card' ) . ']' ) || ( has_class( $wrap, 'split--7-5' ) && q( $wrap, './div[1]/div[' . c( 'faq' ) . ']' ) ) ) {
		$left = q( $wrap, './div[1]' );
		$s    = array_merge( $s, array( 'head_layout' => 'split-7-5', 'heading' => inner( q( $left, './h2' ) ), 'intro' => '', 'faq_style' => 'lines' ) );
		if ( $card = q( $wrap, './/div[' . c( 'cost-card' ) . ']' ) ) {
			$html = '';
			foreach ( qa( $card, './p' ) as $p ) {
				$html .= $sec->ownerDocument->saveHTML( $p );
			}
			$s = array_merge( $s, array( 'card_show' => 'yes', 'card_icon' => icon_name( q( $card, './*[local-name()="svg"]' ) ), 'card_title' => text( q( $card, './h3' ) ), 'card_text' => tidy_html( $html ) ) );
		}
	} else {
		$s = array_merge( $s, head( $wrap ), array( 'faq_style' => has_class( $faq, 'faq--cards' ) ? 'cards' : 'lines' ) );
	}
	$s['items'] = $faq ? faq_items( $faq, $seed ) : array();
	if ( $note = q( $sec, './div[' . c( 'mt-7' ) . ']' ) ) {
		$s['foot'] = inner( $note );
	}
	return widget( 'scd-faq', $s, $seed );
}

function conv_faq_page( DOMElement $sec, $seed ) {
	$items = array();
	$navs  = array();
	foreach ( qa( $sec, './/nav[' . c( 'faq-nav' ) . ']//a' ) as $a ) {
		$navs[ ltrim( $a->getAttribute( 'href' ), '#' ) ] = text( $a );
	}
	foreach ( qa( $sec, './/div[' . c( 'faq-group' ) . ']' ) as $g => $group ) {
		$h      = q( $group, './h2' );
		$anchor = $group->getAttribute( 'id' );
		foreach ( faq_items( q( $group, './div[' . c( 'faq' ) . ']' ), $seed . $g ) as $i => $qa ) {
			$items[] = rep(
				array(
					'topic'    => text( $h ),
					'nav'      => ( 0 === $i && isset( $navs[ $anchor ] ) && $navs[ $anchor ] !== text( $h ) ) ? $navs[ $anchor ] : '',
					'anchor'   => 0 === $i ? $anchor : '',
					'icon'     => icon_name( q( $h, './*[local-name()="svg"]' ) ),
					'question' => $qa['question'],
					'answer'   => $qa['answer'],
				),
				$seed . $g . 'i' . $i
			);
		}
	}
	$src = q( $sec, './/p[' . c( 'source' ) . ']' );
	return widget( 'scd-faq-page', array_merge( section_settings( $sec ), array( 'nav_label' => q( $sec, './/nav' )->getAttribute( 'aria-label' ), 'items' => $items, 'source' => $src ? '<p>' . inner( $src ) . '</p>' : '' ) ), $seed );
}

function conv_emergency( DOMElement $sec, $seed ) {
	$band  = q( $sec, './/div[' . c( 'er-band' ) . ']' );
	$lines = array();
	foreach ( qa( $band, './/ul/li' ) as $li ) {
		$lines[] = inner( $li );
	}
	$call = q( $band, './/div[' . c( 'er-band__call' ) . ']' );
	$tl   = q( $call, './a[' . c( 'textlink' ) . ']' );
	return widget(
		'scd-emergency',
		array_merge(
			section_settings( $sec ),
			array(
				'heading'      => inner( q( $band, './/h2' ) ),
				'lead'         => inner( q( $band, './/p[' . c( 'lead' ) . ']' ) ),
				'list'         => implode( "\n", $lines ),
				'list_label'   => q( $band, './/ul' )->getAttribute( 'aria-label' ),
				'call_heading' => text( q( $call, './p[' . c( 'er-call__label' ) . ']' ) ),
				'call_label'   => text( q( $call, './a[' . c( 'btn' ) . ']' ) ),
				'link_label'   => text( $tl ),
				'link'         => link_value( $tl->getAttribute( 'href' ) ),
				'note'         => inner( q( $call, './p[' . c( 'small' ) . ']' ) ),
			)
		),
		$seed
	);
}

function conv_cta( DOMElement $sec, $seed ) {
	$panel = q( $sec, './/div[' . c( 'cta-band' ) . ' or ' . c( 'urgent-strip' ) . ']' );
	$div   = q( $panel, './div[1]' );
	$as    = qa( $panel, './div[' . c( 'btn-row' ) . ']/a' );
	$ask   = q( $div, './a[' . c( 'textlink' ) . ']' );
	return widget(
		'scd-cta',
		array_merge(
			section_settings( $sec ),
			array(
				'style'     => has_class( $panel, 'urgent-strip' ) ? 'urgent' : 'band',
				'heading'   => inner( q( $div, './h2' ) ),
				'text'      => inner( q( $div, './p' ) ),
				'ask_label' => text( $ask ),
				'ask_url'   => $ask ? link_value( $ask->getAttribute( 'href' ) ) : array( 'url' => '' ),
			),
			button( $as[0] ?? null, 'btn1' ),
			button( $as[1] ?? null, 'btn2' )
		),
		$seed
	);
}

function conv_text( DOMElement $sec, $seed ) {
	$wrap = q( $sec, './div' );
	$s    = section_settings( $sec );
	if ( $aside = q( $wrap, './/div[' . c( 'svc-aside' ) . ']' ) ) {
		$prose = q( $wrap, './div[' . c( 'prose' ) . ']' );
		$lines = array();
		foreach ( qa( $aside, './/li' ) as $li ) {
			$lines[] = inner( $li );
		}
		$body = '';
		foreach ( qa( $prose, './*[not(self::h2)]' ) as $el ) {
			$body .= $sec->ownerDocument->saveHTML( $el );
		}
		$s = array_merge( $s, array( 'layout' => 'aside', 'heading' => inner( q( $prose, './h2' ) ), 'body' => tidy_html( $body ), 'aside_title' => inner( q( $aside, './h3' ) ), 'aside_items' => implode( "\n", $lines ) ) );
	} elseif ( has_class( $wrap, 'wrap-text' ) ) {
		$s = array_merge( $s, array( 'layout' => 'narrow', 'heading' => '', 'body' => inner( $wrap ) ) );
	} else {
		$s = array_merge( $s, array( 'layout' => 'split', 'heading' => inner( q( $wrap, './h2' ) ), 'body' => inner( q( $wrap, './div[' . c( 'prose' ) . ']' ) ) ) );
	}
	return widget( 'scd-text', $s, $seed );
}

function conv_offer( DOMElement $sec, $seed ) {
	$o      = q( $sec, './/div[' . c( 'offer' ) . ']' );
	$lines  = array();
	foreach ( qa( $o, './/ul[' . c( 'offer__list' ) . ']/li' ) as $li ) {
		$lines[] = inner( $li );
	}
	$prices = qa( $o, './/div[' . c( 'offer__prices' ) . ']/div' );
	$as     = qa( $o, './/div[' . c( 'btn-row' ) . ']/a' );
	return widget(
		'scd-offer',
		array_merge(
			section_settings( $sec ),
			array(
				'kicker'      => text( q( $o, './/p[' . c( 'offer__kicker' ) . ']' ) ),
				'title'       => inner( q( $o, './/h2' ) ),
				'items'       => implode( "\n", $lines ),
				'price1'      => text( q( $prices[0], './strong' ) ),
				'price1_note' => inner( q( $prices[0], './span' ) ),
				'price2'      => text( q( $prices[1], './strong' ) ),
				'price2_note' => inner( q( $prices[1], './span' ) ),
				'terms'       => inner( q( $o, './/p[' . c( 'offer__terms' ) . ']' ) ),
			),
			button( $as[0] ?? null, 'btn1' ),
			button( $as[1] ?? null, 'btn2' ),
			image( q( $o, './/div[' . c( 'offer__media' ) . ']/figure' ), 'image' )
		),
		$seed
	);
}

function conv_directory( DOMElement $sec, $seed ) {
	$s = array_merge( section_settings( $sec ), head( q( $sec, './div' ), 'hidden' ), array( 'style' => 'directory', 'groups' => array() ) );
	if ( qa( $sec, './/div[' . c( 'svc-section' ) . ']' ) ) {
		$keys       = array( 'Everyday care' => 'everyday', 'Repairing teeth' => 'repair', 'Replacing teeth' => 'replace', 'Changing your smile' => 'smile' );
		$s['style'] = 'lists';
		$s['head_layout'] = 'hidden';
		$s['heading']     = 'All services';
		foreach ( qa( $sec, './/div[' . c( 'svc-section' ) . ']' ) as $i => $grp ) {
			$h             = text( q( $grp, './div/h2' ) );
			$s['groups'][] = rep( array( 'group' => $keys[ $h ] ?? 'everyday', 'heading' => $h, 'text' => inner( q( $grp, './div/p' ) ) ), $seed . $i );
		}
	}
	return widget( 'scd-service-directory', $s, $seed );
}

function conv_related( DOMElement $sec, $seed ) {
	$s          = array_merge( section_settings( $sec ), head( q( $sec, './div' ) ) );
	$s['pages'] = array();
	foreach ( qa( $sec, './/ul[' . c( 'related-cards' ) . ']//a' ) as $i => $a ) {
		$slug = preg_replace( '#\.html$#', '', $a->getAttribute( 'href' ) );
		if ( in_array( $slug, $GLOBALS['service_slugs'], true ) ) {
			$s['pages'][] = rep( array( 'page' => 'scd-pageid:' . $slug ), $seed . $i );
		} else {
			// Not a treatment page (e.g. a campaign landing page): a custom card.
			$s['pages'][] = rep(
				array(
					'page'    => '',
					'c_title' => inner( q( $a, './/strong' ) ),
					'c_text'  => inner( q( $a, './/span[' . c( 'related-card__body' ) . ']/span' ) ),
					'c_image' => media_value( q( $a, './/img' ) ),
					'c_link'  => link_value( $a->getAttribute( 'href' ) ),
				),
				$seed . $i
			);
		}
	}
	return widget( 'scd-related', $s, $seed );
}

function conv_contact( DOMElement $sec, $seed ) {
	$panel  = q( $sec, './/div[@id="message-form"]' );
	$topics = array();
	foreach ( qa( $panel, './/option' ) as $o ) {
		$topics[] = text( $o );
	}
	$parking = '';
	foreach ( qa( $sec, './/ul[' . c( 'contact-list' ) . ']/li' ) as $li ) {
		if ( 'Parking' === text( q( $li, './span[' . c( 'label' ) . ']' ) ) ) {
			$parking = inner( q( $li, './span[' . c( 'value' ) . ']' ) );
		}
	}
	$h2s = qa( $sec, './/div[1]/h2' );
	return widget(
		'scd-contact',
		array_merge(
			section_settings( $sec ),
			array(
				'heading'       => text( $h2s[0] ),
				'book_text'     => text( q( $sec, './/ul[' . c( 'contact-list' ) . ']/li[3]/a' ) ),
				'parking'       => $parking,
				'hours_heading' => text( $h2s[1] ),
				'hours_note'    => text( q( $sec, './/p[' . c( 'small' ) . ' and ' . c( 'mt-5' ) . ']' ) ),
				'form_show'     => 'yes',
				'form_title'    => text( q( $panel, './h2' ) ),
				'form_intro'    => inner( q( $panel, './p[' . c( 'muted' ) . ']' ) ),
				'topics'        => implode( "\n", $topics ),
				'message_hint'  => text( q( $panel, './/label[contains(@for,"message")]/span' ) ),
				'submit'        => text( q( $panel, './/button' ) ),
			)
		),
		$seed
	);
}

function conv_map( DOMElement $sec, $seed ) {
	return widget( 'scd-map', array_merge( section_settings( $sec ), head( q( $sec, './div' ) ) ), $seed );
}

/* ---------------------------------------------------------------------
 * Flexible section: any section as typed rows (group start/end + content)
 * ------------------------------------------------------------------- */

/** Is the element only inline content (text, links that aren't buttons, emphasis)? */
function inline_only( DOMElement $el ) {
	foreach ( $el->childNodes as $c ) {
		if ( ! $c instanceof DOMElement ) {
			continue;
		}
		if ( ! in_array( $c->nodeName, array( 'a', 'strong', 'b', 'em', 'i', 'br', 'span', 'abbr', 'sup' ), true ) ) {
			return false;
		}
		if ( 'a' === $c->nodeName && has_class( $c, 'btn' ) ) {
			return false;
		}
		if ( 'span' === $c->nodeName && ( has_class( $c, 'icon-badge' ) || has_class( $c, 'status-dot' ) || q( $c, './/*[local-name()="svg"]' ) ) ) {
			return false;
		}
	}
	return true;
}

/** Common row attributes: classes, inline style, id. */
function row_attrs( DOMElement $el ) {
	return array(
		'cls'   => trim( preg_replace( '/\s+/', ' ', $el->getAttribute( 'class' ) ) ),
		'style' => $el->getAttribute( 'style' ),
		'el_id' => $el->getAttribute( 'id' ),
	);
}

/** A button or link (<a>) → flexible row button fields. */
function flex_button( DOMElement $a, $p ) {
	global $BOOKING;
	$href = $a->getAttribute( 'href' );
	$type = $href === $BOOKING ? 'book' : ( 0 === strpos( $href, 'tel:' ) ? 'call' : ( 0 === strpos( $href, 'mailto:' ) && false === strpos( $href, '?' ) ? 'email' : 'custom' ) );
	$svg  = (bool) q( $a, './/*[local-name()="svg"]' );
	$out  = array(
		$p . '_type'  => $type,
		$p . '_label' => phone_token( text( $a ) ),
		$p . '_cls'   => trim( preg_replace( '/\s+/', ' ', $a->getAttribute( 'class' ) ) ),
		$p . '_icon'  => $svg ? ( 'email' === $type ? 'mail' : 'auto' ) : '',
	);
	if ( 'custom' === $type ) {
		$out[ $p . '_url' ] = link_value( $href );
	}
	return $out;
}

/** A list item → one typed row, or null when it needs to be a group. */
function flex_item( DOMElement $li ) {
	$kids = array();
	foreach ( $li->childNodes as $c ) {
		if ( $c instanceof DOMElement ) {
			$kids[] = $c;
		}
	}
	$names = implode( ',', array_map( function ( $k ) { return $k->nodeName . ( has_class( $k, 'icon-badge' ) ? '.badge' : ( has_class( $k, 'icon-wrap' ) ? '.wrap' : '' ) ); }, $kids ) );
	$row   = array_merge( array( 'type' => 'item' ), row_attrs( $li ) );
	if ( has_class( $li, 'lp-dentist' ) ) {
		$img = q( $li, './/img' );
		$ps  = qa( $li, './div/p' );
		return array_merge( $row, array( 'item_style' => 'person', 'media' => media_value( $img ), 'alt' => $img ? $img->getAttribute( 'alt' ) : '', 'title' => inner( q( $li, './/h3' ) ), 'text' => inner( $ps[0] ?? null ), 'extra' => inner( q( $li, './/p[' . c( 'member__quals' ) . ']' ) ) ) );
	}
	if ( 'span.wrap,div' === $names ) {
		return array_merge( $row, array( 'item_style' => 'comfort', 'icon' => icon_name( q( $kids[0], './*' ) ), 'title' => inner( q( $kids[1], './strong' ) ), 'text' => inner( q( $kids[1], './span' ) ) ) );
	}
	if ( 'span.badge,span' === $names ) {
		$strong = q( $kids[1], './strong' );
		$text   = '';
		foreach ( $kids[1]->childNodes as $c ) {
			if ( $c !== $strong ) {
				$text .= $li->ownerDocument->saveHTML( $c );
			}
		}
		return array_merge( $row, array( 'item_style' => 'trust', 'icon' => icon_name( q( $kids[0], './*' ) ), 'title' => inner( $strong ), 'text' => tidy_html( $text ) ) );
	}
	if ( 'span.badge,strong,p' === $names ) {
		return array_merge( $row, array( 'item_style' => 'match', 'icon' => icon_name( q( $kids[0], './*' ) ), 'title' => inner( $kids[1] ), 'text' => inner( $kids[2] ) ) );
	}
	if ( 'strong,span' === $names ) {
		return array_merge( $row, array( 'item_style' => 'mention', 'title' => inner( $kids[0] ), 'text' => inner( $kids[1] ) ) );
	}
	if ( preg_match( '#^h3,p(,span)?$#', $names ) ) {
		return array_merge( $row, array( 'item_style' => 'step', 'title' => inner( $kids[0] ), 'text' => inner( $kids[1] ), 'extra' => isset( $kids[2] ) ? inner( $kids[2] ) : '' ) );
	}
	if ( 'div' === $names && preg_match( '#^h3,p$#', implode( ',', array_map( function ( $k ) { return $k->nodeName; }, qa( $kids[0], './*' ) ) ) ) ) {
		return array_merge( $row, array( 'item_style' => 'step-div', 'title' => inner( q( $kids[0], './h3' ) ), 'text' => inner( q( $kids[0], './p' ) ) ) );
	}
	if ( inline_only( $li ) ) {
		return array_merge( $row, array( 'item_style' => 'li', 'text' => inner( $li ) ) );
	}
	return null;
}

/** Walk an element's children into flexible rows. */
function flex_rows( DOMElement $node, array &$rows ) {
	$children = array();
	foreach ( $node->childNodes as $c ) {
		if ( $c instanceof DOMElement ) {
			$children[] = $c;
		} elseif ( $c instanceof DOMText && '' !== trim( $c->textContent ) ) {
			$rows[] = array( 'type' => 'text', 'text_tag' => 'span', 'text' => trim( $c->textContent ) );
		}
	}
	for ( $i = 0; $i < count( $children ); $i++ ) {
		$el  = $children[ $i ];
		$tag = $el->nodeName;
		if ( in_array( $tag, array( 'h1', 'h2', 'h3', 'h4' ), true ) ) {
			$rows[] = array_merge( array( 'type' => 'heading', 'level' => $tag, 'text' => inner( $el ) ), row_attrs( $el ) );
		} elseif ( 'p' === $tag && q( $el, './/*[@data-hours-status]' ) ) {
			$rows[] = array_merge( array( 'type' => 'status' ), row_attrs( $el ) );
		} elseif ( in_array( $tag, array( 'p', 'strong', 'span' ), true ) && ! has_class( $el, 'icon-badge' ) && inline_only( $el ) ) {
			$rows[] = array_merge( array( 'type' => 'text', 'text_tag' => $tag, 'text' => phone_token( inner( $el ) ) ), row_attrs( $el ) );
		} elseif ( 'span' === $tag && has_class( $el, 'icon-badge' ) ) {
			$rows[] = array_merge( array( 'type' => 'icon', 'icon' => icon_name( q( $el, './*' ) ) ), row_attrs( $el ) );
		} elseif ( 'svg' === $tag ) {
			$rows[] = array( 'type' => 'icon', 'icon' => icon_name( $el ), 'cls' => '' );
		} elseif ( 'figure' === $tag ) {
			$img    = q( $el, './/img' );
			$rows[] = array_merge( array( 'type' => 'image', 'media' => media_value( $img ), 'alt' => $img ? $img->getAttribute( 'alt' ) : '', 'eager' => ( $img && $img->hasAttribute( 'fetchpriority' ) ) ? 'yes' : '' ), row_attrs( $el ) );
		} elseif ( 'div' === $tag && has_class( $el, 'btn-row' ) ) {
			$as  = qa( $el, './a' );
			$row = array_merge(
				array( 'type' => 'buttons', 'row' => 'yes', 'sticky' => $el->hasAttribute( 'data-sticky-anchor' ) ? 'yes' : '' ),
				row_attrs( $el ),
				flex_button( $as[0], 'b1' ),
				isset( $as[1] ) ? flex_button( $as[1], 'b2' ) : array( 'b2_type' => 'none' )
			);
			$row['cls'] = trim( preg_replace( '/(^| )btn-row( |$)/', ' ', $row['cls'] ) );
			$rows[]     = $row;
		} elseif ( 'a' === $tag ) {
			$next   = $children[ $i + 1 ] ?? null;
			$pair   = $next && 'a' === $next->nodeName;
			$rows[] = array_merge( array( 'type' => 'buttons', 'row' => '', 'cls' => '' ), flex_button( $el, 'b1' ), $pair ? flex_button( $next, 'b2' ) : array( 'b2_type' => 'none' ) );
			if ( $pair ) {
				++$i;
			}
		} elseif ( 'details' === $tag ) {
			$rows[] = array_merge( array( 'type' => 'item', 'item_style' => 'faq', 'title' => inner( q( $el, './summary' ) ), 'answer' => inner( q( $el, './/div[' . c( 'faq__body' ) . ']' ) ) ), row_attrs( $el ) );
		} elseif ( 'div' === $tag && 'dl' === $node->nodeName ) {
			$dt     = q( $el, './dt' );
			$rows[] = array_merge( array( 'type' => 'item', 'item_style' => has_class( $dt, 'label' ) ? 'contact' : 'fact', 'title' => inner( $dt ), 'text' => phone_token( inner( q( $el, './dd' ) ) ) ), row_attrs( $el ) );
		} elseif ( 'li' === $tag && ( $item = flex_item( $el ) ) ) {
			$rows[] = $item;
		} else {
			$open = array_merge(
				array(
					'type'            => 'open',
					'tag'             => in_array( $tag, array( 'div', 'ul', 'ol', 'dl', 'aside', 'nav', 'p', 'figure', 'article', 'span', 'li' ), true ) ? $tag : 'div',
					'aria_label'      => $el->getAttribute( 'aria-label' ),
					'aria_labelledby' => $el->getAttribute( 'aria-labelledby' ),
					'sticky'          => $el->hasAttribute( 'data-sticky-anchor' ) ? 'yes' : '',
				),
				row_attrs( $el )
			);
			if ( 'li' === $tag ) {
				fwrite( STDERR, "Note: generic <li> kept as a group: " . substr( $el->ownerDocument->saveHTML( $el ), 0, 120 ) . "\n" );
			}
			$rows[] = $open;
			flex_rows( $el, $rows );
			$rows[] = array( 'type' => 'close' );
		}
	}
}

/** Any section → flexible section widget. */
function conv_flex( DOMElement $sec, $seed ) {
	$rows = array();
	flex_rows( $sec, $rows );
	foreach ( $rows as $i => $row ) {
		$rows[ $i ]['_id'] = eid( $seed . 'row' . $i );
	}
	return widget(
		'scd-section',
		array(
			'sec_tag'        => $sec->nodeName,
			'sec_cls'        => trim( preg_replace( '/\s+/', ' ', $sec->getAttribute( 'class' ) ) ),
			'sec_anchor'     => $sec->getAttribute( 'id' ),
			'sec_aria_label' => $sec->getAttribute( 'aria-label' ),
			'sec_labelledby' => $sec->getAttribute( 'aria-labelledby' ),
			'sec_bg'         => '',
			'sec_size'       => '',
			'rows'           => $rows,
		),
		$seed
	);
}

/** Convert one <section> (or helper div) of <main>. */
function convert_section( DOMElement $sec, $seed, &$flags ) {
	// Campaign landing pages: every section is a flexible section, except the FAQ.
	if ( ! empty( $flags['landing'] ) ) {
		if ( q( $sec, './/div[' . c( 'home-faq' ) . ']' ) ) {
			return conv_faq( $sec, $seed );
		}
		return conv_flex( $sec, $seed );
	}
	// Redesigned treatment-page sections and bands the other widgets don't reproduce exactly.
	$id = $sec->getAttribute( 'id' );
	if ( has_class( $sec, 'promo-band' ) || ( has_class( $sec, 'callout' ) && ! q( $sec, './/h2' ) ) ) {
		return conv_flex( $sec, $seed );
	}
	if ( in_array( $id, array( 'care', 'cost' ), true ) && ! empty( $flags['service'] ) ) {
		return conv_flex( $sec, $seed );
	}
	if ( 'intro' === $id && ! empty( $flags['service'] ) && ( q( $sec, './/div[' . c( 'comfort' ) . ']' ) || q( $sec, './/div[' . c( 'panel--corner' ) . ']' ) ) ) {
		return conv_flex( $sec, $seed );
	}
	if ( 'div' === $sec->nodeName ) {
		if ( has_class( $sec, 'er-sticky' ) ) {
			$flags['sticky'] = true;
		}
		return null; // .mbar is part of the home hero widget.
	}
	if ( has_class( $sec, 'hero-cc' ) ) {
		return conv_home_hero( $sec, $seed );
	}
	if ( has_class( $sec, 'infostrip' ) ) {
		return conv_info_strip( $sec, $seed );
	}
	if ( has_class( $sec, 'svc-hero' ) ) {
		return conv_service_hero( $sec, $seed, ! empty( $flags['sticky'] ) );
	}
	if ( has_class( $sec, 'phero' ) ) {
		return conv_page_hero( $sec, $seed );
	}
	if ( has_class( $sec, 'callout' ) ) {
		return conv_callout( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'er-band' ) . ']' ) ) {
		return conv_emergency( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'cta-band' ) . ' or ' . c( 'urgent-strip' ) . ']' ) ) {
		return conv_cta( $sec, $seed );
	}
	if ( q( $sec, './div[' . c( 'editorial' ) . ' or ' . c( 'comfort' ) . ']' ) ) {
		return conv_media_text( $sec, $seed );
	}
	if ( q( $sec, './/ul[' . c( 'svc-grid' ) . ']' ) ) {
		return conv_services_grid( $sec, $seed );
	}
	if ( q( $sec, './/ul[' . c( 'team-cards' ) . ' or ' . c( 'team--support' ) . ']' ) ) {
		return conv_team( $sec, $seed );
	}
	if ( q( $sec, './/article[' . c( 'profile' ) . ']' ) ) {
		return conv_profiles( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'faq-layout' ) . ']' ) ) {
		return conv_faq_page( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'offer' ) . ']' ) ) {
		return conv_offer( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'directory' ) . ' or ' . c( 'svc-section' ) . ']' ) ) {
		return conv_directory( $sec, $seed );
	}
	if ( q( $sec, './/ul[' . c( 'related-cards' ) . ']' ) ) {
		return conv_related( $sec, $seed );
	}
	if ( q( $sec, './/ul[' . c( 'contact-list' ) . ']' ) ) {
		return conv_contact( $sec, $seed );
	}
	if ( q( $sec, './/figure[' . c( 'mapcard' ) . ']' ) ) {
		return conv_map( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'faq' ) . ' or ' . c( 'cost-card' ) . ']' ) ) {
		return conv_faq( $sec, $seed );
	}
	if ( q( $sec, './/div[' . c( 'svc-aside' ) . ']' ) || ( 'intro' === $sec->getAttribute( 'id' ) ) || q( $sec, './div[' . c( 'wrap-text' ) . ']' ) ) {
		return conv_text( $sec, $seed );
	}
	foreach ( qa( $sec, './/*[' . c( 'concierge' ) . ' or ' . c( 'features' ) . ' or ' . c( 'pay' ) . ' or ' . c( 'ways' ) . ' or ' . c( 'mention' ) . ' or ' . c( 'duo' ) . ' or ' . c( 'nextlinks' ) . ' or ' . c( 'journey' ) . ' or ' . c( 'process' ) . ' or ' . c( 'steps' ) . ' or ' . c( 'factrow' ) . ' or ' . c( 'ticks--cols' ) . ' or ' . c( 'info-cards' ) . ' or ' . c( 'weigh' ) . ' or ' . c( 'care-list' ) . ' or ' . c( 'blog-teaser' ) . ']' ) as $list ) {
		return conv_cards( $sec, $list, $seed );
	}
	// An explanation section with only a heading and an image (e.g. dental implants).
	if ( q( $sec, './/figure[' . c( 'frame' ) . ']' ) && q( $sec, './/h2' ) ) {
		$wrap = q( $sec, './div' );
		$div  = q( $wrap, './div[1]' );
		return widget(
			'scd-cards',
			array_merge(
				section_settings( $sec ),
				array( 'style' => 'info', 'head_layout' => 'stacked', 'heading' => inner( q( $div, './h2' ) ), 'intro' => paras_text( qa( $div, './p' ) ), 'items' => array(), 'side_show' => 'yes' ),
				image( q( $wrap, './figure' ), 'side' )
			),
			$seed
		);
	}
	fwrite( STDERR, 'Flexible section used for: ' . $sec->getAttribute( 'class' ) . '#' . $sec->getAttribute( 'id' ) . "\n" );
	return conv_flex( $sec, $seed );
}

/** Fix static-site anchors: "#explain-h" (a heading ID) → "#explain" (the section anchor). */
function fix_anchors( $data ) {
	$json = json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	$json = preg_replace( '#(scd-page:[a-z0-9-]*\#|"\#)([a-z0-9-]+)-h"#', '$1$2"', $json );
	// The static team page's "Qualifications" card has a garbled, half-duplicated sentence
	// (_build/pages/team.html). Use the intended sentence.
	$json = preg_replace(
		'#Every practising dentist in Australia must be registered with the Dental Board of Australia\. You can check any dentist\\\\n\\\\nAll our dentists are registered with the Dental Board of Australia\. You can check any dentist’s registration on thersquo;s registration on the #u',
		'All our dentists are registered with the Dental Board of Australia. You can check any dentist’s registration on the ',
		$json
	);
	return json_decode( $json, true );
}

/** Convert a built page. */
function convert_page( $file, $slug, $extra = array() ) {
	$dom   = load_html( $file );
	$main  = q( $dom, '//main' );
	$flags = array_merge( array( 'sticky' => (bool) q( $main, './div[' . c( 'er-sticky' ) . ']' ) ), $extra );
	$conts = array();
	$els   = qa( $main, './*' );
	// Landing pages: the phone action bar sits after the footer in the static page.
	if ( ! empty( $flags['landing'] ) && ( $bar = q( $dom, '//body/div[' . c( 'mbar' ) . ']' ) ) ) {
		$els[] = $bar;
	}
	foreach ( $els as $i => $el ) {
		$w = ( ! empty( $flags['landing'] ) && has_class( $el, 'mbar' ) ) ? conv_flex( $el, $slug . $i ) : convert_section( $el, $slug . $i, $flags );
		if ( $w ) {
			$conts[] = container( $w, $slug . $i );
		}
	}
	$body = q( $dom, '//body' );
	$robots = q( $dom, '//meta[@name="robots"]' );
	$title = text( q( $dom, '//title' ) );
	$desc  = q( $dom, '//meta[@name="description"]' );
	return array(
		'elementor'       => fix_anchors( $conts ),
		'seo_title'       => $title,
		'seo_description' => $desc ? $desc->getAttribute( 'content' ) : '',
		'robots'          => $robots ? $robots->getAttribute( 'content' ) : '',
		'body_class'      => $body ? $body->getAttribute( 'class' ) : '',
	);
}

/* ---------------------------------------------------------------------
 * Pages
 * ------------------------------------------------------------------- */

// Treatment page slugs (related-treatment cards to other pages become custom cards).
$GLOBALS['service_slugs'] = array( 'emergency-dentist' );
foreach ( $P['service_groups'] as $g ) {
	foreach ( $g['items'] as $item ) {
		$GLOBALS['service_slugs'][] = preg_replace( '#\.html$#', '', $item['file'] );
	}
}

$pages = array();
$order = array(
	'index'          => array( 'Home', '' ),
	'about'          => array( 'About South Cairns Dental', 'about' ),
	'team'           => array( 'Meet our dentists', 'team' ),
	'new-patients'   => array( 'New patients', 'new-patients' ),
	'services'       => array( 'Services', 'services' ),
	'faq'            => array( 'Frequently asked questions', 'faq' ),
	'special-offers' => array( 'Special offers', 'special-offers' ),
	'contact'        => array( 'Contact us', 'contact' ),
	'privacy'        => array( 'Privacy policy', 'privacy' ),
);
foreach ( $order as $file => $info ) {
	$data    = convert_page( $root . '/' . $file . '.html', $file );
	$pages[] = array_merge(
		array(
			'slug'       => '' === $info[1] ? 'home' : $info[1],
			'title'      => $info[0],
			'front'      => 'index' === $file,
			'menu_order' => 0,
			'excerpt'    => $data['seo_description'],
			'service'    => null,
			'thumb'      => null,
		),
		$data
	);
}

// Treatment pages, in the order of the services menu.
$svc_order = array();
$n         = 0;
$cat       = array();
$thumbs    = array();
foreach ( $P['service_groups'] as $g ) {
	foreach ( $g['items'] as $item ) {
		$slug               = preg_replace( '#\.html$#', '', $item['file'] );
		$svc_order[ $slug ] = ++$n;
		$cat[ $slug ]       = $item;
	}
}
$cat['emergency-dentist'] = array(
	'category' => 'Urgent',
	'desc'     => 'Toothache, a broken tooth, swelling or lost dentures. We set aside appointments every day for urgent problems.',
	'blurb'    => 'Toothache, swelling or a broken tooth? We reserve appointments every day.',
	'photo'    => 'assets/images/treatments/toothache.jpg',
);
$svc_order['emergency-dentist'] = ++$n;
$group_keys = array( 'Everyday care' => 'everyday', 'Repairing teeth' => 'repair', 'Replacing teeth' => 'replace', 'Changing your smile' => 'smile', 'Urgent care' => 'urgent' );
foreach ( glob( $build . '/services/*.json' ) as $jf ) {
	$j       = json_decode( file_get_contents( $jf ), true );
	$slug    = basename( $jf, '.json' );
	$data    = convert_page( $root . '/' . $j['file'], $slug, array( 'service' => true ) );
	$info    = $cat[ $slug ];
	$pages[] = array_merge(
		array(
			'slug'       => $slug,
			'title'      => html_entity_decode( $j['name'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'front'      => false,
			'menu_order' => $svc_order[ $slug ],
			'excerpt'    => html_entity_decode( $info['desc'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
			'service'    => array(
				'group'    => $group_keys[ $j['group'] ],
				'category' => $info['category'],
				'short'    => html_entity_decode( $j['short'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'card'     => html_entity_decode( $info['desc'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'blurb'    => html_entity_decode( $info['blurb'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
				'tone'     => isset( $j['tone'] ) ? $j['tone'] : '',
			),
			'thumb'      => asset( $info['photo'] ),
		),
		$data
	);
}
// Campaign landing pages (Meta ads): their own pages, not treatments, never in menus or service lists.
$landing = array(
	'full-arch-dental-implants' => 'Full arch dental implants',
	'cosmetic-dentistry'        => 'Cosmetic dentistry',
);
foreach ( $landing as $slug => $title ) {
	$data = convert_page( $root . '/' . $slug . '.html', $slug, array( 'landing' => true ) );
	preg_match( '#page-lp--([a-z0-9-]+)#', $data['body_class'], $m );
	$hero    = load_html( $root . '/' . $slug . '.html' );
	$himg    = q( $hero, '//main//section[1]//img' );
	$pages[] = array_merge(
		array(
			'slug'       => $slug,
			'title'      => $title,
			'front'      => false,
			'menu_order' => 0,
			'excerpt'    => $data['seo_description'],
			'service'    => null,
			'landing'    => $m ? $m[1] : '',
			'thumb'      => $himg ? asset( $himg->getAttribute( 'src' ) ) : null,
		),
		$data
	);
}

usort(
	$pages,
	function ( $a, $b ) {
		return ( $a['service'] ? 1 : 0 ) <=> ( $b['service'] ? 1 : 0 ) ?: $a['menu_order'] <=> $b['menu_order'];
	}
);

/* ---------------------------------------------------------------------
 * Blog posts and the posts page
 * ------------------------------------------------------------------- */

$blog_dom = load_html( $root . '/blog.html' );
$posts    = array();
foreach ( $BLOG['posts'] as $p ) {
	if ( 'published' !== $p['status'] ) {
		continue; // Drafts are never imported.
	}
	$posts[] = array(
		'slug'            => $p['slug'],
		'title'           => html_entity_decode( $p['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'date'            => $p['date'],
		'category'        => $p['category'],
		'excerpt'         => html_entity_decode( $p['excerpt'], ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
		'content'         => $p['body'],
		'author'          => $p['author'] ?? 'South Cairns Dental',
		'note'            => $p['note'] ?? '',
		'thumb'           => asset( $IMAGES[ $p['image'] ]['src'] ),
		'related'         => array_map(
			function ( $f ) {
				return preg_replace( '#\.html$#', '', $f );
			},
			$p['related'] ?? array()
		),
		'seo_title'       => $p['title'] . ' | South Cairns Dental blog',
		'seo_description' => $p['description'],
	);
}
$blog_page = array(
	'slug'            => 'blog',
	'title'           => text( q( $blog_dom, '//main//h1' ) ),
	'nav_title'       => 'Blog',
	'excerpt'         => text( q( $blog_dom, '//main//p[' . c( 'lead' ) . ']' ) ),
	'content'         => '<p>' . inner( q( $blog_dom, '//main//p[' . c( 'phero__aside' ) . ']' ) ) . '</p>',
	'seo_title'       => text( q( $blog_dom, '//title' ) ),
	'seo_description' => q( $blog_dom, '//meta[@name="description"]' )->getAttribute( 'content' ),
);

/* ---------------------------------------------------------------------
 * Image alt text for the Media Library
 * ------------------------------------------------------------------- */

$alts = array();
foreach ( $IMAGES as $k => $im ) {
	if ( '_' === $k[0] || empty( $im['src'] ) ) {
		continue;
	}
	$rel = substr( $im['src'], strlen( 'assets/images/' ) );
	if ( 'illustration' !== $im['status'] && ! isset( $alts[ $rel ] ) ) {
		$alts[ $rel ] = html_entity_decode( $im['alt'], ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	}
}
foreach ( $P['team'] as $m ) {
	if ( $m['photo'] ) {
		$alts[ substr( $m['photo'], strlen( 'assets/images/' ) ) ] = $m['name'] . ', ' . $m['role'];
	}
}

$site = array(
	'_note'     => 'Generated by wordpress-theme/tools/build-content.php from the static site. Do not edit by hand.',
	'generated' => gmdate( 'c' ),
	'pages'     => $pages,
	'blog'      => $blog_page,
	'posts'     => $posts,
	'alts'      => $alts,
);
file_put_contents( $out, json_encode( $site, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . "\n" );
echo 'Wrote ' . count( $pages ) . ' pages and ' . count( $posts ) . ' post(s) to ' . $out . "\n";
