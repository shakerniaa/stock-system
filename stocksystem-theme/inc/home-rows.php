<?php
/**
 * Homepage product rows — as many rows as the owner wants, each with
 * its own heading, source and count.
 *
 * Before this, the homepage had exactly one product row hardcoded to
 * «پیشنهاد این هفته» (template-parts/home/featured.php). This adds a
 * repeater so rows can be added, reordered and removed: one row per
 * category, one for discounts, one for newest arrivals, and so on.
 *
 * The original «پیشنهاد این هفته» row is untouched and still controlled
 * from Appearance → «صفحهٔ اصلی»; these rows render after it.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Where a row's products can come from. */
function stocksystem_row_sources() {
	return array(
		'category'     => __( 'یک دستهٔ مشخص', 'stocksystem' ),
		'on_sale'      => __( 'تخفیف‌دارها', 'stocksystem' ),
		'newest'       => __( 'تازه‌ترین‌ها', 'stocksystem' ),
		'best_selling' => __( 'پرفروش‌ترین‌ها', 'stocksystem' ),
		'featured'     => __( 'محصولات ویژه (ستاره‌دار)', 'stocksystem' ),
		'manual'       => __( 'انتخاب دستی (شناسهٔ محصول‌ها)', 'stocksystem' ),
	);
}

/** product_cat terms as id => name, for the row's category picker. */
function stocksystem_row_category_options() {
	$options = array( '0' => __( '— انتخاب دسته —', 'stocksystem' ) );

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return $options;
	}

	$default_id = (int) get_option( 'default_product_cat' );

	foreach ( $terms as $term ) {
		// Same rule as the nav: the locale-dependent "Uncategorized" term
		// is matched by id, never by its slug (inc/nav-data.php).
		if ( $term->term_id === $default_id ) {
			continue;
		}
		$options[ (string) $term->term_id ] = $term->name;
	}

	return $options;
}

/** The rows that are switched on, in their configured order. */
function stocksystem_home_rows() {
	$rows = stocksystem_opt( 'rows' );
	$rows = isset( $rows['items'] ) && is_array( $rows['items'] ) ? $rows['items'] : array();

	$enabled = array();

	foreach ( $rows as $row ) {
		if ( empty( $row['enabled'] ) || '' === trim( (string) $row['heading'] ) ) {
			continue;
		}
		$enabled[] = $row;
	}

	usort(
		$enabled,
		function ( $a, $b ) {
			return (int) $a['order'] <=> (int) $b['order'];
		}
	);

	return $enabled;
}

/**
 * Resolves one row's configuration into actual products.
 *
 * Unlike the single «پیشنهاد این هفته» row, an empty result here is NOT
 * back-filled with "the newest products" — a row the owner explicitly
 * pointed at a category should disappear when that category is empty
 * rather than quietly showing something else under its heading.
 *
 * @return WC_Product[]
 */
