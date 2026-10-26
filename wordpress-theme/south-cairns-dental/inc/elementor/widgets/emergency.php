<?php
/**
 * Emergency band: what counts as an emergency, beside a call card with the practice number.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Emergency band widget.
 */
class Emergency extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-emergency';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Emergency band', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-alert';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Emergency band', 'south-cairns-dental' ) ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'Toothache, a broken tooth or swelling?' ) );
		$this->add_control( 'lead', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4 ) );
		$this->add_control( 'list', array( 'label' => __( 'Common emergencies (one per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 5 ) );
		$this->add_control( 'list_label', array( 'label' => __( 'List name (screen readers)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Common dental emergencies' ) );
		$this->add_control( 'call_heading', array( 'label' => __( 'Call card label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call South Cairns Dental', 'separator' => 'before' ) );
		$this->add_control( 'call_label', array( 'label' => __( 'Button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call now' ) );
		$this->add_control( 'link_label', array( 'label' => __( 'Link label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'First aid while you wait' ) );
		$this->add_control( 'link', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'note', array( 'label' => __( 'Small print', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->end_controls_section();
		$this->section_controls( array( 'size' => 'tight' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$hid = $this->hid();
		$lis = '';
		foreach ( $this->lines( $s['list'] ) as $line ) {
			$lis .= '<li>' . scd_inline( $line ) . '</li>';
		}
		$tel  = scd_practice( 'phone_tel' );
		$link = $this->url( $s['link'] );
		$html = '<div class="er-band reveal"><div><h2 id="' . esc_attr( $hid ) . '" class="scd-head-title">' . scd_inline( $s['heading'] ) . '</h2>'
			. ( $s['lead'] ? '<p class="lead mb-0">' . scd_inline( $s['lead'] ) . '</p>' : '' )
			. ( $lis ? '<ul aria-label="' . esc_attr( $s['list_label'] ) . '">' . $lis . '</ul>' : '' ) . '</div>'
			. '<div class="er-band__call"><p class="er-call__label mb-0">' . esc_html( $s['call_heading'] ) . '</p>'
			. '<a class="er-call__number" href="tel:' . esc_attr( $tel ) . '">' . esc_html( scd_practice( 'phone_display' ) ) . '</a>'
			. scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => 'btn--alert' ) )
			. ( $link && $s['link_label'] ? '<a class="textlink" href="' . esc_url( $link ) . '">' . esc_html( $s['link_label'] ) . '</a>' : '' )
			. ( $s['note'] ? '<p class="small muted mb-0">' . scd_inline( $s['note'] ) . '</p>' : '' ) . '</div></div>';
		echo $this->open_section( $s, '', $hid ) . '<div class="wrap">' . $html . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
