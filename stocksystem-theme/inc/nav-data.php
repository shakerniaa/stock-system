<?php
/**
 * Data helpers for header/footer navigation (categories, brands, price
 * facets). Reads real WooCommerce taxonomies when they exist; falls back
 * to the design's placeholder copy only so the templates render something
 * sane before the client's catalog is imported.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Top-level product categories for the nav bar / mega menu.
 * Falls back to the six categories shown in 01 Home.dc.html.
 */
function stocksystem_nav_categories() {
	$fallback = array(
		(object) array( 'name' => 'لپ‌تاپ استوک', 'count' => 0, 'url' => '#' ),
		(object) array( 'name' => 'آل‌این‌وان', 'count' => 0, 'url' => '#' ),
		(object) array( 'name' => 'کیس و مینی‌پی‌سی', 'count' => 0, 'url' => '#' ),
		(object) array( 'name' => 'مانیتور', 'count' => 0, 'url' => '#' ),
		(object) array( 'name' => 'قطعات و ارتقا', 'count' => 0, 'url' => '#' ),
		(object) array( 'name' => 'لوازم جانبی', 'count' => 0, 'url' => '#' ),
	);

	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return $fallback;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $fallback;
	}

	return array_map(
		function ( $term ) {
			return (object) array(
				'name'  => $term->name,
				'count' => $term->count,
				'url'   => get_term_link( $term ),
			);
		},
		$terms
	);
}

/**
 * Product brands for the mega menu brand column.
 * Falls back to the brand list shown in 11 Desktop States.dc.html.
 */
function stocksystem_nav_brands() {
	$fallback = array(
		(object) array( 'name' => 'HP', 'url' => '#' ),
		(object) array( 'name' => 'Dell', 'url' => '#' ),
		(object) array( 'name' => 'Lenovo', 'url' => '#' ),
		(object) array( 'name' => 'Apple', 'url' => '#' ),
		(object) array( 'name' => 'Asus', 'url' => '#' ),
	);

	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return $fallback;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_brand',
			'hide_empty' => false,
			'number'     => 5,
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $fallback;
	}

	return array_map(
		function ( $term ) {
			return (object) array(
				'name' => $term->name,
				'url'  => get_term_link( $term ),
			);
		},
		$terms
	);
}

/**
 * Price-range facets for the mega menu. Bucket boundaries are a filterable
 * UI facet, not a business number like warranty/rate — safe to ship as a
 * default, but exposed via filter so it stays editable without a code
 * change.
 */
function stocksystem_price_ranges() {
	$ranges = array(
		array(
			'label' => 'تا ۱۵ میلیون',
			'min'   => 0,
			'max'   => 15000000,
		),
		array(
			'label' => '۱۵ تا ۲۵ میلیون',
			'min'   => 15000000,
			'max'   => 25000000,
		),
		array(
			'label' => '۲۵ تا ۴۰ میلیون',
			'min'   => 25000000,
			'max'   => 40000000,
		),
		array(
			'label' => 'بالای ۴۰ میلیون',
			'min'   => 40000000,
			'max'   => null,
		),
	);

	return apply_filters( 'stocksystem_price_ranges', $ranges );
}

/**
 * Featured product for the mega menu promo tile — the newest featured
 * (marketing-tag) product, WooCommerce-driven, no static fallback data.
 */
function stocksystem_nav_featured_product() {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return null;
	}

	$products = wc_get_products(
		array(
			'limit'    => 1,
			'tag'      => array( 'featured' ),
			'status'   => 'publish',
			'orderby'  => 'date',
			'order'    => 'DESC',
		)
	);

	return ! empty( $products ) ? $products[0] : null;
}
