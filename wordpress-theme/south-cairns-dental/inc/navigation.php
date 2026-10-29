<?php
/**
 * Main navigation and footer menus, managed in Appearance > Menus.
 *
 * Main menu structure (the importer creates it; editors can change it):
 * - A top-level item with no children is a plain link.
 * - A top-level item with children becomes a dropdown (About us, Patient information).
 * - A top-level item whose children have their own children becomes the services mega panel:
 *   second-level items WITH children are columns (column heading = item label);
 *   second-level items WITHOUT children are the panel's intro links (give one the CSS class
 *   "is-urgent" to show it in red). The item's Description is the intro paragraph and its
 *   Title Attribute the intro heading.
 *
 * @package South_Cairns_Dental
 */

defined( 'ABSPATH' ) || exit;

/**
 * Menu items for a location as a tree.
 *
 * @param string $location Menu location.
 * @return array|null Tree of items (each with ->children), or null when no menu is assigned.
 */
function scd_menu_tree( $location ) {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return null;
	}
	$items = wp_get_nav_menu_items( $locations[ $location ], array( 'update_post_term_cache' => false ) );
	if ( ! $items ) {
		return null;
	}
	_wp_menu_item_classes_by_context( $items );
	$by_id = array();
	foreach ( $items as $item ) {
		$item->children       = array();
		$by_id[ $item->ID ] = $item;
	}
	$tree = array();
	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;
		if ( $parent && isset( $by_id[ $parent ] ) ) {
			$by_id[ $parent ]->children[] = $item;
		} else {
			$tree[] = $item;
		}
	}
	return $tree;
}

/**
 * Is this menu item (or any descendant) the current page?
 *
 * @param WP_Post $item Menu item.
 * @return bool
 */
function scd_menu_item_is_current( $item ) {
	if ( ! empty( $item->current ) ) {
		return true;
	}
	foreach ( $item->children as $child ) {
		if ( scd_menu_item_is_current( $child ) ) {
			return true;
		}
	}
	return false;
}

/**
 * A link inside a menu, with aria-current and the item's own classes.
 *
 * @param WP_Post $item  Menu item.
 * @param string  $class Classes.
 * @return string
 */
function scd_menu_link( $item, $class = '' ) {
	$classes = trim( $class . ' ' . implode( ' ', array_filter( (array) $item->classes, 'scd_is_custom_menu_class' ) ) );
	$attrs   = $classes ? ' class="' . esc_attr( $classes ) . '"' : '';
	if ( ! empty( $item->current ) ) {
		$attrs .= ' aria-current="page"';
	}
	if ( ! empty( $item->target ) ) {
		$attrs .= ' target="' . esc_attr( $item->target ) . '"';
	}
	if ( ! empty( $item->xfn ) ) {
		$attrs .= ' rel="' . esc_attr( $item->xfn ) . '"';
	}
	return '<a href="' . esc_url( $item->url ) . '"' . $attrs . '>' . esc_html( $item->title ) . '</a>';
}

/**
 * Keep only classes the editor added (not WordPress's generated menu-item-* classes).
 *
 * @param string $class Class.
 * @return bool
 */
function scd_is_custom_menu_class( $class ) {
	return $class && 0 !== strpos( $class, 'menu-item' ) && 0 !== strpos( $class, 'current' ) && 'page_item' !== $class && 0 !== strpos( $class, 'page-item' );
}

/**
 * Main navigation list.
 *
 * @return string
 */
