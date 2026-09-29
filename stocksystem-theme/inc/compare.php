<?php
/**
 * Product comparison.
 *
 * Builds on the grouped-spec system (inc/product-specs.php): the same
 * 55-attribute catalogue that drives the «مشخصات فنی» panel is pivoted
 * into a side-by-side table, plus each device's own test-report figures
 * — which is the comparison a refurbished buyer actually needs and that
 * marketplaces can't show.
 *
 * The selection lives in the visitor's browser (localStorage, see
 * assets/js/compare.js) rather than in user meta: comparing is a
 * throwaway act and requiring a login for it would kill the feature.
 * The compare PAGE is nonetheless server-rendered from ?ids= in the URL,
 * so a comparison is shareable, printable and works on first paint.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Hard cap: more than four columns stops being readable on any screen. */
const STOCKSYSTEM_COMPARE_MAX = 4;

/** The compare page's URL, with optional product ids. */
function stocksystem_compare_url( $ids = array() ) {
	$page_id = (int) get_option( 'stocksystem_compare_page_id' );
	$url     = $page_id ? get_permalink( $page_id ) : home_url( '/compare/' );

	if ( empty( $ids ) ) {
		return $url;
	}

	return add_query_arg( 'ids', implode( ',', array_map( 'absint', $ids ) ), $url );
}

/** Product ids from ?ids=, validated down to real, visible products. */
function stocksystem_compare_requested_products() {
	// phpcs:ignore WordPress.Security.NonceVerification -- read-only, no state change.
	$raw = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( $_GET['ids'] ) ) : '';

	if ( '' === $raw ) {
		return array();
	}

	$products = array();

	foreach ( array_slice( array_unique( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) ), 0, STOCKSYSTEM_COMPARE_MAX ) as $id ) {
		$product = wc_get_product( $id );
		if ( $product && 'publish' === $product->get_status() && $product->is_visible() ) {
			$products[] = $product;
		}
	}

	return $products;
}

/**
 * The comparison table: every row any of the products has a value for,
 * grouped and ordered exactly like the single-product spec panel, with
 * a value per product ('' where that product has nothing).
 *
 * Rows where every product says the same thing are flagged `same` so
 * the front end can offer a "differences only" view — with ~30 spec
 * rows, that is the difference between a useful table and a wall.
 *
 * @return array<int, array{key:string,title:string,rows:array}>
 */
function stocksystem_compare_table( array $products ) {
	if ( empty( $products ) ) {
		return array();
	}

	$values = array();   // product index => slug => value
	$reports = array();  // product index => test report

	foreach ( $products as $i => $product ) {
		$values[ $i ]  = function_exists( 'stocksystem_get_product_attribute_values' ) ? stocksystem_get_product_attribute_values( $product ) : array();
		$reports[ $i ] = function_exists( 'stocksystem_get_test_report' ) ? stocksystem_get_test_report( $product->get_id() ) : null;
	}

	$groups = array();

	/* ---- Test report first: the store's own measurements ---- */
	$report_rows = array(
		'battery' => __( 'سلامت باتری', 'stocksystem' ),
		'runtime' => __( 'ساعت کارکرد', 'stocksystem' ),
		'body'    => __( 'وضعیت بدنه', 'stocksystem' ),
		'pixels'  => __( 'پیکسل سوخته', 'stocksystem' ),
	);

	$test_rows = array();

	foreach ( $report_rows as $key => $label ) {
		$cells = array();
		$any   = false;

		foreach ( $products as $i => $product ) {
			$value = ( $reports[ $i ] && '' !== (string) $reports[ $i ][ $key ] ) ? (string) $reports[ $i ][ $key ] : '';
			if ( '' !== $value ) {
				$any = true;
				if ( 'battery' === $key ) {
					$value = stocksystem_to_persian_digits( $value ) . '٪';
				} elseif ( 'runtime' === $key ) {
					$value = stocksystem_format_number( $value );
				}
			}
			$cells[] = $value;
		}

		if ( $any ) {
			$test_rows[] = array( 'label' => $label, 'cells' => $cells, 'same' => 1 === count( array_unique( $cells ) ) );
		}
	}

	if ( ! empty( $test_rows ) ) {
		$groups[] = array( 'key' => 'test', 'title' => __( 'برگهٔ تست دستگاه', 'stocksystem' ), 'rows' => $test_rows );
	}

	/* ---- Then the spec attributes, in the catalogue's own order ---- */
	$map    = function_exists( 'stocksystem_spec_group_map' ) ? stocksystem_spec_group_map() : array();
	$titles = function_exists( 'stocksystem_spec_group_titles' ) ? stocksystem_spec_group_titles() : array();

	// Every slug any product carries, de-duplicated.
	$slugs = array();
	foreach ( $values as $set ) {
		foreach ( array_keys( $set ) as $slug ) {
			$slugs[ $slug ] = true;
		}
	}
	$slugs = array_keys( $slugs );

	$by_group = array();

	foreach ( $slugs as $slug ) {
		$meta  = isset( $map[ $slug ] ) ? $map[ $slug ] : array( 'group' => 'other', 'order' => 999 );
		$cells = array();

		foreach ( $products as $i => $product ) {
			$cells[] = isset( $values[ $i ][ $slug ] ) ? (string) $values[ $i ][ $slug ] : '';
		}

		$taxonomy = 'pa_' . $slug;

		$by_group[ $meta['group'] ][] = array(
			'order' => $meta['order'],
			'label' => wc_attribute_label( taxonomy_exists( $taxonomy ) ? $taxonomy : $slug ),
			'cells' => $cells,
			'same'  => 1 === count( array_unique( $cells ) ),
		);
	}

	foreach ( array_keys( $titles ) as $group_key ) {
		if ( empty( $by_group[ $group_key ] ) ) {
			continue;
		}

		$rows = $by_group[ $group_key ];
		usort(
			$rows,
			function ( $a, $b ) {
				return $a['order'] <=> $b['order'];
			}
		);

		$groups[] = array( 'key' => $group_key, 'title' => $titles[ $group_key ], 'rows' => $rows );
	}

	return $groups;
}

