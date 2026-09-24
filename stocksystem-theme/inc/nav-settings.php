<?php
/**
 * Navigation, mega menu and footer settings — Appearance → «منو و فوتر».
 *
 * Everything defaults to the automatic behaviour the theme always had
 * (categories, brands and featured product come from WooCommerce), so nothing
 * changes until something is edited here:
 *
 *   • categories   show / hide, rename and reorder — for the nav bar, mega
 *                  menu, mobile menu, footer and homepage tiles at once;
 *   • price ranges the buckets used by the mega menu and the shop page;
 *   • mega menu    per category (and the default «همه» view): brands, price
 *                  links, the highlight tile and an extra link column can each
 *                  stay automatic, be replaced with hand-written links, or be
 *                  hidden;
 *   • footer       social links, trust-badge code (e-Namad …), newsletter.
 *
 * One option: `stocksystem_nav`.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Getters
 * ---------------------------------------------------------------------- */

function stocksystem_nav_settings() {
	static $cache = null;

	if ( null === $cache ) {
		$saved = stocksystem_get_option( 'stocksystem_nav' );
		$saved = is_array( $saved ) ? $saved : array();

		$cache = array(
			'categories' => isset( $saved['categories'] ) && is_array( $saved['categories'] ) ? $saved['categories'] : array(),
			'prices'     => isset( $saved['prices'] ) && is_array( $saved['prices'] ) ? $saved['prices'] : array(),
			'mega'       => isset( $saved['mega'] ) && is_array( $saved['mega'] ) ? $saved['mega'] : array(),
			'footer'     => array_merge(
				array(
					'instagram'       => '',
					'telegram'        => '',
					'whatsapp'        => '',
					'aparat'          => '',
					'linkedin'        => '',
					'badges_html'     => '',
					'newsletter_show' => 1,
				),
				isset( $saved['footer'] ) && is_array( $saved['footer'] ) ? $saved['footer'] : array()
			),
		);
	}

	return $cache;
}

/** Hand-written mega-menu view for a pane key ('all' or 'cat-12'), merged over "all automatic". */
function stocksystem_mega_pane_settings( $key ) {
	$settings = stocksystem_nav_settings();
	$pane     = isset( $settings['mega'][ $key ] ) && is_array( $settings['mega'][ $key ] ) ? $settings['mega'][ $key ] : array();

	return array_merge(
		array(
			'brands_mode'   => 'auto',
			'brands'        => array(),
			'prices_mode'   => 'auto',
			'prices'        => array(),
			'feature_mode'  => 'auto',
			'feature_id'    => 0,
			'feature'       => array( 'eyebrow' => '', 'title' => '', 'text' => '', 'url' => '' ),
			'extra_title'   => '',
			'extra'         => array(),
		),
		$pane
	);
}

/** Rows of array( label, url ) → array( 'name'/'label', 'url' ) with relative paths resolved. */
function stocksystem_nav_rows( $rows ) {
	$out = array();

	foreach ( (array) $rows as $row ) {
		if ( empty( $row['label'] ) ) {
			continue;
		}
		$out[] = array(
			'label' => (string) $row['label'],
			'url'   => stocksystem_home_link( isset( $row['url'] ) ? $row['url'] : '', '#' ),
		);
	}

	return $out;
}

/**
 * Category overrides (show / label / order) applied to the list built by
 * stocksystem_nav_categories() — one place, so the nav bar, mega menu, mobile
 * menu, footer and homepage tiles always agree.
 */
function stocksystem_nav_apply_category_settings( $list ) {
	$overrides = stocksystem_nav_settings()['categories'];

	if ( empty( $overrides ) ) {
		return $list;
	}

	$rows = array();
	foreach ( array_values( $list ) as $index => $category ) {
		$cfg = ! empty( $category->id ) && isset( $overrides[ $category->id ] ) && is_array( $overrides[ $category->id ] ) ? $overrides[ $category->id ] : array();

		if ( isset( $cfg['show'] ) && ! $cfg['show'] ) {
			continue;
		}
		if ( ! empty( $cfg['label'] ) ) {
			$category->name = $cfg['label'];
		}

		$rows[] = array( 'order' => isset( $cfg['order'] ) && '' !== $cfg['order'] ? (int) $cfg['order'] : ( $index + 1 ) * 10, 'index' => $index, 'cat' => $category );
	}

	usort(
		$rows,
		function ( $a, $b ) {
			return array( $a['order'], $a['index'] ) <=> array( $b['order'], $b['index'] );
		}
	);

	return array_map(
		function ( $row ) {
			return $row['cat'];
		},
		$rows
	);
}

/* -------------------------------------------------------------------------
 * Sanitising
 * ---------------------------------------------------------------------- */