function scd_primary_menu() {
	$tree = scd_menu_tree( 'primary' );
	if ( null === $tree ) {
		$tree = scd_default_primary_tree();
	}
	$out = '';
	$n   = 0;
	foreach ( $tree as $item ) {
		++$n;
		$is_mega = false;
		foreach ( $item->children as $child ) {
			if ( $child->children ) {
				$is_mega = true;
				break;
			}
		}
		$current = scd_menu_item_is_current( $item ) ? ' is-current' : '';
		if ( ! $item->children ) {
			$extra = ( 1 === $n && untrailingslashit( $item->url ) === untrailingslashit( home_url() ) ) ? ' nav__link--home' : '';
			// The Blog link stays highlighted while reading a post (WordPress marks it current_page_parent).
			if ( empty( $item->current ) && in_array( 'current_page_parent', (array) $item->classes, true ) ) {
				$extra .= ' is-current';
			}
			$out .= '<li>' . scd_menu_link( $item, 'nav__link' . $extra ) . '</li>';
			continue;
		}
		$panel_id = $is_mega ? 'services-panel' : 'menu-' . sanitize_html_class( sanitize_title( $item->title ) );
		$toggle   = sprintf(
			'<button class="nav__toggle%s" type="button" aria-expanded="false" aria-controls="%s" data-panel-toggle>%s %s</button>',
			esc_attr( $current ),
			esc_attr( $panel_id ),
			esc_html( $item->title ),
			scd_ui_icon( 'chev' )
		);
		if ( $is_mega ) {
			$out .= '<li class="has-panel">' . $toggle . scd_mega_panel( $item, $panel_id ) . '</li>';
		} else {
			$lis = '';
			foreach ( $item->children as $child ) {
				$lis .= '<li>' . scd_menu_link( $child ) . '</li>';
			}
			$out .= '<li class="has-drop">' . $toggle . '<div class="drop" id="' . esc_attr( $panel_id ) . '"><ul>' . $lis . '</ul></div></li>';
		}
	}
	return '<ul class="nav__list">' . $out . '</ul>';
}

/**
 * Services mega panel.
 *
 * @param WP_Post $item     Top-level menu item.
 * @param string  $panel_id Panel ID.
 * @return string
 */
function scd_mega_panel( $item, $panel_id ) {
	$heading = $item->attr_title ? $item->attr_title : __( 'Our services', 'south-cairns-dental' );
	$intro   = '<div class="mega__intro"><h2>' . esc_html( $heading ) . '</h2>';
	if ( $item->description ) {
		$intro .= '<p>' . esc_html( $item->description ) . '</p>';
	}
	$cols  = '';
	$leafs = array();
	foreach ( $item->children as $child ) {
		if ( $child->children ) {
			$lis = '';
			foreach ( $child->children as $link ) {
				$lis .= '<li>' . scd_menu_link( $link ) . '</li>';
			}
			$cols .= '<div class="mega__col"><h3>' . esc_html( $child->title ) . '</h3><ul>' . $lis . '</ul></div>';
		} else {
			$leafs[] = $child;
		}
	}
	$extra = '';
	foreach ( $leafs as $i => $leaf ) {
		$urgent = in_array( 'is-urgent', (array) $leaf->classes, true );
		if ( $urgent ) {
			$intro .= '<div class="mega__urgent">' . scd_menu_link( $leaf ) . '</div>';
		} else {
			$intro .= scd_menu_link( $leaf, 'textlink' );
		}
		$extra .= '<li>' . scd_menu_link( $leaf, $urgent ? 'mega__urgent-link' : '' ) . '</li>';
	}
	$intro .= '</div>';
	$extra  = $extra ? '<ul class="mega__mobile-extra">' . $extra . '</ul>' : '';
	return '<div class="mega" id="' . esc_attr( $panel_id ) . '"><div class="wrap mega__grid">' . $intro . $cols . $extra . '</div></div>';
}

/**
 * A plain list of links for a footer location.
 *
 * @param string $location Menu location.
 * @return string '' when no menu is assigned.
 */
function scd_footer_menu( $location ) {
	$tree = scd_menu_tree( $location );
	if ( null === $tree ) {
		$tree = scd_default_footer_tree( $location );
	}
	if ( ! $tree ) {
		return '';
	}
	$lis = '';
	foreach ( $tree as $item ) {
		$lis .= '<li>' . scd_menu_link( $item ) . '</li>';
	}
	return '<ul>' . $lis . '</ul>';
}

/**
 * The menu's name, used as the footer column heading.
 *
 * @param string $location Menu location.
 * @param string $fallback Fallback heading.
 * @return string
 */
function scd_menu_name( $location, $fallback ) {
	$locations = get_nav_menu_locations();
	if ( ! empty( $locations[ $location ] ) ) {
		$menu = wp_get_nav_menu_object( $locations[ $location ] );
		if ( $menu ) {
			return $menu->name;
		}
	}
	return $fallback;
}

/**
 * Fallback footer links (until menus are assigned), matching the static site. Only pages that exist are listed.
 *
 * @param string $location Footer menu location.
 * @return object[]
 */
