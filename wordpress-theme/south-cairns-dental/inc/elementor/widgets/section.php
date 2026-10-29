<?php
/**
 * Flexible section: a section built from a list of typed rows (headings, paragraphs, images,
 * icons, buttons, opening status and list items), with "group start" and "group end" rows for the
 * columns, panels, lists and cards that hold them. Used for the campaign landing pages and the
 * treatment-page sections whose layouts the other widgets don't cover, so their exact markup and
 * the shared stylesheet apply while every text, image and link stays editable.
 *
 * Editing tips: change text, images and links in each row; duplicate a list-item row to add an
 * item; drag rows to reorder. Group rows carry layout classes and are best left as they are.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Flexible section widget.
 */
class Section extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-section';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Flexible section', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-section';
	}

	/**
	 * Container tags a group may use.
	 *
	 * @return string[]
	 */
	protected function group_tags() {
		return array( 'div', 'ul', 'ol', 'dl', 'aside', 'nav', 'p', 'figure', 'article', 'span', 'li' );
	}

	/**
	 * List item styles: key => label.
	 *
	 * @return string[]
	 */
	protected function item_styles() {
		return array(
			'li'       => __( 'Plain item (tick list)', 'south-cairns-dental' ),
			'mention'  => __( 'Title and text', 'south-cairns-dental' ),
			'comfort'  => __( 'Round icon, title and text', 'south-cairns-dental' ),
			'trust'    => __( 'Icon badge, title and text (trust strip)', 'south-cairns-dental' ),
			'match'    => __( 'Icon badge, title and paragraph (card)', 'south-cairns-dental' ),
			'fact'     => __( 'Fact (term and description)', 'south-cairns-dental' ),
			'contact'  => __( 'Contact row (label and value)', 'south-cairns-dental' ),
			'person'   => __( 'Person (photo, name, role, qualifications)', 'south-cairns-dental' ),
			'step'     => __( 'Numbered step (title, text, note)', 'south-cairns-dental' ),
			'step-div' => __( 'Numbered step in a list', 'south-cairns-dental' ),
			'faq'      => __( 'Question and answer', 'south-cairns-dental' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Content', 'south-cairns-dental' ) ) );

		$r = new Repeater();
		$r->add_control(
			'type',
			array(
				'label'   => __( 'Row', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'text',
				'options' => array(
					'heading' => __( 'Heading', 'south-cairns-dental' ),
					'text'    => __( 'Paragraph / text', 'south-cairns-dental' ),
					'image'   => __( 'Image', 'south-cairns-dental' ),
					'icon'    => __( 'Icon', 'south-cairns-dental' ),
					'buttons' => __( 'Button(s)', 'south-cairns-dental' ),
					'status'  => __( 'Live opening status', 'south-cairns-dental' ),
					'item'    => __( 'List item', 'south-cairns-dental' ),
					'open'    => __( 'Group start (column, panel, list…)', 'south-cairns-dental' ),
					'close'   => __( 'Group end', 'south-cairns-dental' ),
				),
			)
		);
		$r->add_control( 'tag', array( 'label' => __( 'Element', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'div', 'options' => array_combine( $this->group_tags(), $this->group_tags() ), 'condition' => array( 'type' => 'open' ) ) );
		$r->add_control( 'level', array( 'label' => __( 'Level', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'h2', 'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4' ), 'condition' => array( 'type' => 'heading' ) ) );
		$r->add_control( 'text_tag', array( 'label' => __( 'Element', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'p', 'options' => array( 'p' => 'p', 'span' => 'span', 'strong' => 'strong', 'div' => 'div' ), 'condition' => array( 'type' => 'text' ) ) );
		$r->add_control( 'item_style', array( 'label' => __( 'Item style', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'li', 'options' => $this->item_styles(), 'condition' => array( 'type' => 'item' ) ) );

		$r->add_control( 'title', array( 'label' => __( 'Title / name', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'type' => 'item' ) ) );
		$r->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 3,
				'description' => __( 'Links and bold text can be added with HTML. {phone} shows the practice phone number.', 'south-cairns-dental' ),
				'condition'   => array( 'type' => array( 'heading', 'text', 'item' ) ),
			)
		);
		$r->add_control( 'extra', array( 'label' => __( 'Extra line (qualifications / note)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'type' => 'item', 'item_style' => array( 'person', 'step' ) ) ) );
		$r->add_control( 'answer', array( 'label' => __( 'Answer', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG, 'condition' => array( 'type' => 'item', 'item_style' => 'faq' ) ) );
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => '', 'condition' => array( 'type' => array( 'icon', 'item' ) ) ) );
		$r->add_control( 'media', array( 'label' => __( 'Image', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'type' => array( 'image', 'item' ) ) ) );
		$r->add_control( 'alt', array( 'label' => __( 'Alt text (blank = decorative)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'type' => array( 'image', 'item' ) ) ) );
		$r->add_control( 'eager', array( 'label' => __( 'Load first (top of the page)', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'type' => 'image' ) ) );

		foreach ( array( 'b1' => __( 'Button', 'south-cairns-dental' ), 'b2' => __( 'Second button', 'south-cairns-dental' ) ) as $p => $label ) {
			$r->add_control( $p . '_heading', array( 'label' => $label, 'type' => Controls_Manager::HEADING, 'separator' => 'before', 'condition' => array( 'type' => 'buttons' ) ) );
			$r->add_control(
				$p . '_type',
				array(
					'label'     => __( 'Link to', 'south-cairns-dental' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'b1' === $p ? 'book' : 'none',
					'options'   => array(
						'none'   => __( 'No button', 'south-cairns-dental' ),
						'book'   => __( 'Online booking (practice details)', 'south-cairns-dental' ),
						'call'   => __( 'Call the practice', 'south-cairns-dental' ),
						'email'  => __( 'Email the practice', 'south-cairns-dental' ),
						'custom' => __( 'Custom link', 'south-cairns-dental' ),
					),
					'condition' => array( 'type' => 'buttons' ),
				)
			);
			$r->add_control( $p . '_label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'type' => 'buttons' ) ) );
			$r->add_control( $p . '_url', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL, 'condition' => array( 'type' => 'buttons' ) ) );
			$r->add_control( $p . '_cls', array( 'label' => __( 'Button classes', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'btn btn--lime', 'description' => __( 'e.g. btn btn--lime btn--lg, btn btn--ghost, textlink', 'south-cairns-dental' ), 'condition' => array( 'type' => 'buttons' ) ) );
			$r->add_control(
				$p . '_icon',
				array(
					'label'     => __( 'Icon', 'south-cairns-dental' ),
					'type'      => Controls_Manager::SELECT,
					'default'   => 'auto',
					'options'   => array( 'auto' => __( 'Automatic', 'south-cairns-dental' ), '' => __( 'None', 'south-cairns-dental' ), 'calendar' => __( 'Calendar', 'south-cairns-dental' ), 'phone' => __( 'Phone', 'south-cairns-dental' ), 'mail' => __( 'Email', 'south-cairns-dental' ) ),
					'condition' => array( 'type' => 'buttons' ),
				)
			);
		}
		$r->add_control( 'row', array( 'label' => __( 'Wrap in a button row', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'separator' => 'before', 'condition' => array( 'type' => 'buttons' ) ) );
		$r->add_control( 'sticky', array( 'label' => __( 'Phone action bar appears after these buttons scroll away', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'type' => array( 'buttons', 'open' ) ) ) );

		$r->add_control( 'cls', array( 'label' => __( 'CSS classes', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'separator' => 'before', 'condition' => array( 'type!' => 'close' ) ) );
		$r->add_control( 'style', array( 'label' => __( 'Inline style (e.g. --ratio:4 / 3;--pos:50% 30%)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'type!' => 'close' ) ) );
		$r->add_control( 'el_id', array( 'label' => __( 'ID', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'type' => array( 'open', 'heading', 'text', 'item' ) ) ) );
		$r->add_control( 'aria_label', array( 'label' => __( 'Accessible name (aria-label)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'type' => 'open' ) ) );
		$r->add_control( 'aria_labelledby', array( 'label' => __( 'Labelled by (heading ID)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'type' => 'open' ) ) );

		$this->add_control(
			'rows',
			array(
				'label'       => __( 'Rows', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => "{{ type === 'open' ? '▸ ' + tag + (cls ? '.' + cls.split(' ').join('.') : '') : (type === 'close' ? '◂ end' : (type === 'item' ? '• ' + (title || text) : (type === 'buttons' ? 'Buttons: ' + b1_label + (b2_label ? ' / ' + b2_label : '') : (type === 'image' ? 'Image ' + (alt || '') : (type === 'icon' ? 'Icon: ' + icon : (type === 'status' ? 'Opening status' : (text || type))))))) }}",
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'wrapper', array( 'label' => __( 'Section element', 'south-cairns-dental' ) ) );
		$this->add_control( 'sec_tag', array( 'label' => __( 'Element', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'section', 'options' => array( 'section' => 'section', 'aside' => 'aside', 'div' => 'div' ) ) );
		$this->add_control( 'sec_cls', array( 'label' => __( 'CSS classes', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'sec' ) );
		$this->add_control( 'sec_aria_label', array( 'label' => __( 'Accessible name (aria-label)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'sec_labelledby', array( 'label' => __( 'Labelled by (heading ID)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->end_controls_section();

		$this->section_controls();
	}

	/**
	 * Sanitise a class list.
	 *
	 * @param string $cls Classes.
	 * @return string
	 */
	protected function classes( $cls ) {
		return implode( ' ', array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( (string) $cls ) ) ) ) );
	}

	/**
	 * Allow only CSS custom properties used by the design system (--ratio, --pos, --i).
	 *
	 * @param string $style Style.
	 * @return string
	 */
	protected function style( $style ) {
		$out = array();
		foreach ( explode( ';', (string) $style ) as $decl ) {
			if ( preg_match( '#^\s*(--(?:ratio|pos|i))\s*:\s*([0-9.%/ ]+)\s*$#', $decl, $m ) ) {
				$out[] = $m[1] . ':' . trim( $m[2] );
			}
		}
		return implode( ';', $out );
	}

	/**
	 * Common attributes for a row.
	 *
	 * @param array $row Row.
	 * @return string
	 */
	protected function attrs( $row ) {
		$a   = '';
		$cls = $this->classes( $row['cls'] ?? '' );
		if ( $cls ) {
			$a .= ' class="' . esc_attr( $cls ) . '"';
		}
		$style = $this->style( $row['style'] ?? '' );
		if ( $style ) {
			$a .= ' style="' . esc_attr( $style ) . '"';
		}
		if ( ! empty( $row['el_id'] ) ) {
			$a .= ' id="' . esc_attr( sanitize_title( $row['el_id'] ) ) . '"';
		}
		return $a;
	}

	/**
	 * Inline text with {phone}.
	 *
	 * @param string $text Text.
	 * @return string
	 */
	protected function inline( $text ) {
		return scd_inline( str_replace( '{phone}', scd_practice( 'phone_display' ), (string) $text ) );
	}

	/**
	 * An image row's <img>.
	 *
	 * @param array $row Row.
	 * @return string
	 */
	protected function row_img( $row ) {
		$alt = trim( (string) ( $row['alt'] ?? '' ) );
		return $this->img(
			$row['media'] ?? array(),
			array(
				'alt'        => $alt,
				'decorative' => '' === $alt,
				'eager'      => 'yes' === ( $row['eager'] ?? '' ),
				'size'       => 'large',
			)
		);
	}

	/**
	 * One button from a row.
	 *
	 * @param array  $row Row.
	 * @param string $p   Prefix.
	 * @return string
	 */
	protected function row_button( $row, $p ) {
		$type = $row[ $p . '_type' ] ?? 'none';
		if ( 'none' === $type || '' === $type ) {
			return '';
		}
		$cls   = $this->classes( $row[ $p . '_cls' ] ?? 'btn' );
		$link  = $row[ $p . '_url' ] ?? array();
		$label = $row[ $p . '_label' ] ?? '';
		if ( false === strpos( ' ' . $cls . ' ', ' btn ' ) ) {
			// Text links and other non-button links.
			$url = scd_button_url( $type, $this->url( $link ) );
			return $url ? '<a class="' . esc_attr( $cls ) . '" href="' . esc_url( $url ) . '"' . ( 'book' === $type ? ' rel="noopener"' : $this->link_extra( $link ) ) . '>' . $this->inline( $label ) . '</a>' : '';
		}
		return scd_button(
			array(
				'type'     => $type,
				'label'    => $label,
				'url'      => $this->url( $link ),
				'class'    => trim( preg_replace( '/(^| )btn( |$)/', ' ', $cls ) ),
				'icon'     => $row[ $p . '_icon' ] ?? 'auto',
				'external' => ! empty( $link['is_external'] ),
			)
		);
	}

	/**
	 * A list item row.
	 *
	 * @param array $row Row.
	 * @return string
	 */
	protected function row_item( $row ) {
		$a     = $this->attrs( $row );
		$title = $this->inline( $row['title'] ?? '' );
		$text  = $this->inline( $row['text'] ?? '' );
		$icon  = scd_icon( $row['icon'] ?? '' );
		switch ( $row['item_style'] ?? 'li' ) {
			case 'mention':
				return '<li' . $a . '><strong>' . $title . '</strong><span>' . $text . '</span></li>';
			case 'comfort':
				return '<li' . $a . '><span class="icon-wrap">' . $icon . '</span><div><strong>' . $title . '</strong><span>' . $text . '</span></div></li>';
			case 'trust':
				return '<li' . $a . '><span class="icon-badge">' . $icon . '</span><span><strong>' . $title . '</strong>' . $text . '</span></li>';
			case 'match':
				return '<li' . $a . '><span class="icon-badge">' . $icon . '</span><strong>' . $title . '</strong><p>' . $text . '</p></li>';
			case 'fact':
				return '<div' . $a . '><dt>' . $title . '</dt><dd>' . $text . '</dd></div>';
			case 'contact':
				return '<div' . $a . '><dt class="label">' . $title . '</dt><dd class="mb-0">' . $text . '</dd></div>';
			case 'person':
				$alt = trim( (string) ( $row['alt'] ?? '' ) );
				$img = $this->img( $row['media'] ?? array(), array( 'alt' => $alt ? $alt : wp_strip_all_tags( $title ), 'size' => 'scd-portrait' ) );
				return '<li' . $a . '><figure class="frame portrait">' . $img . '</figure><div><h3>' . $title . '</h3><p>' . $text . '</p>' . ( ! empty( $row['extra'] ) ? '<p class="member__quals">' . $this->inline( $row['extra'] ) . '</p>' : '' ) . '</div></li>';
			case 'step':
				return '<li' . $a . '><h3>' . $title . '</h3><p>' . $text . '</p>' . ( ! empty( $row['extra'] ) ? '<span class="process__note">' . $this->inline( $row['extra'] ) . '</span>' : '' ) . '</li>';
			case 'step-div':
				return '<li' . $a . '><div><h3>' . $title . '</h3><p>' . $text . '</p></div></li>';
			case 'faq':
				return '<details' . $a . '><summary>' . $title . '</summary><div class="faq__body">' . $this->rich( $row['answer'] ?? '' ) . '</div></details>';
			default:
				return '<li' . $a . '>' . $text . '</li>';
		}
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$tag   = in_array( $s['sec_tag'], array( 'section', 'aside', 'div' ), true ) ? $s['sec_tag'] : 'section';
		$cls   = $this->classes( $s['sec_cls'] );
		// The Style tab's background and spacing replace the section's own background/spacing classes.
		if ( ! empty( $s['sec_bg'] ) ) {
			$cls = trim( preg_replace( '/\bsec--(mist|lime|fade|canopy|forest)\b/', '', $cls ) ) . ' sec--' . $s['sec_bg'];
		}
		if ( ! empty( $s['sec_size'] ) ) {
			$cls = trim( preg_replace( '/\bsec--(tight|flush)\b/', '', $cls ) ) . ' sec--' . $s['sec_size'];
		}
		$attrs = $cls ? ' class="' . esc_attr( preg_replace( '/\s+/', ' ', $cls ) ) . '"' : '';
		if ( ! empty( $s['sec_anchor'] ) ) {
			$attrs .= ' id="' . esc_attr( sanitize_title( $s['sec_anchor'] ) ) . '"';
		}
		if ( ! empty( $s['sec_labelledby'] ) ) {
			$attrs .= ' aria-labelledby="' . esc_attr( sanitize_title( $s['sec_labelledby'] ) ) . '"';
		} elseif ( ! empty( $s['sec_aria_label'] ) ) {
			$attrs .= ' aria-label="' . esc_attr( $s['sec_aria_label'] ) . '"';
		}

		$html  = '<' . $tag . $attrs . '>';
		$stack = array();
		foreach ( (array) $s['rows'] as $row ) {
			switch ( $row['type'] ) {
				case 'open':
					$t = in_array( $row['tag'], $this->group_tags(), true ) ? $row['tag'] : 'div';
					$a = $this->attrs( $row );
					if ( ! empty( $row['aria_label'] ) ) {
						$a .= ' aria-label="' . esc_attr( $row['aria_label'] ) . '"';
					}
					if ( ! empty( $row['aria_labelledby'] ) ) {
						$a .= ' aria-labelledby="' . esc_attr( sanitize_title( $row['aria_labelledby'] ) ) . '"';
					}
					if ( 'yes' === ( $row['sticky'] ?? '' ) ) {
						$a .= ' data-sticky-anchor';
					}
					$html   .= '<' . $t . $a . '>';
					$stack[] = $t;
					break;
				case 'close':
					if ( $stack ) {
						$html .= '</' . array_pop( $stack ) . '>';
					}
					break;
				case 'heading':
					$h     = in_array( $row['level'], array( 'h1', 'h2', 'h3', 'h4' ), true ) ? $row['level'] : 'h2';
					$html .= '<' . $h . $this->attrs( $row ) . '>' . nl2br( $this->inline( $row['text'] ), false ) . '</' . $h . '>';
					break;
				case 'text':
					$t     = in_array( $row['text_tag'], array( 'p', 'span', 'strong', 'div' ), true ) ? $row['text_tag'] : 'p';
					$html .= '<' . $t . $this->attrs( $row ) . '>' . nl2br( $this->inline( $row['text'] ), false ) . '</' . $t . '>';
					break;
				case 'image':
					$img = $this->row_img( $row );
					if ( $img ) {
						$html .= '<figure' . $this->attrs( $row ) . '>' . $img . '</figure>';
					}
					break;
				case 'icon':
					$svg   = scd_icon( $row['icon'] );
					$a     = $this->attrs( $row );
					$html .= $a ? '<span' . $a . '>' . $svg . '</span>' : $svg;
					break;
				case 'buttons':
					$btns = $this->row_button( $row, 'b1' ) . $this->row_button( $row, 'b2' );
					if ( 'yes' === ( $row['row'] ?? '' ) ) {
						$a     = $this->attrs( array_merge( $row, array( 'cls' => trim( 'btn-row ' . preg_replace( '/(^| )btn-row( |$)/', ' ', (string) ( $row['cls'] ?? '' ) ) ) ) ) );
						$html .= '<div' . $a . ( 'yes' === ( $row['sticky'] ?? '' ) ? ' data-sticky-anchor' : '' ) . '>' . $btns . '</div>';
					} else {
						$html .= $btns;
					}
					break;
				case 'status':
					$html .= '<p' . $this->attrs( $row ) . '>' . scd_hours_status() . '</p>';
					break;
				case 'item':
					$html .= $this->row_item( $row );
					break;
			}
		}
		while ( $stack ) {
			$html .= '</' . array_pop( $stack ) . '>';
		}
		echo $html . '</' . $tag . '>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above.
	}
}
