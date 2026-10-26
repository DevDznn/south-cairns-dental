<?php
/**
 * Cards and lists: one widget for every repeated card or list pattern on the site, each style
 * rendering the static site's markup (concierge cards, features, payment cards, ways to book,
 * mention list, side-by-side cards, next links, numbered steps, facts, ticks, treatment info
 * cards, benefits columns, care list, blog teaser).
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Cards widget.
 */
class Cards extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-cards';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Cards and lists', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	/**
	 * Styles: key => label.
	 *
	 * @return string[]
	 */
	protected function styles() {
		return array(
			'concierge'   => __( 'Link cards with icon (What brings you in)', 'south-cairns-dental' ),
			'features'    => __( 'Icon points with a lime rule (dark sections)', 'south-cairns-dental' ),
			'pay'         => __( 'Icon cards (dark sections)', 'south-cairns-dental' ),
			'ways'        => __( 'Icon cards with a link (ways to book)', 'south-cairns-dental' ),
			'mention'     => __( 'Small white cards (title and text)', 'south-cairns-dental' ),
			'duo'         => __( 'Bordered cards side by side', 'south-cairns-dental' ),
			'nextlinks'   => __( 'Link cards with arrow (keep exploring)', 'south-cairns-dental' ),
			'journey'     => __( 'Numbered cards', 'south-cairns-dental' ),
			'process'     => __( 'Numbered cards with note (treatment process)', 'south-cairns-dental' ),
			'steps'       => __( 'Numbered list', 'south-cairns-dental' ),
			'factrow'     => __( 'Facts with a lime rule', 'south-cairns-dental' ),
			'ticks'       => __( 'Tick list in columns', 'south-cairns-dental' ),
			'ticks-cards' => __( 'Tick list as cards', 'south-cairns-dental' ),
			'info'        => __( 'Information cards (tag, list, warning, call button)', 'south-cairns-dental' ),
			'weigh'       => __( 'Columns of points with icon (benefits / consider / alternatives)', 'south-cairns-dental' ),
			'care'        => __( 'Two-column list (before and after care)', 'south-cairns-dental' ),
			'teaser'      => __( 'Single highlighted link (blog teaser)', 'south-cairns-dental' ),
		);
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls( array( 'heading' => __( 'Section heading', 'south-cairns-dental' ) ) );

		$this->start_controls_section( 'content', array( 'label' => __( 'Cards', 'south-cairns-dental' ) ) );
		$this->add_control( 'style', array( 'label' => __( 'Style', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'default' => 'concierge', 'options' => $this->styles() ) );
		$this->add_control(
			'title_tag',
			array(
				'label'     => __( 'Card title level', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => array( 'h3' => 'H3', 'h2' => __( 'H2 (shown at H3 size)', 'south-cairns-dental' ) ),
				'condition' => array( 'style' => 'duo' ),
			)
		);
		$this->add_control(
			'columns',
			array(
				'label'     => __( 'Columns on wide screens', 'south-cairns-dental' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '',
				'options'   => array( '' => __( 'Automatic', 'south-cairns-dental' ), '2' => '2', '3' => '3', '4' => '4' ),
				'condition' => array( 'style' => array( 'info', 'duo' ) ),
			)
		);

		$r = new Repeater();
		$r->add_control( 'icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => '' ) );
		$r->add_control( 'kicker', array( 'label' => __( 'Small label / tag', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'south-cairns-dental' ),
				'type'        => Controls_Manager::TEXTAREA,
				'rows'        => 4,
				'description' => __( 'Blank line = new paragraph. Links can be added with HTML. {phone} shows the practice phone number.', 'south-cairns-dental' ),
			)
		);
		$r->add_control( 'list', array( 'label' => __( 'List (one item per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 4 ) );
		$r->add_control( 'flag', array( 'label' => __( 'Warning note (information cards) or note (process)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$r->add_control( 'link_label', array( 'label' => __( 'Link label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->repeater_link( $r );
		$r->add_control( 'anchor', array( 'label' => __( 'Anchor ID', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'highlight', array( 'label' => __( 'Highlight (urgent card / dark card)', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$r->add_control( 'call', array( 'label' => __( 'Add a call button', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'south-cairns-dental' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $r->get_controls(),
				'title_field' => '{{{ title || text }}}',
			)
		);
		$this->add_control( 'call_label', array( 'label' => __( 'Call button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Call {phone}' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'side_section', array( 'label' => __( 'Image beside the heading', 'south-cairns-dental' ) ) );
		$this->add_control( 'side_show', array( 'label' => __( 'Show an image', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->image_controls( 'side', array( 'corner' => 'corner-br' ) );
		$this->end_controls_section();

		$this->start_controls_section( 'foot_section', array( 'label' => __( 'Note below', 'south-cairns-dental' ) ) );
		$this->add_control( 'foot', array( 'label' => __( 'Note', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->end_controls_section();

		$this->section_controls();
		$this->heading_style_controls();
	}

	/**
	 * Card title.
	 *
	 * @param string $tag   Tag.
	 * @param string $title Title.
	 * @param string $class Class.
	 * @return string
	 */
	protected function title( $tag, $title, $class = '' ) {
		if ( '' === trim( (string) $title ) ) {
			return '';
		}
		return '<' . $tag . ( $class ? ' class="' . esc_attr( $class ) . '"' : '' ) . '>' . scd_inline( $title ) . '</' . $tag . '>';
	}

	/**
	 * Inline text for <span> contexts (paragraph breaks become line breaks).
	 *
	 * @param string $text Text.
	 * @return string
	 */
	protected function span_text( $text ) {
		return nl2br( scd_inline( trim( preg_replace( "/\n\s*\n/", "\n", (string) $text ) ) ), false );
	}

	/**
	 * Items markup for a style.
	 *
	 * @param array  $s     Settings.
	 * @param string $style Style.
	 * @return string
	 */
	protected function items_html( $s, $style ) {
		$items = (array) $s['items'];
		$out   = '';
		$arrow = scd_ui_icon( 'arrow' );
		$phone = scd_practice( 'phone_display' );
		foreach ( $items as $n => $it ) {
			$url   = $this->url( $it['link'] );
			$extra = $this->link_extra( $it['link'] );
			$id    = ! empty( $it['anchor'] ) ? ' id="' . esc_attr( sanitize_title( $it['anchor'] ) ) . '"' : '';
			$hi    = 'yes' === $it['highlight'];
			$text  = str_replace( '{phone}', $phone, (string) $it['text'] );
			$label = str_replace( '{phone}', $phone, (string) $it['link_label'] );
			switch ( $style ) {
				case 'concierge':
					$more = $label ? '<span class="more">' . scd_inline( $label ) . ' ' . $arrow . '</span>' : '';
					$body = '<span class="concierge__icon">' . scd_icon( $it['icon'] ) . '</span><strong>' . scd_inline( $it['title'] ) . '</strong><span class="d">' . $this->span_text( $text ) . '</span>' . $more;
					$out .= '<li class="' . ( $hi ? 'is-urgent ' : '' ) . 'reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '>' . ( $url ? '<a href="' . esc_url( $url ) . '"' . $extra . '>' . $body . '</a>' : '<a>' . $body . '</a>' ) . '</li>';
					break;
				case 'features':
				case 'pay':
					$cls  = 'features' === $style ? 'feature' : 'pay__item';
					$out .= '<div class="' . $cls . ' reveal" style="--i:' . ( $n % 4 ) . '"' . $id . '>' . scd_icon( $it['icon'] ) . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . ( $url && $label ? '<p><a href="' . esc_url( $url ) . '"' . $extra . '>' . scd_inline( $label ) . '</a></p>' : '' ) . '</div>';
					break;
				case 'ways':
					$out .= '<li class="reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '>' . scd_icon( $it['icon'] ) . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . ( $url ? '<a class="textlink" href="' . esc_url( $url ) . '"' . $extra . '>' . scd_inline( $label ? $label : $url ) . '</a>' : '' ) . '</li>';
					break;
				case 'mention':
					$out .= '<li' . $id . '><strong>' . scd_inline( $it['title'] ) . '</strong><span>' . $this->span_text( $text ) . '</span></li>';
					break;
				case 'duo':
					$tag   = 'h2' === $s['title_tag'] ? 'h2' : 'h3';
					$link  = $url ? '<a class="textlink" href="' . esc_url( $url ) . '"' . $extra . '>' . scd_inline( $label ) . '</a>' : '';
					$paras = $this->paras( $text );
					if ( ! $link ) {
						$paras = preg_replace( '#<p>(?!.*<p>)#s', '<p class="mb-0">', $paras, 1 );
					}
					$out .= '<div class="duo__item reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '>' . $this->title( $tag, $it['title'], 'h2' === $tag ? 'h3-size' : '' ) . $paras . $link . '</div>';
					break;
				case 'nextlinks':
					$body = ( $it['kicker'] ? '<span class="kicker">' . scd_inline( $it['kicker'] ) . '</span>' : '' ) . '<strong>' . scd_inline( $it['title'] ) . '</strong><span>' . $this->span_text( $text ) . '</span>' . $arrow;
					$out .= '<li class="reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '><a href="' . esc_url( $url ) . '"' . $extra . '>' . $body . '</a></li>';
					break;
				case 'journey':
					$out .= '<li class="reveal" style="--i:' . ( $n % 4 ) . '"' . $id . '>' . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . '</li>';
					break;
				case 'process':
					$note = $it['flag'] ? '<span class="process__note">' . scd_inline( $it['flag'] ) . '</span>' : '';
					$out .= '<li class="reveal" style="--i:' . ( $n % 4 ) . '"' . $id . '>' . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . $note . '</li>';
					break;
				case 'steps':
					$out .= '<li' . $id . '><div>' . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . '</div></li>';
					break;
				case 'factrow':
					$out .= '<div' . $id . '><dt>' . scd_inline( $it['title'] ) . '</dt><dd>' . $this->span_text( $text ) . '</dd></div>';
					break;
				case 'ticks':
				case 'ticks-cards':
					$out .= '<li' . $id . '>' . scd_inline( '' !== trim( $text ) ? $text : $it['title'] ) . '</li>';
					break;
				case 'info':
					$tag  = $it['kicker'] ? '<span class="tag">' . scd_inline( $it['kicker'] ) . '</span>' : '';
					$list = '';
					foreach ( $this->lines( $it['list'] ) as $line ) {
						$list .= '<li>' . scd_inline( $line ) . '</li>';
					}
					$list = $list ? '<ul>' . $list . '</ul>' : '';
					$flag = $it['flag'] ? '<p class="info-card__flag">' . scd_inline( $it['flag'] ) . '</p>' : '';
					$call = 'yes' === $it['call'] ? '<div class="mt-auto">' . scd_button( array( 'type' => 'call', 'label' => $s['call_label'], 'class' => 'btn--lime' ) ) . '</div>' : '';
					$out .= '<li class="info-card' . ( $hi || $call ? ' info-card--ask' : '' ) . ' reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '>' . $tag . $this->title( 'h3', $it['title'] ) . $this->paras( $text ) . $list . $flag . $call . '</li>';
					break;
				case 'weigh':
					$list = '';
					foreach ( $this->lines( $it['list'] ) as $line ) {
						$list .= '<li>' . scd_inline( $line ) . '</li>';
					}
					$out .= '<div class="weigh__col reveal" style="--i:' . (int) $n . '"' . $id . '><h3>' . scd_icon( $it['icon'] ) . scd_inline( $it['title'] ) . '</h3><ul>' . $list . '</ul></div>';
					break;
				case 'care':
					$out .= '<li class="reveal" style="--i:' . ( $n % 3 ) . '"' . $id . '><strong>' . scd_inline( $it['title'] ) . '</strong><span>' . $this->span_text( $text ) . '</span></li>';
					break;
				case 'teaser':
					if ( 0 === $n ) {
						$out .= '<a class="blog-teaser" href="' . esc_url( $url ) . '"' . $extra . '>' . ( $it['kicker'] ? '<span class="kicker">' . scd_inline( $it['kicker'] ) . '</span>' : '' ) . '<strong>' . scd_inline( $it['title'] ) . '</strong><span>' . $this->span_text( $text ) . '</span>' . $arrow . '</a>';
					}
					break;
			}
		}
		return $out;
	}

	/**
	 * Wrap items in the style's list element.
	 *
	 * @param array  $s     Settings.
	 * @param string $style Style.
	 * @param string $items Items HTML.
	 * @return string
	 */
	protected function list_html( $s, $style, $items ) {
		$count = count( (array) $s['items'] );
		switch ( $style ) {
			case 'concierge':
				return '<ul class="concierge">' . $items . '</ul>';
			case 'features':
				return '<div class="features">' . $items . '</div>';
			case 'pay':
				return '<div class="pay">' . $items . '</div>';
			case 'ways':
				return '<ul class="ways">' . $items . '</ul>';
			case 'mention':
				return '<ul class="mention">' . $items . '</ul>';
			case 'duo':
				return '<div class="duo' . ( '3' === $s['columns'] ? ' duo--3' : '' ) . '">' . $items . '</div>';
			case 'nextlinks':
				return '<ul class="nextlinks">' . $items . '</ul>';
			case 'journey':
				return '<ol class="journey">' . $items . '</ol>';
			case 'process':
				return '<ol class="process">' . $items . '</ol>';
			case 'steps':
				return '<ol class="steps' . ( $this->is_dark( $s ) ? ' steps--light' : '' ) . '">' . $items . '</ol>';
			case 'factrow':
				return '<dl class="factrow factrow--grid">' . $items . '</dl>';
			case 'ticks':
				return '<ul class="ticks ticks--cols">' . $items . '</ul>';
			case 'ticks-cards':
				return '<ul class="ticks ticks--cols ticks--cards">' . $items . '</ul>';
			case 'info':
				$cols = $s['columns'] ? (int) $s['columns'] : ( 4 === $count ? 4 : ( 2 === $count ? 2 : 3 ) );
				return '<ul class="info-cards info-cards--' . $cols . '">' . $items . '</ul>';
			case 'weigh':
				return '<div class="weigh">' . $items . '</div>';
			case 'care':
				return '<ul class="care-list">' . $items . '</ul>';
			default:
				return $items;
		}
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$style = isset( $this->styles()[ $s['style'] ] ) ? $s['style'] : 'concierge';
		$list  = $this->list_html( $s, $style, $this->items_html( $s, $style ) );
		$foot  = $s['foot'] ? '<div class="mt-7 small muted">' . $this->rich( $s['foot'] ) . '</div>' : '';

		// An image beside the heading: heading and image side by side, cards underneath.
		if ( 'yes' === $s['side_show'] && ( ! empty( $s['side']['id'] ) || ! empty( $s['side']['url'] ) ) ) {
			$label = '' !== trim( (string) $s['heading'] ) ? $this->hid() : '';
			$head  = '<div class="section-head"><h2 id="' . esc_attr( $this->hid() ) . '" class="scd-head-title">' . scd_inline( $s['heading'] ) . '</h2>' . $this->paras( $s['intro'], 'section-head__intro' ) . '</div>';
			$media = $this->frame( $s, 'side' );
			$has   = '' !== trim( wp_strip_all_tags( $list, true ) ) && ! empty( $s['items'] );
			$body  = $has
				? '<div class="wrap"><div class="split split--7-5">' . $head . $media . '</div><div class="mt-7">' . $list . '</div>' . $foot . '</div>'
				: '<div class="wrap split split--6-6"><div><h2 id="' . esc_attr( $this->hid() ) . '" class="scd-head-title">' . scd_inline( $s['heading'] ) . '</h2>' . $this->paras( $s['intro'], 'section-head__intro' ) . '</div>' . $media . '</div>';
			echo $this->open_section( $s, '', $label ) . $body . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}
		if ( 'teaser' === $style ) {
			echo $this->open_section( $s, '', '' ) . '<div class="wrap">' . $list . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}
		echo $this->section( $s, $list . $foot ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helpers.
	}
}
