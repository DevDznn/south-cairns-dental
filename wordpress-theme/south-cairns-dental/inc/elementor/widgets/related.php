<?php
/**
 * Related treatments: photo cards for chosen treatment pages (image, name and one-line
 * summary come from each treatment page).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Related treatments widget.
 */
class Related extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-related';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Related treatments', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Treatment page choices.
	 *
	 * @return string[]
	 */
	protected function page_options() {
		$opts = array();
		foreach ( scd_service_pages() as $page ) {
			$opts[ (string) $page->ID ] = get_the_title( $page );
		}
		return $opts;
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls(
			array(
				'layout'  => 'row',
				'heading' => 'Related treatments',
				'intro'   => 'Not sure which one you need? Your dentist will talk you through the options at your appointment.',
			)
		);
		$this->start_controls_section( 'content', array( 'label' => __( 'Treatments', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'page', array( 'label' => __( 'Treatment page', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => $this->page_options() ) );
		$this->add_control(
			'pages',
			array(
				'label'       => __( 'Treatments', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'description' => __( 'Image: the page’s featured image. Text: the one-line summary in its Treatment details box.', 'south-cairns-dental' ),
			)
		);
		$this->end_controls_section();
		$this->section_controls( array( 'size' => 'tight' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = '';
		foreach ( (array) $s['pages'] as $i => $row ) {
			$d = scd_service_data( (int) $row['page'] );
			if ( ! $d || 'publish' !== get_post_status( $d['id'] ) ) {
				continue;
			}
			$items .= '<li class="related-card reveal" style="--i:' . (int) $i . '"><a href="' . esc_url( $d['url'] ) . '"><span class="related-card__media">' . scd_service_thumb( $d ) . '</span>'
				. '<span class="related-card__body"><strong>' . esc_html( $d['title'] ) . '</strong><span>' . esc_html( $d['blurb'] ? $d['blurb'] : $d['card'] ) . '</span></span></a></li>';
		}
		echo $this->section( $s, '<ul class="related-cards">' . $items . '</ul>', 'sec--related' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
