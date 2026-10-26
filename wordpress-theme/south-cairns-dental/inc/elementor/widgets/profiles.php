<?php
/**
 * Dentist profiles (team page): portrait beside role, name, qualifications, interests, biography
 * and an optional quote, alternating sides. Every portrait uses the same fixed column and 4:5
 * frame so photos line up whatever the biography length.
 *
 * @package South_Cairns_Dental
 */

namespace SCD\Elementor;

use Elementor\Controls_Manager;
use Elementor\Repeater;

defined( 'ABSPATH' ) || exit;

/**
 * Profiles widget.
 */
class Profiles extends Widget {

	/** @return string */
	public function get_name() {
		return 'scd-profiles';
	}

	/** @return string */
	public function get_title() {
		return __( 'SCD Dentist profiles', 'south-cairns-dental' );
	}

	/** @return string */
	public function get_icon() {
		return 'eicon-person';
	}

	/**
	 * Controls.
	 */
	protected function register_controls() {
		$this->start_controls_section( 'content', array( 'label' => __( 'Profiles', 'south-cairns-dental' ) ) );
		$r = new Repeater();
		$r->add_control( 'image', array( 'label' => __( 'Portrait', 'south-cairns-dental' ), 'type' => Controls_Manager::MEDIA ) );
		$r->add_control( 'role', array( 'label' => __( 'Role', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'name', array( 'label' => __( 'Name', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'known_as', array( 'label' => __( 'Known as', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$r->add_control( 'quals', array( 'label' => __( 'Qualifications', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT, 'label_block' => true ) );
		$r->add_control( 'tags', array( 'label' => __( 'Areas of interest (one per line)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$r->add_control( 'bio', array( 'label' => __( 'Biography', 'south-cairns-dental' ), 'type' => Controls_Manager::WYSIWYG ) );
		$r->add_control( 'quote', array( 'label' => __( 'Quote', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXTAREA, 'rows' => 3 ) );
		$r->add_control( 'anchor', array( 'label' => __( 'Anchor ID (for jump links)', 'south-cairns-dental' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'profiles', array( 'label' => __( 'Profiles', 'south-cairns-dental' ), 'type' => Controls_Manager::REPEATER, 'fields' => $r->get_controls(), 'title_field' => '{{{ name }}}' ) );
		$this->end_controls_section();
		$this->section_controls();
	}

	/**
	 * Output.
	 */
	protected function render() {
		$s   = $this->get_settings_for_display();
		$out = '';
		foreach ( (array) $s['profiles'] as $i => $p ) {
			$slug  = $p['anchor'] ? sanitize_title( $p['anchor'] ) : sanitize_title( $p['name'] );
			$hid   = $slug . '-h';
			$tags  = '';
			foreach ( $this->lines( $p['tags'] ) as $t ) {
				$tags .= '<li>' . esc_html( $t ) . '</li>';
			}
			$tags  = $tags ? '<ul class="tags" aria-label="' . esc_attr__( 'Areas of interest', 'south-cairns-dental' ) . '">' . $tags . '</ul>' : '';
			$known = $p['known_as'] ? ' <span class="profile__aka">(' . esc_html( sprintf( /* translators: %s: nickname */ __( 'known as %s', 'south-cairns-dental' ), $p['known_as'] ) ) . ')</span>' : '';
			$quote = $p['quote'] ? '<blockquote class="profile__quote"><p>&ldquo;' . scd_inline( $p['quote'] ) . '&rdquo;</p><footer>' . esc_html( $p['name'] ) . '</footer></blockquote>' : '';
			$has   = ! empty( $p['image']['id'] ) || ! empty( $p['image']['url'] );
			$img   = $has
				? $this->img( $p['image'], array( 'alt' => trim( $p['name'] . ', ' . $p['role'], ', ' ), 'size' => 'scd-portrait', 'eager' => 0 === $i ) )
				: scd_img_tag( array( 'url' => scd_theme_image( 'team/portrait-placeholder.svg' ), 'decorative' => true, 'width' => 800, 'height' => 1000 ) );
			$fig   = '<figure class="frame portrait frame--corner' . ( $has ? '' : ' portrait--placeholder' ) . '" style="--ratio:4 / 5">' . $img . '</figure>';
			$out  .= '<article class="profile' . ( trim( wp_strip_all_tags( (string) $p['bio'] ) ) ? ' profile--full' : '' ) . ' reveal" id="' . esc_attr( $slug ) . '" aria-labelledby="' . esc_attr( $hid ) . '">'
				. '<div class="profile__media">' . $fig . '</div><div class="profile__body">'
				. ( $p['role'] ? '<p class="profile__role">' . esc_html( $p['role'] ) . '</p>' : '' )
				. '<h2 id="' . esc_attr( $hid ) . '">' . esc_html( $p['name'] ) . $known . '</h2>'
				. ( $p['quals'] ? '<p class="profile__quals">' . esc_html( $p['quals'] ) . '</p>' : '' )
				. $tags . '<div class="prose">' . $this->rich( $p['bio'] ) . '</div>' . $quote . '</div></article>';
		}
		echo $this->open_section( $s, 'sec--profiles' ) . '<div class="wrap profiles">' . $out . '</div></section>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
