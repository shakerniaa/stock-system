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
 */
function stocksystem_standard_attributes_catalog() {
	return array(
		'shared'    => array(
			'title' => __( 'مشترک بین دسته‌ها', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'پردازنده', 'stocksystem' ),
					'slug'   => 'cpu',
					'values' => array( 'Intel Core i3-1115G4', 'Intel Core i5-1135G7', 'Intel Core i5-1235U', 'Intel Core i7-1165G7', 'Intel Core i7-1255U', 'AMD Ryzen 5 3500U', 'AMD Ryzen 5 5500U', 'AMD Ryzen 7 5700U', 'Apple M1' ),
				),
				array(
					'label'  => __( 'نسل پردازنده', 'stocksystem' ),
					'slug'   => 'cpu-gen',
					'values' => array( 'نسل ۸', 'نسل ۹', 'نسل ۱۰', 'نسل ۱۱', 'نسل ۱۲', 'نسل ۱۳' ),
				),
				array(
					'label'  => __( 'نوع رم', 'stocksystem' ),
					'slug'   => 'ram-type',
					'values' => array( 'DDR3', 'DDR4', 'DDR5' ),
				),
				array(
					'label'  => __( 'کارت گرافیک', 'stocksystem' ),
					'slug'   => 'gpu',
					'values' => array( 'Intel UHD Graphics', 'Intel Iris Xe', 'NVIDIA GTX 1650', 'NVIDIA MX450', 'NVIDIA RTX 3050', 'AMD Radeon Vega' ),
				),
				array(
					'label'  => __( 'نوع گرافیک', 'stocksystem' ),
					'slug'   => 'gpu-type',
					'values' => array( 'یکپارچه (Integrated)', 'مجزا (Dedicated)' ),
				),
			),
		),
		'laptop'    => array(
			'title' => __( 'لپ‌تاپ و آل‌این‌وان', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'رزولوشن نمایشگر', 'stocksystem' ),
					'slug'   => 'resolution',
					'values' => array( 'HD (1366×768)', 'FHD (1920×1080)', '2K', '4K' ),
				),
				array(
					'label'  => __( 'نوع پنل نمایشگر', 'stocksystem' ),
					'slug'   => 'panel-type',
					'values' => array( 'IPS', 'TN', 'OLED', 'VA' ),
				),
				array(
					'label'  => __( 'وزن', 'stocksystem' ),
					'slug'   => 'weight',
					'values' => array( '۱٫۳ کیلوگرم', '۱٫۵ کیلوگرم', '۱٫۸ کیلوگرم', '۲٫۲ کیلوگرم' ),
				),
				array(
					'label'  => __( 'پورت‌ها', 'stocksystem' ),
					'slug'   => 'ports',
					'values' => array( 'USB-C', 'USB-A', 'HDMI', 'Thunderbolt 4', 'RJ45', 'Jack صدا' ),
				),
				array(
					'label'  => __( 'قابلیت ارتقا رم', 'stocksystem' ),
					'slug'   => 'ram-upgrade',
					'values' => array( 'تا ۱۶GB', 'تا ۳۲GB', 'تا ۶۴GB', 'غیرقابل ارتقا' ),
				),
				array(
					'label'  => __( 'صفحه‌کلید', 'stocksystem' ),
					'slug'   => 'keyboard',
					'values' => array( 'بک‌لایت‌دار', 'بدون نور پس‌زمینه', 'دارای نامبرپد' ),
				),
				array(
					'label'  => __( 'نوع اتصال مانیتور (آل‌این‌وان)', 'stocksystem' ),
					'slug'   => 'aio-connect',
					'values' => array( 'تمام‌یکپارچه', 'جدا‌شونده' ),
				),
				array(
					'label'  => __( 'صفحهٔ لمسی', 'stocksystem' ),
					'slug'   => 'touchscreen',
					'values' => array( 'دارد', 'ندارد' ),
				),
			),
		),
		'monitor'   => array(
			'title' => __( 'مانیتور', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'نرخ تازه‌سازی', 'stocksystem' ),
					'slug'   => 'refresh-rate',
					'values' => array( '60Hz', '75Hz', '100Hz', '144Hz' ),
				),
				array(
					'label'  => __( 'نوع سطح صفحه', 'stocksystem' ),
					'slug'   => 'panel-curve',
					'values' => array( 'تخت (Flat)', 'خمیده (Curved)' ),
				),
			),
		),
		'desktop'   => array(
			'title' => __( 'کیس و مینی‌کیس', 'stocksystem' ),
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
			),
		),
		'parts'     => array(
			'title' => __( 'قطعات', 'stocksystem' ),
			'items' => array(
				array(
					'label'  => __( 'فرکانس رم', 'stocksystem' ),
					'slug'   => 'ram-freq',
					'values' => array( '2400MHz', '2666MHz', '3200MHz', '3600MHz' ),
				),
				array(
					'label'  => __( 'اینترفیس ذخیره‌سازی', 'stocksystem' ),
					'slug'   => 'storage-if',
					'values' => array( 'SATA', 'NVMe', 'M.2' ),
				),
				array(
					'label'  => __( 'حافظهٔ کارت گرافیک (VRAM)', 'stocksystem' ),
					'slug'   => 'vram',
					'values' => array( '2GB', '4GB', '6GB', '8GB' ),
				),
				array(
					'label'  => __( 'سوکت مادربرد', 'stocksystem' ),
					'slug'   => 'cpu-socket',
					'values' => array( 'LGA1151', 'LGA1200', 'LGA1700', 'AM4', 'AM5' ),
				),
				array(
					'label'  => __( 'چیپ‌ست مادربرد', 'stocksystem' ),
					'slug'   => 'chipset',
					'values' => array( 'H410', 'B460', 'B560', 'B660', 'B450', 'B550' ),
				),
				array(
					'label'  => __( 'سرتیفیکیت پاور', 'stocksystem' ),
					'slug'   => 'psu-cert',
					'values' => array( '80Plus White', '80Plus Bronze', '80Plus Gold' ),
				),
				array(
					'label'  => __( 'نوع خنک‌کننده', 'stocksystem' ),
					'slug'   => 'cooler-type',
					'values' => array( 'فن هوایی (Air)', 'واترکولینگ (AIO)' ),
				),
			),
		),
		'accessory' => array(
			'title' => __( 'لوازم جانبی', 'stocksystem' ),
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
			<table class="widefat striped" style="max-width:900px;margin-bottom:24px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'ویژگی', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'مقادیر نمونه', 'stocksystem' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $group['items'] as $item ) : ?>
						<tr>
							<td><?php echo esc_html( $item['label'] ); ?></td>
							<td><?php echo esc_html( implode( '، ', $item['values'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
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
