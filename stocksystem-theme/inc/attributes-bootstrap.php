<?php
/**
 * One-click bulk creation of standard WooCommerce global attributes +
 * sample values, so the owner isn't stuck adding ~20 attributes by hand
 * through Products → Attributes. Idempotent: safe to run repeatedly —
 * existing attributes/terms (including the site's own pa_ram, pa_size,
 * pa_color, pa_ذخیره‌سازی) are detected by label and left untouched;
 * only missing ones are created, and only missing terms are added to
 * attributes that already exist.
 *
 * Admin page: استوک سیستم → ویژگی‌های استاندارد.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The full catalog of attributes this store deals in, grouped only for
 * display on the admin page — WooCommerce attributes themselves aren't
 * grouped. `label` is what shows in Products → Attributes and in the
 * product-edit screen; the slug WooCommerce generates from it is not
 * hardcoded here on purpose (sanitize_title matches WooCommerce's own
 * `wc_sanitize_taxonomy_name()` closely enough, and we always look
 * attributes up by label at run time rather than assuming a slug).
 *
 * `type` is passed to wc_create_attribute() ('select' for all of these
 * — no attribute here needs a text/color/image swatch).
 *
 * `values` are the Persian-digit sample terms created alongside the
 * attribute so it isn't an empty dropdown on day one; the owner can add
 * more from Products → Attributes → [attribute] → Configure terms at
 * any time.
 *
 * `slug` is set explicitly (short, ASCII, latin) for every item rather
 * than left to WooCommerce's auto-slug from the Persian label: the full
 * taxonomy name is `pa_` + slug and WordPress caps taxonomy names at 32
 * characters, which a sanitized Persian phrase blows past easily (e.g.
 * «نوع اتصال مانیتور (آل‌این‌وان)» → `pa_نوع-اتصال-مانیتور-آلاینوان`,
 * well over the limit). Short latin slugs sidestep that entirely and
 * match the site's existing `pa_ram` / `pa_size` / `pa_color` pattern —
 * only the legacy `pa_ذخیره‌سازی` breaks that pattern, and it is left
 * alone here rather than renamed.
 *
 * An attribute that applies to more than one category (CPU, RAM type,
 * resolution, ports, …) is listed exactly ONCE, under whichever group
 * it's most associated with — never duplicated per category. Each
 * item's `used_by` is display-only (shown on the admin page as "هم در:
 * ...") so the owner can see where a shared attribute applies without
 * the catalog defining it twice (which would just make the second
 * definition a same-run no-op, but would be confusing to read).
 *
 * TO ADD A NEW ATTRIBUTE LATER: add one more array() item to whichever
 * group below fits best — label, slug (short/ASCII/unique across the
 * WHOLE catalog, not just its group), and a values array. Nothing else
 * changes. Deploy the theme update and click "ایجاد ویژگی‌های استاندارد"
 * again on استوک سیستم ← ویژگی‌های استاندارد — the run is idempotent, so
 * only the new item gets created; everything already on the site is
 * detected by label and left alone.
 */
