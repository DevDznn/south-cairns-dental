<?php
/**
 * New patient offer: what's included, the two prices, terms, booking buttons and the flyer.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Offer widget.
 */
class Offer extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-offer';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Special offer', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Offer', 'south-cairns-dental' ) ) );
		$this->add_control( 'kicker', array( 'label' => __( 'Small line above the heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'New patient special offer' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'Your first check-up, sorted' ) );
		$this->add_control( 'items', array( 'label' => __( 'What’s included (one per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 6 ) );
		$this->add_control( 'price1', array( 'label' => __( 'First price', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'separator' => 'before' ) );
		$this->add_control( 'price1_note', array( 'label' => __( 'First price note', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'price2', array( 'label' => __( 'Second price', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'price2_note', array( 'label' => __( 'Second price note', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'terms', array( 'label' => __( 'Terms', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->button_controls( 'btn1', __( 'First button', 'south-cairns-dental' ), array( 'type' => 'book', 'label' => 'Book online', 'style' => 'forest' ) );
		$this->button_controls( 'btn2', __( 'Second button', 'south-cairns-dental' ), array( 'type' => 'call', 'label' => 'Call {phone}', 'style' => 'ghost' ) );
		$this->end_controls_section();
		$this->start_controls_section( 'image_section', array( 'label' => __( 'Image', 'south-cairns-dental' ) ) );
		$this->image_controls( 'image', array( 'ratio' => '1 / 1', 'corner' => 'corner-tr' ) );
		$this->end_controls_section();
		$this->section_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$hid = $this->hid();
		$lis = '';
		foreach ( $this->lines( $s['items'] ) as $line ) {
			$lis .= '<li>' . scd_inline( $line ) . '</li>';
		}
		$prices = '';
		foreach ( array( 'price1', 'price2' ) as $p ) {
			if ( $s[ $p ] ) {
				$prices .= '<div><strong>' . esc_html( $s[ $p ] ) . '</strong><span>' . scd_inline( $s[ $p . '_note' ] ) . '</span></div>';
			}
		}
		$html = '<div class="offer reveal"><div class="offer__body">'
			. ( $s['kicker'] ? '<p class="offer__kicker">' . esc_html( $s['kicker'] ) . '</p>' : '' )
			. '<h2 class="offer__title" id="' . esc_attr( $hid ) . '">' . scd_inline( $s['title'] ) . '</h2>'
			. ( $lis ? '<ul class="ticks offer__list">' . $lis . '</ul>' : '' )
			. ( $prices ? '<div class="offer__prices">' . $prices . '</div>' : '' )
			. ( $s['terms'] ? '<p class="small offer__terms">' . scd_inline( $s['terms'] ) . '</p>' : '' )
			. $this->buttons( $s, array( 'btn1', 'btn2' ) ) . '</div>'
			. '<div class="offer__media">' . $this->frame( $s, 'image' ) . '</div></div>';
		echo $this->open_section( $s, '', $hid ) . '<div class="wrap">' . $html . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
