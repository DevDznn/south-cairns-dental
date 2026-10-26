<?php
/**
 * Team cards: the homepage dentist cards (portrait, name, role, qualifications, link to their
 * profile) or the compact support-team cards on the team page. Portraits always use the same
 * 4:5 frame so they line up whatever the original photo's size.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Team widget.
 */
class Team extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-team';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Team cards', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls( array( 'layout' => 'row', 'heading' => 'Meet your dentists' ) );
		$this->start_controls_section( 'content', array( 'label' => __( 'People', 'south-cairns-dental' ) ) );
		$this->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards'   => __( 'Large cards (dentists)', 'south-cairns-dental' ),
					'support' => __( 'Small cards (support team)', 'south-cairns-dental' ),
				),
			)
		);
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Portrait', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA, 'description' => __( 'Shown at 4:5 (portrait). Leave empty for the branded placeholder.', 'south-cairns-dental' ) ) );
		$r->add_control( 'pos', array( 'label' => __( 'Focal point', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => '50% 22%' ) );
		$r->add_control( 'name', array( 'label' => __( 'Name', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'role', array( 'label' => __( 'Role', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'quals', array( 'label' => __( 'Qualifications', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->repeater_link( $r, 'link', __( 'Link (e.g. their profile)', 'south-cairns-dental' ) );
		$r->add_control( 'anchor', array( 'label' => __( 'Anchor ID', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'people', array( 'label' => __( 'People', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ name }}}' ) );
		$this->end_controls_section();
		$this->section_controls();
		$this->heading_style_controls();
	}

	/**
	 * Portrait frame.
	 *
	 * @param array $p Person.
	 * @return string
	 */
	protected function portrait( $p ) {
		$has = ! empty( $p['image']['id'] ) || ! empty( $p['image']['url'] );
		$alt = trim( $p['name'] . ( $p['role'] ? ', ' . $p['role'] : '' ) );
		if ( $has ) {
			$img = $this->img( $p['image'], array( 'alt' => $alt, 'size' => 'scd-portrait' ) );
			$cls = 'frame portrait frame--corner';
		} else {
			$img = scd_img_tag( array( 'url' => scd_theme_image( 'team/portrait-placeholder.svg' ), 'decorative' => true, 'width' => 800, 'height' => 1000 ) );
			$cls = 'frame portrait portrait--placeholder frame--corner';
		}
		$pos = $p['pos'] ? ';--pos:' . $p['pos'] : '';
		return '<figure class="' . esc_attr( $cls ) . '" style="' . esc_attr( '--ratio:4 / 5' . $pos ) . '">' . $img . '</figure>';
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$out = '';
		foreach ( (array) $s['people'] as $i => $p ) {
			$url = $this->url( $p['link'] );
			$id  = $p['anchor'] ? ' id="' . esc_attr( sanitize_title( $p['anchor'] ) ) . '"' : '';
			if ( 'support' === $s['style'] ) {
				$out .= '<li class="member"' . $id . '>' . $this->portrait( $p ) . '<h3>' . esc_html( $p['name'] ) . '</h3><p class="member__role">' . esc_html( $p['role'] ) . '</p></li>';
				continue;
			}
			$inner = $this->portrait( $p ) . '<h3>' . esc_html( $p['name'] ) . '</h3><p>' . esc_html( $p['role'] ) . '</p>' . ( $p['quals'] ? '<p class="member__quals">' . esc_html( $p['quals'] ) . '</p>' : '' );
			$out  .= '<li class="team-card reveal" style="--i:' . (int) $i . '"' . $id . '>' . ( $url ? '<a href="' . esc_url( $url ) . '">' . $inner . '</a>' : $inner ) . '</li>';
		}
		$list = 'support' === $s['style'] ? '<ul class="team team--support">' . $out . '</ul>' : '<ul class="team-cards">' . $out . '</ul>';
		echo $this->section( $s, $list ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
