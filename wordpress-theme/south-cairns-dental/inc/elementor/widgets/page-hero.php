<?php
/**
 * Inner page hero: breadcrumbs, heading, introduction, buttons and an optional side panel,
 * note, jump links, image or portrait strip.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Page hero widget.
 */
class Page_Hero extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-page-hero';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Page hero', 'south-cairns-dental' );
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
			'variant',
			array(
				'label'   => __( 'Style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'Dark green', 'south-cairns-dental' ),
					'band'  => __( 'Dark green band (Services)', 'south-cairns-dental' ),
					'plain' => __( 'Plain (white)', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'   => __( 'Layout', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'split-7-5',
				'options' => array(
					'single'      => __( 'One column', 'south-cairns-dental' ),
					'narrow'      => __( 'One narrow column', 'south-cairns-dental' ),
					'split-7-5'   => __( 'Text with side panel (7/5)', 'south-cairns-dental' ),
					'split-6-6'   => __( 'Text with side panel (6/6)', 'south-cairns-dental' ),
					'title-split' => __( 'Heading left, introduction right', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'kicker', array( 'label' => __( 'Small line above the heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => __( 'Page heading', 'south-cairns-dental' ) ) );
		$this->add_control( 'lead', array( 'label' => __( 'Introduction', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4 ) );
		$this->button_controls( 'btn1', __( 'First button', 'south-cairns-dental' ), array( 'type' => 'none', 'label' => 'Book online', 'style' => 'lime' ) );
		$this->button_controls( 'btn2', __( 'Second button', 'south-cairns-dental' ), array( 'type' => 'none', 'label' => 'Call {phone}', 'style' => 'ghost' ) );
		$this->button_controls( 'link', __( 'Text link', 'south-cairns-dental' ), array( 'type' => 'none', 'style' => 'textlink' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'crumbs_section', array( 'label' => __( 'Breadcrumbs', 'south-cairns-dental' ) ) );
		$this->add_control( 'crumbs_show', array( 'label' => __( 'Show breadcrumbs', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->repeater_link( $r );
		$this->add_control(
			'crumbs',
			array(
				'label'       => __( 'Links between Home and this page', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ label }}}',
				'condition'   => array( 'crumbs_show' => 'yes' ),
			)
		);
		$this->add_control(
			'crumb_current',
			array(
				'label'       => __( 'This page (blank = page title)', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXT,
				'condition'   => array( 'crumbs_show' => 'yes' ),
			)
		);
		$this->add_control(
			'crumbs_inside',
			array(
				'label'        => __( 'Breadcrumbs inside the text column', 'south-cairns-dental' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition'    => array( 'crumbs_show' => 'yes' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section( 'aside_section', array( 'label' => __( 'Side panel', 'south-cairns-dental' ) ) );
		$this->add_control(
			'aside',
			array(
				'label'   => __( 'Show', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''      => __( 'Nothing', 'south-cairns-dental' ),
					'panel' => __( 'White panel with text', 'south-cairns-dental' ),
					'note'  => __( 'Note with a lime edge', 'south-cairns-dental' ),
					'jump'  => __( 'Jump links', 'south-cairns-dental' ),
					'image' => __( 'Image', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'aside_text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG, 'condition' => array( 'aside' => array( 'panel', 'note' ) ) ) );
		$this->add_control( 'jump_label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Jump to', 'condition' => array( 'aside' => 'jump' ) ) );
		$r = new Repeater();
		$r->add_control( 'label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'anchor', array( 'label' => __( 'Anchor (e.g. #alex-bornsztejn)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'jump', array( 'label' => __( 'Links', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ label }}}', 'condition' => array( 'aside' => 'jump' ) ) );
		$this->image_controls( 'image', array( 'corner' => 'corner-tr' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'strip_section', array( 'label' => __( 'Portrait strip (below)', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Photo', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'name', array( 'label' => __( 'Name', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->repeater_link( $r );
		$this->add_control( 'strip', array( 'label' => __( 'Portraits', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ name }}}' ) );
		$this->end_controls_section();

		$this->heading_style_controls( 'h1' );
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s       = $this->get_settings_for_display();
		$variant = $s['variant'];
		$layout  = $s['layout'];
		$classes = 'phero' . ( $variant ? ' phero--' . $variant : '' ) . ( $s['strip'] ? ' phero--about' : '' );

		$crumbs = '';
		if ( 'yes' === $s['crumbs_show'] ) {
			$trail = array();
			foreach ( (array) $s['crumbs'] as $c ) {
				$trail[] = array( $c['label'], $this->url( $c['link'] ) );
			}
			$crumbs = scd_crumbs( $trail, $s['crumb_current'] ? $s['crumb_current'] : get_the_title() );
		}
		$inside = 'yes' === $s['crumbs_inside'];

		$btns = $this->buttons( $s, array( 'btn1', 'btn2' ) );
		$link = $this->button( $s, 'link' );
		$kick = $s['kicker'] ? '<p class="kicker">' . scd_inline( $s['kicker'] ) . '</p>' : '';
		$h1   = '<h1>' . nl2br( scd_inline( $s['title'] ), false ) . '</h1>';
		$tail = $btns . ( $link ? ( $btns || 'band' === $variant ? '<p class="mt-5 mb-0">' . $link . '</p>' : $link ) : '' );
		$lead = $s['lead'] ? '<p class="lead' . ( '' === $tail ? ' mb-0' : '' ) . '">' . scd_inline( $s['lead'] ) . '</p>' : '';

		$aside = '';
		switch ( $s['aside'] ) {
			case 'panel':
				$aside = '<div class="panel panel--white">' . $this->rich( $s['aside_text'] ) . '</div>';
				break;
			case 'note':
				$aside = $this->rich_block( $s['aside_text'], 'phero__aside mb-0' );
				break;
			case 'jump':
				$lis = '';
				foreach ( (array) $s['jump'] as $j ) {
					$lis .= '<li><a href="' . esc_url( $j['anchor'] ) . '">' . scd_inline( $j['label'] ) . '</a></li>';
				}
				$aside = '<nav class="jump" aria-label="' . esc_attr( $s['jump_label'] ) . '"><p class="jump__label">' . esc_html( $s['jump_label'] ) . '</p><ul>' . $lis . '</ul></nav>';
				break;
			case 'image':
				$aside = $this->frame( $s, 'image', array( 'eager' => true ) );
				break;
		}

		if ( 'title-split' === $layout ) {
			$body = ( $inside ? '' : $crumbs ) . '<div class="split split--7-5 split--top">' . ( $inside ? '<div>' . $crumbs . $kick . $h1 . '</div>' : $kick . $h1 ) . '<div>' . $lead . $tail . '</div></div>';
			$wrap = 'wrap';
		} elseif ( 0 === strpos( $layout, 'split-' ) && '' !== $aside ) {
			$text = '<div>' . ( $inside ? $crumbs : '' ) . $kick . $h1 . $lead . $tail . '</div>';
			if ( $inside ) {
				$body = $text . $aside;
				$wrap = 'wrap split split--' . substr( $layout, 6 );
			} else {
				$body = $crumbs . '<div class="split split--' . esc_attr( substr( $layout, 6 ) ) . '">' . $text . $aside . '</div>';
				$wrap = 'wrap';
			}
		} else {
			$body = $crumbs . $kick . $h1 . $lead . $tail;
			$wrap = 'narrow' === $layout ? 'wrap wrap-text' : 'wrap';
		}

		$strip = '';
		if ( $s['strip'] ) {
			$lis = '';
			foreach ( $s['strip'] as $i => $p ) {
				$url  = $this->url( $p['link'] );
				$img  = $this->img( $p['image'], array( 'decorative' => true, 'size' => 'scd-portrait', 'eager' => true ) );
				$name = '<span>' . esc_html( $p['name'] ) . '</span>';
				$lis .= '<li class="reveal" style="--i:' . (int) $i . '">' . ( $url ? '<a href="' . esc_url( $url ) . '">' . $img . $name . '</a>' : $img . $name ) . '</li>';
			}
			$strip = '<ul class="portrait-strip" aria-label="' . esc_attr__( 'Our team', 'south-cairns-dental' ) . '">' . $lis . '</ul>';
		}

		echo '<section class="' . esc_attr( $classes ) . '"><div class="' . esc_attr( $wrap ) . '">' . $body . $strip . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- parts escaped above.
	}
}