function stocksystem_nav_clean_url( $value ) {
	// Absolute URLs or site-relative paths («/repair/»); javascript: etc. are dropped.
	return esc_url_raw( trim( (string) $value ) );
}

function stocksystem_nav_clean_rows( $rows, $max = 40 ) {
	$out = array();

	foreach ( array_slice( is_array( $rows ) ? $rows : array(), 0, $max ) as $row ) {
		$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
		if ( '' === $label ) {
			continue;
		}
		$out[] = array(
			'label' => $label,
			'url'   => isset( $row['url'] ) ? stocksystem_nav_clean_url( $row['url'] ) : '',
		);
	}

	return $out;
}

function stocksystem_nav_sanitize( $input ) {
	$in  = is_array( $input ) ? wp_unslash( $input ) : array();
	$out = array( 'categories' => array(), 'prices' => array(), 'mega' => array(), 'footer' => array() );

	// Categories.
	foreach ( isset( $in['categories'] ) && is_array( $in['categories'] ) ? $in['categories'] : array() as $term_id => $cfg ) {
		$term_id = absint( $term_id );
		if ( ! $term_id || ! is_array( $cfg ) ) {
			continue;
		}
		$out['categories'][ $term_id ] = array(
			'show'  => empty( $cfg['show'] ) ? 0 : 1,
			'label' => isset( $cfg['label'] ) ? sanitize_text_field( $cfg['label'] ) : '',
			'order' => ( isset( $cfg['order'] ) && '' !== trim( (string) $cfg['order'] ) ) ? (string) absint( $cfg['order'] ) : '',
		);
	}

	// Price ranges.
	foreach ( array_slice( isset( $in['prices'] ) && is_array( $in['prices'] ) ? $in['prices'] : array(), 0, 12 ) as $row ) {
		$label = isset( $row['label'] ) ? sanitize_text_field( $row['label'] ) : '';
		if ( '' === $label ) {
			continue;
		}
		$min = isset( $row['min'] ) ? absint( str_replace( array( ',', '،', ' ' ), '', stocksystem_to_latin_digits( (string) $row['min'] ) ) ) : 0;
		$max = isset( $row['max'] ) ? trim( str_replace( array( ',', '،', ' ' ), '', stocksystem_to_latin_digits( (string) $row['max'] ) ) ) : '';
		$out['prices'][] = array( 'label' => $label, 'min' => $min, 'max' => '' === $max ? '' : absint( $max ) );
	}

	// Mega menu panes.
	$modes = array(
		'brands_mode'  => array( 'auto', 'manual', 'hide' ),
		'prices_mode'  => array( 'auto', 'manual', 'hide' ),
		'feature_mode' => array( 'auto', 'product', 'custom', 'hide' ),
	);
	foreach ( isset( $in['mega'] ) && is_array( $in['mega'] ) ? $in['mega'] : array() as $key => $pane ) {
		$key = sanitize_key( $key );
		if ( ! preg_match( '/^(all|cat-\d+)$/', $key ) || ! is_array( $pane ) ) {
			continue;
		}

		$clean = array();
		foreach ( $modes as $field => $allowed ) {
			$value           = isset( $pane[ $field ] ) ? sanitize_key( $pane[ $field ] ) : 'auto';
			$clean[ $field ] = in_array( $value, $allowed, true ) ? $value : 'auto';
		}
		$clean['brands']      = stocksystem_nav_clean_rows( isset( $pane['brands'] ) ? $pane['brands'] : array() );
		$clean['prices']      = stocksystem_nav_clean_rows( isset( $pane['prices'] ) ? $pane['prices'] : array() );
		$clean['feature_id']  = isset( $pane['feature_id'] ) ? absint( $pane['feature_id'] ) : 0;
		$clean['feature']     = array(
			'eyebrow' => isset( $pane['feature']['eyebrow'] ) ? sanitize_text_field( $pane['feature']['eyebrow'] ) : '',
			'title'   => isset( $pane['feature']['title'] ) ? sanitize_text_field( $pane['feature']['title'] ) : '',
			'text'    => isset( $pane['feature']['text'] ) ? sanitize_text_field( $pane['feature']['text'] ) : '',
			'url'     => isset( $pane['feature']['url'] ) ? stocksystem_nav_clean_url( $pane['feature']['url'] ) : '',
		);
		$clean['extra_title'] = isset( $pane['extra_title'] ) ? sanitize_text_field( $pane['extra_title'] ) : '';
		$clean['extra']       = stocksystem_nav_clean_rows( isset( $pane['extra'] ) ? $pane['extra'] : array() );

		// Store only panes that differ from "all automatic".
		$is_default = 'auto' === $clean['brands_mode'] && 'auto' === $clean['prices_mode'] && 'auto' === $clean['feature_mode']
			&& '' === $clean['extra_title'] && empty( $clean['extra'] ) && '' === $clean['feature']['eyebrow'];
		if ( ! $is_default ) {
			$out['mega'][ $key ] = $clean;
		}
	}

	// Footer.
	$footer = isset( $in['footer'] ) && is_array( $in['footer'] ) ? $in['footer'] : array();
	foreach ( array( 'instagram', 'telegram', 'whatsapp', 'aparat', 'linkedin' ) as $network ) {
		$out['footer'][ $network ] = isset( $footer[ $network ] ) ? stocksystem_nav_clean_url( $footer[ $network ] ) : '';
	}
	$badges = isset( $footer['badges_html'] ) ? (string) $footer['badges_html'] : '';
	$out['footer']['badges_html']     = current_user_can( 'unfiltered_html' ) ? $badges : wp_kses_post( $badges );
	$out['footer']['newsletter_show'] = empty( $footer['newsletter_show'] ) ? 0 : 1;

	return $out;
}

