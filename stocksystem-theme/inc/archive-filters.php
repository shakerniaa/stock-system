<?php
/**
 * Archive filter sidebar: query modification + URL/state helpers shared
 * by taxonomy-product_cat.php, taxonomy-product_brand.php, and search.php.
 * Source: 02 Category.dc.html (brand/CPU/RAM/price/in-stock),
 * 13 Search Results.dc.html §13-B (+ grading/battery/runtime facets).
 *
 * GET params: brand[], cpu[], ram[], grading[], min_price, max_price
 * (native WooCommerce — WC_Query::price_filter() already applies these),
 * battery_min, runtime, in_stock.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Taxonomy facets available in the sidebar, keyed by the GET param name.
 * Attribute taxonomies (cpu/ram) only appear once the store admin has
 * actually created them under Products → Attributes; taxonomy_exists()
 * keeps the facet from rendering (and from being queried) until then.
 */
function stocksystem_archive_taxonomy_facets() {
	return array(
		'brand'    => array(
			'taxonomy' => 'product_brand',
			'label'    => __( 'برند', 'stocksystem' ),
		),
		'grading'  => array(
			'taxonomy' => 'product_grading',
			'label'    => __( 'گرید ظاهری', 'stocksystem' ),
		),
		'cpu'      => array(
			'taxonomy' => function_exists( 'wc_attribute_taxonomy_name' ) ? wc_attribute_taxonomy_name( 'cpu' ) : 'pa_cpu',
			'label'    => __( 'پردازنده', 'stocksystem' ),
		),
		'ram'      => array(
			'taxonomy' => function_exists( 'wc_attribute_taxonomy_name' ) ? wc_attribute_taxonomy_name( 'ram' ) : 'pa_ram',
			'label'    => __( 'حافظهٔ RAM', 'stocksystem' ),
		),
	);
}

/**
 * Published products carrying $term, limited to the product category being
 * browsed (when on one). WordPress' stored term count is unreliable for the
 * brand taxonomy, and a per-category count is what a filter should show.
 */
function stocksystem_facet_term_count( $term ) {
	static $cache = array();

	$scope = is_tax( 'product_cat' ) ? (int) get_queried_object_id() : 0;
	$key   = $term->term_id . ':' . $scope;

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$tax_query = array(
		array(
			'taxonomy' => $term->taxonomy,
			'field'    => 'term_id',
			'terms'    => array( $term->term_id ),
		),
	);

	if ( $scope ) {
		$tax_query[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => array( $scope ),
		);
	}

	$query = new WP_Query(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => false,
			'tax_query'      => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		)
	);

	$cache[ $key ] = (int) $query->found_posts;

	return $cache[ $key ];
}

/**
 * Applies the sidebar's checkbox/toggle facets to the main archive query.
 * Price range is left to WooCommerce's own WC_Query::price_filter(),
 * which already reads min_price/max_price unconditionally.
 */
function stocksystem_apply_archive_filters( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->is_post_type_archive( 'product' ) && ! $query->is_tax( array( 'product_cat', 'product_brand' ) ) && ! $query->is_search() ) {
		return;
	}

	$tax_query = $query->get( 'tax_query' );
	if ( ! is_array( $tax_query ) ) {
		$tax_query = array();
	}

	foreach ( stocksystem_archive_taxonomy_facets() as $param => $facet ) {
		if ( empty( $_GET[ $param ] ) || ! taxonomy_exists( $facet['taxonomy'] ) ) {
			continue;
		}

		$values = array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $param ] ) );

		$tax_query[] = array(
			'taxonomy' => $facet['taxonomy'],
			'field'    => 'slug',
			'terms'    => $values,
		);
	}

	if ( count( $tax_query ) > 1 && ! isset( $tax_query['relation'] ) ) {
		$tax_query['relation'] = 'AND';
	}

	if ( ! empty( $tax_query ) ) {
		$query->set( 'tax_query', $tax_query );
	}

	$meta_query = $query->get( 'meta_query' );
	if ( ! is_array( $meta_query ) ) {
		$meta_query = array();
	}

	if ( ! empty( $_GET['in_stock'] ) ) {
		$meta_query[] = array(
			'key'   => '_stock_status',
			'value' => 'instock',
		);
	}

	// Stock-specific facets (13-B): battery health %, runtime hours.
	// Meta keys per README "Custom data the theme needs".
	if ( ! empty( $_GET['battery_min'] ) ) {
		$meta_query[] = array(
			'key'     => '_battery_health',
			'value'   => (int) $_GET['battery_min'],
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}

	if ( ! empty( $_GET['runtime'] ) ) {
		$buckets = array(
			'low'  => array( 0, 2000 ),
			'mid'  => array( 2000, 5000 ),
			'high' => array( 5000, 999999 ),
		);
		$bucket = sanitize_key( $_GET['runtime'] );
		if ( isset( $buckets[ $bucket ] ) ) {
			$meta_query[] = array(
				'key'     => '_runtime_hours',
				'value'   => $buckets[ $bucket ],
				'compare' => 'BETWEEN',
				'type'    => 'NUMERIC',
			);
		}
	}

	if ( count( $meta_query ) > 1 && ! isset( $meta_query['relation'] ) ) {
		$meta_query['relation'] = 'AND';
	}

	if ( ! empty( $meta_query ) ) {
		$query->set( 'meta_query', $meta_query );
	}
}
add_action( 'pre_get_posts', 'stocksystem_apply_archive_filters' );