/* -------------------------------------------------------------------------
 * The compare page
 * ---------------------------------------------------------------------- */

function stocksystem_create_compare_page() {
	if ( get_option( 'stocksystem_compare_page_id' ) ) {
		return;
	}

	$existing = get_page_by_path( 'compare' );

	$page_id = $existing ? $existing->ID : wp_insert_post(
		array(
			'post_title'     => __( 'مقایسهٔ محصولات', 'stocksystem' ),
			'post_name'      => 'compare',
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'page_template'  => 'page-templates/compare.php',
			'comment_status' => 'closed',
		)
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_post_meta( $page_id, '_wp_page_template', 'page-templates/compare.php' );
		update_option( 'stocksystem_compare_page_id', (int) $page_id );
	}
}
add_action( 'after_switch_theme', 'stocksystem_create_compare_page' );
// after_switch_theme alone would never fire for a site that only uploads a
// new theme zip over the active theme, so the page is also created lazily.
// One get_option() per admin request when it already exists.
add_action( 'admin_init', 'stocksystem_create_compare_page' );

/**
 * A comparison of arbitrary products is not a page Google should index:
 * it is user-generated, near-infinite in combinations, and duplicates
 * content that already lives on the product pages.
 */
function stocksystem_compare_noindex( $robots ) {
	if ( stocksystem_is_compare_page() ) {
		$robots['noindex']  = true;
		$robots['nofollow'] = true;
	}

	return $robots;
}
add_filter( 'wp_robots', 'stocksystem_compare_noindex', 20 );

function stocksystem_is_compare_page() {
	$page_id = (int) get_option( 'stocksystem_compare_page_id' );

	return $page_id && is_page( $page_id );
}

/**
 * Hands the compare UI the strings and limits it needs, so nothing
 * user-visible is hardcoded in JavaScript.
 */
function stocksystem_compare_script_data() {
	if ( is_admin() ) {
		return;
	}

	wp_localize_script(
		'stocksystem-compare',
		'stocksystemCompare',
		array(
			'max'        => STOCKSYSTEM_COMPARE_MAX,
			'url'        => stocksystem_compare_url(),
			'labelAdd'   => __( 'افزودن به مقایسه', 'stocksystem' ),
			'labelAdded' => __( 'در مقایسه', 'stocksystem' ),
			'compare'    => __( 'مقایسه', 'stocksystem' ),
			'clear'      => __( 'پاک کردن', 'stocksystem' ),
			'full'       => sprintf(
				/* translators: %s: maximum number of products */
				__( 'حداکثر %s محصول را می‌توان مقایسه کرد.', 'stocksystem' ),
				stocksystem_to_persian_digits( STOCKSYSTEM_COMPARE_MAX )
			),
		)
	);
}
add_action( 'wp_enqueue_scripts', 'stocksystem_compare_script_data', 20 );
