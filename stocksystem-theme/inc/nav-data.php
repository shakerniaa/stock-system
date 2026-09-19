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

	// WooCommerce always creates an "Uncategorized" default bucket; it
	// isn't a real shop category and shouldn't appear in the nav, mega
	// menu, homepage tiles or footer.
	$exclude = array();
	$default = get_term( (int) get_option( 'default_product_cat' ), 'product_cat' );
	if ( $default && ! is_wp_error( $default ) && 'uncategorized' === $default->slug ) {
		$exclude[] = $default->term_id;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => false,
			'exclude'    => $exclude,
			'menu_order' => 'ASC', // WooCommerce's own manual category order (Products > Categories drag-sort).
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
 * Inner SVG paths (24x24 viewBox, stroke icons) for a product category,
 * matched by keyword on its name — 01 Home.dc.html gives each category
 * tile its own icon (laptop, all-in-one, tower, headset, wrench, …),
 * not one shared glyph. Falls back to a generic box.
 */
function stocksystem_category_icon( $name ) {
	$icons = array(
		'آل‌این‌وان' => '<rect x="2.5" y="4" width="19" height="12.5" rx="2"></rect><path d="M8 20h8M12 16.5V20"></path>',
		'مینی'       => '<rect x="6" y="3" width="12" height="18" rx="2"></rect><path d="M9.5 7h5M9.5 11h5"></path>',
		'کیس'        => '<rect x="6" y="3" width="12" height="18" rx="2"></rect><path d="M9.5 7h5M9.5 11h5"></path>',
		'مانیتور'    => '<rect x="3" y="4" width="18" height="12" rx="2"></rect><path d="M8 20h8M12 16v4"></path>',
		'قطعات'      => '<rect x="6.5" y="6.5" width="11" height="11" rx="2"></rect><path d="M9.5 2.5v4M14.5 2.5v4M9.5 17.5v4M14.5 17.5v4M2.5 9.5h4M2.5 14.5h4M17.5 9.5h4M17.5 14.5h4"></path>',
		'لوازم'      => '<rect x="2.5" y="12" width="5" height="8" rx="1.5"></rect><rect x="16.5" y="12" width="5" height="8" rx="1.5"></rect><path d="M4 12a8 8 0 0 1 16 0"></path>',
		'تعمیر'      => '<path d="M4 20l9-9"></path><path d="M14.5 9.5a3.5 3.5 0 0 0 4.8-4.6l-2.3 2.3-2.2-.6-.6-2.2 2.3-2.3a3.5 3.5 0 0 0-4.6 4.8"></path>',
		'لپ‌تاپ'     => '<rect x="4" y="4.5" width="16" height="11" rx="2"></rect><path d="M2 19h20"></path>',
	);

	foreach ( $icons as $keyword => $paths ) {
		if ( false !== mb_strpos( $name, $keyword ) ) {
			return $paths;
		}
	}

	return '<path d="M4 8l8-4 8 4v8l-8 4-8-4z"></path><path d="M4 8l8 4 8-4M12 12v8"></path>';
}

/**
 * Product brands for the mega menu brand column.
 * Falls back to the brand list shown in 11 Desktop States.dc.html.
 */
/**
 * Published-product count per brand term id. WordPress' stored term count is
 * unreliable for the brand taxonomy (always 0), so count relationships
 * directly — one query, cached for the request.
 *
 * @return int[] term_id => count
 */
function stocksystem_brand_product_counts() {
	static $counts = null;

	if ( null !== $counts ) {
		return $counts;
	}

	global $wpdb;

	// phpcs:disable WordPress.DB.DirectDatabaseQuery
	$rows = $wpdb->get_results(
		"SELECT tt.term_id AS term_id, COUNT(DISTINCT p.ID) AS total
		FROM {$wpdb->term_taxonomy} tt
		INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id AND p.post_type = 'product' AND p.post_status = 'publish'
		WHERE tt.taxonomy = 'product_brand'
		GROUP BY tt.term_id"
	);
	// phpcs:enable WordPress.DB.DirectDatabaseQuery

	$counts = array();
	foreach ( (array) $rows as $row ) {
		$counts[ (int) $row->term_id ] = (int) $row->total;
	}

	return $counts;
}

/**
 * Published products priced within a stocksystem_price_ranges() bucket
 * (min inclusive, max exclusive; null max = open-ended).
 */
function stocksystem_price_range_count( $range ) {
	$meta_query = array(
		array(
			'key'     => '_price',
			'value'   => (float) $range['min'],
			'compare' => '>=',
			'type'    => 'NUMERIC',
		),
	);

	if ( null !== $range['max'] ) {
		$meta_query[] = array(
			'key'     => '_price',
			'value'   => (float) $range['max'],
			'compare' => '<',
			'type'    => 'NUMERIC',
		);
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		)
	);

	return (int) $query->found_posts;
}

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
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $fallback;
	}

	// Only brands that actually have products, biggest first.
	$counts = stocksystem_brand_product_counts();
	$terms  = array_filter(
		$terms,
		function ( $term ) use ( $counts ) {
			return ! empty( $counts[ $term->term_id ] );
		}
	);

	if ( empty( $terms ) ) {
		return $fallback;
	}

	usort(
		$terms,
		function ( $a, $b ) use ( $counts ) {
			return ( $counts[ $b->term_id ] <=> $counts[ $a->term_id ] ) ?: strcmp( $a->name, $b->name );
		}
	);

	$terms = array_slice( $terms, 0, 5 );

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
