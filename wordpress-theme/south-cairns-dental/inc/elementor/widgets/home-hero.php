<?php
/**
 * Homepage hero: heading, introduction, buttons, trust points, photo, booking card and the
 * phone action bar that appears once the hero buttons scroll away.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Home hero widget.
 */
class Home_Hero extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-home-hero';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Home hero', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-header';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Heading and text', 'south-cairns-dental' ) ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => 'Gentle, affordable dentistry' ) );
		$this->add_control( 'title_highlight', array( 'label' => __( 'Heading, second line (lime)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'for South Cairns families.' ) );
		$this->add_control( 'lead', array( 'label' => __( 'Introduction', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 5 ) );
		$this->button_controls( 'btn1', __( 'First button', 'south-cairns-dental' ), array( 'type' => 'book', 'label' => 'Book online', 'style' => 'lime' ) );
		$this->button_controls( 'btn2', __( 'Second button', 'south-cairns-dental' ), array( 'type' => 'call', 'label' => 'Call {phone}', 'style' => 'ghost' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'trust_section', array( 'label' => __( 'Trust points', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => 'tooth' ) );
		$r->add_control( 'strong', array( 'label' => __( 'Highlighted words', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'text', array( 'label' => __( 'Rest of the line', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'trust', array( 'label' => __( 'Points', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ strong }}} {{{ text }}}' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'image_section', array( 'label' => __( 'Photo', 'south-cairns-dental' ) ) );
		$this->image_controls( 'image', array( 'ratio' => '1 / 1', 'pos' => '50% 40%' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'card_section', array( 'label' => __( 'Booking card', 'south-cairns-dental' ) ) );
		$this->add_control( 'card_show', array( 'label' => __( 'Show the booking card', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'card_title', array( 'label' => __( 'Card heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Book today', 'condition' => array( 'card_show' => 'yes' ) ) );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'value', array( 'label' => __( 'Value', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'card_rows', array( 'label' => __( 'Rows', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}}', 'condition' => array( 'card_show' => 'yes' ) ) );
		$this->button_controls( 'card_btn', __( 'Card button', 'south-cairns-dental' ), array( 'type' => 'call', 'label' => 'Call {phone}', 'style' => 'lime' ) );
		$this->add_control( 'card_alt_label', array( 'label' => __( 'Link under the button', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Or book online', 'condition' => array( 'card_show' => 'yes' ) ) );
		$this->add_control(
			'card_alt_type',
			array(
				'label'     => __( 'Link to', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'book',
				'options'   => array( 'book' => __( 'Online booking', 'south-cairns-dental' ), 'custom' => __( 'Custom link', 'south-cairns-dental' ) ),
				'condition' => array( 'card_show' => 'yes' ),
			)
		);
		$this->add_control( 'card_alt_url', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL, 'condition' => array( 'card_show' => 'yes', 'card_alt_type' => 'custom' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'bar_section', array( 'label' => __( 'Phone action bar', 'south-cairns-dental' ) ) );
		$this->add_control( 'bar_show', array( 'label' => __( 'Show on phones after the hero', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'bar_call', array( 'label' => __( 'Call label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call now', 'condition' => array( 'bar_show' => 'yes' ) ) );
		$this->add_control( 'bar_book', array( 'label' => __( 'Booking label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Book online', 'condition' => array( 'bar_show' => 'yes' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'scd_section', array( 'label' => __( 'Section', 'south-cairns-dental' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control(
			'sec_padding',
			array(
				'label'      => __( 'Inner padding', 'south-cairns-dental' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'rem', 'vw' ),
				'allowed_dimensions' => 'vertical',
				'selectors'  => array( '{{WRAPPER}} .hero-cc > .wrap' => 'padding-top: {{TOP}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();
		$this->heading_style_controls( '.hero-cc h1' );
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$hid   = $this->hid();
		$title = scd_inline( $s['title'] );
		if ( ! empty( $s['title_highlight'] ) ) {
			$title .= ' <span class="hl">' . scd_inline( $s['title_highlight'] ) . '</span>';
		}
		$trust = '';
		foreach ( (array) $s['trust'] as $t ) {
			$trust .= '<span>' . scd_icon( $t['icon'] ) . '<b>' . scd_inline( $t['strong'] ) . '</b> ' . scd_inline( $t['text'] ) . '</span>';
		}
		$card = '';
		if ( 'yes' === $s['card_show'] ) {
			$rows = '';
			foreach ( (array) $s['card_rows'] as $row ) {
				$rows .= '<div><dt>' . scd_inline( $row['label'] ) . '</dt><dd>' . scd_inline( $row['value'] ) . '</dd></div>';
			}
			$alt_url = 'custom' === $s['card_alt_type'] ? $this->url( $s['card_alt_url'] ) : scd_practice( 'booking_url' );
			$alt     = ( $alt_url && $s['card_alt_label'] ) ? '<p class="bookcard__alt"><a href="' . esc_url( $alt_url ) . '" rel="noopener">' . esc_html( $s['card_alt_label'] ) . '</a></p>' : '';
			$card    = '<div class="bookcard" data-tilt><span class="open-pill">' . scd_hours_status() . '</span>'
				. '<h2 id="' . esc_attr( $this->hid( 'card' ) ) . '">' . esc_html( $s['card_title'] ) . '</h2>'
				. ( $rows ? '<dl>' . $rows . '</dl>' : '' ) . $this->button( $s, 'card_btn' ) . $alt . '</div>';
		}
		$html  = '<section class="hero-dark hero-cc" aria-labelledby="' . esc_attr( $hid ) . '"><div class="wrap"><div>';
		$html .= '<h1 id="' . esc_attr( $hid ) . '">' . $title . '</h1>';
		$html .= $s['lead'] ? '<p class="lead">' . scd_inline( $s['lead'] ) . '</p>' : '';
		$html .= $this->buttons( $s, array( 'btn1', 'btn2' ), 'btn-row', ' data-sticky-anchor' );
		$html .= $trust ? '<div class="hero-trust">' . $trust . '</div>' : '';
		$html .= '</div><div class="hero-photo">' . $this->frame( $s, 'image', array( 'eager' => true, 'size' => 'full' ) ) . $card . '</div></div></section>';
		if ( 'yes' === $s['bar_show'] ) {
			$html .= '<div class="mbar">'
				. scd_button( array( 'type' => 'call', 'label' => $s['bar_call'], 'class' => 'btn--ghost-light' ) )
				. scd_button( array( 'type' => 'book', 'label' => $s['bar_book'], 'class' => 'btn--lime', 'icon' => '' ) )
				. '</div>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every part is escaped above.
	}
}
