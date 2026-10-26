<?php
/**
 * Booking band (green panel with booking and call buttons) or the urgent strip
 * (white panel with a red edge, call and emergency buttons).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Call-to-action widget.
 */
class Cta extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-cta';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Booking band', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-call-to-action';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Booking band', 'south-cairns-dental' ) ) );
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'band',
				'options' => array(
					'band'   => __( 'Green booking band', 'south-cairns-dental' ),
					'urgent' => __( 'Urgent strip (red edge)', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'Ready to book?' ) );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4, 'description' => __( 'Links can be added with HTML.', 'south-cairns-dental' ) ) );
		$this->button_controls( 'btn1', __( 'First button', 'south-cairns-dental' ), array( 'type' => 'book', 'label' => 'Book online', 'style' => 'lime' ) );
		$this->button_controls( 'btn2', __( 'Second button', 'south-cairns-dental' ), array( 'type' => 'call', 'label' => 'Call {phone}', 'style' => 'ghost-light' ) );
		$this->add_control( 'ask_label', array( 'label' => __( 'Text link under the text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'separator' => 'before' ) );
		$this->add_control( 'ask_url', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL ) );
		$this->end_controls_section();
		$this->section_controls( array( 'size' => 'tight' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$hid   = $this->hid();
		$ask   = $this->url( $s['ask_url'] );
		$ask   = ( $ask && $s['ask_label'] ) ? '<a class="textlink textlink--light" href="' . esc_url( $ask ) . '">' . esc_html( $s['ask_label'] ) . '</a>' : '';
		$text  = '<div><h2 id="' . esc_attr( $hid ) . '" class="scd-head-title">' . scd_inline( $s['heading'] ) . '</h2>' . ( $s['text'] ? '<p>' . scd_inline( $s['text'] ) . '</p>' : '' ) . $ask . '</div>';
		$btns  = $this->buttons( $s, array( 'btn1', 'btn2' ) );
		$panel = 'urgent' === $s['style'] ? 'urgent-strip' : 'cta-band';
		echo $this->open_section( $s, '', $hid ) . '<div class="wrap"><div class="' . esc_attr( $panel ) . '">' . $text . $btns . '</div></div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
