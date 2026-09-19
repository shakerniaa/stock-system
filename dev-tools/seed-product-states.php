<?php
/**
 * Dev seed for the product-card / buy-box states seed-catalog.php doesn't
 * cover (15 Product States §15-B/C): colour swatches, size chips, grouped
 * "model rows", and a no-price "quote only" product. Run after
 * seed-catalog.php (needs its categories and the HP laptops). Idempotent.
 *
 *   wp eval-file /path/to/repo/dev-tools/seed-product-states.php
 *
 * Under `wp eval-file` top-level variables aren't visible to functions via
 * `global` — everything is passed as parameters.
 */

function ps_existing( $name ) {
	$found = get_posts( array( 'post_type' => 'product', 'title' => $name, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
}

function ps_term( $name, $taxonomy ) {
	$existing = get_term_by( 'name', $name, $taxonomy );
	if ( $existing ) {
		return (int) $existing->term_id;
	}
	$inserted = wp_insert_term( $name, $taxonomy );
	return is_wp_error( $inserted ) ? 0 : (int) $inserted['term_id'];
}

function ps_image( $product_id, $file, $alt ) {
	$path = __DIR__ . '/placeholder-images/' . $file;
	if ( ! file_exists( $path ) || has_post_thumbnail( $product_id ) ) {
		return;
	}
	$upload = wp_upload_bits( basename( $path ), null, file_get_contents( $path ) );
	if ( $upload['error'] ) {
		return;
	}
	$attach_id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => $alt, 'post_status' => 'inherit' ), $upload['file'], $product_id );
	require_once ABSPATH . 'wp-admin/includes/image.php';
	wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );
	update_post_meta( $attach_id, '_wp_attachment_image_alt', $alt );
	set_post_thumbnail( $product_id, $attach_id );
}

function ps_attribute( $slug, $label, $terms ) {
	$taxonomy = 'pa_' . $slug;

	if ( ! taxonomy_exists( $taxonomy ) ) {
		wc_create_attribute( array( 'name' => $label, 'slug' => $slug, 'type' => 'select', 'order_by' => 'menu_order', 'has_archives' => false ) );
		delete_transient( 'wc_attribute_taxonomies' );
		foreach ( wc_get_attribute_taxonomies() as $tax ) {
			if ( ! taxonomy_exists( 'pa_' . $tax->attribute_name ) ) {
				register_taxonomy( 'pa_' . $tax->attribute_name, 'product', array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true ) );
			}
		}
	}

	$ids = array();
	foreach ( $terms as $name => $meta ) {
		$id = ps_term( $name, $taxonomy );
		if ( $id && $meta ) {
			update_term_meta( $id, 'swatch_color', $meta );
		}
		$ids[ $name ] = $id;
	}

	return $ids;
}

function ps_variable( $name, $slug, $label, $terms, $prices, $cat_id, $image, $short ) {
	if ( ps_existing( $name ) ) {
		return 'exists: ' . $name;
	}

	$term_ids = ps_attribute( $slug, $label, $terms );

	$product = new WC_Product_Variable();
	$product->set_name( $name );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_short_description( $short );
	$product->set_category_ids( array( $cat_id ) );

	$attribute = new WC_Product_Attribute();
	$attribute->set_id( wc_attribute_taxonomy_id_by_name( $slug ) );
	$attribute->set_name( 'pa_' . $slug );
	$attribute->set_options( array_values( $term_ids ) );
	$attribute->set_visible( true );
	$attribute->set_variation( true );
	$product->set_attributes( array( $attribute ) );
	$product_id = $product->save();

	$i = 0;
	foreach ( array_keys( $terms ) as $term_name ) {
		$term      = get_term( $term_ids[ $term_name ], 'pa_' . $slug );
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_attributes( array( 'pa_' . $slug => $term->slug ) );
		$variation->set_regular_price( $prices[ $i ] );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( 8 );
		$variation->set_stock_status( 'instock' );
		$variation->save();
		++$i;
	}

	wc_delete_product_transients( $product_id );
	ps_image( $product_id, $image, $name );

	return 'created: ' . $name;
}

$cat_accessories = (int) ( get_term_by( 'name', 'لوازم جانبی', 'product_cat' )->term_id ?? 0 );
$cat_laptops     = (int) ( get_term_by( 'name', 'لپ‌تاپ استوک', 'product_cat' )->term_id ?? 0 );
$cat_parts       = (int) ( get_term_by( 'name', 'قطعات و ارتقا', 'product_cat' )->term_id ?? 0 );

// State 7 — colour swatches.
echo ps_variable(
	'کیف ضدضربهٔ لپ‌تاپ ۱۴ اینچ',
	'color',
	'رنگ',
	array( 'مشکی' => '#06292A', 'نقره‌ای' => '#B9CBC9', 'آبی' => '#2B6CB0' ),
	array( 890000, 890000, 940000 ),
	$cat_accessories,
	'mouse.png',
	'کیف با پد ضدضربه و زیپ ضدآب؛ رنگ دلخواه را انتخاب کنید.'
) . "\n";

// State 8 — size chips.
echo ps_variable(
	'کاور محافظ لپ‌تاپ',
	'size',
	'سایز',
	array( '۱۳ اینچ' => '', '۱۴ اینچ' => '', '۱۵٫۶ اینچ' => '' ),
	array( 490000, 540000, 590000 ),
	$cat_accessories,
	'mouse.png',
	'کاور نرم با لایهٔ ضدخش؛ سایز لپ‌تاپ خود را انتخاب کنید.'
) . "\n";

// State 9 — grouped product: model rows.
if ( ! ps_existing( 'لپ‌تاپ‌های اداری HP — انتخاب مدل' ) ) {
	$children = array_filter( array( ps_existing( 'HP EliteBook 840 G8' ), ps_existing( 'HP Pavilion 15' ), ps_existing( 'Dell Inspiron 3520' ) ) );
	$grouped  = new WC_Product_Grouped();
	$grouped->set_name( 'لپ‌تاپ‌های اداری HP — انتخاب مدل' );
	$grouped->set_status( 'publish' );
	$grouped->set_catalog_visibility( 'visible' );
	$grouped->set_short_description( 'چند مدل هم‌رده؛ موجودی هر مدل را در همین کارت ببینید.' );
	$grouped->set_category_ids( array( $cat_laptops ) );
	$grouped->set_children( array_values( $children ) );
	$id = $grouped->save();
	ps_image( $id, 'laptop.png', 'لپ‌تاپ‌های اداری HP' );
	echo "created: grouped\n";
} else {
	echo "exists: grouped\n";
}

// State 11 — no price: quote only.
if ( ! ps_existing( 'سرور HP ProLiant DL380 Gen10' ) ) {
	$quote = new WC_Product_Simple();
	$quote->set_name( 'سرور HP ProLiant DL380 Gen10' );
	$quote->set_status( 'publish' );
	$quote->set_catalog_visibility( 'visible' );
	$quote->set_short_description( 'سرور رک تست‌شده؛ قیمت بر اساس پیکربندی (پردازنده، رم، دیسک) اعلام می‌شود.' );
	$quote->set_category_ids( array( $cat_parts ) );
	$quote->set_regular_price( '' );
	$quote->set_stock_status( 'instock' );
	$id = $quote->save();
	ps_image( $id, 'mini-pc.png', 'سرور HP ProLiant DL380' );
	echo "created: quote\n";
} else {
	echo "exists: quote\n";
}

if ( function_exists( 'stocksystem_flush_mega_menu_data' ) ) {
	stocksystem_flush_mega_menu_data();
}