function stocksystem_standard_attributes_catalog() {
	return array(
		'shared'    => array(
			'title' => __( 'مشترک بین همهٔ دسته‌های کامپیوتری', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'پردازنده', 'stocksystem' ),
					'slug'   => 'cpu',
					'values' => array( 'Intel Core i3-1115G4', 'Intel Core i5-1135G7', 'Intel Core i5-1235U', 'Intel Core i5-10400', 'Intel Core i7-1165G7', 'Intel Core i7-1255U', 'Intel Core i7-10700', 'Intel Core i9-9900K', 'AMD Ryzen 3 3200U', 'AMD Ryzen 5 3500U', 'AMD Ryzen 5 5500U', 'AMD Ryzen 7 5700U', 'AMD Ryzen 5 5600X', 'Apple M1', 'Apple M2' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts' ),
				),
				array(
					'label'  => __( 'نسل پردازنده', 'stocksystem' ),
					'slug'   => 'cpu-gen',
					'values' => array( 'نسل ۶', 'نسل ۷', 'نسل ۸', 'نسل ۹', 'نسل ۱۰', 'نسل ۱۱', 'نسل ۱۲', 'نسل ۱۳' ),
					'used_by' => array( 'laptop', 'aio', 'desktop' ),
				),
				array(
					'label'  => __( 'تعداد هسته پردازنده', 'stocksystem' ),
					'slug'   => 'cpu-cores',
					'values' => array( '۲ هسته', '۴ هسته', '۶ هسته', '۸ هسته', '۱۲ هسته و بیشتر' ),
					'used_by' => array( 'laptop', 'aio', 'desktop' ),
				),
				array(
					'label'  => __( 'نوع رم', 'stocksystem' ),
					'slug'   => 'ram-type',
					'values' => array( 'DDR2', 'DDR3', 'DDR3L', 'DDR4', 'DDR5' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts' ),
				),
				array(
					'label'  => __( 'تعداد اسلات رم', 'stocksystem' ),
					'slug'   => 'ram-slots',
					'values' => array( '۱ اسلات', '۲ اسلات', '۴ اسلات' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts (مادربرد)' ),
				),
				array(
					'label'  => __( 'نوع ذخیره‌سازی', 'stocksystem' ),
					'slug'   => 'storage-type',
					'values' => array( 'HDD', 'SSD SATA', 'SSD NVMe', 'HDD + SSD' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts' ),
				),
				array(
					'label'  => __( 'اینترفیس ذخیره‌سازی', 'stocksystem' ),
					'slug'   => 'storage-if',
					'values' => array( 'SATA', 'NVMe', 'M.2', 'mSATA', 'IDE' ),
					'used_by' => array( 'parts' ),
				),
				array(
					'label'  => __( 'کارت گرافیک', 'stocksystem' ),
					'slug'   => 'gpu',
					'values' => array( 'Intel UHD Graphics', 'Intel Iris Xe', 'NVIDIA GT 730', 'NVIDIA GTX 1650', 'NVIDIA MX450', 'NVIDIA RTX 3050', 'NVIDIA RTX 3060', 'AMD Radeon Vega', 'AMD Radeon RX 570' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts' ),
				),
				array(
					'label'  => __( 'نوع گرافیک', 'stocksystem' ),
					'slug'   => 'gpu-type',
					'values' => array( 'یکپارچه (Integrated)', 'مجزا (Dedicated)' ),
					'used_by' => array( 'laptop', 'aio', 'desktop' ),
				),
				array(
					'label'  => __( 'حافظهٔ کارت گرافیک (VRAM)', 'stocksystem' ),
					'slug'   => 'vram',
					'values' => array( '2GB', '4GB', '6GB', '8GB', '12GB' ),
					'used_by' => array( 'laptop', 'aio', 'desktop', 'parts' ),
				),
				array(
					'label'  => __( 'سیستم‌عامل', 'stocksystem' ),
					'slug'   => 'os',
					'values' => array( 'ویندوز ۱۰', 'ویندوز ۱۱', 'بدون سیستم‌عامل (Free DOS)', 'macOS' ),
					'used_by' => array( 'laptop', 'aio', 'desktop' ),
				),
				array(
					'label'  => __( 'رزولوشن نمایشگر', 'stocksystem' ),
					'slug'   => 'resolution',
					'values' => array( 'HD (1366×768)', 'FHD (1920×1080)', '2K (2560×1440)', '4K (3840×2160)' ),
					'used_by' => array( 'laptop', 'aio', 'monitor' ),
				),
				array(
					'label'  => __( 'نوع پنل نمایشگر', 'stocksystem' ),
					'slug'   => 'panel-type',
					'values' => array( 'IPS', 'TN', 'VA', 'OLED' ),
					'used_by' => array( 'laptop', 'aio', 'monitor' ),
				),
				array(
					'label'  => __( 'نرخ تازه‌سازی', 'stocksystem' ),
					'slug'   => 'refresh-rate',
					'values' => array( '60Hz', '75Hz', '90Hz', '100Hz', '120Hz', '144Hz' ),
					'used_by' => array( 'laptop', 'aio', 'monitor' ),
				),
				array(
					'label'  => __( 'صفحهٔ لمسی', 'stocksystem' ),
					'slug'   => 'touchscreen',
					'values' => array( 'دارد', 'ندارد' ),
					'used_by' => array( 'laptop', 'aio' ),
				),
				array(
					'label'  => __( 'نوع پایهٔ نمایشگر', 'stocksystem' ),
					'slug'   => 'stand',
					'values' => array( 'ثابت', 'قابل تنظیم ارتفاع', 'قابل نصب دیواری (VESA)' ),
					'used_by' => array( 'aio', 'monitor' ),
				),
				array(
					'label'  => __( 'پورت‌ها', 'stocksystem' ),
					'slug'   => 'ports',
					'values' => array( 'USB-C', 'USB-A 3.0', 'USB-A 2.0', 'HDMI', 'Thunderbolt 4', 'DisplayPort', 'VGA', 'DVI', 'RJ45', 'Jack صدا' ),
					'used_by' => array( 'laptop', 'aio', 'monitor', 'desktop' ),
				),
				array(
					'label'  => __( 'اتصال بی‌سیم (Wi-Fi/بلوتوث)', 'stocksystem' ),
					'slug'   => 'wireless',
					'values' => array( 'Wi-Fi 5 + بلوتوث ۴٫۲', 'Wi-Fi 5 + بلوتوث ۵٫۰', 'Wi-Fi 6 + بلوتوث ۵٫۱', 'ندارد' ),
					'used_by' => array( 'laptop', 'aio', 'desktop' ),
				),
				array(
					'label'  => __( 'وبکم', 'stocksystem' ),
					'slug'   => 'webcam',
					'values' => array( 'دارد HD', 'دارد FHD', 'ندارد' ),
					'used_by' => array( 'laptop', 'aio', 'monitor' ),
				),
			),
		),
		'laptop'    => array(
			'title' => __( 'مخصوص لپ‌تاپ', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'وزن', 'stocksystem' ),
					'slug'   => 'weight',
					'values' => array( '۱٫۱ کیلوگرم', '۱٫۳ کیلوگرم', '۱٫۵ کیلوگرم', '۱٫۸ کیلوگرم', '۲٫۲ کیلوگرم', '۲٫۵ کیلوگرم و بیشتر' ),
				),
				array(
					'label'  => __( 'قابلیت ارتقا رم', 'stocksystem' ),
					'slug'   => 'ram-upgrade',
					'values' => array( 'تا ۱۶GB', 'تا ۳۲GB', 'تا ۶۴GB', 'غیرقابل ارتقا (لحیم‌شده)' ),
				),
				array(
					'label'  => __( 'صفحه‌کلید', 'stocksystem' ),
					'slug'   => 'keyboard',
					'values' => array( 'بک‌لایت‌دار', 'بدون نور پس‌زمینه', 'دارای نامبرپد' ),
				),
				array(
					'label'  => __( 'حسگر اثر انگشت', 'stocksystem' ),
					'slug'   => 'fingerprint',
					'values' => array( 'دارد', 'ندارد' ),
				),
				array(
					'label'  => __( 'جنس بدنه', 'stocksystem' ),
					'slug'   => 'body-material',
					'values' => array( 'پلاستیک', 'آلومینیوم', 'منیزیم-آلومینیوم', 'کربن‌فایبر' ),
				),
				array(
					'label'  => __( 'نوع کاربری لپ‌تاپ', 'stocksystem' ),
					'slug'   => 'device-type',
					'values' => array( 'اداری/دانشجویی', 'گیمینگ', 'ورک‌استیشن', 'اولترابوک', 'تبدیل‌شونده ۲در۱' ),
				),
				array(
					'label'  => __( 'نوع اتصال مانیتور (آل‌این‌وان)', 'stocksystem' ),
					'slug'   => 'aio-connect',
					'values' => array( 'تمام‌یکپارچه', 'جدا‌شونده' ),
				),
			),
		),
		'monitor'   => array(
			'title' => __( 'مخصوص مانیتور', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'نوع سطح صفحه', 'stocksystem' ),
					'slug'   => 'panel-curve',
					'values' => array( 'تخت (Flat)', 'خمیده (Curved)' ),
				),
				array(
					'label'  => __( 'زمان پاسخ', 'stocksystem' ),
					'slug'   => 'response-time',
					'values' => array( '1ms', '4ms', '5ms', '8ms' ),
				),
				array(
					'label'  => __( 'نسبت تصویر', 'stocksystem' ),
					'slug'   => 'aspect-ratio',
					'values' => array( '16:9', '16:10', '21:9', '4:3' ),
				),
				array(
					'label'  => __( 'اسپیکر داخلی', 'stocksystem' ),
					'slug'   => 'speaker',
					'values' => array( 'دارد', 'ندارد' ),
				),
			),
		),
		'desktop'   => array(
			'title' => __( 'مخصوص کیس و مینی‌کیس', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'فرم‌فاکتور', 'stocksystem' ),
					'slug'   => 'form-factor',
					'values' => array( 'Tower', 'Mini Tower', 'SFF (کوچک)', 'Micro/Mini PC' ),
				),
				array(
					'label'  => __( 'توان منبع تغذیه', 'stocksystem' ),
					'slug'   => 'psu-watt',
					'values' => array( 'ندارد', '۲۵۰ وات', '۳۰۰ وات', '۵۰۰ وات', '۶۵۰ وات' ),
				),
				array(
					'label'  => __( 'اسلات گرافیک آزاد', 'stocksystem' ),
					'slug'   => 'gpu-slot',
					'values' => array( 'دارد (PCIe x16)', 'ندارد' ),
				),
				array(
					'label'  => __( 'جنس بدنهٔ کیس', 'stocksystem' ),
					'slug'   => 'case-material',
					'values' => array( 'فلزی', 'پلاستیکی', 'ترکیبی (فلز و پلاستیک)' ),
				),
				array(
					'label'  => __( 'نورپردازی RGB', 'stocksystem' ),
					'slug'   => 'rgb',
					'values' => array( 'دارد', 'ندارد' ),
				),
			),
		),
		'parts'     => array(
			'title' => __( 'قطعات (رم، هارد، گرافیک، مادربرد، پاور، کیس، خنک‌کننده)', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'فرکانس رم', 'stocksystem' ),
					'slug'   => 'ram-freq',
					'values' => array( '1600MHz', '2133MHz', '2400MHz', '2666MHz', '3200MHz', '3600MHz' ),
				),
				array(
					'label'  => __( 'تایمینگ رم (CL)', 'stocksystem' ),
					'slug'   => 'ram-cl',
					'values' => array( 'CL16', 'CL18', 'CL19', 'CL22' ),
				),
				array(
					'label'  => __( 'فرم‌فاکتور رم', 'stocksystem' ),
					'slug'   => 'ram-form',
					'values' => array( 'DIMM (دسکتاپ)', 'SO-DIMM (لپ‌تاپ)' ),
				),
				array(
					'label'  => __( 'فرم‌فاکتور ذخیره‌سازی', 'stocksystem' ),
					'slug'   => 'storage-form',
					'values' => array( '۲٫۵ اینچ', '۳٫۵ اینچ', 'M.2 2280', 'mSATA' ),
				),
				array(
					'label'  => __( 'اینترفیس کارت گرافیک', 'stocksystem' ),
					'slug'   => 'gpu-interface',
					'values' => array( 'PCIe 3.0 x16', 'PCIe 4.0 x16' ),
				),
				array(
					'label'  => __( 'کانکتور برق کارت گرافیک', 'stocksystem' ),
					'slug'   => 'gpu-power',
					'values' => array( 'بدون نیاز به کانکتور', '۶ پین', '۸ پین', '۶+۸ پین' ),
				),
				array(
					'label'  => __( 'سوکت پردازنده (مادربرد)', 'stocksystem' ),
					'slug'   => 'cpu-socket',
					'values' => array( 'LGA1151', 'LGA1200', 'LGA1700', 'AM4', 'AM5' ),
				),
				array(
					'label'  => __( 'چیپ‌ست مادربرد', 'stocksystem' ),
					'slug'   => 'chipset',
					'values' => array( 'H410', 'B460', 'B560', 'B660', 'B450', 'B550' ),
				),
				array(
					'label'  => __( 'فرم‌فاکتور مادربرد', 'stocksystem' ),
					'slug'   => 'mobo-form',
					'values' => array( 'ATX', 'Micro-ATX', 'Mini-ITX' ),
				),
				array(
					'label'  => __( 'سرتیفیکیت پاور', 'stocksystem' ),
					'slug'   => 'psu-cert',
					'values' => array( '80Plus White', '80Plus Bronze', '80Plus Gold' ),
				),
				array(
					'label'  => __( 'نوع مدولار بودن پاور', 'stocksystem' ),
					'slug'   => 'psu-modular',
					'values' => array( 'فول مدولار', 'نیمه مدولار', 'غیرمدولار' ),
				),
				array(
					'label'  => __( 'نوع خنک‌کننده', 'stocksystem' ),
					'slug'   => 'cooler-type',
					'values' => array( 'فن هوایی (Air)', 'واترکولینگ (AIO)' ),
				),
			),
		),
		'accessory' => array(
			'title' => __( 'لوازم جانبی (کیبورد، ماوس، کیف، هاب و...)', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'نوع اتصال', 'stocksystem' ),
					'slug'   => 'connect-type',
					'values' => array( 'باسیم', 'بی‌سیم', 'بلوتوث' ),
				),
				array(
					'label'  => __( 'جنس', 'stocksystem' ),
					'slug'   => 'material',
					'values' => array( 'پارچه‌ای', 'چرمی', 'پلاستیکی', 'نئوپرن' ),
				),
				array(
					'label'  => __( 'نوع سوییچ کیبورد', 'stocksystem' ),
					'slug'   => 'switch-type',
					'values' => array( 'ممبران', 'مکانیکی بلو', 'مکانیکی رد', 'مکانیکی براون' ),
				),
				array(
					'label'  => __( 'دقت حسگر ماوس (DPI)', 'stocksystem' ),
					'slug'   => 'dpi',
					'values' => array( '800', '1600', '3200', '6400' ),
				),
			),
		),
	);
}