function stocksystem_home_row_products( $row ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$count = max( 1, min( 12, (int) $row['count'] ) );
	$args  = array( 'limit' => $count, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' );

	switch ( $row['source'] ) {
		case 'category':
			$term = get_term( (int) $row['category'], 'product_cat' );
			if ( ! $term || is_wp_error( $term ) ) {
				return array();
			}
			$args['category'] = array( $term->slug );
			break;

		case 'on_sale':
			$args['on_sale'] = true;
			break;

		case 'best_selling':
			$args['orderby'] = 'popularity';
			break;

		case 'featured':
			$args['featured'] = true;
			break;

		case 'manual':
			$ids = array_filter( array_map( 'absint', preg_split( '/[\s,]+/', (string) $row['ids'] ) ) );
			if ( empty( $ids ) ) {
				return array();
			}
			$products = array();
			foreach ( array_slice( $ids, 0, $count ) as $id ) {
				$product = wc_get_product( $id );
				if ( $product && 'publish' === $product->get_status() ) {
					$products[] = $product;
				}
			}
			return $products;

		case 'newest':
		default:
			break;
	}

	return wc_get_products( $args );
}

/** The "see all" link for a row: explicit, else derived from its source. */
function stocksystem_home_row_link( $row ) {
	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

	if ( '' !== trim( (string) $row['link_url'] ) ) {
		return stocksystem_home_link( $row['link_url'], $shop_url );
	}

	switch ( $row['source'] ) {
		case 'category':
			$link = get_term_link( (int) $row['category'], 'product_cat' );
			return is_wp_error( $link ) ? $shop_url : $link;

		case 'on_sale':
			return add_query_arg( 'on_sale', '1', $shop_url );

		default:
			return $shop_url;
	}
}

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

function stocksystem_rows_admin_page( $pages ) {
	$pages['rows'] = array(
		'group'    => 'محتوای صفحه‌ها',
		'title'    => __( 'ردیف‌های محصول صفحهٔ اصلی', 'stocksystem' ),
		'menu'     => __( 'ردیف‌های محصول', 'stocksystem' ),
		'intro'    => __( 'هر تعداد ردیف محصول که بخواهی به صفحهٔ اصلی اضافه کن — مثلاً یک ردیف برای لپ‌تاپ، یکی برای تخفیف‌ها و یکی برای تازه‌ترین‌ها. این ردیف‌ها بعد از ردیف «پیشنهاد این هفته» (که از نمایش ← صفحهٔ اصلی تنظیم می‌شود) نمایش داده می‌شوند و ترتیبشان با عدد «ترتیب» مشخص می‌شود.', 'stocksystem' ),
		'view'     => '/',
		'sections' => array(
			array(
				'title'  => __( 'ردیف‌ها', 'stocksystem' ),
				'fields' => array(
					'items' => array(
						'type'      => 'repeater',
						'label'     => __( 'ردیف‌های محصول', 'stocksystem' ),
						'max'       => 12,
						'add_label' => __( '+ ردیف تازه', 'stocksystem' ),
						'fields'    => array(
							'enabled'    => array( 'type' => 'checkbox', 'label' => __( 'نمایش داده شود', 'stocksystem' ), 'default' => 1 ),
							'order'      => array( 'type' => 'number', 'label' => __( 'ترتیب', 'stocksystem' ), 'default' => 1, 'min' => 1, 'max' => 99 ),
							'heading'    => array( 'type' => 'text', 'label' => __( 'عنوان ردیف', 'stocksystem' ), 'wide' => true ),
							'source'     => array( 'type' => 'select', 'label' => __( 'محصول‌ها از کجا بیایند', 'stocksystem' ), 'default' => 'category', 'options_cb' => 'stocksystem_row_sources' ),
							'category'   => array( 'type' => 'select', 'label' => __( 'دسته (فقط وقتی «یک دستهٔ مشخص» انتخاب شده)', 'stocksystem' ), 'default' => '0', 'options_cb' => 'stocksystem_row_category_options' ),
							'ids'        => array( 'type' => 'text', 'label' => __( 'شناسهٔ محصول‌ها (فقط برای انتخاب دستی، با کاما)', 'stocksystem' ), 'allow_empty' => true, 'wide' => true ),
							'count'      => array( 'type' => 'number', 'label' => __( 'تعداد محصول', 'stocksystem' ), 'default' => 4, 'min' => 1, 'max' => 12 ),
							'link_label' => array( 'type' => 'text', 'label' => __( 'متن لینک گوشه', 'stocksystem' ), 'default' => __( 'مشاهدهٔ همه ←', 'stocksystem' ) ),
							'link_url'   => array( 'type' => 'url', 'label' => __( 'لینک گوشه (خالی = خودکار)', 'stocksystem' ), 'allow_empty' => true ),
						),
						'default'   => array(),
					),
				),
			),
		),
	);

	return $pages;
}
add_filter( 'stocksystem_admin_pages', 'stocksystem_rows_admin_page' );
