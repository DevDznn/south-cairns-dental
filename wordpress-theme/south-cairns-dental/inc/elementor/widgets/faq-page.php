<?php
/**
 * The full FAQ page: topic links in a sticky side column and the questions grouped under
 * topic headings. Each question names its topic; consecutive questions with the same topic
 * form one group, and the topic list is built from them.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * FAQ page widget.
 */
class Faq_Page extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-faq-page';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD FAQ by topic', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-toggle';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Questions', 'south-cairns-dental' ) ) );
		$this->add_control( 'nav_label', array( 'label' => __( 'Topic list name (screen readers)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'FAQ topics' ) );
		$r = new Repeater();
		$r->add_control( 'topic', array( 'label' => __( 'Topic heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'nav', array( 'label' => __( 'Topic link label (blank = heading)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'anchor', array( 'label' => __( 'Topic anchor ID (e.g. costs)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'icon', array( 'label' => __( 'Topic icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => 'clipboard' ) );
		$r->add_control( 'question', array( 'label' => __( 'Question', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'separator' => 'before' ) );
		$r->add_control( 'answer', array( 'label' => __( 'Answer', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG, 'description' => __( '[scd_hours_table] shows the opening hours table.', 'south-cairns-dental' ) ) );
		$this->add_control( 'items', array( 'label' => __( 'Questions', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ topic }}}: {{{ question }}}' ) );
		$this->add_control( 'source', array( 'label' => __( 'Sources note', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->end_controls_section();
		$this->section_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$groups = array();
		foreach ( (array) $s['items'] as $it ) {
			$topic = trim( (string) $it['topic'] );
			$last  = end( $groups );
			if ( ! $groups || ( '' !== $topic && $last['topic'] !== $topic ) ) {
				$groups[] = array(
					'topic'  => $topic,
					'nav'    => $it['nav'] ? $it['nav'] : $topic,
					'anchor' => sanitize_title( $it['anchor'] ? $it['anchor'] : $topic ),
					'icon'   => $it['icon'],
					'qa'     => '',
				);
			}
			$groups[ count( $groups ) - 1 ]['qa'] .= '<details><summary>' . scd_inline( $it['question'] ) . '</summary><div class="faq__body">' . $this->rich( $it['answer'] ) . '</div></details>';
		}
		$nav  = '';
		$body = '';
		foreach ( $groups as $g ) {
			$nav  .= '<li><a href="#' . esc_attr( $g['anchor'] ) . '">' . scd_inline( $g['nav'] ) . '</a></li>';
			$body .= '<div class="faq-group" id="' . esc_attr( $g['anchor'] ) . '"><h2>' . scd_icon( $g['icon'] ) . scd_inline( $g['topic'] ) . '</h2><div class="faq">' . $g['qa'] . '</div></div>';
		}
		$source = $this->rich_block( $s['source'], 'source mt-7' );
		echo $this->open_section( $s ) . '<div class="wrap faq-layout"><nav class="faq-nav" aria-label="' . esc_attr( $s['nav_label'] ) . '"><ul>' . $nav . '</ul></nav><div>' . $body . $source . '</div></div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