/**
 * Finds an existing global attribute by label (case-insensitive), the
 * same way stocksystem_find_attribute_taxonomy() in inc/import-batches.php
 * does, so this never creates a duplicate of pa_ram / pa_size /
 * pa_color / pa_ذخیره‌سازی or of anything a previous run already made.
 */
function stocksystem_find_global_attribute_by_label( $label ) {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return null;
	}
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		if ( 0 === strcasecmp( $attribute->attribute_label, $label ) ) {
			return $attribute;
		}
	}
	return null;
}

/**
 * Creates every attribute in the catalog that doesn't already exist
 * (matched by label), and adds every sample term that doesn't already
 * exist on it (matched by term name, case-insensitive). Returns a
 * report array for the admin page to render — nothing here is silent.
 */
function stocksystem_run_standard_attributes_bootstrap() {
	$report = array(
		'attributes_created' => array(),
		'attributes_skipped' => array(),
		'terms_created'      => array(),
		'errors'             => array(),
	);

	foreach ( stocksystem_standard_attributes_catalog() as $group ) {
		foreach ( $group['items'] as $item ) {
			$label    = $item['label'];
			$existing = stocksystem_find_global_attribute_by_label( $label );

			if ( $existing ) {
				$attribute_id = (int) $existing->attribute_id;
				$taxonomy     = wc_attribute_taxonomy_name( $existing->attribute_name );
				$report['attributes_skipped'][] = $label;
			} else {
				$attribute_id = wc_create_attribute(
					array(
						'name'         => $label,
						'slug'         => $item['slug'],
						'type'         => 'select',
						'order_by'     => 'menu_order',
						'has_archives' => false,
					)
				);

				if ( is_wp_error( $attribute_id ) ) {
					$report['errors'][] = $label . ': ' . $attribute_id->get_error_message();
					continue;
				}

				// wc_get_attribute()/wc_get_attribute_taxonomies() read
				// through the `wc_attribute_taxonomies` transient, which
				// is stale until the next full request even after
				// delete_transient() — this hits the table directly, so
				// it's correct within the same request the attribute
				// was just created in.
				$taxonomy = wc_attribute_taxonomy_name_by_id( $attribute_id );

				if ( ! $taxonomy ) {
					$report['errors'][] = $label . ': ' . __( 'شناسهٔ ویژگی تازه‌ساخته‌شده پیدا نشد.', 'stocksystem' );
					continue;
				}

				if ( ! taxonomy_exists( $taxonomy ) ) {
					register_taxonomy(
						$taxonomy,
						apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
						apply_filters( 'woocommerce_taxonomy_args_' . $taxonomy, array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true ) )
					);
				}

				$report['attributes_created'][] = $label;
			}

			if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
				$report['errors'][] = $label . ': ' . __( 'تاکسونومی ساخته نشد، مقادیر نمونه رد شدند.', 'stocksystem' );
				continue;
			}

			foreach ( $item['values'] as $value ) {
				if ( term_exists( $value, $taxonomy ) ) {
					continue;
				}
				$result = wp_insert_term( $value, $taxonomy );
				if ( is_wp_error( $result ) ) {
					// term_exists() already guards the common case; a
					// leftover error here is worth surfacing, not hiding.
					$report['errors'][] = $label . ' « ' . $value . ' »: ' . $result->get_error_message();
					continue;
				}
				$report['terms_created'][] = $label . ' « ' . $value . ' »';
			}
		}
	}

	return $report;
}

