<?php
/**
 * Homepage service cards with category filter chips. Cards are edited here, or can be
 * filled automatically from the treatment pages (featured image, category, card description).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Services grid widget.
 */
class Services_Grid extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-services-grid';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Service cards', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-posts-grid';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls( array( 'layout' => 'row', 'heading' => 'Dental care for the whole family' ) );

		$this->start_controls_section( 'content', array( 'label' => __( 'Cards', 'south-cairns-dental' ) ) );
		$this->add_control(
			'source',
			array(
				'label'   => __( 'Cards', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'manual',
				'options' => array(
					'manual' => __( 'Edit the cards below', 'south-cairns-dental' ),
					'auto'   => __( 'From the treatment pages', 'south-cairns-dental' ),
				),
			)
		);
		$this->add_control( 'chips', array( 'label' => __( 'Show category filter', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'chips_all', array( 'label' => __( '"All" filter label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'All treatments', 'condition' => array( 'chips' => 'yes' ) ) );
		$this->add_control( 'more_label', array( 'label' => __( 'Card link label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Learn more' ) );

		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Image', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'category', array( 'label' => __( 'Category', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => array_combine( scd_service_categories(), scd_service_categories() ), 'default' => 'Preventive' ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'text', array( 'label' => __( 'Description', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->repeater_link( $r );
		$r->add_control( 'more', array( 'label' => __( 'Link label (blank = default)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'urgent', array( 'label' => __( 'Urgent (red, wide card)', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Cards', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ title }}}',
				'condition'   => array( 'source' => 'manual' ),
			)
		);
		$this->end_controls_section();

		$this->section_controls( array( 'bg' => 'fade' ) );
		$this->heading_style_controls();
	}

	/**
	 * Cards from treatment pages.
	 *
	 * @return array[]
	 */
	protected function auto_items() {
		$items = array();
		foreach ( scd_service_pages() as $page ) {
			$d       = scd_service_data( $page );
			$items[] = array(
				'image'    => array( 'id' => $d['thumb_id'], 'url' => '' ),
				'category' => $d['category'] ? $d['category'] : ( $d['urgent'] ? 'Urgent' : '' ),
				'title'    => $d['title'],
				'text'     => $d['card'],
				'link'     => array( 'url' => $d['url'] ),
				'more'     => '',
				'urgent'   => $d['urgent'] ? 'yes' : '',
			);
		}
		// Urgent care last, as on the static site.
		usort(
			$items,
			function ( $a, $b ) {
				return (int) ( 'yes' === $a['urgent'] ) - (int) ( 'yes' === $b['urgent'] );
			}
		);
		return $items;
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$items = 'auto' === $s['source'] ? $this->auto_items() : (array) $s['items'];
		$cats  = array();
		$cards = '';
		foreach ( $items as $i => $it ) {
			$cat = (string) $it['category'];
			if ( $cat && ! in_array( $cat, $cats, true ) ) {
				$cats[] = $cat;
			}
			$urgent = 'yes' === $it['urgent'];
			$more   = $it['more'] ? $it['more'] : $s['more_label'];
			$img    = $this->img( $it['image'], array( 'decorative' => true, 'size' => 'scd-card', 'img_class' => 'is-photo' ) );
			$cards .= '<li class="svc-card' . ( $urgent ? ' svc-card--urgent' : '' ) . ' reveal" style="--i:' . ( $urgent ? 2 : $i % 4 ) . '" data-cat="' . esc_attr( $cat ) . '">'
				. '<a href="' . esc_url( $this->url( $it['link'] ) ) . '"><span class="svc-card__media">' . $img . '</span>'
				. '<span class="svc-card__body"><span class="svc-card__cat">' . esc_html( $cat ) . '</span><strong>' . scd_inline( $it['title'] ) . '</strong>'
				. '<span class="svc-card__desc">' . scd_inline( $it['text'] ) . '</span><span class="svc-card__more">' . esc_html( $more ) . ' ' . scd_ui_icon( 'arrow' ) . '</span></span></a></li>';
		}
		$chips = '';
		if ( 'yes' === $s['chips'] && count( $cats ) > 1 ) {
			$order = array_values( array_intersect( scd_service_categories(), $cats ) );
			$order = array_merge( $order, array_diff( $cats, $order ) );
			$chips = '<button type="button" class="chip" aria-pressed="true" data-filter="all">' . esc_html( $s['chips_all'] ) . '</button>';
			foreach ( $order as $c ) {
				$chips .= '<button type="button" class="chip" aria-pressed="false" data-filter="' . esc_attr( $c ) . '">' . esc_html( $c ) . '</button>';
			}
			$chips = '<div class="chips" role="group" aria-label="' . esc_attr__( 'Filter treatments', 'south-cairns-dental' ) . '">' . $chips . '</div>';
		}
		$grid = $chips . '<ul class="svc-grid" data-filter-grid>' . $cards . '</ul><p class="svc-grid__status visually-hidden" role="status" aria-live="polite"></p>';
		echo $this->section( $s, $grid ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
