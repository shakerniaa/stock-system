<?php
/**
 * Homepage hero variants — five alternative hero designs the owner can
 * switch between from the admin, plus every piece of copy, image, link
 * and colour each one needs.
 *
 * Source: stocksystem-dev-kit/HomePage.dc.html (rounds 1a–1e). The
 * design file ships each variant as a static mock with its data inlined
 * in JS; this file is the real data layer behind them, and
 * template-parts/home/hero-*.php are the real renderers.
 *
 * Variants:
 *   classic — the original 7/5 split hero (template-parts/home/hero-classic.php)
 *   slider  — 1a, full-bleed slider with titled tabs
 *   bento   — 1b, one large banner + four tiles, nothing hidden
 *   sidebar — 1c, marketplace layout: categories | banner | deal column
 *   finder  — 1d, "which laptop?" two-question picker
 *   deal    — 1e, campaign hero with a live countdown + deal cards
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** The variant keys, with their admin labels. */
function stocksystem_hero_variants() {
	return array(
		'classic' => __( 'کلاسیک (طرح فعلی) — متن راست، تصویرها چپ', 'stocksystem' ),
		'slider'  => __( '۱a — اسلایدر تمام‌عرض با تب‌های عنوان‌دار', 'stocksystem' ),
		'bento'   => __( '۱b — گرید بنری (بنتو): یک بنر بزرگ + چهار کاشی', 'stocksystem' ),
		'sidebar' => __( '۱c — منوی دسته‌بندی کنار بنر + ستون پیشنهاد روز', 'stocksystem' ),
		'finder'  => __( '۱d — «کدام دستگاه مناسب من است؟» (دو سؤال)', 'stocksystem' ),
		'deal'    => __( '۱e — پیشنهاد ویژه با شمارش معکوس زنده', 'stocksystem' ),
	);
}

/** Colour themes a slide/tile can use, matching the design's THEMES map. */
function stocksystem_hero_themes() {
	return array(
		'teal'   => __( 'فیروزه‌ای (پیش‌فرض)', 'stocksystem' ),
		'orange' => __( 'نارنجی (حراج و کمپین)', 'stocksystem' ),
		'deep'   => __( 'سبز تیره', 'stocksystem' ),
		'red'    => __( 'قرمز (تخفیف ویژه)', 'stocksystem' ),
	);
}

/**
 * CSS custom properties per theme. Kept here rather than in CSS so a
 * repeater row can pick its theme by key and the template can inline
 * just the four variables the component reads.
 */
function stocksystem_hero_theme_vars( $key ) {
	$themes = array(
		'teal'   => array( 'bg' => 'var(--c-ink-800)', 'accent' => 'var(--c-teal-300)', 'chip-bg' => 'rgba(14,186,175,.14)', 'chip-border' => 'rgba(14,186,175,.35)', 'cta-bg' => 'var(--c-teal-500)', 'cta-fg' => 'var(--c-ink-900)' ),
		'orange' => array( 'bg' => 'var(--c-ink-800)', 'accent' => '#F9A85C', 'chip-bg' => 'rgba(245,130,32,.16)', 'chip-border' => 'rgba(245,130,32,.4)', 'cta-bg' => 'var(--c-orange-500)', 'cta-fg' => '#FFFFFF' ),
		'deep'   => array( 'bg' => 'var(--c-ink-700)', 'accent' => 'var(--c-teal-300)', 'chip-bg' => 'rgba(14,186,175,.14)', 'chip-border' => 'rgba(14,186,175,.35)', 'cta-bg' => 'var(--c-orange-500)', 'cta-fg' => '#FFFFFF' ),
		'red'    => array( 'bg' => '#4A1113', 'accent' => '#FFB4B4', 'chip-bg' => 'rgba(198,40,40,.22)', 'chip-border' => 'rgba(255,180,180,.4)', 'cta-bg' => '#FFFFFF', 'cta-fg' => '#8E1B1E' ),
	);

	$theme = isset( $themes[ $key ] ) ? $themes[ $key ] : $themes['teal'];
	$out   = '';
	foreach ( $theme as $prop => $value ) {
		$out .= '--hero-' . $prop . ':' . $value . ';';
	}

	return $out;
}

