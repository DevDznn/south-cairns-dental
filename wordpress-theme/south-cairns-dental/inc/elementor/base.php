<?php
/**
 * Base class for the South Cairns Dental widgets: shared controls (section background and
 * spacing, section heading, buttons, images) and rendering helpers. Each widget renders one
 * section with the static site's markup, so the shared stylesheet applies unchanged.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Shared widget behaviour.
 */
abstract class Widget extends \Elementor\Widget_Base {

	/**
	 * Category.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( 'south-cairns-dental' );
	}

	/**
	 * Keywords.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'south cairns', 'scd', 'dental' );
	}

	/**
	 * One wrapper less (Elementor's optimised markup).
	 *
	 * @return bool
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Practice details and treatment pages can change outside the widget, so don't cache output.
	 *
	 * @return bool
	 */
	protected function is_dynamic_content(): bool {
		return true;
	}

	/* ---------------------------------------------------------------------
	 * Controls
	 * ------------------------------------------------------------------- */

	/**
	 * Section background, spacing and anchor (Style tab).
	 *
	 * @param array $defaults Defaults: bg, size.
	 */
	protected function section_controls( $defaults = array() ) {
		$defaults = wp_parse_args( $defaults, array( 'bg' => '', 'size' => '' ) );
		$this->start_controls_section( 'scd_section', array( 'label' => __( 'Section', 'south-cairns-dental' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control(
			'sec_bg',
			array(
				'label'   => __( 'Background', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['bg'],
				'options' => array(
					''       => __( 'White', 'south-cairns-dental' ),
					'mist'   => __( 'Mist (pale green)', 'south-cairns-dental' ),
					'lime'   => __( 'Pale lime', 'south-cairns-dental' ),
					'fade'   => __( 'White fading to mist', 'south-cairns-dental' ),
					'canopy' => __( 'Canopy (dark green)', 'south-cairns-dental' ),
					'forest' => __( 'Forest green', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control(
			'sec_size',
			array(
				'label'   => __( 'Spacing', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $defaults['size'],
				'options' => array(
					''      => __( 'Standard', 'south-cairns-dental' ),
					'tight' => __( 'Compact', 'south-cairns-dental' ),
					'flush' => __( 'No space above', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control(
			'sec_bg_color',
			array(
				'label'     => __( 'Custom background colour', 'south-cairns-dental' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} > section' => 'background: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'sec_padding',
			array(
				'label'      => __( 'Custom padding', 'south-cairns-dental' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem', 'em', 'vw' ),
				'selectors'  => array( '{{WRAPPER}} > section' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'sec_anchor',
			array(
				'label'       => __( 'Anchor ID', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXT,
				'description' => __( 'For links like #costs. Letters, numbers and hyphens.', 'south-cairns-dental' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Heading colour and typography (Style tab).
	 *
	 * @param string $selector Heading selector inside the widget.
	 */
	protected function heading_style_controls( $selector = '.scd-head-title' ) {
		$this->start_controls_section( 'scd_heading_style', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_control(
			'heading_color',
			array(
				'label'     => __( 'Colour', 'south-cairns-dental' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} ' . $selector => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'heading_typography', 'selector' => '{{WRAPPER}} ' . $selector ) );
		$this->end_controls_section();
	}

	/**
	 * Section heading controls (kicker, heading, introduction, buttons, layout).
	 *
	 * @param array $d Defaults and options: heading, intro, layout, layouts (allowed), tag.
	 */
	protected function head_controls( $d = array() ) {
		$d = wp_parse_args(
			$d,
			array(
				'heading' => '',
				'intro'   => '',
				'layout'  => 'stacked',
				'layouts' => array( 'stacked', 'row', 'split-5-7', 'split-4-8', 'hidden', 'bare' ),
				'buttons' => true,
			)
		);
		$labels = array(
			'stacked'   => __( 'Heading above', 'south-cairns-dental' ),
			'row'       => __( 'Heading above, button on the right', 'south-cairns-dental' ),
			'split-5-7' => __( 'Heading on the left (5/7 columns)', 'south-cairns-dental' ),
			'split-4-8' => __( 'Heading on the left (4/8 columns)', 'south-cairns-dental' ),
			'split-7-5' => __( 'Heading on the left (7/5 columns)', 'south-cairns-dental' ),
			'hidden'    => __( 'Heading for screen readers only', 'south-cairns-dental' ),
			'bare'      => __( 'Heading directly above, no introduction', 'south-cairns-dental' ),
		);
		$this->start_controls_section( 'scd_head', array( 'label' => __( 'Section heading', 'south-cairns-dental' ) ) );
		$this->add_control(
			'head_layout',
			array(
				'label'   => __( 'Layout', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['layout'],
				'options' => array_intersect_key( $labels, array_flip( $d['layouts'] ) ),
			)
		);
		$this->add_control( 'head_kicker', array( 'label' => __( 'Small line above the heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control(
			'heading',
			array(
				'label'       => __( 'Heading', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 2,
				'default'     => $d['heading'],
				'label_block' => true,
			)
		);
		$this->add_control(
			'heading_tag',
			array(
				'label'   => __( 'Heading level', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h1' => 'H1' ),
			)
		);
		$this->add_control(
			'intro',
			array(
				'label'       => __( 'Introduction', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'default'     => $d['intro'],
				'description' => __( 'Leave a blank line between paragraphs. Links and bold text can be added with HTML.', 'south-cairns-dental' ),
			)
		);
		$this->add_control(
			'intro_style',
			array(
				'label'   => __( 'Introduction text', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'narrow',
				'options' => array(
					'narrow' => __( 'Grey, comfortable line length', 'south-cairns-dental' ),
					'muted'  => __( 'Grey, full width', 'south-cairns-dental' ),
					'normal' => __( 'Body colour', 'south-cairns-dental' ),
				),
			)
		);
		if ( $d['buttons'] ) {
			$this->button_controls( 'head_btn1', __( 'Button', 'south-cairns-dental' ), array( 'type' => 'none', 'style' => 'ghost' ) );
			$this->button_controls( 'head_btn2', __( 'Second button', 'south-cairns-dental' ), array( 'type' => 'none', 'style' => 'ghost' ) );
		}
		$this->end_controls_section();
	}

	/**
	 * Button controls (inside an open controls section).
	 *
	 * @param string $p     Control prefix.
	 * @param string $label Heading label.
	 * @param array  $d     Defaults: type, label, style, size, icon.
	 */
	protected function button_controls( $p, $label, $d = array() ) {
		$d = wp_parse_args( $d, array( 'type' => 'none', 'label' => '', 'style' => 'lime', 'size' => '', 'icon' => 'auto' ) );
		$this->add_control( $p . '_heading', array( 'label' => $label, 'type' => Controls_Manager::HEADING, 'separator' => 'before' ) );
		$this->add_control(
			$p . '_type',
			array(
				'label'   => __( 'Link to', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['type'],
				'options' => array(
					'none'   => __( 'No button', 'south-cairns-dental' ),
					'book'   => __( 'Online booking (practice details)', 'south-cairns-dental' ),
					'call'   => __( 'Call the practice', 'south-cairns-dental' ),
					'email'  => __( 'Email the practice', 'south-cairns-dental' ),
					'custom' => __( 'Custom link', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control(
			$p . '_label',
			array(
				'label'       => __( 'Label', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['label'],
				'description' => __( '{phone} shows the practice phone number.', 'south-cairns-dental' ),
				'condition'   => array( $p . '_type!' => 'none' ),
			)
		);
		$this->add_control(
			$p . '_url',
			array(
				'label'     => __( 'Link', 'south-cairns-dental' ),
				'type'      => Controls_Manager::URL,
				'condition' => array( $p . '_type' => 'custom' ),
			)
		);
		$this->add_control(
			$p . '_style',
			array(
				'label'     => __( 'Style', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $d['style'],
				'options'   => array(
					'lime'        => __( 'Lime', 'south-cairns-dental' ),
					'forest'      => __( 'Forest green', 'south-cairns-dental' ),
					'ghost'       => __( 'Outline', 'south-cairns-dental' ),
					'ghost-light' => __( 'Outline (on dark)', 'south-cairns-dental' ),
					'alert'       => __( 'Red (urgent)', 'south-cairns-dental' ),
					'alert-ghost' => __( 'Red outline', 'south-cairns-dental' ),
					'textlink'    => __( 'Underlined text link', 'south-cairns-dental' ),
				),
				'condition' => array( $p . '_type!' => 'none' ),
			)
		);
		$this->add_control(
			$p . '_size',
			array(
				'label'     => __( 'Size', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $d['size'],
				'options'   => array(
					''   => __( 'Standard', 'south-cairns-dental' ),
					'sm' => __( 'Small', 'south-cairns-dental' ),
					'lg' => __( 'Large', 'south-cairns-dental' ),
				),
				'condition' => array( $p . '_type!' => 'none', $p . '_style!' => 'textlink' ),
			)
		);
		$this->add_control(
			$p . '_icon',
			array(
				'label'     => __( 'Icon', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => $d['icon'],
				'options'   => array(
					'auto'     => __( 'Automatic', 'south-cairns-dental' ),
					''         => __( 'None', 'south-cairns-dental' ),
					'calendar' => __( 'Calendar', 'south-cairns-dental' ),
					'phone'    => __( 'Phone', 'south-cairns-dental' ),
					'mail'     => __( 'Email', 'south-cairns-dental' ),
				),
				'condition' => array( $p . '_type!' => 'none', $p . '_style!' => 'textlink' ),
			)
		);
	}

	/**
	 * Image controls (inside an open controls section).
	 *
	 * @param string $p Control prefix.
	 * @param array  $d Defaults: label, ratio, pos, corner, url.
	 */
	protected function image_controls( $p, $d = array() ) {
		$d = wp_parse_args( $d, array( 'label' => __( 'Image', 'south-cairns-dental' ), 'ratio' => '4 / 3', 'pos' => '', 'corner' => 'corner', 'url' => '' ) );
		$this->add_control(
			$p,
			array(
				'label'   => $d['label'],
				'type'    => Controls_Manager::MEDIA,
				'default' => array( 'url' => $d['url'] ? $d['url'] : \Elementor\Utils::get_placeholder_image_src() ),
			)
		);
		$this->add_control(
			$p . '_ratio',
			array(
				'label'       => __( 'Shape (width / height)', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['ratio'],
				'description' => __( 'For example 4 / 3, 1 / 1 or 4 / 5. The image is cropped to fit.', 'south-cairns-dental' ),
			)
		);
		$this->add_control(
			$p . '_pos',
			array(
				'label'       => __( 'Focal point', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => $d['pos'],
				'placeholder' => '50% 50%',
				'description' => __( 'Which part stays in view when cropped: horizontal % then vertical %.', 'south-cairns-dental' ),
			)
		);
		$this->add_control(
			$p . '_corner',
			array(
				'label'   => __( 'Rounded corner', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $d['corner'],
				'options' => array(
					''          => __( 'None', 'south-cairns-dental' ),
					'corner'    => __( 'Top left', 'south-cairns-dental' ),
					'corner-tr' => __( 'Top right', 'south-cairns-dental' ),
					'corner-bl' => __( 'Bottom left', 'south-cairns-dental' ),
					'corner-br' => __( 'Bottom right', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control(
			$p . '_fit',
			array(
				'label'        => __( 'Show the whole image (diagrams)', 'south-cairns-dental' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);
	}

	/**
	 * A repeater URL control definition.
	 *
	 * @param Repeater $r     Repeater.
	 * @param string   $name  Control name.
	 * @param string   $label Label.
	 */
	protected function repeater_link( $r, $name = 'link', $label = '' ) {
		$r->add_control( $name, array( 'label' => $label ? $label : __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL ) );
	}

	/* ---------------------------------------------------------------------
	 * Rendering helpers
	 * ------------------------------------------------------------------- */

	/**
	 * Open a section with the chosen background and spacing.
	 *
	 * @param array  $s       Settings.
	 * @param string $classes Extra classes.
	 * @param string $label   aria-labelledby target ID ('' for none).
	 * @param string $base    Base class ('sec' for standard sections).
	 * @return string
	 */
	protected function open_section( $s, $classes = '', $label = '', $base = 'sec' ) {
		$cls = array( $base );
		if ( ! empty( $s['sec_bg'] ) ) {
			$cls[] = 'sec--' . $s['sec_bg'];
		}
		if ( ! empty( $s['sec_size'] ) ) {
			$cls[] = 'sec--' . $s['sec_size'];
		}
		if ( $classes ) {
			$cls[] = $classes;
		}
		$attrs = ' class="' . esc_attr( implode( ' ', $cls ) ) . '"';
		if ( ! empty( $s['sec_anchor'] ) ) {
			$attrs .= ' id="' . esc_attr( sanitize_title( $s['sec_anchor'] ) ) . '"';
		}
		if ( $label ) {
			$attrs .= ' aria-labelledby="' . esc_attr( $label ) . '"';
		}
		return '<section' . $attrs . '>';
	}

	/**
	 * Is the section dark (light text)?
	 *
	 * @param array $s Settings.
	 * @return bool
	 */
	protected function is_dark( $s ) {
		return isset( $s['sec_bg'] ) && in_array( $s['sec_bg'], array( 'canopy', 'forest' ), true );
	}

	/**
	 * A stable ID for the section heading.
	 *
	 * @param string $suffix Suffix.
	 * @return string
	 */
	protected function hid( $suffix = 'h' ) {
		return 'scd-' . $this->get_id() . '-' . $suffix;
	}

	/**
	 * Paragraphs from a textarea (blank line = new paragraph; single line breaks kept).
	 *
	 * @param string $text  Text.
	 * @param string $class Paragraph class.
	 * @return string
	 */
	protected function paras( $text, $class = '' ) {
		$text = trim( (string) $text );
		if ( '' === $text ) {
			return '';
		}
		if ( preg_match( '#^\s*<(p|ul|ol|table|div|h[2-6])[\s>]#i', $text ) ) {
			return wp_kses_post( $text );
		}
		$out = '';
		foreach ( preg_split( "/\n\s*\n/", $text ) as $para ) {
			$out .= '<p' . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . nl2br( scd_inline( trim( $para ) ), false ) . '</p>';
		}
		return $out;
	}

	/**
	 * Rich text (WYSIWYG) output with shortcodes ([scd_hours_table]).
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	protected function rich( $html ) {
		$html = (string) $html;
		if ( '' === trim( $html ) ) {
			return '';
		}
		$html = wp_kses_post( $html );
		return do_shortcode( false === strpos( $html, '<p' ) && false === strpos( $html, '<ul' ) && false === strpos( $html, '<table' ) && false === strpos( $html, '[scd_' ) ? wpautop( $html ) : $html );
	}

	/**
	 * Rich text with a class: one paragraph gets the class itself (as on the static site),
	 * several paragraphs are wrapped in a div with the class.
	 *
	 * @param string $html  HTML.
	 * @param string $class Classes.
	 * @return string
	 */
	protected function rich_block( $html, $class ) {
		$html = trim( $this->rich( $html ) );
		if ( '' === $html ) {
			return '';
		}
		if ( 1 === substr_count( $html, '<p' ) && preg_match( '#^<p(?: class="([^"]*)")?>(.*)</p>$#s', $html, $m ) ) {
			return '<p class="' . esc_attr( trim( $class . ' ' . $m[1] ) ) . '">' . $m[2] . '</p>';
		}
		return '<div class="' . esc_attr( $class ) . '">' . $html . '</div>';
	}

	/**
	 * Lines from a textarea as an array.
	 *
	 * @param string $text Text.
	 * @return string[]
	 */
	protected function lines( $text ) {
		return array_values( array_filter( array_map( 'trim', preg_split( "/\r?\n/", (string) $text ) ), 'strlen' ) );
	}

	/**
	 * URL from an Elementor URL control value.
	 *
	 * @param mixed $link Control value.
	 * @return string
	 */
	protected function url( $link ) {
		return is_array( $link ) && ! empty( $link['url'] ) ? $link['url'] : '';
	}

	/**
	 * Target/rel attributes from an Elementor URL control value.
	 *
	 * @param mixed $link Control value.
	 * @return string
	 */
	protected function link_extra( $link ) {
		$out = '';
		if ( ! is_array( $link ) ) {
			return $out;
		}
		$rel = array();
		if ( ! empty( $link['is_external'] ) ) {
			$out  .= ' target="_blank"';
			$rel[] = 'noopener';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( ! $rel && ! empty( $link['url'] ) && preg_match( '#^https?://#', $link['url'] ) && ! scd_is_internal_url( $link['url'] ) ) {
			$rel[] = 'noopener';
		}
		if ( $rel ) {
			$out .= ' rel="' . esc_attr( implode( ' ', array_unique( $rel ) ) ) . '"';
		}
		return $out;
	}

	/**
	 * A button or text link from button controls.
	 *
	 * @param array  $s     Settings.
	 * @param string $p     Prefix.
	 * @param string $extra Extra classes.
	 * @param string $attrs Extra pre-escaped attributes.
	 * @return string
	 */
	protected function button( $s, $p, $extra = '', $attrs = '' ) {
		$type = isset( $s[ $p . '_type' ] ) ? $s[ $p . '_type' ] : 'none';
		if ( 'none' === $type || '' === $type ) {
			return '';
		}
		$label = isset( $s[ $p . '_label' ] ) ? $s[ $p . '_label' ] : '';
		$link  = isset( $s[ $p . '_url' ] ) ? $s[ $p . '_url' ] : array();
		$style = isset( $s[ $p . '_style' ] ) ? $s[ $p . '_style' ] : 'lime';
		if ( 'textlink' === $style ) {
			$url = scd_button_url( $type, $this->url( $link ) );
			if ( '' === $url ) {
				return '';
			}
			$rel = 'book' === $type ? ' rel="noopener"' : $this->link_extra( $link );
			return '<a class="' . esc_attr( trim( 'textlink ' . $extra ) ) . '" href="' . esc_url( $url ) . '"' . $rel . $attrs . '>' . scd_inline( str_replace( '{phone}', scd_practice( 'phone_display' ), $label ) ) . '</a>';
		}
		$classes = 'forest' === $style ? '' : 'btn--' . $style;
		if ( ! empty( $s[ $p . '_size' ] ) ) {
			$classes .= ' btn--' . $s[ $p . '_size' ];
		}
		return scd_button(
			array(
				'type'     => $type,
				'label'    => $label,
				'url'      => $this->url( $link ),
				'class'    => trim( $classes . ' ' . $extra ),
				'icon'     => isset( $s[ $p . '_icon' ] ) ? $s[ $p . '_icon' ] : 'auto',
				'external' => ! empty( $link['is_external'] ),
				'attrs'    => $attrs,
			)
		);
	}

	/**
	 * Buttons wrapped in a row (or '' when there are none).
	 *
	 * @param array    $s        Settings.
	 * @param string[] $prefixes Button prefixes.
	 * @param string   $class    Row classes.
	 * @param string   $attrs    Extra row attributes (pre-escaped).
	 * @return string
	 */
	protected function buttons( $s, $prefixes, $class = 'btn-row', $attrs = '' ) {
		$html = '';
		foreach ( $prefixes as $p ) {
			$html .= $this->button( $s, $p );
		}
		return '' === $html ? '' : '<div class="' . esc_attr( $class ) . '"' . $attrs . '>' . $html . '</div>';
	}

	/**
	 * Image frame from image controls.
	 *
	 * @param array  $s     Settings.
	 * @param string $p     Prefix.
	 * @param array  $extra Extra scd_frame() args.
	 * @return string
	 */
	protected function frame( $s, $p, $extra = array() ) {
		$media = isset( $s[ $p ] ) ? $s[ $p ] : array();
		if ( empty( $media['id'] ) && empty( $media['url'] ) ) {
			return '';
		}
		$corner = isset( $s[ $p . '_corner' ] ) ? $s[ $p . '_corner' ] : 'corner';
		return scd_frame(
			array_merge(
				array(
					'id'      => isset( $media['id'] ) ? (int) $media['id'] : 0,
					'url'     => isset( $media['url'] ) ? $media['url'] : '',
					'alt'     => isset( $media['alt'] ) ? $media['alt'] : '',
					'class'   => $corner ? 'frame--' . $corner : '',
					'ratio'   => ! empty( $s[ $p . '_ratio' ] ) ? $s[ $p . '_ratio' ] : '4 / 3',
					'pos'     => isset( $s[ $p . '_pos' ] ) ? $s[ $p . '_pos' ] : '',
					'contain' => ! empty( $s[ $p . '_fit' ] ),
					'illus'   => $this->is_svg( $media ),
				),
				$extra
			)
		);
	}

	/**
	 * Is the media an SVG illustration?
	 *
	 * @param array $media Media control value.
	 * @return bool
	 */
	protected function is_svg( $media ) {
		return ! empty( $media['url'] ) && '.svg' === strtolower( substr( wp_parse_url( $media['url'], PHP_URL_PATH ), -4 ) );
	}

	/**
	 * Plain <img> from a media control value.
	 *
	 * @param array $media Media.
	 * @param array $args  scd_img_tag() args.
	 * @return string
	 */
	protected function img( $media, $args = array() ) {
		if ( empty( $media['id'] ) && empty( $media['url'] ) ) {
			return '';
		}
		return scd_img_tag(
			array_merge(
				array(
					'id'  => isset( $media['id'] ) ? (int) $media['id'] : 0,
					'url' => isset( $media['url'] ) ? $media['url'] : '',
				),
				$args
			)
		);
	}

	/**
	 * The section heading block. Returns [ head HTML, wrapper start, wrapper end ] for the layout.
	 *
	 * Layouts:
	 * - stacked: <div class="section-head">…</div> then the content.
	 * - row:     heading and intro on the left, button on the right.
	 * - split-*: heading column beside the content (two-column grid).
	 * - hidden:  heading for screen readers only.
	 *
	 * @param array $s Settings.
	 * @return array{0:string,1:string,2:string}
	 */
	protected function head( $s ) {
		$layout  = isset( $s['head_layout'] ) ? $s['head_layout'] : 'stacked';
		$heading = isset( $s['heading'] ) ? trim( (string) $s['heading'] ) : '';
		$tag     = isset( $s['heading_tag'] ) && in_array( $s['heading_tag'], array( 'h1', 'h2', 'h3' ), true ) ? $s['heading_tag'] : 'h2';
		$dark    = $this->is_dark( $s );
		$kicker  = ! empty( $s['head_kicker'] ) ? '<p class="kicker">' . scd_inline( $s['head_kicker'] ) . '</p>' : '';
		$intro   = isset( $s['intro'] ) ? $s['intro'] : '';
		$istyle  = isset( $s['intro_style'] ) ? $s['intro_style'] : 'narrow';
		$muted   = ! $dark && 'normal' !== $istyle;
		$h       = '' !== $heading ? sprintf( '<%1$s id="%2$s" class="scd-head-title%4$s">%3$s</%1$s>', $tag, esc_attr( $this->hid() ), nl2br( scd_inline( $heading ), false ), 'hidden' === $layout ? ' visually-hidden' : '' ) : '';
		$btns    = $this->button( $s, 'head_btn1' ) . $this->button( $s, 'head_btn2' );

		if ( 'hidden' === $layout ) {
			return array( $h, '<div class="wrap">', '</div>' );
		}
		if ( 'row' === $layout ) {
			$p = $this->paras( $intro, $muted ? 'muted mb-0' : 'mb-0' );
			return array( '<div class="section-head section-head--row"><div>' . $kicker . $h . $p . '</div>' . $btns . '</div>', '<div class="wrap">', '</div>' );
		}
		if ( 0 === strpos( $layout, 'split-' ) ) {
			$cols = substr( $layout, 6 );
			$p    = $this->paras( $intro, $dark ? '' : ( 'narrow' === $istyle ? 'section-head__intro' : ( 'muted' === $istyle ? 'muted' : '' ) ) );
			if ( '' !== $btns ) {
				$count = substr_count( $btns, 'class="btn' );
				if ( $count > 1 ) {
					$btns = '<div class="btn-row mt-6">' . $btns . '</div>';
				} elseif ( $count ) {
					$btns = $this->button( $s, 'head_btn1', 'mt-5' ) . $this->button( $s, 'head_btn2', 'mt-5' );
				} else {
					$btns = '<p>' . $btns . '</p>';
				}
			}
			return array( '<div>' . $kicker . $h . $p . $btns . '</div>', '<div class="wrap split split--' . esc_attr( $cols ) . ' split--top">', '</div>' );
		}
		if ( '' === $heading && '' === trim( $intro ) && '' === $kicker ) {
			return array( '', '<div class="wrap">', '</div>' );
		}
		// Heading directly above the content, with the standard gap (no section-head block).
		if ( 'bare' === $layout ) {
			return array( $kicker . $h . '<div class="mt-6">', '<div class="wrap">', '</div></div>' );
		}
		$p = $this->paras( $intro, $dark ? 'mb-0' : ( 'narrow' === $istyle ? 'section-head__intro' : ( 'muted' === $istyle ? 'muted mb-0' : 'mb-0' ) ) );
		if ( '' !== $btns ) {
			$btns = '<div class="btn-row mt-5">' . $btns . '</div>';
		}
		return array( '<div class="section-head">' . $kicker . $h . $p . $btns . '</div>', '<div class="wrap">', '</div>' );
	}

	/**
	 * Wrap content in a standard section with its heading.
	 *
	 * @param array  $s       Settings.
	 * @param string $content Section content.
	 * @param string $classes Extra section classes.
	 * @param string $after   HTML after the wrapper (inside the section).
	 * @return string
	 */
	protected function section( $s, $content, $classes = '', $after = '' ) {
		list( $head, $open, $close ) = $this->head( $s );
		$label                       = ( isset( $s['heading'] ) && '' !== trim( (string) $s['heading'] ) ) ? $this->hid() : '';
		return $this->open_section( $s, $classes, $label ) . $open . $head . $content . $close . $after . '</section>';
	}
}
