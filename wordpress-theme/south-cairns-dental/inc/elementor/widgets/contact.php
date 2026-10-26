<?php
/**
 * Contact details (from Customize > Practice details), opening hours and the enquiry form.
 *
 * The form sends nothing itself: when submitted it opens the visitor's own email app with the
 * message addressed to the practice (exactly as on the static site). No data is stored on the
 * website, and nothing pretends to book an appointment.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Contact widget.
 */
class Contact extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-contact';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Contact details and form', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Contact details', 'south-cairns-dental' ) ) );
		$this->add_control( 'info', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'Phone, email, booking link, address and opening hours come from Appearance > Customize > Practice details.', 'south-cairns-dental' ), 'content_classes' => 'elementor-descriptor' ) );
		$this->add_control( 'heading', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Contact details' ) );
		$this->add_control( 'book_text', array( 'label' => __( 'Booking link text', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Choose an appointment time' ) );
		$this->add_control( 'parking', array( 'label' => __( 'Parking', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 2 ) );
		$this->add_control( 'hours_heading', array( 'label' => __( 'Hours heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Opening hours' ) );
		$this->add_control( 'hours_note', array( 'label' => __( 'Note under the hours', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$this->end_controls_section();

		$this->start_controls_section( 'form_section', array( 'label' => __( 'Enquiry form', 'south-cairns-dental' ) ) );
		$this->add_control( 'form_show', array( 'label' => __( 'Show the form', 'south-cairns-dental' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'form_title', array( 'label' => __( 'Heading', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Send us a question' ) );
		$this->add_control( 'form_intro', array( 'label' => __( 'Introduction', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$this->add_control( 'topics', array( 'label' => __( 'Topics (one per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 5, 'default' => "General question\nNew patient enquiry\nExisting appointment\nTreatment question\nSomething else" ) );
		$this->add_control( 'message_hint', array( 'label' => __( 'Message hint', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true, 'default' => 'Please don’t include detailed medical information.' ) );
		$this->add_control( 'submit', array( 'label' => __( 'Button label', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'default' => 'Send by email' ) );
		$this->end_controls_section();
		$this->section_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s     = $this->get_settings_for_display();
		$hid   = $this->hid();
		$email = scd_practice( 'email' );
		$book  = scd_practice( 'booking_url' );
		$rows  = '<li><span class="label">' . esc_html__( 'Phone', 'south-cairns-dental' ) . '</span><a href="tel:' . esc_attr( scd_practice( 'phone_tel' ) ) . '">' . esc_html( scd_practice( 'phone_display' ) ) . '</a></li>';
		if ( $email ) {
			$rows .= '<li><span class="label">' . esc_html__( 'Email', 'south-cairns-dental' ) . '</span><a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a></li>';
		}
		if ( $book ) {
			$rows .= '<li><span class="label">' . esc_html__( 'Book online', 'south-cairns-dental' ) . '</span><a href="' . esc_url( $book ) . '" rel="noopener">' . esc_html( $s['book_text'] ) . '</a></li>';
		}
		$rows .= '<li><span class="label">' . esc_html__( 'Address', 'south-cairns-dental' ) . '</span><span class="value">' . esc_html( scd_practice( 'address_line1' ) ) . '<br>' . esc_html( scd_address_locality() ) . '</span></li>';
		if ( $s['parking'] ) {
			$rows .= '<li><span class="label">' . esc_html__( 'Parking', 'south-cairns-dental' ) . '</span><span class="value">' . scd_inline( $s['parking'] ) . '</span></li>';
		}
		$left = '<div><h2 id="' . esc_attr( $hid ) . '" class="h3-size">' . esc_html( $s['heading'] ) . '</h2><ul class="contact-list">' . $rows . '</ul>'
			. '<h2 class="mt-7 h3-size">' . esc_html( $s['hours_heading'] ) . '</h2>' . scd_hours_table()
			. ( $s['hours_note'] ? '<p class="small muted mt-5 mb-0">' . esc_html( $s['hours_note'] ) . '</p>' : '' ) . '</div>';

		$form = '';
		if ( 'yes' === $s['form_show'] && $email ) {
			$fid    = 'f' . $this->get_id();
			$fh     = $this->hid( 'form' );
			$topics = '';
			foreach ( $this->lines( $s['topics'] ) as $t ) {
				$topics .= '<option>' . esc_html( $t ) . '</option>';
			}
			$form = '<div class="panel panel--corner" id="message-form"><h2 id="' . esc_attr( $fh ) . '">' . esc_html( $s['form_title'] ) . '</h2>'
				. ( $s['form_intro'] ? '<p class="muted">' . scd_inline( $s['form_intro'] ) . '</p>' : '' )
				. '<form class="form mt-5" data-mailto-form="' . esc_attr( $email ) . '" novalidate aria-labelledby="' . esc_attr( $fh ) . '">'
				. '<div class="form__row"><div class="field"><label for="' . $fid . '-name">' . esc_html__( 'Your name', 'south-cairns-dental' ) . '</label><input id="' . $fid . '-name" name="name" type="text" autocomplete="name" required aria-describedby="' . $fid . '-name-error"><p class="error" id="' . $fid . '-name-error" hidden>' . esc_html__( 'Enter your name.', 'south-cairns-dental' ) . '</p></div>'
				. '<div class="field"><label for="' . $fid . '-phone">' . esc_html__( 'Phone', 'south-cairns-dental' ) . ' <span class="hint">' . esc_html__( 'Optional', 'south-cairns-dental' ) . '</span></label><input id="' . $fid . '-phone" name="phone" type="tel" autocomplete="tel"></div></div>'
				. '<div class="field"><label for="' . $fid . '-email">' . esc_html__( 'Email', 'south-cairns-dental' ) . '</label><input id="' . $fid . '-email" name="email" type="email" autocomplete="email" required aria-describedby="' . $fid . '-email-error"><p class="error" id="' . $fid . '-email-error" hidden>' . esc_html__( 'Enter an email address, for example name@example.com.', 'south-cairns-dental' ) . '</p></div>'
				. '<div class="field"><label for="' . $fid . '-topic">' . esc_html__( 'What is it about?', 'south-cairns-dental' ) . '</label><select id="' . $fid . '-topic" name="topic">' . $topics . '</select></div>'
				. '<div class="field"><label for="' . $fid . '-message">' . esc_html__( 'Message', 'south-cairns-dental' ) . ( $s['message_hint'] ? ' <span class="hint">' . esc_html( $s['message_hint'] ) . '</span>' : '' ) . '</label><textarea id="' . $fid . '-message" name="message" required aria-describedby="' . $fid . '-message-error"></textarea><p class="error" id="' . $fid . '-message-error" hidden>' . esc_html__( 'Enter a message.', 'south-cairns-dental' ) . '</p></div>'
				. '<div><button class="btn" type="submit">' . scd_ui_icon( 'mail' ) . '<span>' . esc_html( $s['submit'] ) . '</span></button></div>'
				. '<p class="form__status" role="status" hidden></p></form></div>';
		}
		echo $this->open_section( $s, '', $hid ) . '<div class="wrap split split--5-7 split--top">' . $left . $form . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