/** Shorthand for one hero setting, e.g. stocksystem_hero( 'variant' ). */
function stocksystem_hero( $key = null ) {
	$values = stocksystem_opt( 'hero' );

	if ( null === $key ) {
		return $values;
	}

	return isset( $values[ $key ] ) ? $values[ $key ] : '';
}

/** The active variant, falling back to the classic hero. */
function stocksystem_hero_variant() {
	$variant = stocksystem_hero( 'variant' );

	return isset( stocksystem_hero_variants()[ $variant ] ) ? $variant : 'classic';
}

/* -------------------------------------------------------------------------
 * Data helpers the variants need (none of this existed before)
 * ---------------------------------------------------------------------- */

/**
 * Published-product count per category term id. WordPress' own term
 * `count` includes drafts and private products for some statuses, so
 * this counts published, visible products directly — one query, cached
 * per request. Used by the 1c sidebar and the bento tiles.
 *
 * @return int[] term_id => count
 */
function stocksystem_category_product_counts() {
	static $counts = null;

	if ( null !== $counts ) {
		return $counts;
	}

	global $wpdb;

	$rows = $wpdb->get_results(
		"SELECT tt.term_id, COUNT( DISTINCT p.ID ) AS total
		 FROM {$wpdb->term_taxonomy} tt
		 INNER JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
		 INNER JOIN {$wpdb->posts} p ON p.ID = tr.object_id
		 WHERE tt.taxonomy = 'product_cat' AND p.post_type = 'product' AND p.post_status = 'publish'
		 GROUP BY tt.term_id"
	);

	$counts = array();
	foreach ( $rows as $row ) {
		$counts[ (int) $row->term_id ] = (int) $row->total;
	}

	return $counts;
}

/**
 * The three headline stats the 1d finder hero shows. Each falls back to
 * a computed value when the admin leaves the field blank, so the hero is
 * never wrong about the shop just because nobody filled a box in.
 */
function stocksystem_hero_stats() {
	$manual = stocksystem_hero( 'finder_stats' );
	$stats  = array();

	foreach ( (array) $manual as $row ) {
		if ( '' === trim( (string) $row['value'] ) || '' === trim( (string) $row['label'] ) ) {
			continue;
		}
		$stats[] = array( 'value' => $row['value'], 'label' => $row['label'] );
	}

	if ( ! empty( $stats ) ) {
		return $stats;
	}

	// Nothing set: derive from the catalogue.
	$total = 0;
	foreach ( stocksystem_category_product_counts() as $count ) {
		$total += $count;
	}

	$stats[] = array( 'value' => stocksystem_format_number( $total ), 'label' => __( 'دستگاه موجود', 'stocksystem' ) );
	$stats[] = array( 'value' => stocksystem_business( 'warranty_text' ), 'label' => __( 'گارانتی', 'stocksystem' ) );

	$battery = stocksystem_average_battery_health();
	if ( $battery ) {
		$stats[] = array( 'value' => stocksystem_to_persian_digits( $battery ) . '٪', 'label' => __( 'میانگین سلامت باتری', 'stocksystem' ) );
	}

	return $stats;
}

/**
 * Mean `_battery_health` across published products that have one, as an
 * int percentage, or 0 when no product has been tested yet.
 */
function stocksystem_average_battery_health() {
	global $wpdb;

	$avg = $wpdb->get_var(
		"SELECT AVG( CAST( pm.meta_value AS DECIMAL(5,2) ) )
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = '_battery_health' AND pm.meta_value <> ''
		   AND p.post_type = 'product' AND p.post_status = 'publish'"
	);

	return $avg ? (int) round( (float) $avg ) : 0;
}