function scd_default_footer_tree( $location ) {
	$pages = array(
		'footer_about'    => array( 'about' => __( 'About South Cairns Dental', 'south-cairns-dental' ), 'team' => __( 'Meet our dentists', 'south-cairns-dental' ), 'new-patients' => __( 'New patients', 'south-cairns-dental' ) ),
		'footer_patients' => array( 'faq' => __( 'Frequently asked questions', 'south-cairns-dental' ), 'special-offers' => __( 'Special offers', 'south-cairns-dental' ), 'payments' => __( 'Payments', 'south-cairns-dental' ), 'blog' => __( 'Blog', 'south-cairns-dental' ), 'contact' => __( 'Contact us', 'south-cairns-dental' ), 'privacy' => __( 'Privacy policy', 'south-cairns-dental' ) ),
		'footer_legal'    => array( 'privacy' => __( 'Privacy policy', 'south-cairns-dental' ) ),
	);
	$links = array();
	if ( 'footer_services' === $location ) {
		foreach ( scd_service_pages() as $page ) {
			$links[ $page->ID ] = get_the_title( $page );
		}
	} elseif ( isset( $pages[ $location ] ) ) {
		foreach ( $pages[ $location ] as $slug => $label ) {
			$page = get_page_by_path( $slug );
			if ( $page && 'publish' === $page->post_status ) {
				$links[ $page->ID ] = $label;
			}
		}
	}
	$tree = array();
	foreach ( $links as $id => $label ) {
		$tree[] = (object) array( 'title' => $label, 'url' => get_permalink( $id ), 'current' => is_page( $id ), 'children' => array(), 'classes' => array(), 'target' => '', 'xfn' => '' );
	}
	return $tree;
}

/**
 * Fallback main menu (until one is assigned in Appearance > Menus), matching the static site.
 *
 * @return object[]
 */
function scd_default_primary_tree() {
	$mk      = function ( $title, $slug, $children = array(), $classes = array() ) {
		$url = '' === $slug ? home_url( '/' ) : ( '#' === $slug ? '#' : scd_page_url( $slug ) );
		$cur = false;
		if ( '' === $slug ) {
			$cur = is_front_page();
		} elseif ( '#' !== $slug ) {
			$page = get_page_by_path( $slug );
			$cur  = $page && is_page( $page->ID );
		}
		return (object) array(
			'title'       => $title,
			'url'         => $url,
			'current'     => $cur,
			'children'    => $children,
			'classes'     => $classes,
			'attr_title'  => '',
			'description' => '',
			'target'      => '',
			'xfn'         => '',
		);
	};
	$groups  = array();
	foreach ( scd_service_groups() as $key => $label ) {
		if ( 'urgent' === $key ) {
			continue;
		}
		$links = array();
		foreach ( scd_service_pages( $key ) as $page ) {
			$links[] = $mk( get_the_title( $page ), $page->post_name );
		}
		if ( $links ) {
			$groups[] = $mk( $label, '#', $links );
		}
	}
	$services              = $mk(
		__( 'Services', 'south-cairns-dental' ),
		'#',
		array_merge(
			array(
				$mk( __( 'Services overview', 'south-cairns-dental' ), 'services' ),
				$mk( __( 'Emergency dental care', 'south-cairns-dental' ), 'emergency-dentist', array(), array( 'is-urgent' ) ),
			),
			$groups
		)
	);
	$services->description = __( 'General, restorative and cosmetic dentistry for children and adults, all under one roof in Edmonton.', 'south-cairns-dental' );
	if ( ! $groups ) {
		$services = $mk( __( 'Services', 'south-cairns-dental' ), 'services' );
	}
	return array(
		$mk( __( 'Home', 'south-cairns-dental' ), '' ),
		$mk(
			__( 'About us', 'south-cairns-dental' ),
			'#',
			array(
				$mk( __( 'About South Cairns Dental', 'south-cairns-dental' ), 'about' ),
				$mk( __( 'Meet our dentists', 'south-cairns-dental' ), 'team' ),
				$mk( __( 'New patients', 'south-cairns-dental' ), 'new-patients' ),
			)
		),
		$services,
		$mk(
			__( 'Patient information', 'south-cairns-dental' ),
			'#',
			array(
				$mk( __( 'Frequently asked questions', 'south-cairns-dental' ), 'faq' ),
				$mk( __( 'Special offers', 'south-cairns-dental' ), 'special-offers' ),
				$mk( __( 'Payments', 'south-cairns-dental' ), 'payments' ),
				$mk( __( 'Emergency dental care', 'south-cairns-dental' ), 'emergency-dentist' ),
			)
		),
		$mk( __( 'Blog', 'south-cairns-dental' ), 'blog' ),
		$mk( __( 'Contact us', 'south-cairns-dental' ), 'contact' ),
	);
}
