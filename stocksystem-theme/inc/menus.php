<?php
/**
 * Editable link lists — three menu locations under Appearance → Menus, each
 * falling back to the built-in links while nothing is assigned:
 *
 *   header_extra     the links after the category names in the main nav bar
 *   topbar_links     the two links at the end of the thin top strip
 *   footer_services  the «خدمات و راهنما» column of the footer
 *
 * The category links themselves (nav bar, mega menu, footer column, home tiles)
 * come from WooCommerce categories: Products → Categories.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_register_menus() {
	register_nav_menus(
		array(
			'header_extra'    => __( 'نوار اصلی — لینک‌های بعد از دسته‌ها', 'stocksystem' ),
			'topbar_links'    => __( 'نوار بالای سایت — لینک‌های سمت چپ', 'stocksystem' ),
			'footer_services' => __( 'فوتر — ستون خدمات و راهنما', 'stocksystem' ),
		)
	);
}
add_action( 'after_setup_theme', 'stocksystem_register_menus' );

/**
 * Links for a menu location: the assigned menu's items, or `$defaults`
 * (array of array( 'title' =>, 'url' =>, 'current' => bool )).
 *
 * @return array[] Each with title, url, current.
 */
function stocksystem_menu_links( $location, $defaults ) {
	$locations = get_nav_menu_locations();

	if ( empty( $locations[ $location ] ) ) {
		return $defaults;
	}

	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( empty( $items ) ) {
		return $defaults;
	}

	$request_path = isset( $_SERVER['REQUEST_URI'] ) ? untrailingslashit( (string) wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$links        = array();
	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent ) {
			continue; // One level only.
		}
		$links[] = array(
			'title'   => $item->title,
			'url'     => $item->url,
			'current' => '' !== $request_path && untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) ) === $request_path,
		);
	}

	return $links ? $links : $defaults;
}
