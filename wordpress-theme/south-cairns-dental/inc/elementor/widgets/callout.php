<?php
/**
 * Callout band: a full-width lime, pale or red band with a short message, an optional large
 * badge (e.g. 000) and an optional call button.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Callout widget.
 */
class Callout extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-callout';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Callout band', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-alert';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Callout', 'south-cairns-dental' ) ) );
		$this->add_control(
			'tone',
			array(
				'label'   => __( 'Colour', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'brand',
				'options' => array(
					'brand' => __( 'Lime', 'south-cairns-dental' ),
					'info'  => __( 'Pale lime', 'south-cairns-dental' ),
					'alert' => __( 'Red (urgent)', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'badge', array( 'label' => __( 'Large badge (e.g. 000)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_control( 'call', array( 'label' => __( 'Add a call button', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control( 'call_label', array( 'label' => __( 'Button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call {phone}', 'condition' => array( 'call' => 'yes' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$hid   = $this->hid();
		$badge = $s['badge'] ? '<p class="callout__badge" aria-hidden="true">' . esc_html( $s['badge'] ) . '</p>' : '';
		$cls   = array( 'alert' => 'btn--alert', 'brand' => 'btn--lime', 'info' => '' );
		$btn   = 'yes' === $s['call'] ? scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => isset( $cls[ $s['tone'] ] ) ? $cls[ $s['tone'] ] : '' ) ) : '';
		echo '<section class="callout callout--' . esc_attr( $s['tone'] ) . '" aria-labelledby="' . esc_attr( $hid ) . '"><div class="wrap callout__inner">' . $badge // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. '<div><h2 id="' . esc_attr( $hid ) . '" class="callout__title">' . scd_inline( $s['title'] ) . '</h2>' . $this->rich( $s['text'] ) . '</div>' . $btn . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
