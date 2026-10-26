<?php
/**
 * Treatment pages. Each treatment is an ordinary page built in Elementor; a "Treatment details"
 * box on the page marks it as a treatment and holds what the service directory, service lists
 * and related-treatment cards show (group, category, short descriptions). The card image is the
 * page's featured image; the order is the page's Order attribute.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Treatment groups.
 *
 * @return string[] key => label
 */
function scd_service_groups() {
	return apply_filters(
		'scd_service_groups',
		array(
			'everyday' => __( 'Everyday care', 'south-cairns-dental' ),
			'repair'   => __( 'Repairing teeth', 'south-cairns-dental' ),
			'replace'  => __( 'Replacing teeth', 'south-cairns-dental' ),
			'smile'    => __( 'Changing your smile', 'south-cairns-dental' ),
			'urgent'   => __( 'Urgent care', 'south-cairns-dental' ),
		)
	);
}

/**
 * Treatment categories used by the homepage filter.
 *
 * @return string[]
 */
function scd_service_categories() {
	return apply_filters( 'scd_service_categories', array( 'Preventive', 'General', 'Restorative', 'Cosmetic', 'Urgent' ) );
}

/**
 * Meta fields: key => [ label, type ].
 *
 * @return array
 */
function scd_service_meta_fields() {
	return array(
		'_scd_service'          => array( __( 'This page is a treatment', 'south-cairns-dental' ), 'checkbox' ),
		'_scd_service_group'    => array( __( 'Group', 'south-cairns-dental' ), 'group' ),
		'_scd_service_category' => array( __( 'Category (homepage filter)', 'south-cairns-dental' ), 'category' ),
		'_scd_service_short'    => array( __( 'Short name used in sentences (e.g. "fillings")', 'south-cairns-dental' ), 'text' ),
		'_scd_service_card'     => array( __( 'Card description (homepage and service lists)', 'south-cairns-dental' ), 'textarea' ),
		'_scd_service_blurb'    => array( __( 'One-line summary (related-treatment cards)', 'south-cairns-dental' ), 'textarea' ),
		'_scd_service_tone'     => array( __( 'Urgent styling (red hero, sticky call bar)', 'south-cairns-dental' ), 'tone' ),
	);
}

/**
 * Register the meta so it is available to the REST API (block editor) and sanitised on save.
 */
function scd_register_service_meta() {
	foreach ( scd_service_meta_fields() as $key => $field ) {
		register_post_meta(
			'page',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'textarea' === $field[1] ? 'sanitize_textarea_field' : 'sanitize_text_field',
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}
}
add_action( 'init', 'scd_register_service_meta' );

/**
 * Is the page a treatment?
 *
 * @param int $post_id Page ID.
 * @return bool
 */
function scd_is_service( $post_id ) {
	return $post_id && '1' === get_post_meta( $post_id, '_scd_service', true );
}

/**
 * Published treatment pages, in page order.
 *
 * @param string $group Limit to a group key ('' for all).
 * @return WP_Post[]
 */
function scd_service_pages( $group = '' ) {
	$meta = array(
		array(
			'key'   => '_scd_service',
			'value' => '1',
		),
	);
	if ( $group ) {
		$meta[] = array(
			'key'   => '_scd_service_group',
			'value' => $group,
		);
	}
	return get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			'meta_query'     => $meta, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- small, cached query.
			'no_found_rows'  => true,
		)
	);
}

/**
 * A treatment's card data.
 *
 * @param WP_Post|int $page Page.
 * @return array
 */
function scd_service_data( $page ) {
	$page = get_post( $page );
	if ( ! $page ) {
		return array();
	}
	$card = get_post_meta( $page->ID, '_scd_service_card', true );
	return array(
		'id'       => $page->ID,
		'title'    => get_the_title( $page ),
		'url'      => get_permalink( $page ),
		'group'    => get_post_meta( $page->ID, '_scd_service_group', true ),
		'category' => get_post_meta( $page->ID, '_scd_service_category', true ),
		'card'     => $card ? $card : get_the_excerpt( $page ),
		'blurb'    => get_post_meta( $page->ID, '_scd_service_blurb', true ),
		'short'    => get_post_meta( $page->ID, '_scd_service_short', true ),
		'urgent'   => 'urgent' === get_post_meta( $page->ID, '_scd_service_tone', true ),
		'thumb_id' => get_post_thumbnail_id( $page ),
	);
}

/**
 * Card thumbnail for a treatment (decorative: the name sits beside it).
 *
 * @param array  $data  scd_service_data().
 * @param string $class Image class.
 * @return string
 */
