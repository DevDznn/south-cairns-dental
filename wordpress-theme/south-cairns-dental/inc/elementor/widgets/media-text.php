<?php
/**
 * Image beside text: the homepage introduction (with badge and counters), the About story
 * (with a list of questions) and the nervous-patients section (with an icon list).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Media and text widget.
 */
class Media_Text extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-media-text';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Image and text', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-image-box';
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
				'default' => 'editorial',
				'options' => array(
					'editorial' => __( 'Equal columns (introduction)', 'south-cairns-dental' ),
					'about'     => __( 'Equal columns, photo with shadow', 'south-cairns-dental' ),
					'comfort'   => __( 'Narrower image column (illustration)', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'kicker', array( 'label' => __( 'Small line above the heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2, 'default' => __( 'Heading', 'south-cairns-dental' ) ) );
		$this->add_control( 'body', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_control(
			'body_style',
			array(
				'label'   => __( 'Text style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'prose',
				'options' => array(
					'prose' => __( 'Paragraphs (first one larger)', 'south-cairns-dental' ),
					'lead'  => __( 'Large introduction', 'south-cairns-dental' ),
				),
			)
		);
		$this->button_controls( 'btn1', __( 'Button', 'south-cairns-dental' ), array( 'type' => 'none', 'style' => 'forest' ) );
		$this->button_controls( 'btn2', __( 'Second button / link', 'south-cairns-dental' ), array( 'type' => 'none', 'style' => 'textlink' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'list_section', array( 'label' => __( 'List', 'south-cairns-dental' ) ) );
		$this->add_control(
			'list_style',
			array(
				'label'   => __( 'List style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => '',
				'options' => array(
					''        => __( 'No list', 'south-cairns-dental' ),
					'asks'    => __( 'Large questions with a lime edge', 'south-cairns-dental' ),
					'comfort' => __( 'Icon, title and text', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'list_label', array( 'label' => __( 'List name for screen readers', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'condition' => array( 'list_style' => 'asks' ) ) );
		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => 'chat' ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'list', array( 'label' => __( 'Items', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ title }}}', 'condition' => array( 'list_style!' => '' ) ) );
		$this->end_controls_section();

		$this->start_controls_section( 'stats_section', array( 'label' => __( 'Counters', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'number', array( 'label' => __( 'Number', 'south-cairns-dental' ), 'type' => Controls_Manager::NUMBER ) );
		$r->add_control( 'label', array( 'label' => __( 'Label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->add_control( 'stats', array( 'label' => __( 'Counters', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ number }}} {{{ label }}}' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'image_section', array( 'label' => __( 'Image', 'south-cairns-dental' ) ) );
		$this->image_controls( 'image', array( 'ratio' => '5 / 6', 'corner' => 'corner-tr' ) );
		$this->add_control( 'badge_title', array( 'label' => __( 'Badge heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'separator' => 'before' ) );
		$this->add_control( 'badge_text', array( 'label' => __( 'Badge text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->end_controls_section();

		$this->section_controls( array( 'bg' => 'mist' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$layout = $s['layout'];
		$hid    = $this->hid();

		$badge = '';
		if ( $s['badge_title'] || $s['badge_text'] ) {
			$badge = '<div class="editorial__badge"><strong>' . scd_inline( $s['badge_title'] ) . '</strong><span>' . scd_inline( $s['badge_text'] ) . '</span></div>';
		}
		$frame = $this->frame( $s, 'image' );
		$media = 'comfort' === $layout ? '<div class="reveal">' . $frame . '</div>' : '<div class="editorial__media reveal">' . $frame . $badge . '</div>';

		$text  = $s['kicker'] ? '<p class="kicker">' . scd_inline( $s['kicker'] ) . '</p>' : '';
		$text .= '<h2 id="' . esc_attr( $hid ) . '" class="scd-head-title">' . nl2br( scd_inline( $s['heading'] ), false ) . '</h2>';
		if ( 'lead' === $s['body_style'] ) {
			$text .= str_replace( '<p>', '<p class="lead">', $this->rich( $s['body'] ) );
		} elseif ( $s['body'] ) {
			$text .= '<div class="prose">' . $this->rich( $s['body'] ) . '</div>';
		}

		if ( 'asks' === $s['list_style'] && $s['list'] ) {
			$lis = '';
			foreach ( $s['list'] as $it ) {
				$lis .= '<li>' . scd_inline( $it['title'] ) . '</li>';
			}
			$text .= '<ul class="asks"' . ( $s['list_label'] ? ' aria-label="' . esc_attr( $s['list_label'] ) . '"' : '' ) . '>' . $lis . '</ul>';
		} elseif ( 'comfort' === $s['list_style'] && $s['list'] ) {
			$lis = '';
			foreach ( $s['list'] as $it ) {
				$lis .= '<li><span class="icon-wrap">' . scd_icon( $it['icon'] ) . '</span><div><strong>' . scd_inline( $it['title'] ) . '</strong><span>' . scd_inline( $it['text'] ) . '</span></div></li>';
			}
			$text .= '<ul class="comfort__list">' . $lis . '</ul>';
		}

		if ( $s['stats'] ) {
			$dl = '';
			foreach ( $s['stats'] as $st ) {
				$num = (int) $st['number'];
				$dl .= '<div><dt>' . scd_inline( $st['label'] ) . '</dt><dd data-count="' . esc_attr( $num ) . '">' . esc_html( $num ) . '</dd></div>';
			}
			$text .= '<dl class="stats">' . $dl . '</dl>';
		}
		$text .= $this->buttons( $s, array( 'btn1', 'btn2' ), 'comfort' === $layout ? 'btn-row' : 'btn-row mt-6' );

		$wrap = 'comfort' === $layout ? 'wrap comfort' : ( 'about' === $layout ? 'wrap editorial editorial--about' : 'wrap editorial' );
		echo $this->open_section( $s, '', $hid ) . '<div class="' . esc_attr( $wrap ) . '">' . $media . '<div>' . $text . '</div></div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