/**
 * Deal cards for the 1e hero: on-sale products plus the "sold / left"
 * progress the design shows. WooCommerce has no "how many were in this
 * batch" number, so the bar is computed from managed stock: a product
 * that started at 10 and has 2 left reads 80%. Without managed stock
 * there is nothing honest to show, so the bar is omitted rather than
 * faked.
 */
function stocksystem_hero_deal_products( $limit = 4 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}

	$ids = stocksystem_hero( 'deal_product_ids' );
	$products = array();

	if ( is_array( $ids ) && ! empty( $ids ) ) {
		foreach ( array_slice( $ids, 0, $limit ) as $id ) {
			$product = wc_get_product( $id );
			if ( $product && 'publish' === $product->get_status() ) {
				$products[] = $product;
			}
		}
	}

	if ( empty( $products ) ) {
		$products = wc_get_products( array( 'limit' => $limit, 'status' => 'publish', 'on_sale' => true ) );
	}

	if ( empty( $products ) ) {
		$products = wc_get_products( array( 'limit' => $limit, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' ) );
	}

	$cards = array();

	foreach ( $products as $product ) {
		$stock = $product->get_manage_stock() ? (int) $product->get_stock_quantity() : null;
		$sold  = null;

		if ( null !== $stock ) {
			$total_sold = (int) $product->get_total_sales();
			$batch      = $stock + $total_sold;
			if ( $batch > 0 ) {
				$sold = min( 100, max( 0, (int) round( $total_sold / $batch * 100 ) ) );
			}
		}

		$cards[] = array(
			'product'  => $product,
			'sold'     => $sold,
			'left'     => null !== $stock ? $stock : null,
			'discount' => stocksystem_product_discount_percent( $product ),
		);
	}

	return $cards;
}

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

/**
 * Registers «هیروی صفحهٔ اصلی» on the استوک سیستم menu through the
 * declarative admin kit, so every field here gets its storage,
 * sanitising, repeater UI and reset button for free.
 */
function stocksystem_hero_admin_page( $pages ) {
	// The kit's select renderer walks options as key => label, so these
	// are passed straight through rather than reshaped into rows.
	$variant_options = stocksystem_hero_variants();
	$theme_options   = stocksystem_hero_themes();

	$pages['hero'] = array(
		'group'    => 'محتوای صفحه‌ها',
		'title'    => __( 'هیروی صفحهٔ اصلی', 'stocksystem' ),
		'menu'     => __( 'هیروی صفحهٔ اصلی', 'stocksystem' ),
		'intro'    => __( 'مدل هیرو (بالاترین بخش صفحهٔ اصلی) را انتخاب کن. فقط تنظیمات همان مدلی که انتخاب کرده‌ای روی سایت اثر دارد؛ بقیه ذخیره می‌مانند تا هر وقت خواستی برگردی. متن‌ها، تصویرها، رنگ‌ها و لینک‌های هر مدل را از همین‌جا عوض کن.', 'stocksystem' ),
		'view'     => '/',
		'sections' => array(
			array(
				'title'  => __( 'انتخاب مدل', 'stocksystem' ),
				'fields' => array(
					'variant' => array( 'type' => 'select', 'label' => __( 'مدل هیرو', 'stocksystem' ), 'default' => 'classic', 'options' => $variant_options, 'wide' => true ),
				),
			),
			array(
				'title'  => __( '۱a — اسلایدر', 'stocksystem' ),
				'intro'  => __( 'هر ردیف یک اسلاید است و عنوان کوتاهش در تب پایین اسلایدر می‌آید.', 'stocksystem' ),
				'fields' => array(
					'slider_autoplay' => array( 'type' => 'checkbox', 'label' => __( 'پخش خودکار', 'stocksystem' ), 'default' => 1 ),
					'slider_interval' => array( 'type' => 'number', 'label' => __( 'فاصلهٔ تعویض (ثانیه)', 'stocksystem' ), 'default' => 6, 'min' => 2, 'max' => 20 ),
					'slider_slides'   => array(
						'type'      => 'repeater',
						'label'     => __( 'اسلایدها', 'stocksystem' ),
						'max'       => 8,
						'add_label' => __( '+ اسلاید تازه', 'stocksystem' ),
						'fields'    => array(
							'tab'        => array( 'type' => 'text', 'label' => __( 'عنوان تب (کوتاه)', 'stocksystem' ) ),
							'eyebrow'    => array( 'type' => 'text', 'label' => __( 'خط بالای تیتر', 'stocksystem' ), 'wide' => true ),
							'title'      => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'wide' => true ),
							'body'       => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'wide' => true ),
							'cta'        => array( 'type' => 'text', 'label' => __( 'دکمهٔ اصلی — متن', 'stocksystem' ) ),
							'cta_url'    => array( 'type' => 'url', 'label' => __( 'دکمهٔ اصلی — لینک', 'stocksystem' ) ),
							'cta2'       => array( 'type' => 'text', 'label' => __( 'دکمهٔ دوم — متن (اختیاری)', 'stocksystem' ) ),
							'cta2_url'   => array( 'type' => 'url', 'label' => __( 'دکمهٔ دوم — لینک', 'stocksystem' ) ),
							'image'      => array( 'type' => 'image', 'label' => __( 'تصویر دستگاه', 'stocksystem' ) ),
							'price_label'=> array( 'type' => 'text', 'label' => __( 'برچسب قیمت (مثلاً «از»)', 'stocksystem' ) ),
							'price'      => array( 'type' => 'text', 'label' => __( 'قیمت (خالی = بدون برچسب قیمت)', 'stocksystem' ) ),
							'theme'      => array( 'type' => 'select', 'label' => __( 'رنگ', 'stocksystem' ), 'default' => 'teal', 'options' => $theme_options ),
						),
						'default'   => array(
							array(
								'tab' => __( 'استوک تست‌شده', 'stocksystem' ),
								'eyebrow' => __( 'تست‌شده · گارانتی‌دار', 'stocksystem' ),
								'title' => __( 'لپ‌تاپ حرفه‌ای، با قیمتی که منطقی است', 'stocksystem' ),
								'body' => __( 'هر دستگاه پیش از فروش تست کامل می‌شود و برگهٔ وضعیت دارد: سلامت باتری، ساعت کارکرد و وضعیت بدنه.', 'stocksystem' ),
								'cta' => __( 'مشاهدهٔ لپ‌تاپ‌ها', 'stocksystem' ), 'cta_url' => '',
								'cta2' => __( 'استاندارد درجه‌بندی', 'stocksystem' ), 'cta2_url' => '/stock-condition/',
								'image' => 0, 'price_label' => '', 'price' => '', 'theme' => 'teal',
							),
							array(
								'tab' => __( 'حراج', 'stocksystem' ),
								'eyebrow' => __( 'حراج پایان فصل', 'stocksystem' ),
								'title' => __( 'ارزان‌تر از هر وقت', 'stocksystem' ),
								'body' => __( 'تخفیف فقط روی قیمت است؛ گارانتی و برگهٔ تست کامل می‌ماند.', 'stocksystem' ),
								'cta' => __( 'خرید با تخفیف', 'stocksystem' ), 'cta_url' => '',
								'cta2' => '', 'cta2_url' => '',
								'image' => 0, 'price_label' => '', 'price' => '', 'theme' => 'orange',
							),
						),
					),
				),
			),
			array(
				'title'  => __( '۱b — گرید بنری (بنتو)', 'stocksystem' ),
				'intro'  => __( 'یک بنر بزرگ سمت راست و چهار کاشی کوچک کنارش — همه هم‌زمان دیده می‌شوند.', 'stocksystem' ),
				'fields' => array(
					'bento_eyebrow' => array( 'type' => 'text', 'label' => __( 'بنر اصلی — خط بالا', 'stocksystem' ), 'default' => __( 'تست‌شده · گارانتی‌دار', 'stocksystem' ), 'wide' => true ),
					'bento_title'   => array( 'type' => 'text', 'label' => __( 'بنر اصلی — تیتر', 'stocksystem' ), 'default' => __( 'لپ‌تاپ حرفه‌ای، با قیمتی که منطقی است', 'stocksystem' ), 'wide' => true ),
					'bento_body'    => array( 'type' => 'textarea', 'label' => __( 'بنر اصلی — متن', 'stocksystem' ), 'default' => __( 'هر دستگاه برگهٔ تست دارد: سلامت باتری، ساعت کارکرد، وضعیت بدنه.', 'stocksystem' ) ),
					'bento_cta'     => array( 'type' => 'text', 'label' => __( 'بنر اصلی — دکمه', 'stocksystem' ), 'default' => __( 'مشاهدهٔ لپ‌تاپ‌ها', 'stocksystem' ) ),
					'bento_cta_url' => array( 'type' => 'url', 'label' => __( 'بنر اصلی — لینک دکمه', 'stocksystem' ), 'default' => '' ),
					'bento_image'   => array( 'type' => 'image', 'label' => __( 'بنر اصلی — تصویر', 'stocksystem' ) ),
					'bento_theme'   => array( 'type' => 'select', 'label' => __( 'بنر اصلی — رنگ', 'stocksystem' ), 'default' => 'teal', 'options' => $theme_options ),
					'bento_tiles'   => array(
						'type'      => 'repeater',
						'label'     => __( 'کاشی‌های کناری', 'stocksystem' ),
						'max'       => 4,
						'add_label' => __( '+ کاشی تازه', 'stocksystem' ),
						'fields'    => array(
							'eyebrow' => array( 'type' => 'text', 'label' => __( 'خط بالا', 'stocksystem' ) ),
							'title'   => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'wide' => true ),
							'body'    => array( 'type' => 'text', 'label' => __( 'متن کوتاه (اختیاری)', 'stocksystem' ), 'wide' => true ),
							'cta'     => array( 'type' => 'text', 'label' => __( 'متن لینک', 'stocksystem' ) ),
							'url'     => array( 'type' => 'url', 'label' => __( 'لینک', 'stocksystem' ) ),
							'image'   => array( 'type' => 'image', 'label' => __( 'تصویر (اختیاری)', 'stocksystem' ) ),
							'bg'      => array( 'type' => 'color', 'label' => __( 'رنگ پس‌زمینه', 'stocksystem' ), 'default' => '#0B3A38' ),
							'fg'      => array( 'type' => 'color', 'label' => __( 'رنگ متن', 'stocksystem' ), 'default' => '#EBEDEC' ),
						),
						'default'   => array(
							array( 'eyebrow' => __( 'حراج پایان فصل', 'stocksystem' ), 'title' => __( 'تخفیف‌های این هفته', 'stocksystem' ), 'body' => '', 'cta' => __( 'خرید ←', 'stocksystem' ), 'url' => '', 'image' => 0, 'bg' => '#C62828', 'fg' => '#FFFFFF' ),
							array( 'eyebrow' => __( 'خرید اقساطی', 'stocksystem' ), 'title' => __( 'پرداخت در چند قسط', 'stocksystem' ), 'body' => __( 'اعتبارسنجی آنلاین', 'stocksystem' ), 'cta' => __( 'محاسبهٔ قسط ←', 'stocksystem' ), 'url' => '', 'image' => 0, 'bg' => '#FFFFFF', 'fg' => '#06292A' ),
							array( 'eyebrow' => __( 'تعمیرات تخصصی', 'stocksystem' ), 'title' => __( 'عیب‌یابی رایگان لپ‌تاپ', 'stocksystem' ), 'body' => __( 'تعمیر برد، نمایشگر، لولا', 'stocksystem' ), 'cta' => __( 'ثبت درخواست ←', 'stocksystem' ), 'url' => '/repair/', 'image' => 0, 'bg' => '#0B3A38', 'fg' => '#EBEDEC' ),
							array( 'eyebrow' => __( 'تازه رسید', 'stocksystem' ), 'title' => __( 'محمولهٔ تازه', 'stocksystem' ), 'body' => '', 'cta' => __( 'مشاهده ←', 'stocksystem' ), 'url' => '', 'image' => 0, 'bg' => '#FDEEE0', 'fg' => '#06292A' ),
						),
					),
				),
			),
			array(
				'title'  => __( '۱c — دسته‌بندی کنار بنر', 'stocksystem' ),
				'intro'  => __( 'ستون دسته‌ها از همان دسته‌بندی‌های «منو و فوتر» خوانده می‌شود؛ اینجا فقط بنر وسط و ستون پیشنهاد روز را تنظیم می‌کنی.', 'stocksystem' ),
				'fields' => array(
					'sidebar_eyebrow'  => array( 'type' => 'text', 'label' => __( 'بنر — خط بالا', 'stocksystem' ), 'default' => __( 'ارتقای رم و SSD روی هر دستگاه', 'stocksystem' ), 'wide' => true ),
					'sidebar_title'    => array( 'type' => 'text', 'label' => __( 'بنر — تیتر', 'stocksystem' ), 'default' => __( 'دستگاهت را خودت پیکربندی کن', 'stocksystem' ), 'wide' => true ),
					'sidebar_body'     => array( 'type' => 'textarea', 'label' => __( 'بنر — متن', 'stocksystem' ), 'default' => __( 'رم تا ۳۲ گیگ و SSD تا ۱ ترابایت، نصب در کارگاه.', 'stocksystem' ) ),
					'sidebar_cta'      => array( 'type' => 'text', 'label' => __( 'بنر — دکمه', 'stocksystem' ), 'default' => __( 'شروع پیکربندی', 'stocksystem' ) ),
					'sidebar_cta_url'  => array( 'type' => 'url', 'label' => __( 'بنر — لینک دکمه', 'stocksystem' ), 'default' => '' ),
					'sidebar_image'    => array( 'type' => 'image', 'label' => __( 'بنر — تصویر', 'stocksystem' ) ),
					'sidebar_theme'    => array( 'type' => 'select', 'label' => __( 'بنر — رنگ', 'stocksystem' ), 'default' => 'teal', 'options' => $theme_options ),
					'sidebar_deal_on'  => array( 'type' => 'checkbox', 'label' => __( 'ستون «پیشنهاد روز» نمایش داده شود', 'stocksystem' ), 'default' => 1 ),
					'sidebar_deal_title' => array( 'type' => 'text', 'label' => __( 'عنوان ستون', 'stocksystem' ), 'default' => __( 'پیشنهاد روز', 'stocksystem' ) ),
					'sidebar_deal_id'  => array( 'type' => 'number', 'label' => __( 'شناسهٔ محصول پیشنهاد روز (خالی = اولین محصول تخفیف‌دار)', 'stocksystem' ), 'default' => 0, 'min' => 0, 'allow_empty' => true ),
					'sidebar_deal_end' => array( 'type' => 'datetime', 'label' => __( 'پایان پیشنهاد (برای شمارش معکوس)', 'stocksystem' ), 'default' => '', 'allow_empty' => true ),
				),
			),
			array(
				'title'  => __( '۱d — «کدام دستگاه مناسب من است؟»', 'stocksystem' ),
				'intro'  => __( 'هر گزینه یک لینک به نتایج فیلترشدهٔ فروشگاه است. اگر لینکی خالی بماند، آن گزینه به فروشگاه می‌رود.', 'stocksystem' ),
				'fields' => array(
					'finder_title' => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'نمی‌دانید کدام لپ‌تاپ؟ دو سؤال جواب دهید.', 'stocksystem' ), 'wide' => true ),
					'finder_body'  => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'default' => __( 'دستگاه‌های تست‌شده را بر اساس کار روزانه و بودجهٔ شما فیلتر می‌کنیم — بدون نیاز به دانستن نسل پردازنده.', 'stocksystem' ) ),
					'finder_q1'    => array( 'type' => 'text', 'label' => __( 'سؤال اول', 'stocksystem' ), 'default' => __( '۱. بیشتر برای چه کاری؟', 'stocksystem' ) ),
					'finder_q2'    => array( 'type' => 'text', 'label' => __( 'سؤال دوم', 'stocksystem' ), 'default' => __( '۲. بودجه؟', 'stocksystem' ) ),
					'finder_cta'   => array( 'type' => 'text', 'label' => __( 'متن دکمهٔ نتیجه', 'stocksystem' ), 'default' => __( 'نمایش دستگاه‌ها', 'stocksystem' ) ),
					'finder_uses'  => array(
						'type' => 'repeater', 'label' => __( 'گزینه‌های سؤال اول', 'stocksystem' ), 'max' => 8, 'add_label' => __( '+ گزینه', 'stocksystem' ),
						'fields' => array(
							'label' => array( 'type' => 'text', 'label' => __( 'برچسب', 'stocksystem' ) ),
							'url'   => array( 'type' => 'url', 'label' => __( 'لینک نتایج', 'stocksystem' ), 'wide' => true ),
						),
						'default' => array(
							array( 'label' => __( 'اداری و درسی', 'stocksystem' ), 'url' => '' ),
							array( 'label' => __( 'برنامه‌نویسی', 'stocksystem' ), 'url' => '' ),
							array( 'label' => __( 'گرافیک و طراحی', 'stocksystem' ), 'url' => '' ),
							array( 'label' => __( 'گیمینگ', 'stocksystem' ), 'url' => '' ),
							array( 'label' => __( 'سبک و سفری', 'stocksystem' ), 'url' => '' ),
						),
					),
					'finder_budgets' => array(
						'type' => 'repeater', 'label' => __( 'گزینه‌های بودجه', 'stocksystem' ), 'max' => 6, 'add_label' => __( '+ بازهٔ قیمت', 'stocksystem' ),
						'fields' => array(
							'label' => array( 'type' => 'text', 'label' => __( 'برچسب', 'stocksystem' ) ),
							'url'   => array( 'type' => 'url', 'label' => __( 'لینک نتایج', 'stocksystem' ), 'wide' => true ),
						),
						'default' => array(),
					),
					'finder_stats' => array(
						'type' => 'repeater', 'label' => __( 'آمار کنار تیتر (خالی = محاسبهٔ خودکار)', 'stocksystem' ), 'max' => 4, 'add_label' => __( '+ آمار', 'stocksystem' ),
						'fields' => array(
							'value' => array( 'type' => 'text', 'label' => __( 'عدد', 'stocksystem' ) ),
							'label' => array( 'type' => 'text', 'label' => __( 'برچسب', 'stocksystem' ) ),
						),
						'default' => array(),
					),
				),
			),
			array(
				'title'  => __( '۱e — پیشنهاد ویژه با شمارش معکوس', 'stocksystem' ),
				'fields' => array(
					'deal_title'   => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'حراج این هفته — تا پایان موجودی', 'stocksystem' ), 'wide' => true ),
					'deal_body'    => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'default' => __( 'همهٔ دستگاه‌ها تست‌شده، با برگهٔ وضعیت و گارانتی کامل؛ تخفیف فقط روی قیمت است.', 'stocksystem' ) ),
					'deal_end'     => array( 'type' => 'datetime', 'label' => __( 'پایان حراج (شمارش معکوس)', 'stocksystem' ), 'default' => '', 'allow_empty' => true ),
					'deal_count'   => array( 'type' => 'number', 'label' => __( 'تعداد کارت', 'stocksystem' ), 'default' => 4, 'min' => 2, 'max' => 8 ),
					'deal_cta'     => array( 'type' => 'text', 'label' => __( 'متن دکمهٔ پایین', 'stocksystem' ), 'default' => __( 'همهٔ پیشنهادها', 'stocksystem' ) ),
					'deal_cta_url' => array( 'type' => 'url', 'label' => __( 'لینک دکمهٔ پایین', 'stocksystem' ), 'default' => '' ),
				),
			),
		),
	);

	return $pages;
}
add_filter( 'stocksystem_admin_pages', 'stocksystem_hero_admin_page' );