function scd_service_thumb( $data, $class = '' ) {
	$img = scd_img_tag(
		array(
			'id'         => $data['thumb_id'],
			'decorative' => true,
			'size'       => 'scd-card',
			'img_class'  => trim( $class . ' is-photo' ),
		)
	);
	if ( '' === $img ) {
		$img = scd_img_tag(
			array(
				'url'        => scd_theme_image( 'brand/scd-mark-512.png' ),
				'decorative' => true,
				'img_class'  => $class,
				'width'      => 512,
				'height'     => 512,
			)
		);
	}
	return $img;
}

/**
 * Services directory: every treatment by group (urgent care sits under "Replacing teeth").
 *
 * @return string
 */
function scd_service_directory() {
	$groups = scd_service_groups();
	$urgent = scd_service_pages( 'urgent' );
	$out    = '<div class="directory">';
	foreach ( $groups as $key => $label ) {
		if ( 'urgent' === $key ) {
			continue;
		}
		$pages = scd_service_pages( $key );
		if ( ! $pages ) {
			continue;
		}
		$out .= '<div class="directory__col"><div class="directory__group"><h3>' . esc_html( $label ) . '</h3><ul>';
		foreach ( $pages as $page ) {
			$out .= '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . scd_ui_icon( 'arrow' ) . '</a></li>';
		}
		$out .= '</ul></div>';
		if ( 'replace' === $key && $urgent ) {
			$out .= '<div class="directory__group directory__group--urgent"><h3>' . esc_html( $groups['urgent'] ) . '</h3><ul>';
			foreach ( $urgent as $page ) {
				$out .= '<li><a href="' . esc_url( get_permalink( $page ) ) . '">' . esc_html( get_the_title( $page ) ) . scd_ui_icon( 'arrow' ) . '</a></li>';
			}
			$out .= '</ul></div>';
		}
		$out .= '</div>';
	}
	return $out . '</div>';
}

/**
 * A detailed list of one group's treatments (Services overview page).
 *
 * @param string $group Group key.
 * @return string
 */
function scd_service_list( $group ) {
	$items = '';
	foreach ( scd_service_pages( $group ) as $page ) {
		$d      = scd_service_data( $page );
		$items .= '<li class="svc-item svc-item--thumb reveal"><a href="' . esc_url( $d['url'] ) . '">' . scd_service_thumb( $d, 'svc-item__thumb' )
			. '<strong>' . esc_html( $d['title'] ) . '</strong><span>' . esc_html( $d['card'] ) . '</span><span class="arrow">' . scd_ui_icon( 'arrow' ) . '</span></a></li>';
	}
	return '<ul class="svc-list">' . $items . '</ul>';
}

/**
 * Add the meta box.
 */
function scd_service_meta_box() {
	add_meta_box( 'scd-service', __( 'Treatment details', 'south-cairns-dental' ), 'scd_service_meta_box_html', 'page', 'side' );
}
add_action( 'add_meta_boxes', 'scd_service_meta_box' );

/**
 * Meta box contents.
 *
 * @param WP_Post $post Page.
 */
function scd_service_meta_box_html( $post ) {
	wp_nonce_field( 'scd_service_meta', 'scd_service_nonce' );
	echo '<p class="description">' . esc_html__( 'Treatment pages appear in the homepage service cards, the services directory and lists, and related-treatment cards. The card image is the Featured image.', 'south-cairns-dental' ) . '</p>';
	foreach ( scd_service_meta_fields() as $key => $field ) {
		list( $label, $type ) = $field;
		$value                = (string) get_post_meta( $post->ID, $key, true );
		$id                   = 'scd' . $key;
		echo '<p>';
		switch ( $type ) {
			case 'checkbox':
				printf( '<label><input type="checkbox" name="%1$s" id="%2$s" value="1" %3$s> %4$s</label>', esc_attr( $key ), esc_attr( $id ), checked( $value, '1', false ), esc_html( $label ) );
				break;
			case 'group':
			case 'category':
			case 'tone':
				$opts = 'group' === $type ? scd_service_groups() : ( 'category' === $type ? array_combine( scd_service_categories(), scd_service_categories() ) : array( '' => __( 'Normal', 'south-cairns-dental' ), 'urgent' => __( 'Urgent', 'south-cairns-dental' ) ) );
				printf( '<label for="%1$s">%2$s</label><br><select name="%3$s" id="%1$s" class="widefat">', esc_attr( $id ), esc_html( $label ), esc_attr( $key ) );
				if ( 'tone' !== $type ) {
					echo '<option value="">' . esc_html__( '(none)', 'south-cairns-dental' ) . '</option>';
				}
				foreach ( $opts as $k => $l ) {
					printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $value, (string) $k, false ), esc_html( $l ) );
				}
				echo '</select>';
				break;
			case 'textarea':
				printf( '<label for="%1$s">%2$s</label><br><textarea name="%3$s" id="%1$s" rows="3" class="widefat">%4$s</textarea>', esc_attr( $id ), esc_html( $label ), esc_attr( $key ), esc_textarea( $value ) );
				break;
			default:
				printf( '<label for="%1$s">%2$s</label><br><input type="text" name="%3$s" id="%1$s" value="%4$s" class="widefat">', esc_attr( $id ), esc_html( $label ), esc_attr( $key ), esc_attr( $value ) );
		}
		echo '</p>';
	}
}

