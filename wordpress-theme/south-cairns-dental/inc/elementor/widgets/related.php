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
		$r->add_control( 'page', array( 'label' => __( 'Treatment page', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => array( '' => __( '(custom card)', 'south-cairns-dental' ) ) + $this->page_options() ) );
		$r->add_control( 'c_title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'page' => '' ) ) );
		$r->add_control( 'c_text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'condition' => array( 'page' => '' ) ) );
		$r->add_control( 'c_image', array( 'label' => __( 'Image', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA, 'condition' => array( 'page' => '' ) ) );
		$r->add_control( 'c_link', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL, 'condition' => array( 'page' => '' ) ) );
		$this->add_control(
			'pages',
			array(
				'label'       => __( 'Treatments', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{ c_title || page }}',
				'description' => __( 'Choose a treatment page (image: its featured image; text: the one-line summary in its Treatment details box), or "custom card" to link any page, such as a campaign landing page.', 'south-cairns-dental' ),
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
			if ( empty( $row['page'] ) ) {
				$url = $this->url( $row['c_link'] ?? array() );
				if ( ! $url || empty( $row['c_title'] ) ) {
					continue;
				}
				$img    = $this->img( $row['c_image'] ?? array(), array( 'decorative' => true, 'size' => 'scd-card', 'img_class' => 'is-photo' ) );
				$items .= '<li class="related-card reveal" style="--i:' . (int) $i . '"><a href="' . esc_url( $url ) . '"><span class="related-card__media">' . $img . '</span>'
					. '<span class="related-card__body"><strong>' . esc_html( $row['c_title'] ) . '</strong><span>' . scd_inline( $row['c_text'] ?? '' ) . '</span></span></a></li>';
				continue;
			}
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
