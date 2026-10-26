<?php
/**
 * Questions and answers (accordion), in three layouts: heading column beside the questions
 * (homepage, new patients), questions beside a side card (treatment FAQs with the cost card),
 * or questions only. Native <details> elements, so answers open without JavaScript too.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * FAQ widget.
 */
class Faq extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-faq';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Questions (FAQ)', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-accordion';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->head_controls(
			array(
				'layout'  => 'split-4-8',
				'heading' => 'Questions patients ask us',
				'layouts' => array( 'split-4-8', 'split-5-7', 'split-7-5', 'stacked' ),
			)
		);
		$this->start_controls_section( 'content', array( 'label' => __( 'Questions', 'south-cairns-dental' ) ) );
		$this->add_control(
			'faq_style',
			array(
				'label'   => __( 'Style', 'south-cairns-dental' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cards',
				'options' => array(
					'cards' => __( 'Cards', 'south-cairns-dental' ),
					'lines' => __( 'Lines', 'south-cairns-dental' ),
				),
			)
		);
		$r = new Repeater();
		$r->add_control( 'question', array( 'label' => __( 'Question', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'answer', array( 'label' => __( 'Answer', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->add_control( 'items', array( 'label' => __( 'Questions', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ question }}}' ) );
		$this->add_control( 'foot', array( 'label' => __( 'Note below', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$this->end_controls_section();

		$this->start_controls_section( 'card_section', array( 'label' => __( 'Side card', 'south-cairns-dental' ) ) );
		$this->add_control( 'card_show', array( 'label' => __( 'Show a side card (e.g. cost and payment)', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes' ) );
		$this->add_control( 'card_icon', array( 'label' => __( 'Icon', 'south-cairns-dental' ), 'type' => Controls_Manager::SELECT, 'options' => scd_icon_options(), 'default' => 'wallet', 'condition' => array( 'card_show' => 'yes' ) ) );
		$this->add_control( 'card_title', array( 'label' => __( 'Title', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Cost and payment', 'condition' => array( 'card_show' => 'yes' ) ) );
		$this->add_control( 'card_text', array( 'label' => __( 'Text', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG, 'condition' => array( 'card_show' => 'yes' ) ) );
		$this->end_controls_section();

		$this->section_controls( array( 'bg' => 'mist' ) );
		$this->heading_style_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s      = $this->get_settings_for_display();
		$layout = $s['head_layout'];
		$qa     = '';
		foreach ( (array) $s['items'] as $it ) {
			$qa .= '<details><summary>' . scd_inline( $it['question'] ) . '</summary><div class="faq__body">' . $this->rich( $it['answer'] ) . '</div></details>';
		}
		$faq  = $qa ? '<div class="faq' . ( 'cards' === $s['faq_style'] ? ' faq--cards' : '' ) . '">' . $qa . '</div>' : '';
		$card = '';
		if ( 'yes' === $s['card_show'] ) {
			$card = '<div class="cost-card reveal">' . scd_icon( $s['card_icon'] ) . '<h3>' . esc_html( $s['card_title'] ) . '</h3>' . str_replace( '<p>', '<p>', $this->rich( $s['card_text'] ) ) . '</div>';
		}
		$foot  = $s['foot'] ? '<div class="wrap mt-7 small muted">' . $this->rich( $s['foot'] ) . '</div>' : '';
		$label = '' !== trim( (string) $s['heading'] ) ? $this->hid() : '';
		$h     = '' !== trim( (string) $s['heading'] ) ? '<h2 id="' . esc_attr( $this->hid() ) . '" class="scd-head-title">' . scd_inline( $s['heading'] ) . '</h2>' : '';

		// Questions beside a side card: the heading sits above the questions.
		if ( 'split-7-5' === $layout ) {
			$intro = $this->paras( $s['intro'], 'section-head__intro' );
			$body  = $faq ? '<div class="wrap split split--7-5 split--top"><div>' . $h . $intro . $faq . '</div>' . $card . '</div>' : '<div class="wrap split split--5-7 split--top"><div>' . $h . $intro . '</div>' . $card . '</div>';
			echo $this->open_section( $s, '', $label ) . $body . $foot . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}
		// Homepage style: intro column with a button, cards beside it.
		if ( 'split-4-8' === $layout && 'cards' === $s['faq_style'] ) {
			$btns = $this->button( $s, 'head_btn1', 'mt-6' ) . $this->button( $s, 'head_btn2', 'mt-6' );
			$body = '<div class="wrap split split--4-8 split--top home-faq"><div class="home-faq__intro">' . $h . $this->paras( $s['intro'], 'section-head__intro' ) . $btns . '</div>' . $faq . '</div>';
			echo $this->open_section( $s, '', $label ) . $body . $foot . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}
		echo $this->section( $s, $faq . $card, '', $foot ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
