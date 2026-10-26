<?php
/**
 * Treatment directory, filled automatically from the treatment pages: either the compact
 * directory (About, Blog) or the detailed group lists of the Services overview page.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Service directory widget.
 */
class Service_Directory extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-service-directory';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Treatment directory', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-bullet-list';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls( array( 'layout' => 'row' ) );
		$this->start_controls_section( 'content', array( 'label' => __( 'Treatments', 'south-cairns-dental' ) ) );
		$this->add_control(
			'style',
			array(
				'label'       => __( 'Style', 'south-cairns-dental' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'directory',
				'options'     => array(
					'directory' => __( 'Compact directory', 'south-cairns-dental' ),
					'lists'     => __( 'Detailed lists by group', 'south-cairns-dental' ),
				),
				'description' => __( 'Treatments come from pages with "This page is a treatment" ticked (Treatment details box). Order them with each page’s Order attribute.', 'south-cairns-dental' ),
			)
		);
		$r = new Repeater();
		$r->add_control( 'group', array( 'label' => __( 'Group', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_service_groups(), 'default' => 'everyday' ) );
		$r->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Introduction', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control(
			'groups',
			array(
				'label'       => __( 'Groups', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ heading }}}',
				'condition'   => array( 'style' => 'lists' ),
			)
		);
		$this->end_controls_section();
		$this->section_controls( array( 'bg' => 'mist' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s = $this->get_settings_for_display();
		if ( 'lists' === $s['style'] ) {
			$html = '';
			foreach ( (array) $s['groups'] as $i => $g ) {
				$style = 0 === $i ? ' style="border-top:0;padding-top:0"' : '';
				$html .= '<div class="svc-section"' . $style . '><div><h2>' . esc_html( $g['heading'] ) . '</h2>' . $this->paras( $g['text'], 'muted' ) . '</div>' . scd_service_list( $g['group'] ) . '</div>';
			}
		} else {
			$html = scd_service_directory();
		}
		echo $this->section( $s, $html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
