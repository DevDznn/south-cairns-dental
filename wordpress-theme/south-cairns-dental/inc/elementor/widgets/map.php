<?php
/**
 * Map: a Google Maps embed of the practice (no API key) with the address, directions and
 * "open in Google Maps" always visible beneath it, so the details show even if the map can't load.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

defined( 'ABSPATH' ) || exit;

/**
 * Map widget.
 */
class Map extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-map';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Map', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-google-maps';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls( array( 'heading' => 'Find us on Stokes Street', 'layouts' => array( 'stacked', 'hidden' ), 'buttons' => false ) );
		$this->section_controls( array( 'bg' => 'mist' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		echo $this->section( $this->get_settings_for_display(), scd_map_card() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