function stocksystem_nav_register_setting() {
	register_setting(
		'stocksystem_nav_group',
		'stocksystem_nav',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'stocksystem_nav_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'stocksystem_nav_register_setting' );

/** Menu data is cached briefly; an edit here must show at once. */
function stocksystem_nav_flush_caches() {
	delete_transient( 'stocksystem_mega_menu_data' );
}
add_action( 'update_option_stocksystem_nav', 'stocksystem_nav_flush_caches' );
add_action( 'add_option_stocksystem_nav', 'stocksystem_nav_flush_caches' );

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

function stocksystem_nav_admin_menu() {
	add_theme_page(
		__( 'منو و فوتر', 'stocksystem' ),
		__( 'منو و فوتر', 'stocksystem' ),
		'manage_options',
		'stocksystem-nav',
		'stocksystem_nav_render_page'
	);
}
add_action( 'admin_menu', 'stocksystem_nav_admin_menu' );

function stocksystem_nav_admin_assets( $hook ) {
	if ( 'appearance_page_stocksystem-nav' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'stocksystem-home-admin', STOCKSYSTEM_URI . '/assets/css/admin-home.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-nav-admin', STOCKSYSTEM_URI . '/assets/css/admin-nav.css', array( 'stocksystem-home-admin' ), STOCKSYSTEM_VERSION );
	wp_enqueue_script( 'stocksystem-nav-admin', STOCKSYSTEM_URI . '/assets/js/admin-nav.js', array(), STOCKSYSTEM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'stocksystem_nav_admin_assets' );

function stocksystem_nav_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_nav_reset' );
	delete_option( 'stocksystem_nav' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'stocksystem-nav', 'reset' => '1' ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_nav_reset', 'stocksystem_nav_handle_reset' );

/**
 * A repeatable list of label + link rows.
 *
 * @param string $name   Input name prefix, e.g. stocksystem_nav[mega][all][brands].
 * @param array  $rows   Saved rows (label, url).
 * @param array  $fields field => placeholder; first is the label.
 */
function stocksystem_nav_repeater( $name, $rows, $fields = null ) {
	if ( null === $fields ) {
		$fields = array(
			'label' => __( 'نوشته', 'stocksystem' ),
			'url'   => __( 'لینک (آدرس کامل یا مسیر مثل /shop/)', 'stocksystem' ),
		);
	}

	$row_html = function ( $index, $row ) use ( $name, $fields ) {
		?>
		<div class="ss-rep__row">
			<?php foreach ( $fields as $field => $placeholder ) : ?>
				<input type="<?php echo 'url' === $field ? 'text' : 'text'; ?>" name="<?php echo esc_attr( $name . '[' . $index . '][' . $field . ']' ); ?>" value="<?php echo esc_attr( isset( $row[ $field ] ) ? $row[ $field ] : '' ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php echo 'url' === $field ? ' dir="ltr" class="ss-rep__url"' : ''; ?>>
			<?php endforeach; ?>
			<span class="ss-rep__tools">
				<button type="button" class="button-link ss-rep__up" aria-label="<?php esc_attr_e( 'بالاتر', 'stocksystem' ); ?>">↑</button>
				<button type="button" class="button-link ss-rep__down" aria-label="<?php esc_attr_e( 'پایین‌تر', 'stocksystem' ); ?>">↓</button>
				<button type="button" class="button-link button-link-delete ss-rep__remove"><?php esc_html_e( 'حذف', 'stocksystem' ); ?></button>
			</span>
		</div>
		<?php
	};
	?>
	<div class="ss-rep" data-name="<?php echo esc_attr( $name ); ?>">
		<div class="ss-rep__rows">
			<?php foreach ( array_values( (array) $rows ) as $i => $row ) { $row_html( $i, $row ); } ?>
		</div>
		<template class="ss-rep__tpl"><?php $row_html( '__i__', array() ); ?></template>
		<button type="button" class="button ss-rep__add"><?php esc_html_e( '+ افزودن', 'stocksystem' ); ?></button>
	</div>
	<?php
}

function stocksystem_nav_mode_select( $name, $value, $options ) {
	?>
	<select name="<?php echo esc_attr( $name ); ?>" class="ss-mode">
		<?php foreach ( $options as $key => $label ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $value, $key ); ?>><?php echo esc_html( $label ); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
}

function stocksystem_nav_render_pane( $key, $title, $products ) {
	$pane = stocksystem_mega_pane_settings( $key );
	$base = 'stocksystem_nav[mega][' . $key . ']';
	?>
	<details class="ss-pane">
		<summary><?php echo esc_html( $title ); ?></summary>
		<div class="ss-pane__body">

			<div class="ss-pane__block" data-mode-scope>
				<h4><?php esc_html_e( 'ستون «برند»', 'stocksystem' ); ?></h4>
				<?php
				stocksystem_nav_mode_select(
					$base . '[brands_mode]',
					$pane['brands_mode'],
					array(
						'auto'   => __( 'خودکار — برندهای دارای کالا', 'stocksystem' ),
						'manual' => __( 'دستی — لینک‌های خودم', 'stocksystem' ),
						'hide'   => __( 'نمایش داده نشود', 'stocksystem' ),
					)
				);
				?>
				<div class="ss-mode-panel" data-mode="manual"><?php stocksystem_nav_repeater( $base . '[brands]', $pane['brands'] ); ?></div>
			</div>

			<div class="ss-pane__block" data-mode-scope>
				<h4><?php esc_html_e( 'ستون «بازهٔ قیمت»', 'stocksystem' ); ?></h4>
				<?php
				stocksystem_nav_mode_select(
					$base . '[prices_mode]',
					$pane['prices_mode'],
					array(
						'auto'   => __( 'خودکار — بازه‌های قیمت بخش پایین همین صفحه', 'stocksystem' ),
						'manual' => __( 'دستی — لینک‌های خودم', 'stocksystem' ),
						'hide'   => __( 'نمایش داده نشود', 'stocksystem' ),
					)
				);
				?>
				<div class="ss-mode-panel" data-mode="manual"><?php stocksystem_nav_repeater( $base . '[prices]', $pane['prices'] ); ?></div>
			</div>

			<div class="ss-pane__block" data-mode-scope>
				<h4><?php esc_html_e( 'کارت پیشنهاد (سمت چپ)', 'stocksystem' ); ?></h4>
				<?php
				stocksystem_nav_mode_select(
					$base . '[feature_mode]',
					$pane['feature_mode'],
					array(
						'auto'    => __( 'خودکار — کالای ویژه/تخفیف‌دار همین دسته', 'stocksystem' ),
						'product' => __( 'یک کالای مشخص', 'stocksystem' ),
						'custom'  => __( 'متن و لینک دلخواه (مثلاً کمپین)', 'stocksystem' ),
						'hide'    => __( 'نمایش داده نشود', 'stocksystem' ),
					)
				);
				?>
				<div class="ss-grid ss-grid--tight">
					<div class="ss-field"><label><span class="ss-field__label"><?php esc_html_e( 'برچسب کوچک بالای کارت (خالی = «پیشنهاد هفته»)', 'stocksystem' ); ?></span>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[feature][eyebrow]" value="<?php echo esc_attr( $pane['feature']['eyebrow'] ); ?>"></label></div>
					<div class="ss-field ss-mode-panel" data-mode="product"><label><span class="ss-field__label"><?php esc_html_e( 'کالا', 'stocksystem' ); ?></span>
						<select name="<?php echo esc_attr( $base ); ?>[feature_id]">
							<option value="0">—</option>
							<?php foreach ( $products as $product ) : ?>
								<option value="<?php echo esc_attr( $product->get_id() ); ?>" <?php selected( (int) $pane['feature_id'], $product->get_id() ); ?>><?php echo esc_html( $product->get_name() ); ?></option>
							<?php endforeach; ?>
						</select></label></div>
					<div class="ss-field ss-mode-panel" data-mode="custom"><label><span class="ss-field__label"><?php esc_html_e( 'عنوان', 'stocksystem' ); ?></span>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[feature][title]" value="<?php echo esc_attr( $pane['feature']['title'] ); ?>"></label></div>
					<div class="ss-field ss-mode-panel" data-mode="custom"><label><span class="ss-field__label"><?php esc_html_e( 'متن زیر عنوان (مثلاً قیمت یا شعار)', 'stocksystem' ); ?></span>
						<input type="text" name="<?php echo esc_attr( $base ); ?>[feature][text]" value="<?php echo esc_attr( $pane['feature']['text'] ); ?>"></label></div>
					<div class="ss-field ss-mode-panel" data-mode="custom"><label><span class="ss-field__label"><?php esc_html_e( 'لینک', 'stocksystem' ); ?></span>
						<input type="text" dir="ltr" name="<?php echo esc_attr( $base ); ?>[feature][url]" value="<?php echo esc_attr( $pane['feature']['url'] ); ?>"></label></div>
				</div>
			</div>

			<div class="ss-pane__block">
				<h4><?php esc_html_e( 'ستون اضافه (اختیاری)', 'stocksystem' ); ?></h4>
				<p class="description"><?php esc_html_e( 'یک ستون لینک دلخواه کنار بقیه؛ مثلاً «راهنمای خرید»، «تازه‌ها» یا «حراج». عنوان را خالی بگذارید تا نمایش داده نشود.', 'stocksystem' ); ?></p>
				<div class="ss-field"><label><span class="ss-field__label"><?php esc_html_e( 'عنوان ستون', 'stocksystem' ); ?></span>
					<input type="text" name="<?php echo esc_attr( $base ); ?>[extra_title]" value="<?php echo esc_attr( $pane['extra_title'] ); ?>"></label></div>
				<?php stocksystem_nav_repeater( $base . '[extra]', $pane['extra'] ); ?>
			</div>
		</div>
	</details>
	<?php
}

function stocksystem_nav_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings  = stocksystem_nav_settings();
	$products  = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => 300, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
	$all_terms = array();

	if ( taxonomy_exists( 'product_cat' ) ) {
		// Same rule as stocksystem_nav_categories_build(): the default "Uncategorized"
		// bucket is identified by its term id, not its (locale-dependent) slug.
		$default_id = (int) get_option( 'default_product_cat' );
		$exclude    = $default_id ? array( $default_id ) : array();
		$terms   = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => false, 'exclude' => $exclude, 'menu_order' => 'ASC' ) );
		$all_terms = is_wp_error( $terms ) ? array() : $terms;
	}

	$default_ranges = stocksystem_price_ranges_default();
	$price_rows     = array();
	foreach ( ! empty( $settings['prices'] ) ? $settings['prices'] : $default_ranges as $range ) {
		$price_rows[] = array(
			'label' => $range['label'],
			'min'   => $range['min'],
			'max'   => null === $range['max'] ? '' : $range['max'],
		);
	}
	?>
	<div class="wrap ss-home ss-navpage">
		<h1><?php esc_html_e( 'منو و فوتر', 'stocksystem' ); ?></h1>
		<p class="description"><?php esc_html_e( 'همه‌چیز پیش‌فرض خودکار است (از دسته‌ها و برندهای ووکامرس)؛ فقط هرجا خواستید دستی‌اش کنید.', 'stocksystem' ); ?></p>

		<?php if ( isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'منو و فوتر به حالت خودکار برگشتند.', 'stocksystem' ); ?></p></div>
		<?php endif; ?>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'stocksystem_nav_group' ); ?>

			<section class="ss-card">
				<div class="ss-card__head"><h2><?php esc_html_e( '۱) دسته‌ها', 'stocksystem' ); ?></h2></div>
				<p class="description"><?php esc_html_e( 'هر تغییر اینجا همزمان در نوار اصلی، مگامنو، منوی موبایل، فوتر و بخش دسته‌های صفحهٔ اصلی دیده می‌شود. نام اصلی دسته در ووکامرس عوض نمی‌شود. دسته‌های جدید را از «محصولات ← دسته‌ها» بسازید.', 'stocksystem' ); ?></p>
				<table class="ss-cats">
					<thead><tr><th><?php esc_html_e( 'دستهٔ ووکامرس', 'stocksystem' ); ?></th><th><?php esc_html_e( 'نمایش', 'stocksystem' ); ?></th><th><?php esc_html_e( 'نام در منو (خالی = همان نام)', 'stocksystem' ); ?></th><th><?php esc_html_e( 'ترتیب', 'stocksystem' ); ?></th></tr></thead>
					<tbody>
					<?php foreach ( $all_terms as $index => $term ) : ?>
						<?php
						$cfg   = isset( $settings['categories'][ $term->term_id ] ) ? $settings['categories'][ $term->term_id ] : array();
						$shown = ! isset( $cfg['show'] ) || $cfg['show'];
						$name  = 'stocksystem_nav[categories][' . $term->term_id . ']';
						?>
						<tr>
							<td><strong><?php echo esc_html( $term->name ); ?></strong></td>
							<td>
								<input type="hidden" name="<?php echo esc_attr( $name ); ?>[show]" value="0">
								<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[show]" value="1" <?php checked( $shown ); ?>>
							</td>
							<td><input type="text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( isset( $cfg['label'] ) ? $cfg['label'] : '' ); ?>" placeholder="<?php echo esc_attr( $term->name ); ?>"></td>
							<td><input type="number" min="1" max="999" class="small-text" name="<?php echo esc_attr( $name ); ?>[order]" value="<?php echo esc_attr( isset( $cfg['order'] ) ? $cfg['order'] : '' ); ?>" placeholder="<?php echo esc_attr( ( $index + 1 ) * 10 ); ?>"></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description"><?php esc_html_e( 'ترتیب: عدد کوچک‌تر بالاتر (یا راست‌تر) نمایش داده می‌شود. خالی = ترتیب دسته‌ها در ووکامرس.', 'stocksystem' ); ?></p>
			</section>

			<section class="ss-card">
				<div class="ss-card__head"><h2><?php esc_html_e( '۲) بازه‌های قیمت', 'stocksystem' ); ?></h2></div>
				<p class="description"><?php esc_html_e( 'برای ستون «بازهٔ قیمت» مگامنو و بخش «با بودجه شروع کنید» در صفحهٔ فروشگاه. مبلغ‌ها به تومان. «تا» را برای بازهٔ باز (مثلاً «بالای ۴۰ میلیون») خالی بگذارید. همه را که پاک کنید، بازه‌های پیش‌فرض برمی‌گردند.', 'stocksystem' ); ?></p>
				<?php
				stocksystem_nav_repeater(
					'stocksystem_nav[prices]',
					$price_rows,
					array(
						'label' => __( 'نوشته، مثل «تا ۱۵ میلیون»', 'stocksystem' ),
						'min'   => __( 'از (تومان)', 'stocksystem' ),
						'max'   => __( 'تا (تومان)', 'stocksystem' ),
					)
				);
				?>
			</section>

			<section class="ss-card">
				<div class="ss-card__head"><h2><?php esc_html_e( '۳) مگامنو', 'stocksystem' ); ?></h2></div>
				<p class="description"><?php esc_html_e( 'با حرکت موس روی «همهٔ دسته‌ها»، سمت راست فهرست دسته‌هاست و سمت چپ محتوای دستهٔ زیر موس. برای «همه» و هر دسته جدا تنظیم کنید که هر ستون خودکار باشد، دستی نوشته شود، یا نمایش داده نشود.', 'stocksystem' ); ?></p>
				<?php
				stocksystem_nav_render_pane( 'all', __( 'نمای پیش‌فرض («همه») — وقتی هنوز روی دسته‌ای نرفته‌اند', 'stocksystem' ), $products );
				foreach ( $all_terms as $term ) {
					stocksystem_nav_render_pane( 'cat-' . $term->term_id, sprintf( /* translators: %s: category name */ __( 'دستهٔ «%s»', 'stocksystem' ), $term->name ), $products );
				}
				?>
			</section>

			<section class="ss-card">
				<div class="ss-card__head"><h2><?php esc_html_e( '۴) فوتر', 'stocksystem' ); ?></h2></div>
				<p class="description"><?php esc_html_e( 'لینک‌های ستون «خدمات و راهنما» از «نمایش ← فهرست‌ها» (جایگاه «فوتر») و متن‌ها از «نمایش ← متن‌های سایت» عوض می‌شوند؛ اینجا شبکه‌های اجتماعی، نمادها و خبرنامه.', 'stocksystem' ); ?></p>
				<div class="ss-grid">
					<?php
					$socials = array(
						'instagram' => __( 'اینستاگرام', 'stocksystem' ),
						'telegram'  => __( 'تلگرام', 'stocksystem' ),
						'whatsapp'  => __( 'واتساپ', 'stocksystem' ),
						'aparat'    => __( 'آپارات', 'stocksystem' ),
						'linkedin'  => __( 'لینکدین', 'stocksystem' ),
					);
					foreach ( $socials as $key => $label ) :
						?>
						<div class="ss-field"><label><span class="ss-field__label"><?php echo esc_html( $label ); ?></span>
							<input type="text" dir="ltr" name="stocksystem_nav[footer][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $settings['footer'][ $key ] ); ?>" placeholder="https://"></label>
							<?php if ( 'whatsapp' === $key ) : ?><span class="ss-field__help"><?php esc_html_e( 'مثل https://wa.me/989034535025', 'stocksystem' ); ?></span><?php endif; ?>
						</div>
					<?php endforeach; ?>
					<div class="ss-field ss-field--wide"><label><span class="ss-field__label"><?php esc_html_e( 'نمادها و مجوزها (کد HTML)', 'stocksystem' ); ?></span>
						<textarea rows="4" dir="ltr" name="stocksystem_nav[footer][badges_html]" placeholder="&lt;a href=…&gt;&lt;img …&gt;&lt;/a&gt;"><?php echo esc_textarea( $settings['footer']['badges_html'] ); ?></textarea></label>
						<span class="ss-field__help"><?php esc_html_e( 'کدی که اینماد، ساماندهی، زرین‌پال و... به شما می‌دهند را همان‌طور اینجا بگذارید؛ کنار حق کپی در پایین فوتر نمایش داده می‌شود.', 'stocksystem' ); ?></span>
					</div>
					<div class="ss-field">
						<input type="hidden" name="stocksystem_nav[footer][newsletter_show]" value="0">
						<label><input type="checkbox" name="stocksystem_nav[footer][newsletter_show]" value="1" <?php checked( ! empty( $settings['footer']['newsletter_show'] ) ); ?>> <?php esc_html_e( 'کادر خبرنامه در فوتر نمایش داده شود', 'stocksystem' ); ?></label>
						<span class="ss-field__help"><a href="<?php echo esc_url( admin_url( 'users.php?page=stocksystem-newsletter' ) ); ?>"><?php esc_html_e( 'دیدن ایمیل‌های ثبت‌شده و دریافت فایل', 'stocksystem' ); ?></a></span>
					</div>
				</div>
			</section>

			<p class="submit">
				<?php submit_button( __( 'ذخیرهٔ تغییرات', 'stocksystem' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'دیدن سایت', 'stocksystem' ); ?></a>
			</p>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ss-reset" onsubmit="return confirm('<?php echo esc_js( __( 'منو، مگامنو و فوتر به حالت خودکار برگردند؟', 'stocksystem' ) ); ?>');">
			<input type="hidden" name="action" value="stocksystem_nav_reset">
			<?php wp_nonce_field( 'stocksystem_nav_reset' ); ?>
			<button type="submit" class="button-link button-link-delete"><?php esc_html_e( 'بازگشت همه‌چیز به حالت خودکار', 'stocksystem' ); ?></button>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Newsletter — the footer form used to post nowhere. Emails are stored (deduped)
 * and listed under Users → «خبرنامه», with a CSV download.
 * ---------------------------------------------------------------------- */

function stocksystem_newsletter_handle() {
	$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$redirect = remove_query_arg( 'newsletter', $redirect );

	if ( ! isset( $_POST['stocksystem_newsletter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_newsletter_nonce'] ) ), 'stocksystem_newsletter' ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'error', $redirect ) . '#footer-newsletter' );
		exit;
	}

	// Honeypot: real visitors never fill the hidden field.
	if ( ! empty( $_POST['website'] ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect ) . '#footer-newsletter' );
		exit;
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'newsletter', 'invalid', $redirect ) . '#footer-newsletter' );
		exit;
	}

	$list = get_option( 'stocksystem_newsletter', array() );
	$list = is_array( $list ) ? $list : array();

	if ( ! isset( $list[ strtolower( $email ) ] ) && count( $list ) < 20000 ) {
		$list[ strtolower( $email ) ] = time();
		update_option( 'stocksystem_newsletter', $list, false );
	}

	wp_safe_redirect( add_query_arg( 'newsletter', 'ok', $redirect ) . '#footer-newsletter' );
	exit;
}
add_action( 'admin_post_nopriv_stocksystem_newsletter', 'stocksystem_newsletter_handle' );
add_action( 'admin_post_stocksystem_newsletter', 'stocksystem_newsletter_handle' );

