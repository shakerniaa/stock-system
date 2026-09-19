<?php
/**
 * Dev catalog seed: real categories/brands matching the design's nav
 * fallback lists, plus test products exercising every product-card
 * badge state (on sale, low stock, out of stock, featured, clearance,
 * free shipping, bestseller) and one variable RAM/storage configurator
 * product with add-ons. Safe to re-run (skips products that exist).
 *
 * Usage (from the WordPress site root):
 *   wp eval-file /path/to/repo/dev-tools/seed-catalog.php
 *
 * Gotcha: under `wp eval-file`, top-level variables are NOT visible to
 * functions via `global` — everything is passed as parameters below.
 */

function ss_seed_image( $path, $product_id, $alt ) {
	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$filetype = wp_check_filetype( basename( $path ), null );
	$upload   = wp_upload_bits( basename( $path ), null, file_get_contents( $path ) );

	if ( $upload['error'] ) {
		return 0;
	}

	$attachment = array(
		'post_mime_type' => $filetype['type'],
		'post_title'      => $alt,
		'post_status'     => 'inherit',
	);

	$attach_id = wp_insert_attachment( $attachment, $upload['file'], $product_id );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
	wp_update_attachment_metadata( $attach_id, $attach_data );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );

	return $attach_id;
}

function ss_term( $name, $taxonomy, $parent = 0 ) {
	$existing = get_term_by( 'name', $name, $taxonomy );
	if ( $existing ) {
		return $existing->term_id;
	}
	$args = $parent ? array( 'parent' => $parent ) : array();
	$inserted = wp_insert_term( $name, $taxonomy, $args );
	return is_wp_error( $inserted ) ? 0 : $inserted['term_id'];
}

// ---- Categories (matches stocksystem_nav_categories() fallback) ----
$categories = array();
foreach ( array( 'لپ‌تاپ استوک', 'آل‌این‌وان', 'کیس و مینی‌پی‌سی', 'مانیتور', 'قطعات و ارتقا', 'لوازم جانبی' ) as $name ) {
	$categories[ $name ] = ss_term( $name, 'product_cat' );
}

// ---- Brands (matches stocksystem_nav_brands() fallback) ----
$brands = array();
foreach ( array( 'HP', 'Dell', 'Lenovo', 'Apple', 'Asus' ) as $name ) {
	$brands[ $name ] = ss_term( $name, 'product_brand' );
}

// grading terms already seeded by stocksystem_seed_grading_terms() — just fetch ids.
$grades = array();
foreach ( array( 'A', 'B', 'C' ) as $slug ) {
	$term = get_term_by( 'slug', strtolower( $slug ), 'product_grading' );
	if ( $term ) {
		$grades[ $slug ] = $term->term_id;
	}
}

$img_dir = dirname( __DIR__ ) . '/stocksystem-dev-kit/assets/products/';

