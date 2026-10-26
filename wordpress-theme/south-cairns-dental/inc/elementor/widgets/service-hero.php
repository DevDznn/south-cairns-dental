<?php
/**
 * Treatment page hero: breadcrumbs (Home › Services › treatment), group label, heading,
 * introduction, booking or call buttons, live opening status and photo. The urgent tone gives
 * the red emergency treatment, a "Call now" button and an optional sticky call bar on phones.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Service hero widget.
 */
class Service_Hero extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-service-hero';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Treatment hero', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-banner';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Hero', 'south-cairns-dental' ) ) );
		$this->add_control(
			'tone',
			array(
				'label'   => __( 'Tone', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''       => __( 'Standard (book online + call)', 'south-cairns-dental' ),
					'urgent' => __( 'Urgent (red, call now)', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'kicker', array( 'label' => __( 'Group label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Everyday care' ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => __( 'Treatment name', 'south-cairns-dental' ) ) );
		$this->add_control( 'lead', array( 'label' => __( 'Introduction', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4 ) );
		$this->add_control( 'book_label', array( 'label' => __( 'Booking button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Book online', 'condition' => array( 'tone' => '' ) ) );
		$this->add_control( 'call_label', array( 'label' => __( 'Call button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call {phone}' ) );
		$this->add_control( 'link_label', array( 'label' => __( 'Extra text link', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'link_url', array( 'label' => __( 'Link', 'south-cairns-dental' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'status', array( 'label' => __( 'Show live opening status', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'sticky', array( 'label' => __( 'Sticky call bar on phones', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'condition' => array( 'tone' => 'urgent' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'crumbs_section', array( 'label' => __( 'Breadcrumbs', 'south-cairns-dental' ) ) );
		$this->add_control( 'crumb_parent', array( 'label' => __( 'Parent label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Services' ) );
		$this->add_control( 'crumb_parent_url', array( 'label' => __( 'Parent link (blank = Services page)', 'south-cairns-dental' ), 'type' => Controls_Manager::URL ) );
		$this->add_control( 'crumb_current', array( 'label' => __( 'This page (blank = page title)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->end_controls_section();

		$this->start_controls_section( 'image_section', array( 'label' => __( 'Photo', 'south-cairns-dental' ) ) );
		$this->image_controls( 'image', array( 'ratio' => '3 / 2' ) );
		$this->end_controls_section();

		$this->heading_style_controls( 'h1' );
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$urgent = 'urgent' === $s['tone'];
		$parent = $this->url( $s['crumb_parent_url'] );
		$crumbs = scd_crumbs( array( array( $s['crumb_parent'], $parent ? $parent : scd_page_url( 'services' ) ) ), $s['crumb_current'] ? $s['crumb_current'] : get_the_title() );

		if ( $urgent ) {
			$btns = scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => 'btn--alert btn--lg' ) );
		} else {
			$btns = scd_button( array( 'type' => 'book', 'label' => $s['book_label'], 'class' => 'btn--lime btn--lg' ) )
				. scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => 'btn--ghost btn--lg' ) );
		}
		$link = $this->url( $s['link_url'] );
		if ( $link && $s['link_label'] ) {
			$btns .= '<a class="textlink" href="' . esc_url( $link ) . '">' . esc_html( $s['link_label'] ) . '</a>';
		}
		$html  = '<section class="phero svc-hero' . ( $urgent ? ' svc-hero--urgent er-hero' : '' ) . '"><div class="wrap">' . $crumbs;
		$html .= '<div class="svc-hero__grid"><div class="svc-hero__text">';
		$html .= $s['kicker'] ? '<p class="svc-hero__kicker">' . esc_html( $s['kicker'] ) . '</p>' : '';
		$html .= '<h1>' . nl2br( scd_inline( $s['title'] ), false ) . '</h1>';
		$html .= $s['lead'] ? '<p class="lead">' . scd_inline( $s['lead'] ) . '</p>' : '';
		$html .= '<div class="btn-row"' . ( $urgent ? ' data-sticky-anchor' : '' ) . '>' . $btns . '</div>';
		$html .= 'yes' === $s['status'] ? '<p class="svc-hero__status">' . scd_hours_status() . '</p>' : '';
		$html .= '</div><div class="svc-hero__media">' . $this->frame( $s, 'image', array( 'eager' => true ) ) . '</div></div></div></section>';
		if ( $urgent && 'yes' === $s['sticky'] ) {
			$html .= '<div class="er-sticky">' . scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => 'btn--alert btn--lg' ) ) . '</div>';
		}
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- parts escaped above.
	}
}