/**
 * Current request URL with $param's $value added to (or, for a single-
 * value param, replacing) its GET array — for a filter checkbox's no-JS
 * href fallback.
 */
function stocksystem_filter_add_url( $param, $value ) {
	$current = isset( $_GET[ $param ] ) ? (array) wp_unslash( $_GET[ $param ] ) : array();
	if ( ! in_array( $value, $current, true ) ) {
		$current[] = $value;
	}
	// 2-arg add_query_arg() operates on the current request URI (including
	// its existing query string), so every other active filter is kept.
	return esc_url( add_query_arg( $param, $current ) );
}

/**
 * Current request URL with $value removed from $param's GET array — used
 * by both the sidebar checkboxes (already-checked state) and the active
 * filter chips' "×" link.
 */
function stocksystem_filter_remove_url( $param, $value ) {
	$current = isset( $_GET[ $param ] ) ? (array) wp_unslash( $_GET[ $param ] ) : array();
	$current = array_diff( $current, array( $value ) );

	if ( empty( $current ) ) {
		return esc_url( remove_query_arg( $param ) );
	}

	return esc_url( add_query_arg( $param, $current ) );
}

/**
 * Flat list of active facet chips for the "فیلترهای فعال" row: taxonomy
 * checkboxes (with resolved term names) plus the price range, each with
 * a remove URL.
 */
function stocksystem_active_filter_chips() {
	$chips = array();

	foreach ( stocksystem_archive_taxonomy_facets() as $param => $facet ) {
		if ( empty( $_GET[ $param ] ) || ! taxonomy_exists( $facet['taxonomy'] ) ) {
			continue;
		}

		foreach ( (array) wp_unslash( $_GET[ $param ] ) as $slug ) {
			$term = get_term_by( 'slug', sanitize_title( $slug ), $facet['taxonomy'] );
			if ( ! $term ) {
				continue;
			}
			$chips[] = array(
				'label'      => $term->name,
				'remove_url' => stocksystem_filter_remove_url( $param, $slug ),
			);
		}
	}

	if ( ! empty( $_GET['min_price'] ) || ! empty( $_GET['max_price'] ) ) {
		$min = ! empty( $_GET['min_price'] ) ? stocksystem_format_number( wp_unslash( $_GET['min_price'] ) ) : '';
		$max = ! empty( $_GET['max_price'] ) ? stocksystem_format_number( wp_unslash( $_GET['max_price'] ) ) : '';

		$chips[] = array(
			/* translators: 1: min price, 2: max price, Persian digits */
			'label'      => trim( sprintf( __( '%1$s تا %2$s تومان', 'stocksystem' ), $min, $max ) ),
			'remove_url' => esc_url( remove_query_arg( array( 'min_price', 'max_price' ) ) ),
		);
	}

	if ( ! empty( $_GET['in_stock'] ) ) {
		$chips[] = array(
			'label'      => __( 'فقط موجود', 'stocksystem' ),
			'remove_url' => esc_url( remove_query_arg( 'in_stock' ) ),
		);
	}

	return $chips;
}

/**
 * The bare archive URL with every filter param stripped, keeping only the
 * search query itself (on search.php) — "حذف همه".
 */
function stocksystem_clear_all_filters_url() {
	$filter_params = array_merge(
		array_keys( stocksystem_archive_taxonomy_facets() ),
		array( 'min_price', 'max_price', 'battery_min', 'runtime', 'in_stock' )
	);
	return esc_url( remove_query_arg( $filter_params ) );
}