function ss_existing_product_id( $name ) {
	$found = get_posts( array( 'post_type' => 'product', 'title' => $name, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
}

function ss_make_simple_product( $args, $categories, $brands, $grades, $img_dir ) {
	$existing = ss_existing_product_id( $args['name'] );
	if ( $existing ) {
		return $existing;
	}

	$product = new WC_Product_Simple();
	$product->set_name( $args['name'] );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_regular_price( $args['regular_price'] );
	if ( ! empty( $args['sale_price'] ) ) {
		$product->set_sale_price( $args['sale_price'] );
	}
	$product->set_manage_stock( true );
	$product->set_stock_quantity( $args['stock_qty'] );
	$product->set_stock_status( $args['stock_qty'] > 0 ? 'instock' : 'outofstock' );
	$product->set_low_stock_amount( $args['low_stock_amount'] ?? 3 );
	$product->set_short_description( $args['short_description'] ?? '' );
	$product->set_description( $args['description'] ?? '' );

	$cat_ids = array();
	foreach ( $args['categories'] as $cat_name ) {
		if ( ! empty( $categories[ $cat_name ] ) ) {
			$cat_ids[] = $categories[ $cat_name ];
		}
	}
	$product->set_category_ids( $cat_ids );

	$product_id = $product->save();

	if ( ! empty( $args['brand'] ) && ! empty( $brands[ $args['brand'] ] ) ) {
		wp_set_object_terms( $product_id, array( $brands[ $args['brand'] ] ), 'product_brand' );
	}
	if ( ! empty( $args['grade'] ) && ! empty( $grades[ $args['grade'] ] ) ) {
		wp_set_object_terms( $product_id, array( $grades[ $args['grade'] ] ), 'product_grading' );
	}
	if ( ! empty( $args['tags'] ) ) {
		wp_set_object_terms( $product_id, $args['tags'], 'product_tag' );
	}

	if ( ! empty( $args['image'] ) ) {
		$attach_id = ss_seed_image( $img_dir . $args['image'], $product_id, $args['name'] );
		if ( $attach_id ) {
			set_post_thumbnail( $product_id, $attach_id );
		}
	}

	if ( ! empty( $args['test_report'] ) ) {
		foreach ( $args['test_report'] as $key => $value ) {
			update_post_meta( $product_id, '_' . $key, $value );
		}
	}

	return $product_id;
}

$created = array();

$created[] = ss_make_simple_product( array(
	'name'              => 'HP EliteBook 840 G8',
	'regular_price'     => 32900000,
	'stock_qty'         => 5,
	'categories'        => array( 'لپ‌تاپ استوک' ),
	'brand'             => 'HP',
	'grade'             => 'A',
	'tags'              => array( 'bestseller' ),
	'image'             => 'elitebook-840-g8.png',
	'short_description' => 'Core i7 نسل یازدهم، ۱۶ گیگابایت رم، ۵۱۲ گیگابایت SSD — مناسب کار اداری و برنامه‌نویسی سبک.',
	'test_report'       => array( 'battery_health' => 92, 'runtime_hours' => 1240, 'body_condition' => 'A', 'dead_pixels' => 'ندارد', 'test_date' => '۱۴۰۵/۰۶/۱۰' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'Dell Inspiron 3520',
	'regular_price'     => 24500000,
	'sale_price'        => 19800000,
	'stock_qty'         => 8,
	'categories'        => array( 'لپ‌تاپ استوک' ),
	'brand'             => 'Dell',
	'grade'             => 'B',
	'tags'              => array( 'new-arrival' ),
	'image'             => 'dell-inspiron-3520.png',
	'short_description' => 'Core i5 نسل دوازدهم، ۸ گیگابایت رم، ۲۵۶ گیگابایت SSD — مناسب کار روزمره و مرور وب.',
	'test_report'       => array( 'battery_health' => 84, 'runtime_hours' => 980, 'body_condition' => 'B', 'dead_pixels' => 'ندارد', 'test_date' => '۱۴۰۵/۰۶/۱۲' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'HP Pavilion 15',
	'regular_price'     => 27800000,
	'stock_qty'         => 2,
	'low_stock_amount'  => 3,
	'categories'        => array( 'لپ‌تاپ استوک' ),
	'brand'             => 'HP',
	'grade'             => 'B',
	'image'             => 'hp-pavilion-15.png',
	'short_description' => 'Core i5 نسل یازدهم، ۱۲ گیگابایت رم، ۵۱۲ گیگابایت SSD، کارت گرافیک مجزا.',
	'test_report'       => array( 'battery_health' => 78, 'runtime_hours' => 1560, 'body_condition' => 'B', 'dead_pixels' => 'ندارد', 'test_date' => '۱۴۰۵/۰۶/۰۸' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'Microsoft Surface Laptop 4',
	'regular_price'     => 41500000,
	'stock_qty'         => 0,
	'categories'        => array( 'لپ‌تاپ استوک' ),
	'grade'             => 'A',
	'image'             => 'surface-laptop-4.png',
	'short_description' => 'Ryzen 5، ۸ گیگابایت رم، ۲۵۶ گیگابایت SSD، بدنه آلومینیومی.',
	'test_report'       => array( 'battery_health' => 88, 'runtime_hours' => 640, 'body_condition' => 'A', 'dead_pixels' => 'ندارد', 'test_date' => '۱۴۰۵/۰۵/۲۸' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'Lenovo ThinkCentre M720q Mini PC',
	'regular_price'     => 15900000,
	'stock_qty'         => 6,
	'categories'        => array( 'کیس و مینی‌پی‌سی' ),
	'brand'             => 'Lenovo',
	'grade'             => 'B',
	'tags'              => array( 'featured' ),
	'short_description' => 'Core i5 نسل هشتم، ۸ گیگابایت رم، ۲۵۶ گیگابایت SSD — مناسب دفتر کار و کیوسک.',
	'test_report'       => array( 'battery_health' => '', 'runtime_hours' => 2100, 'body_condition' => 'B', 'dead_pixels' => '', 'test_date' => '۱۴۰۵/۰۶/۰۱' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'Asus ProArt 27" Monitor',
	'regular_price'     => 12400000,
	'stock_qty'         => 4,
	'categories'        => array( 'مانیتور' ),
	'brand'             => 'Asus',
	'grade'             => 'A',
	'short_description' => 'مانیتور ۲۷ اینچ QHD، پنل IPS، مناسب طراحی و ادیت.',
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'کیت ارتقای رم ۸ گیگابایت DDR4',
	'regular_price'     => 1450000,
	'stock_qty'         => 20,
	'categories'        => array( 'قطعات و ارتقا' ),
	'tags'              => array( 'free-shipping' ),
	'short_description' => 'رم لپ‌تاپ DDR4 2666MHz، تست‌شده و سازگار با اکثر لپ‌تاپ‌های استوک.',
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'ماوس بی‌سیم لاجیتک M185',
	'regular_price'     => 890000,
	'sale_price'        => 690000,
	'stock_qty'         => 30,
	'categories'        => array( 'لوازم جانبی' ),
	'tags'              => array( 'clearance' ),
	'short_description' => 'ماوس بی‌سیم با باتری تا ۱۲ ماه — رنگ مشکی.',
), $categories, $brands, $grades, $img_dir );

// ---- Variable product: RAM/storage configurator + add-ons ----
if ( ! ss_existing_product_id( 'Lenovo ThinkPad T14 (قابل‌تنظیم)' ) ) {
$var_product = new WC_Product_Variable();
$var_product->set_name( 'Lenovo ThinkPad T14 (قابل‌تنظیم)' );
$var_product->set_status( 'publish' );
$var_product->set_catalog_visibility( 'visible' );
$var_product->set_short_description( 'Core i7 نسل یازدهم — رم و فضای ذخیره‌سازی را متناسب با نیاز خودتان انتخاب کنید.' );

$cat_ids = array( $categories['لپ‌تاپ استوک'] );
$var_product->set_category_ids( $cat_ids );

// Global attributes pa_ram / pa_storage — create if missing.
function ss_ensure_global_attribute( $slug, $label, $terms ) {
	global $wpdb;
	$taxonomy = 'pa_' . $slug;

	if ( ! taxonomy_exists( $taxonomy ) ) {
		$attr_id = wc_create_attribute( array(
			'name'         => $label,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		delete_transient( 'wc_attribute_taxonomies' );
		// Re-register taxonomies for this request.
		if ( function_exists( 'wc_register_taxonomy' ) ) {
			wc_register_taxonomy( $taxonomy, array() );
		}
		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			register_taxonomy(
				'pa_' . $tax->attribute_name,
				'product',
				array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true )
			);
		}
	}

	$term_ids = array();
	foreach ( $terms as $term_name ) {
		$term_ids[] = ss_term( $term_name, $taxonomy );
	}
	return $term_ids;
}

$ram_term_ids     = ss_ensure_global_attribute( 'ram', 'RAM', array( '8GB', '16GB', '32GB' ) );
$storage_term_ids = ss_ensure_global_attribute( 'storage', 'Storage', array( '256GB SSD', '512GB SSD', '1TB SSD' ) );

$ram_attr = new WC_Product_Attribute();
$ram_attr->set_id( wc_attribute_taxonomy_id_by_name( 'ram' ) );
$ram_attr->set_name( 'pa_ram' );
$ram_attr->set_options( $ram_term_ids );
$ram_attr->set_visible( true );
$ram_attr->set_variation( true );

$storage_attr = new WC_Product_Attribute();
$storage_attr->set_id( wc_attribute_taxonomy_id_by_name( 'storage' ) );
$storage_attr->set_name( 'pa_storage' );
$storage_attr->set_options( $storage_term_ids );
$storage_attr->set_visible( true );
$storage_attr->set_variation( true );

$var_product->set_attributes( array( $ram_attr, $storage_attr ) );
$var_product_id = $var_product->save();

wp_set_object_terms( $var_product_id, array( $brands['Lenovo'] ), 'product_brand' );
wp_set_object_terms( $var_product_id, array( $grades['A'] ), 'product_grading' );

update_post_meta( $var_product_id, '_addons', wp_json_encode( array(
	array( 'id' => 'win', 'label' => 'لایسنس دائم ویندوز ۱۱ پرو', 'price' => 1200000 ),
	array( 'id' => 'bag', 'label' => 'کیف و ماوس هدیه', 'price' => 450000 ),
	array( 'id' => 'warranty-ext', 'label' => 'تمدید گارانتی ۱ ماه دیگر', 'price' => 900000 ),
), JSON_UNESCAPED_UNICODE ) );

$combos = array(
	array( 'ram' => '8GB', 'storage' => '256GB SSD', 'price' => 28500000, 'qty' => 4 ),
	array( 'ram' => '16GB', 'storage' => '512GB SSD', 'price' => 33900000, 'qty' => 6 ),
	array( 'ram' => '32GB', 'storage' => '1TB SSD', 'price' => 41200000, 'qty' => 2 ),
);

foreach ( $combos as $combo ) {
	$variation = new WC_Product_Variation();
	$variation->set_parent_id( $var_product_id );
	$variation->set_attributes( array(
		'pa_ram'     => sanitize_title( $combo['ram'] ),
		'pa_storage' => sanitize_title( $combo['storage'] ),
	), $categories, $brands, $grades, $img_dir );
	$variation->set_regular_price( $combo['price'] );
	$variation->set_manage_stock( true );
	$variation->set_stock_quantity( $combo['qty'] );
	$variation->set_stock_status( 'instock' );
	$variation->save();
}

$var_product = wc_get_product( $var_product_id );
$var_product->set_price( 28500000 );
$var_product->save();

$created[] = $var_product_id;
}

echo "Created product IDs: " . implode( ', ', $created ) . "\n";
echo "Categories: " . implode( ', ', array_keys( $categories ) ) . "\n";
echo "Brands: " . implode( ', ', array_keys( $brands ) ) . "\n";
