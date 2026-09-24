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
	static $memo = null;

	// Called by the nav bar, mega menu, mobile drawer, footer and homepage — build once per request.
	if ( null === $memo ) {
		$memo = stocksystem_nav_categories_build();
	}

	return $memo;
}

function stocksystem_nav_categories_build() {
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

	// WooCommerce always creates a default "Uncategorized" bucket for products
	// with no category; it isn't a real shop category and shouldn't appear in
	// the nav, mega menu, homepage tiles or footer. Its slug is only literally
	// "uncategorized" on an English install — on a Persian one WordPress gives
	// it a Persian slug — so the term id (not the slug) is what identifies it.
	$exclude = array();
	$default_id = (int) get_option( 'default_product_cat' );
	if ( $default_id ) {
		$exclude[] = $default_id;
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

	// Show / hide / rename / reorder from Appearance → «منو و فوتر».
	return stocksystem_nav_apply_category_settings(
		array_map(
			function ( $term ) {
				return (object) array(
					'id'    => $term->term_id,
					'slug'  => $term->slug,
					'name'  => $term->name,
					'count' => $term->count,
					'url'   => get_term_link( $term ),
				);
			},
			$terms
		)
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
 * Price-range facets for the mega menu and the shop page. Boundaries are a UI
 * facet (not a business number): edit them in Appearance → «منو و فوتر»;
 * still filterable with `stocksystem_price_ranges`.
 */
function stocksystem_price_ranges_default() {
	return array(
		array(
			'label' => __( 'تا ۱۵ میلیون', 'stocksystem' ),
			'min'   => 0,
			'max'   => 15000000,
		),
		array(
			'label' => __( '۱۵ تا ۲۵ میلیون', 'stocksystem' ),
			'min'   => 15000000,
			'max'   => 25000000,
		),
		array(
			'label' => __( '۲۵ تا ۴۰ میلیون', 'stocksystem' ),
			'min'   => 25000000,
			'max'   => 40000000,
		),
		array(
			'label' => __( 'بالای ۴۰ میلیون', 'stocksystem' ),
			'min'   => 40000000,
			'max'   => null,
		),
	);
}

function stocksystem_price_ranges() {
	$ranges = stocksystem_price_ranges_default();
	$custom = stocksystem_nav_settings()['prices'];

	if ( ! empty( $custom ) ) { // Edited in Appearance → «منو و فوتر».
		$ranges = array();
		foreach ( $custom as $row ) {
			$ranges[] = array(
				'label' => $row['label'],
				'min'   => (int) $row['min'],
				'max'   => ( '' === $row['max'] || null === $row['max'] ) ? null : (int) $row['max'],
			);
		}
	}

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

/**
 * Per-category mega-menu content: the brands that actually have products in
 * the category (with counts) and a featured product from it, so hovering a
 * category in the menu changes what the other columns offer instead of
 * showing the same global lists. Products are stored as ids; the whole map
 * is cached briefly and dropped whenever a product or term changes.
 *
 * @return array[] category term id => [ 'brands' => [ [name, slug, count] ], 'featured' => product id|0 ]
 */
function stocksystem_mega_menu_data() {
	static $memo = null;

	if ( null !== $memo ) {
		return $memo;
	}

	$cached = get_transient( 'stocksystem_mega_menu_data' );
	if ( is_array( $cached ) ) {
		$memo = $cached;
		return $memo;
	}

	global $wpdb;

	$data = array();

	if ( taxonomy_exists( 'product_brand' ) ) {
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$rows = $wpdb->get_results(
			"SELECT cat.term_id AS cat_id, t.name AS name, t.slug AS slug, COUNT(DISTINCT p.ID) AS total
			FROM {$wpdb->term_relationships} trc
			INNER JOIN {$wpdb->term_taxonomy} cat ON cat.term_taxonomy_id = trc.term_taxonomy_id AND cat.taxonomy = 'product_cat'
			INNER JOIN {$wpdb->term_relationships} trb ON trb.object_id = trc.object_id
			INNER JOIN {$wpdb->term_taxonomy} br ON br.term_taxonomy_id = trb.term_taxonomy_id AND br.taxonomy = 'product_brand'
			INNER JOIN {$wpdb->terms} t ON t.term_id = br.term_id
			INNER JOIN {$wpdb->posts} p ON p.ID = trc.object_id AND p.post_type = 'product' AND p.post_status = 'publish'
			GROUP BY cat.term_id, t.term_id, t.name, t.slug
			ORDER BY total DESC, t.name ASC"
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery

		foreach ( (array) $rows as $row ) {
			$data[ (int) $row->cat_id ]['brands'][] = array(
				'name'  => $row->name,
				'slug'  => $row->slug,
				'count' => (int) $row->total,
			);
		}
	}

	if ( function_exists( 'wc_get_products' ) ) {
		foreach ( stocksystem_nav_categories() as $category ) {
			if ( empty( $category->id ) ) {
				continue;
			}

			$featured = 0;
			foreach ( array( array( 'tag' => array( 'featured' ) ), array( 'on_sale' => true ), array() ) as $extra ) {
				$found = wc_get_products(
					array_merge(
						array(
							'limit'        => 1,
							'status'       => 'publish',
							'stock_status' => 'instock',
							'category'     => array( $category->slug ),
							'orderby'  => 'date',
							'order'    => 'DESC',
							'return'   => 'ids',
						),
						$extra
					)
				);

				if ( ! empty( $found ) ) {
					$featured = (int) $found[0];
					break;
				}
			}

			$data[ (int) $category->id ]['featured'] = $featured;
		}
	}

	set_transient( 'stocksystem_mega_menu_data', $data, 15 * MINUTE_IN_SECONDS );
	$memo = $data;

	return $memo;
}

function stocksystem_flush_mega_menu_data() {
	delete_transient( 'stocksystem_mega_menu_data' );
}
add_action( 'save_post_product', 'stocksystem_flush_mega_menu_data' );
add_action( 'deleted_post', 'stocksystem_flush_mega_menu_data' );
add_action( 'set_object_terms', 'stocksystem_flush_mega_menu_data' );
add_action( 'edited_term', 'stocksystem_flush_mega_menu_data' );

/**
 * Stable key tying a category link (nav bar or mega menu) to its mega-menu
 * pane: the term id, or the list position for the placeholder categories.
 */
function stocksystem_mega_key( $category, $index ) {
	return ! empty( $category->id ) ? 'cat-' . $category->id : 'i-' . $index;
}