function stocksystem_attributes_bootstrap_menu() {
	add_submenu_page(
		'stocksystem',
		__( 'ویژگی‌های استاندارد محصولات', 'stocksystem' ),
		__( 'ویژگی‌های استاندارد', 'stocksystem' ),
		'manage_woocommerce',
		'stocksystem-attributes-bootstrap',
		'stocksystem_render_attributes_bootstrap_page'
	);
}
add_action( 'admin_menu', 'stocksystem_attributes_bootstrap_menu', 20 );

function stocksystem_render_attributes_bootstrap_page() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		return;
	}

	$report = null;

	if ( isset( $_POST['stocksystem_run_attributes_bootstrap'] ) ) {
		check_admin_referer( 'stocksystem_attributes_bootstrap' );
		$report = stocksystem_run_standard_attributes_bootstrap();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'ویژگی‌های استاندارد محصولات', 'stocksystem' ); ?></h1>
		<p>
			<?php esc_html_e( 'این ابزار یک‌بار برای همیشه، مجموعهٔ ویژگی‌های استانداردی که برای لپ‌تاپ، آل‌این‌وان، مانیتور، کیس/مینی‌کیس، قطعات و لوازم جانبی لازم است را با چند مقدار نمونه می‌سازد — تا لازم نباشد هرکدام را دستی از Products ← Attributes اضافه کنی.', 'stocksystem' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'اجرای دوباره این ابزار مشکلی ندارد: ویژگی‌ها و مقادیری که از قبل وجود دارند (از جمله RAM، سایز و رنگ فعلی‌ات) نادیده گرفته می‌شوند و فقط موارد جدید اضافه می‌شوند.', 'stocksystem' ); ?>
		</p>

		<?php if ( $report ) : ?>
			<div class="notice notice-success">
				<p>
					<strong><?php esc_html_e( 'انجام شد.', 'stocksystem' ); ?></strong>
					<?php
					printf(
						/* translators: 1: created attributes count, 2: created terms count, 3: skipped attributes count */
						esc_html__( '%1$d ویژگی جدید ساخته شد، %2$d مقدار نمونه اضافه شد، %3$d ویژگی از قبل موجود بود و رد شد.', 'stocksystem' ),
						count( $report['attributes_created'] ),
						count( $report['terms_created'] ),
						count( $report['attributes_skipped'] )
					);
					?>
				</p>
			</div>

			<?php if ( ! empty( $report['errors'] ) ) : ?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e( 'چند مورد با خطا مواجه شد:', 'stocksystem' ); ?></strong></p>
					<ul style="list-style:disc;padding-inline-start:20px;">
						<?php foreach ( $report['errors'] as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $report['attributes_created'] ) ) : ?>
				<h2><?php esc_html_e( 'ویژگی‌های ساخته‌شده', 'stocksystem' ); ?></h2>
				<p><?php echo esc_html( implode( '، ', $report['attributes_created'] ) ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $report['attributes_skipped'] ) ) : ?>
				<h2><?php esc_html_e( 'ویژگی‌های از قبل موجود (رد شد)', 'stocksystem' ); ?></h2>
				<p><?php echo esc_html( implode( '، ', $report['attributes_skipped'] ) ); ?></p>
			<?php endif; ?>

			<p>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=product&page=product_attributes' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'رفتن به Products ← Attributes برای مشاهده', 'stocksystem' ); ?>
				</a>
			</p>
		<?php endif; ?>

		<h2><?php esc_html_e( 'فهرست ویژگی‌هایی که ساخته می‌شوند', 'stocksystem' ); ?></h2>
		<?php foreach ( stocksystem_standard_attributes_catalog() as $group ) : ?>
			<h3><?php echo esc_html( $group['title'] ); ?></h3>
			<table class="widefat striped" style="max-width:1100px;margin-bottom:24px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ویژگی', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'مقادیر نمونه', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'همچنین کاربرد دارد در', 'stocksystem' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $group['items'] as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item['label'] ); ?></td>
							<td><?php echo esc_html( implode( '، ', $item['values'] ) ); ?></td>
							<td><?php echo ! empty( $item['used_by'] ) ? esc_html( implode( '، ', $item['used_by'] ) ) : '—'; ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				</tbody>
			</table>
		<?php endforeach; ?>

		<form method="post">
			<?php wp_nonce_field( 'stocksystem_attributes_bootstrap' ); ?>
			<p>
				<button type="submit" name="stocksystem_run_attributes_bootstrap" value="1" class="button button-primary button-hero" onclick="return confirm('<?php echo esc_js( __( 'ویژگی‌های بالا ساخته شوند؟', 'stocksystem' ) ); ?>');">
					<?php esc_html_e( 'ایجاد ویژگی‌های استاندارد', 'stocksystem' ); ?>
				</button>
			</p>
		</form>
	</div>
	<?php
}