function stocksystem_newsletter_menu() {
	add_users_page(
		__( 'خبرنامه', 'stocksystem' ),
		__( 'خبرنامه', 'stocksystem' ),
		'manage_options',
		'stocksystem-newsletter',
		'stocksystem_newsletter_page'
	);
}
add_action( 'admin_menu', 'stocksystem_newsletter_menu' );

function stocksystem_newsletter_export() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_newsletter_export' );

	$list = get_option( 'stocksystem_newsletter', array() );
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=newsletter-' . gmdate( 'Y-m-d' ) . '.csv' );
	echo "\xEF\xBB\xBF" . "email,date\n"; // BOM so Excel reads UTF-8.
	foreach ( (array) $list as $email => $time ) {
		echo esc_html( $email ) . ',' . esc_html( gmdate( 'Y-m-d', (int) $time ) ) . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	exit;
}
add_action( 'admin_post_stocksystem_newsletter_export', 'stocksystem_newsletter_export' );

function stocksystem_newsletter_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_newsletter_delete' );

	$email = isset( $_GET['email'] ) ? strtolower( sanitize_email( wp_unslash( $_GET['email'] ) ) ) : '';
	$list  = get_option( 'stocksystem_newsletter', array() );
	if ( $email && isset( $list[ $email ] ) ) {
		unset( $list[ $email ] );
		update_option( 'stocksystem_newsletter', $list, false );
	}

	wp_safe_redirect( add_query_arg( array( 'page' => 'stocksystem-newsletter' ), admin_url( 'users.php' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_newsletter_delete', 'stocksystem_newsletter_delete' );

function stocksystem_newsletter_page() {
	$list = get_option( 'stocksystem_newsletter', array() );
	$list = is_array( $list ) ? array_reverse( $list, true ) : array();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'خبرنامه', 'stocksystem' ); ?></h1>
		<p><?php echo esc_html( sprintf( /* translators: %s: count */ __( '%s ایمیل ثبت شده است.', 'stocksystem' ), stocksystem_to_persian_digits( count( $list ) ) ) ); ?>
			<?php if ( $list ) : ?>
				<a class="button" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=stocksystem_newsletter_export' ), 'stocksystem_newsletter_export' ) ); ?>"><?php esc_html_e( 'دریافت فایل CSV', 'stocksystem' ); ?></a>
			<?php endif; ?>
		</p>
		<?php if ( $list ) : ?>
			<table class="widefat striped" style="max-width:640px">
				<thead><tr><th><?php esc_html_e( 'ایمیل', 'stocksystem' ); ?></th><th><?php esc_html_e( 'تاریخ', 'stocksystem' ); ?></th><th></th></tr></thead>
				<tbody>
				<?php foreach ( array_slice( $list, 0, 500, true ) as $email => $time ) : ?>
					<tr>
						<td dir="ltr" style="text-align:left"><?php echo esc_html( $email ); ?></td>
						<td><?php echo esc_html( stocksystem_jdate( 'j F Y', (int) $time ) ); ?></td>
						<td><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=stocksystem_newsletter_delete&email=' . rawurlencode( $email ) ), 'stocksystem_newsletter_delete' ) ); ?>"><?php esc_html_e( 'حذف', 'stocksystem' ); ?></a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Mega menu rendering — combines the automatic data with the hand-written view
 * ---------------------------------------------------------------------- */

/**
 * Final arguments for one mega-menu pane.
 *
 * @param string $key  'all' or 'cat-<id>'.
 * @param array  $auto brands (array of name/url/count), prices (array of label/url),
 *                     featured (WC_Product|null), all_url, base_url, hidden.
 * @return array key, brands|null, prices|null, feature|null, extra|null, all_url, hidden.
 */
function stocksystem_mega_pane_args( $key, $auto ) {
	$cfg = stocksystem_mega_pane_settings( $key );

	// Brands.
	if ( 'hide' === $cfg['brands_mode'] ) {
		$brands = null;
	} elseif ( 'manual' === $cfg['brands_mode'] ) {
		$brands = array();
		foreach ( stocksystem_nav_rows( $cfg['brands'] ) as $row ) {
			$brands[] = array( 'name' => $row['label'], 'url' => $row['url'] );
		}
	} else {
		$brands = $auto['brands'];
	}

	// Price links.
	if ( 'hide' === $cfg['prices_mode'] ) {
		$prices = null;
	} elseif ( 'manual' === $cfg['prices_mode'] ) {
		$prices = stocksystem_nav_rows( $cfg['prices'] );
	} else {
		$prices = $auto['prices'];
	}

	// Highlight tile.
	$feature = null;
	$product = null;

	if ( 'product' === $cfg['feature_mode'] && $cfg['feature_id'] && function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product( $cfg['feature_id'] );
		$product = ( $product && 'publish' === $product->get_status() ) ? $product : null;
	}
	if ( ! $product && ( 'auto' === $cfg['feature_mode'] || 'product' === $cfg['feature_mode'] ) ) {
		$product = $auto['featured'];
	}

	if ( $product && 'hide' !== $cfg['feature_mode'] && 'custom' !== $cfg['feature_mode'] ) {
		$feature = array(
			'eyebrow' => '',
			'title'   => $product->get_name(),
			'text'    => stocksystem_format_number( $product->get_price() ) . ' ' . __( 'تومان', 'stocksystem' ),
			'url'     => $product->get_permalink(),
		);
	} elseif ( 'custom' === $cfg['feature_mode'] && '' !== $cfg['feature']['title'] ) {
		$feature = array(
			'eyebrow' => '',
			'title'   => $cfg['feature']['title'],
			'text'    => $cfg['feature']['text'],
			'url'     => stocksystem_home_link( $cfg['feature']['url'], '#' ),
		);
	}

	if ( $feature ) {
		$feature['eyebrow'] = '' !== $cfg['feature']['eyebrow'] ? $cfg['feature']['eyebrow'] : __( 'پیشنهاد هفته', 'stocksystem' );
	}

	// Extra column.
	$extra      = null;
	$extra_rows = stocksystem_nav_rows( $cfg['extra'] );
	if ( '' !== $cfg['extra_title'] && $extra_rows ) {
		$extra = array( 'title' => $cfg['extra_title'], 'links' => $extra_rows );
	}

	return array(
		'key'     => $key,
		'brands'  => $brands,
		'prices'  => $prices,
		'feature' => $feature,
		'extra'   => $extra,
		'all_url' => $auto['all_url'],
		'hidden'  => $auto['hidden'],
	);
}
