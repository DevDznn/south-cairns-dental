<?php
/**
 * Text section: heading beside the text, text beside a ticked panel ("when it may be
 * considered"), or a single narrow column of text (policies).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Text widget.
 */
class Text extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-text';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Text section', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-text';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Text', 'south-cairns-dental' ) ) );
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split',
				'options' => array(
					'split'  => __( 'Heading on the left, text on the right', 'south-cairns-dental' ),
					'aside'  => __( 'Text with a ticked panel beside it', 'south-cairns-dental' ),
					'narrow' => __( 'One narrow column', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control( 'body', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_control( 'aside_title', array( 'label' => __( 'Panel heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'condition' => array( 'layout' => 'aside' ) ) );
		$this->add_control( 'aside_items', array( 'label' => __( 'Panel points (one per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 5, 'condition' => array( 'layout' => 'aside' ) ) );
		$this->end_controls_section();
		$this->section_controls();
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s    = $this->get_settings_for_display();
		$hid  = $this->hid();
		$has  = '' !== trim( (string) $s['heading'] );
		$h    = $has ? '<h2 id="' . esc_attr( $hid ) . '" class="scd-head-title">' . nl2br( scd_inline( $s['heading'] ), false ) . '</h2>' : '';
		$body = $this->rich( $s['body'] );
		switch ( $s['layout'] ) {
			case 'aside':
				$lis = '';
				foreach ( $this->lines( $s['aside_items'] ) as $line ) {
					$lis .= '<li>' . scd_inline( $line ) . '</li>';
				}
				$html = '<div class="wrap split split--7-5 split--top"><div class="prose">' . $h . $body . '</div><div class="panel panel--white svc-aside reveal"><h3>' . scd_inline( $s['aside_title'] ) . '</h3><ul class="ticks">' . $lis . '</ul></div></div>';
				break;
			case 'narrow':
				$html = '<div class="wrap wrap-text prose">' . $h . $body . '</div>';
				break;
			default:
				$html = '<div class="wrap split split--5-7 split--top">' . $h . '<div class="prose">' . $body . '</div></div>';
		}
		echo $this->open_section( $s, '', $has ? $hid : '' ) . $html . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
