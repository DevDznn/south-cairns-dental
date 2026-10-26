<?php
/**
 * Contact-at-a-glance strip (four items on a dark band).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Info strip widget.
 */
class Info_Strip extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-info-strip';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Contact strip', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-info-box';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Items', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => 'phone' ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'description' => __( '{phone} shows the practice phone number.', 'south-cairns-dental' ) ) );
		$r->add_control(
			'link_type',
			array(
				'label'   => __( 'Text links to', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'none',
				'options' => array(
					'none'   => __( 'Nothing', 'south-cairns-dental' ),
					'call'   => __( 'Call the practice', 'south-cairns-dental' ),
					'map'    => __( 'Google Maps', 'south-cairns-dental' ),
					'custom' => __( 'Custom link', 'south-cairns-dental' ),
				),
			)
		);
		$r->add_control( 'link', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL, 'condition' => array( 'link_type' => 'custom' ) ) );
		$r->add_control( 'urgent', array( 'label' => __( 'Urgent (red title)', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control( 'items', array( 'label' => __( 'Items', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ title }}}' ) );
		$this->end_controls_section();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$out = '';
		foreach ( (array) $s['items'] as $it ) {
			$text = esc_html( str_replace( '{phone}', scd_practice( 'phone_display' ), $it['text'] ) );
			switch ( $it['link_type'] ) {
				case 'call':
					$url = 'tel:' . scd_practice( 'phone_tel' );
					break;
				case 'map':
					$url = scd_maps_link();
					break;
				case 'custom':
					$url = $this->url( $it['link'] );
					break;
				default:
					$url = '';
			}
			$rel   = 'map' === $it['link_type'] ? ' rel="noopener"' : '';
			$text  = $url ? '<a href="' . esc_url( $url ) . '"' . $rel . '>' . $text . '</a>' : '<span>' . $text . '</span>';
			$out  .= '<div class="infostrip__item' . ( 'yes' === $it['urgent'] ? ' infostrip__item--urgent' : '' ) . '">' . scd_icon( $it['icon'] ) . '<div><strong>' . esc_html( $it['title'] ) . '</strong>' . $text . '</div></div>';
		}
		echo '<section class="infostrip" aria-label="' . esc_attr__( 'Contact at a glance', 'south-cairns-dental' ) . '"><div class="wrap">' . $out . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