/**
 * Save the meta box.
 *
 * @param int $post_id Page ID.
 */
function scd_save_service_meta( $post_id ) {
	if ( ! isset( $_POST['scd_service_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scd_service_nonce'] ) ), 'scd_service_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}
	$groups     = scd_service_groups();
	$categories = scd_service_categories();
	foreach ( scd_service_meta_fields() as $key => $field ) {
		$type = $field[1];
		if ( 'checkbox' === $type ) {
			$value = empty( $_POST[ $key ] ) ? '' : '1';
		} else {
			$raw   = isset( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitised below by type.
			$value = 'textarea' === $type ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
			if ( 'group' === $type && ! isset( $groups[ $value ] ) ) {
				$value = '';
			} elseif ( 'category' === $type && ! in_array( $value, $categories, true ) ) {
				$value = '';
			} elseif ( 'tone' === $type && 'urgent' !== $value ) {
				$value = '';
			}
		}
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
		} else {
			update_post_meta( $post_id, $key, $value );
		}
	}
}
add_action( 'save_post_page', 'scd_save_service_meta' );

/**
 * Blog posts: "Article details" box (byline, closing note, related treatments).
 */
function scd_article_meta_box() {
	add_meta_box( 'scd-article', __( 'Article details', 'south-cairns-dental' ), 'scd_article_meta_box_html', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'scd_article_meta_box' );

/**
 * Article details box contents.
 *
 * @param WP_Post $post Post.
 */
function scd_article_meta_box_html( $post ) {
	wp_nonce_field( 'scd_article_meta', 'scd_article_nonce' );
	$related = array_map( 'absint', (array) get_post_meta( $post->ID, '_scd_related', true ) );
	printf(
		'<p><label for="scd-author">%s</label><br><input type="text" class="widefat" id="scd-author" name="scd_author" value="%s" placeholder="%s"></p>',
		esc_html__( 'Byline', 'south-cairns-dental' ),
		esc_attr( get_post_meta( $post->ID, '_scd_author', true ) ),
		esc_attr( scd_practice( 'name' ) )
	);
	printf(
		'<p><label for="scd-note">%s</label><br><textarea class="widefat" rows="2" id="scd-note" name="scd_note">%s</textarea></p>',
		esc_html__( 'Closing note', 'south-cairns-dental' ),
		esc_textarea( get_post_meta( $post->ID, '_scd_note', true ) )
	);
	echo '<p><strong>' . esc_html__( 'Related treatments', 'south-cairns-dental' ) . '</strong></p><div style="max-height:180px;overflow:auto">';
	foreach ( scd_service_pages() as $page ) {
		printf( '<label style="display:block"><input type="checkbox" name="scd_related[]" value="%d" %s> %s</label>', (int) $page->ID, checked( in_array( $page->ID, $related, true ), true, false ), esc_html( get_the_title( $page ) ) );
	}
	echo '</div>';
}

/**
 * Save the article details box.
 *
 * @param int $post_id Post ID.
 */
function scd_save_article_meta( $post_id ) {
	if ( ! isset( $_POST['scd_article_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['scd_article_nonce'] ) ), 'scd_article_meta' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_scd_author', isset( $_POST['scd_author'] ) ? sanitize_text_field( wp_unslash( $_POST['scd_author'] ) ) : '' );
	update_post_meta( $post_id, '_scd_note', isset( $_POST['scd_note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['scd_note'] ) ) : '' );
	$related = isset( $_POST['scd_related'] ) ? array_values( array_filter( array_map( 'absint', (array) wp_unslash( $_POST['scd_related'] ) ) ) ) : array();
	update_post_meta( $post_id, '_scd_related', $related );
}
add_action( 'save_post_post', 'scd_save_article_meta' );
