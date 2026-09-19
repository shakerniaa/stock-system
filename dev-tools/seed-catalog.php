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
	// Manual category order (WooCommerce term meta "order") = design order.
	update_term_meta( $categories[ $name ], 'order', count( $categories ) - 1 );
}

// ---- Brands (matches stocksystem_nav_brands() fallback) ----
$brands = array();
foreach ( array( 'HP', 'Dell', 'Lenovo', 'Microsoft', 'Apple', 'Asus' ) as $name ) {
	$brands[ $name ] = ss_term( $name, 'product_brand' );
}

// Product tags drive the card badges/ribbons by *slug* (see
// stocksystem_product_badges()); the visible name must be Persian since tag
// archives show it as the page title.
foreach ( array(
	'bestseller'    => 'پرفروش‌ترین',
	'new-arrival'   => 'تازه‌رسید',
	'featured'      => 'ویژه',
	'free-shipping' => 'ارسال رایگان',
	'clearance'     => 'حراج ویژه',
) as $tag_slug => $tag_name ) {
	if ( ! term_exists( $tag_slug, 'product_tag' ) ) {
		wp_insert_term( $tag_name, 'product_tag', array( 'slug' => $tag_slug ) );
	}
}

// grading terms already seeded by stocksystem_seed_grading_terms() — just fetch ids.
$grades = array();
foreach ( array( 'A', 'B', 'C' ) as $slug ) {
	$term = get_term_by( 'slug', strtolower( $slug ), 'product_grading' );
	if ( $term ) {
		$grades[ $slug ] = $term->term_id;
	}
}

// Real photos from the design kit first, then generated placeholder art
// (php dev-tools/make-placeholder-images.php) for everything else.
$img_dir = array(
	dirname( __DIR__ ) . '/stocksystem-dev-kit/assets/products/',
	__DIR__ . '/placeholder-images/',
);

function ss_attach_first_image( $product_id, $file, $img_dirs, $alt ) {
	foreach ( (array) $img_dirs as $dir ) {
		if ( file_exists( $dir . $file ) ) {
			$attach_id = ss_seed_image( $dir . $file, $product_id, $alt );
			if ( $attach_id ) {
				set_post_thumbnail( $product_id, $attach_id );
			}
			return;
		}
	}
}

function ss_existing_product_id( $name ) {
	$found = get_posts( array( 'post_type' => 'product', 'title' => $name, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	return $found ? (int) $found[0] : 0;
}

function ss_make_simple_product( $args, $categories, $brands, $grades, $img_dir ) {
	$existing = ss_existing_product_id( $args['name'] );
	if ( $existing ) {
		if ( ! empty( $args['image'] ) && ! has_post_thumbnail( $existing ) ) {
			ss_attach_first_image( $existing, $args['image'], $img_dir, $args['name'] );
		}
		if ( ! empty( $args['sku'] ) && ! get_post_meta( $existing, '_sku', true ) ) {
			update_post_meta( $existing, '_sku', $args['sku'] );
		}
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
	if ( ! empty( $args['sku'] ) ) {
		$product->set_sku( $args['sku'] );
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
		ss_attach_first_image( $product_id, $args['image'], $img_dir, $args['name'] );
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
	'sku'               => 'SS-HP840G8-16-512',
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
	'sku'               => 'SS-DL3520-8-256',
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
	'sku'               => 'SS-HP15-12-512',
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
	'brand'             => 'Microsoft',
	'sku'               => 'SS-MS-SL4-8-256',
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
	'sku'               => 'SS-LN-M720Q-8-256',
	'regular_price'     => 15900000,
	'stock_qty'         => 6,
	'categories'        => array( 'کیس و مینی‌پی‌سی' ),
	'brand'             => 'Lenovo',
	'grade'             => 'B',
	'tags'              => array( 'featured' ),
	'image'             => 'mini-pc.png',
	'short_description' => 'Core i5 نسل هشتم، ۸ گیگابایت رم، ۲۵۶ گیگابایت SSD — مناسب دفتر کار و کیوسک.',
	'test_report'       => array( 'battery_health' => '', 'runtime_hours' => 2100, 'body_condition' => 'B', 'dead_pixels' => '', 'test_date' => '۱۴۰۵/۰۶/۰۱' ),
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'Asus ProArt 27" Monitor',
	'sku'               => 'SS-AS-PA27',
	'regular_price'     => 12400000,
	'stock_qty'         => 4,
	'categories'        => array( 'مانیتور' ),
	'brand'             => 'Asus',
	'grade'             => 'A',
	'image'             => 'monitor.png',
	'short_description' => 'مانیتور ۲۷ اینچ QHD، پنل IPS، مناسب طراحی و ادیت.',
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'کیت ارتقای رم ۸ گیگابایت DDR4',
	'sku'               => 'SS-RAM-8-DDR4',
	'regular_price'     => 1450000,
	'stock_qty'         => 20,
	'categories'        => array( 'قطعات و ارتقا' ),
	'tags'              => array( 'free-shipping' ),
	'image'             => 'ram-module.png',
	'short_description' => 'رم لپ‌تاپ DDR4 2666MHz، تست‌شده و سازگار با اکثر لپ‌تاپ‌های استوک.',
), $categories, $brands, $grades, $img_dir );

$created[] = ss_make_simple_product( array(
	'name'              => 'ماوس بی‌سیم لاجیتک M185',
	'sku'               => 'SS-LG-M185',
	'regular_price'     => 890000,
	'sale_price'        => 690000,
	'stock_qty'         => 30,
	'categories'        => array( 'لوازم جانبی' ),
	'tags'              => array( 'clearance' ),
	'image'             => 'mouse.png',
	'short_description' => 'ماوس بی‌سیم با باتری تا ۱۲ ماه — رنگ مشکی.',
), $categories, $brands, $grades, $img_dir );

// ---- Variable product: RAM/storage configurator + add-ons ----
if ( ! ss_existing_product_id( 'Lenovo ThinkPad T14 (قابل‌تنظیم)' ) ) {
$var_product = new WC_Product_Variable();
$var_product->set_name( 'Lenovo ThinkPad T14 (قابل‌تنظیم)' );
$var_product->set_status( 'publish' );
$var_product->set_date_created( '2026-09-01 10:00:00' ); // older than the photographed laptops so the homepage hero (newest laptops) uses real photos.
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

// Variable product image (also covers re-runs where it already existed).
$var_id = ss_existing_product_id( 'Lenovo ThinkPad T14 (قابل‌تنظیم)' );
if ( $var_id && ! has_post_thumbnail( $var_id ) ) {
	ss_attach_first_image( $var_id, 'laptop.png', $img_dir, 'Lenovo ThinkPad T14' );
}

// ---- Blog: real guide posts with covers (the homepage teaser and /blog/
// otherwise show empty grey boxes). Idempotent by title. ----
function ss_make_post( $title, $category, $content, $cover, $img_dirs ) {
	$found = get_posts( array( 'post_type' => 'post', 'title' => $title, 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $found ) {
		$post_id = (int) $found[0];
	} else {
		$cat_id  = ss_term( $category, 'category' );
		$post_id = wp_insert_post( array(
			'post_title'    => $title,
			'post_status'   => 'publish',
			'post_type'     => 'post',
			'post_author'   => 1,
			'post_content'  => $content,
			'post_category' => array( $cat_id ),
		) );
	}
	if ( $post_id && ! has_post_thumbnail( $post_id ) ) {
		ss_attach_first_image( $post_id, $cover, $img_dirs, $title );
	}
	return $post_id;
}

// The default "Hello world" post isn't real content.
foreach ( get_posts( array( 'post_type' => 'post', 'title' => 'سلام دنیا!', 'post_status' => 'any', 'numberposts' => 1, 'fields' => 'ids' ) ) as $hello ) {
	wp_delete_post( $hello, true );
}
wp_update_user( array( 'ID' => 1, 'display_name' => 'تیم فنی استوک سیستم' ) );

$promo_id = ss_existing_product_id( 'HP EliteBook 840 G8' );
$promo    = $promo_id ? '[stocksystem_product_promo id="' . $promo_id . '"]' : '';

ss_make_post(
	'لپ‌تاپ استوک بخریم یا نو؟ مقایسهٔ واقعی قیمت و عمر مفید',
	'راهنمای خرید',
	"<p>وقتی بودجه ثابت است، پرسش درست این نیست که «نو بهتر است یا استوک»، بلکه این است که با این مبلغ کدام سطح از سخت‌افزار به دست می‌آید. یک لپ‌تاپ سازمانی ردهٔ بالا که سه سال کار کرده، در بسیاری موارد از یک لپ‌تاپ خانگی نوی هم‌قیمت، بدنهٔ محکم‌تر و صفحه‌کلید بهتری دارد.</p><h2>سه سناریو با عدد</h2><p>برای کار اداری و مرور وب، نسل هشتم با ۱۶ گیگابایت رم کافی است. برای برنامه‌نویسی و ماشین مجازی، نسل یازدهم با ۱۶ تا ۳۲ گیگابایت. برای طراحی و رندر، کارت گرافیک مجزا لازم است و همین‌جاست که اختلاف قیمت نو و استوک بیشترین معنا را پیدا می‌کند.</p>{$promo}<h2>چه چیزی را باید بررسی کرد</h2><p>سلامت باتری، ساعت کارکرد دیسک و وضعیت بدنه سه عددی هستند که در برگهٔ تست هر دستگاه ما ثبت می‌شود؛ پس لازم نیست به حافظهٔ فروشنده اعتماد کنید.</p>",
	'cover-buying-guide.png',
	$img_dir
);
ss_make_post(
	'نشانه‌های خرابی SSD و کاری که باید فوراً انجام دهید',
	'عیب‌یابی',
	"<p>حافظهٔ SSD معمولاً بی‌سروصدا خراب نمی‌شود؛ پیش از مرگ کامل، علامت‌های هشدار می‌دهد. شناخت این علامت‌ها می‌تواند اطلاعات شما را نجات دهد.</p><h2>پنج علامت هشدار</h2><p>کندشدن ناگهانی، فریز شدن هنگام ذخیره، فایل‌های خراب، ریست‌های تصادفی و ناپدید شدن درایو از بایوس، پرتکرارترین نشانه‌هایی هستند که در کارگاه می‌بینیم.</p><h2>اولین کار</h2><p>فوراً از اطلاعات مهم نسخهٔ پشتیبان بگیرید و دستگاه را کمتر روشن نگه دارید؛ هر بار روشن‌شدن ممکن است شانس بازیابی را کم کند.</p>",
	'cover-troubleshooting.png',
	$img_dir
);
ss_make_post(
	'EliteBook در برابر Latitude: کدام برای کار اداری بهتر است؟',
	'مقایسه',
	"<p>این دو سری، رقیب اصلی در بازار لپ‌تاپ سازمانی استوک‌اند. هر دو بدنهٔ مقاوم و صفحه‌کلید خوبی دارند، ولی در جزئیات تفاوت‌هایی هست که برای خریدار مهم است.</p><h2>بدنه و صفحه‌کلید</h2><p>EliteBook بدنهٔ آلومینیومی سبک‌تری دارد؛ Latitude معمولاً کمی ضخیم‌تر و مقاوم‌تر در برابر ضربه است.</p><h2>باتری و هزینهٔ قطعات</h2><p>در هر دو سری قطعات یدکی فراوان است و هزینهٔ تعویض باتری تفاوت چندانی ندارد.</p>",
	'cover-comparison.png',
	$img_dir
);
// Inline product callout (05 Blog "محصول مرتبط با این مقاله") — the product
// id differs per database, so look it up by SKU.
$promo_product_id = function_exists( 'wc_get_product_id_by_sku' ) ? wc_get_product_id_by_sku( 'SS-HP840G8-16-512' ) : 0;
$promo_shortcode  = $promo_product_id ? '[stocksystem_product_promo id="' . $promo_product_id . '"]' : '';

ss_make_post(
	'چطور سلامت باتری لپ‌تاپ استوک را قبل از خرید چک کنیم؟',
	'عیب‌یابی',
	"<p>باتری، اولین چیزی است که در یک لپ‌تاپ کارکرده افت می‌کند. خوشبختانه ویندوز ابزار داخلی دقیقی دارد که در چند ثانیه گزارش کاملی از ظرفیت واقعی باتری می‌سازد.</p><h2>خواندن گزارش باتری</h2><p>در Command Prompt فرمان powercfg /batteryreport را اجرا کنید و عدد Design Capacity را با Full Charge Capacity مقایسه کنید.</p>" . $promo_shortcode . "<h2>چرخهٔ شارژ چقدر مهم است؟</h2><p>اگر ظرفیت فعلی زیر ۸۰٪ ظرفیت اولیه باشد، قیمت دستگاه باید حداقل به اندازهٔ یک باتری نو اصلاح شود.</p>",
	'cover-battery.png',
	$img_dir
);

// ---- Demo customer + orders so the My Account dashboard has something
// to show (dev only — password below is a throwaway local test value). ----
$demo_phone = '09121234567';
$demo_user  = get_user_by( 'login', $demo_phone );
if ( ! $demo_user ) {
	$demo_id = wc_create_new_customer( 'reza.demo@example.test', $demo_phone, 'TestPass123!' );
	if ( ! is_wp_error( $demo_id ) ) {
		wp_update_user( array( 'ID' => $demo_id, 'display_name' => 'رضا کاظمی', 'first_name' => 'رضا', 'last_name' => 'کاظمی' ) );
		update_user_meta( $demo_id, 'billing_phone', $demo_phone );
		update_user_meta( $demo_id, 'billing_first_name', 'رضا' );
		update_user_meta( $demo_id, 'billing_last_name', 'کاظمی' );
		$demo_user = get_user_by( 'id', $demo_id );
	}
}
if ( $demo_user && ! wc_get_orders( array( 'customer_id' => $demo_user->ID, 'limit' => 1 ) ) ) {
	foreach ( array(
		array( 'HP EliteBook 840 G8', 'processing' ),
		array( 'Dell Inspiron 3520', 'completed' ),
	) as $row ) {
		$pid = ss_existing_product_id( $row[0] );
		if ( ! $pid ) {
			continue;
		}
		$order = wc_create_order( array( 'customer_id' => $demo_user->ID ) );
		$order->add_product( wc_get_product( $pid ), 1 );
		$order->set_billing_first_name( 'رضا' );
		$order->set_billing_last_name( 'کاظمی' );
		$order->set_billing_phone( $demo_phone );
		$order->set_payment_method_title( 'پرداخت در محل' );
		$order->calculate_totals();
		$order->set_status( $row[1] );
		$order->save();
	}
}

echo "Created product IDs: " . implode( ', ', $created ) . "\n";
echo "Categories: " . implode( ', ', array_keys( $categories ) ) . "\n";
echo "Brands: " . implode( ', ', array_keys( $brands ) ) . "\n";
