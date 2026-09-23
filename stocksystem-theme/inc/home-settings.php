<?php
/**
 * Homepage content settings — every text, link, image and section switch on
 * the front page, editable from Appearance → «صفحهٔ اصلی» (no code).
 *
 * One option (`stocksystem_home`) holds everything. Defaults below are the
 * copy that used to be hard-coded in template-parts/home/*.php, so a fresh
 * install (or an emptied field) renders exactly what the design shows.
 * Rule: an empty text field falls back to its default; checkboxes and
 * numbers are always saved explicitly.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Defaults, icons, getter
 * ---------------------------------------------------------------------- */

function stocksystem_home_defaults() {
	return array(
		'hero'       => array(
			'eyebrow'    => '', // empty = «استوک تست‌شده · <متن گارانتی>».
			'title'      => 'لپ‌تاپ و کامپیوتر حرفه‌ای، با قیمتی که منطقی است',
			'desc'       => 'هر دستگاه پیش از فروش تست سخت‌افزاری کامل می‌شود و برگهٔ وضعیت دارد: سلامت باتری، ساعت کارکرد و وضعیت بدنه — بدون ابهام.',
			'cta1_label' => 'مشاهدهٔ لپ‌تاپ‌ها',
			'cta1_url'   => '', // empty = first product category.
			'cta2_label' => 'وضعیت کالای استوک چیست؟',
			'cta2_url'   => '', // empty = /stock-condition/.
			'pillars'    => array( 'اعتماد', 'کیفیت', 'تکنولوژی' ),
			'images'     => array( 0, 0, 0 ), // attachment ids; empty = latest products of the first category.
		),
		'categories' => array(
			'show'       => 1,
			'order'      => 1,
			'heading'    => 'دسته‌بندی خدمات و محصولات',
			'link_label' => 'مشاهدهٔ همه ←',
			'link_url'   => '',
			'ids'        => array(), // empty = all top-level categories.
		),
		'featured'   => array(
			'show'        => 1,
			'order'       => 2,
			'heading'     => 'پیشنهاد این هفته',
			'link_label'  => 'همهٔ تخفیف‌ها ←',
			'link_url'    => '',
			'source'      => 'on_sale',
			'count'       => 4,
			'product_ids' => array(),
		),
		'trust'      => array(
			'show'  => 1,
			'order' => 3,
			'items' => array(
				array( 'enabled' => 1, 'icon' => 'shield', 'title' => '', 'desc' => 'قطعات اصلی و تعمیر در فروشگاه' ), // empty title = warranty text.
				array( 'enabled' => 1, 'icon' => 'sheet', 'title' => 'برگهٔ تست هر دستگاه', 'desc' => 'سلامت باتری، ساعت کارکرد، وضعیت بدنه' ),
				array( 'enabled' => 1, 'icon' => 'truck', 'title' => 'ارسال سریع', 'desc' => 'تحویل حضوری رایگان در نیشابور · ارسال با پست به سراسر کشور' ),
				array( 'enabled' => 1, 'icon' => 'store', 'title' => 'فروشگاه فیزیکی', 'desc' => '' ), // empty desc = store address.
			),
		),
		'blog'       => array(
			'show'        => 1,
			'order'       => 4,
			'heading'     => 'راهنمای خرید و نگهداری',
			'link_label'  => 'همهٔ مقالات ←',
			'count'       => 3,
			'category_id' => 0,
		),
	);
}

/** Inner SVG markup (24×24, stroke icons) for the trust-band icon picker. */
function stocksystem_home_icons() {
	return array(
		'shield' => array( 'label' => 'سپر (گارانتی)', 'svg' => '<path d="M12 3l8 3v6c0 4.5-3.2 7.8-8 9-4.8-1.2-8-4.5-8-9V6z"></path><path d="M9 12l2.2 2.2L15.5 10"></path>' ),
		'sheet'  => array( 'label' => 'برگه (گزارش تست)', 'svg' => '<rect x="3" y="4" width="18" height="14" rx="2"></rect><path d="M7 9h6M7 13h4"></path>' ),
		'truck'  => array( 'label' => 'کامیون (ارسال)', 'svg' => '<path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"></path><circle cx="7" cy="18" r="1.6"></circle><circle cx="17" cy="18" r="1.6"></circle>' ),
		'store'  => array( 'label' => 'فروشگاه', 'svg' => '<path d="M20 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7"></path><path d="M4 7l8-4 8 4"></path><path d="M10 19v-5h4v5"></path>' ),
		'phone'  => array( 'label' => 'تلفن (پشتیبانی)', 'svg' => '<path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path>' ),
		'wrench' => array( 'label' => 'آچار (تعمیر)', 'svg' => '<path d="M14.5 6.5a4 4 0 0 0-5.3 5.3L3.5 17.5l3 3 5.7-5.7a4 4 0 0 0 5.3-5.3l-2.5 2.5-2.5-.5-.5-2.5z"></path>' ),
		'clock'  => array( 'label' => 'ساعت (سرعت)', 'svg' => '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>' ),
		'return' => array( 'label' => 'بازگشت کالا', 'svg' => '<path d="M9 7L4 12l5 5"></path><path d="M4 12h11a5 5 0 0 1 0 10h-2"></path>' ),
	);
}

function stocksystem_home_merge( $defaults, $saved ) {
	if ( ! is_array( $saved ) ) {
		return $defaults;
	}

	foreach ( $defaults as $key => $default ) {
		if ( ! array_key_exists( $key, $saved ) ) {
			continue;
		}
		$value = $saved[ $key ];

		if ( is_string( $default ) ) {
			// Empty text falls back to the shipped copy.
			if ( is_scalar( $value ) && '' !== trim( (string) $value ) ) {
				$defaults[ $key ] = (string) $value;
			}
		} elseif ( is_int( $default ) ) {
			if ( is_scalar( $value ) ) {
				$defaults[ $key ] = (int) $value;
			}
		} elseif ( is_array( $default ) && is_array( $value ) ) {
			if ( 'pillars' === $key ) {
				// Once saved, the list is the admin's own: a cleared box removes that word.
				$defaults[ $key ] = array_values( array_filter( array_map( 'strval', $value ), 'strlen' ) );
			} elseif ( empty( $default ) ) {
				$defaults[ $key ] = $value; // id lists.
			} else {
				// Keyed groups and fixed-length lists both merge index by index.
				$defaults[ $key ] = stocksystem_home_merge( $default, $value );
			}
		}
	}

	return $defaults;
}

/**
 * Settings for the front page, merged over the defaults.
 *
 * @param string|null $section hero|categories|featured|trust|blog, or null for all.
 */
function stocksystem_home( $section = null ) {
	static $cache = null;

	if ( null === $cache ) {
		$cache = stocksystem_home_merge( stocksystem_home_defaults(), stocksystem_get_option( 'stocksystem_home' ) );
	}

	if ( null === $section ) {
		return $cache;
	}

	return isset( $cache[ $section ] ) ? $cache[ $section ] : array();
}

/** A link saved in the admin, resolved against this site when it is a relative path. */
function stocksystem_home_link( $url, $fallback = '' ) {
	$url = (string) $url;

	if ( '' === $url ) {
		return $fallback;
	}

	return ( 0 === strpos( $url, '/' ) && 0 !== strpos( $url, '//' ) ) ? home_url( $url ) : $url;
}

/** Order in which the movable sections render (hero is always first). */
function stocksystem_home_section_order() {
	$sections = array();
	foreach ( array( 'categories', 'featured', 'trust', 'blog' ) as $slug ) {
		$cfg = stocksystem_home( $slug );
		if ( ! empty( $cfg['show'] ) ) {
			$sections[ $slug ] = (int) $cfg['order'];
		}
	}

	// Optional extra sections (استوک سیستم ← بخش‌های تازهٔ صفحهٔ اصلی) share the same ordering.
	$extra = stocksystem_opt( 'homex' );
	foreach ( array( 'banner' => 'banner', 'testi' => 'testimonials', 'brands' => 'brands', 'count' => 'counters' ) as $prefix => $slug ) {
		if ( ! empty( $extra[ $prefix . '_on' ] ) ) {
			$sections[ $slug ] = (int) $extra[ $prefix . '_order' ];
		}
	}

	asort( $sections );

	return array_keys( $sections );
}

/* -------------------------------------------------------------------------
 * Sanitising
 * ---------------------------------------------------------------------- */

function stocksystem_home_sanitize( $input ) {
	$in  = is_array( $input ) ? wp_unslash( $input ) : array();
	$def = stocksystem_home_defaults();
	$out = array();

	$text = function ( $value ) {
		return sanitize_text_field( (string) $value );
	};
	$url  = function ( $value ) {
		// Absolute URLs or site-relative paths like «/repair/» (kept relative so a
		// domain change never breaks them); javascript: and friends are dropped.
		return esc_url_raw( trim( (string) $value ) );
	};
	$ints = function ( $values ) {
		return array_values( array_filter( array_map( 'absint', (array) $values ) ) );
	};
	$get  = function ( $section, $key, $fallback = '' ) use ( $in ) {
		return isset( $in[ $section ][ $key ] ) ? $in[ $section ][ $key ] : $fallback;
	};

	// Hero.
	$pillars = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$pillars[] = $text( isset( $in['hero']['pillars'][ $i ] ) ? $in['hero']['pillars'][ $i ] : '' );
	}
	$images = array();
	for ( $i = 0; $i < 3; $i++ ) {
		$id       = isset( $in['hero']['images'][ $i ] ) ? absint( $in['hero']['images'][ $i ] ) : 0;
		$images[] = ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
	}
	$out['hero'] = array(
		'eyebrow'    => $text( $get( 'hero', 'eyebrow' ) ),
		'title'      => $text( $get( 'hero', 'title' ) ),
		'desc'       => sanitize_textarea_field( (string) $get( 'hero', 'desc' ) ),
		'cta1_label' => $text( $get( 'hero', 'cta1_label' ) ),
		'cta1_url'   => $url( $get( 'hero', 'cta1_url' ) ),
		'cta2_label' => $text( $get( 'hero', 'cta2_label' ) ),
		'cta2_url'   => $url( $get( 'hero', 'cta2_url' ) ),
		'pillars'    => $pillars,
		'images'     => $images,
	);

	$order = function ( $section ) use ( $get, $def ) {
		return max( 1, min( 9, absint( $get( $section, 'order', $def[ $section ]['order'] ) ) ) );
	};

	// Categories.
	$out['categories'] = array(
		'show'       => empty( $in['categories']['show'] ) ? 0 : 1,
		'order'      => $order( 'categories' ),
		'heading'    => $text( $get( 'categories', 'heading' ) ),
		'link_label' => $text( $get( 'categories', 'link_label' ) ),
		'link_url'   => $url( $get( 'categories', 'link_url' ) ),
		'ids'        => $ints( $get( 'categories', 'ids', array() ) ),
	);

	// Featured products.
	$sources = array( 'on_sale', 'newest', 'best_selling', 'featured', 'manual' );
	$source  = $text( $get( 'featured', 'source', 'on_sale' ) );
	$count   = absint( $get( 'featured', 'count', 4 ) );
	$out['featured'] = array(
		'show'        => empty( $in['featured']['show'] ) ? 0 : 1,
		'order'       => $order( 'featured' ),
		'heading'     => $text( $get( 'featured', 'heading' ) ),
		'link_label'  => $text( $get( 'featured', 'link_label' ) ),
		'link_url'    => $url( $get( 'featured', 'link_url' ) ),
		'source'      => in_array( $source, $sources, true ) ? $source : 'on_sale',
		'count'       => in_array( $count, array( 4, 8, 12 ), true ) ? $count : 4,
		'product_ids' => $ints( $get( 'featured', 'product_ids', array() ) ),
	);

	// Trust band.
	$icons = stocksystem_home_icons();
	$items = array();
	for ( $i = 0; $i < 4; $i++ ) {
		$raw     = isset( $in['trust']['items'][ $i ] ) ? $in['trust']['items'][ $i ] : array();
		$icon    = isset( $raw['icon'] ) ? sanitize_key( $raw['icon'] ) : $def['trust']['items'][ $i ]['icon'];
		$items[] = array(
			'enabled' => empty( $raw['enabled'] ) ? 0 : 1,
			'icon'    => isset( $icons[ $icon ] ) ? $icon : $def['trust']['items'][ $i ]['icon'],
			'title'   => $text( isset( $raw['title'] ) ? $raw['title'] : '' ),
			'desc'    => $text( isset( $raw['desc'] ) ? $raw['desc'] : '' ),
		);
	}
	$out['trust'] = array(
		'show'  => empty( $in['trust']['show'] ) ? 0 : 1,
		'order' => $order( 'trust' ),
		'items' => $items,
	);

	// Blog teaser.
	$blog_count = absint( $get( 'blog', 'count', 3 ) );
	$out['blog'] = array(
		'show'        => empty( $in['blog']['show'] ) ? 0 : 1,
		'order'       => $order( 'blog' ),
		'heading'     => $text( $get( 'blog', 'heading' ) ),
		'link_label'  => $text( $get( 'blog', 'link_label' ) ),
		'count'       => in_array( $blog_count, array( 3, 6 ), true ) ? $blog_count : 3,
		'category_id' => absint( $get( 'blog', 'category_id', 0 ) ),
	);

	return $out;
}

function stocksystem_home_register_setting() {
	register_setting(
		'stocksystem_home_group',
		'stocksystem_home',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'stocksystem_home_sanitize',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'stocksystem_home_register_setting' );

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

function stocksystem_home_admin_menu() {
	add_theme_page(
		__( 'صفحهٔ اصلی', 'stocksystem' ),
		__( 'صفحهٔ اصلی', 'stocksystem' ),
		'manage_options',
		'stocksystem-home',
		'stocksystem_home_render_page'
	);
}
add_action( 'admin_menu', 'stocksystem_home_admin_menu' );

function stocksystem_home_admin_assets( $hook ) {
	if ( 'appearance_page_stocksystem-home' !== $hook ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_style( 'stocksystem-home-admin', STOCKSYSTEM_URI . '/assets/css/admin-home.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_script( 'stocksystem-home-admin', STOCKSYSTEM_URI . '/assets/js/admin-home.js', array( 'jquery' ), STOCKSYSTEM_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'stocksystem_home_admin_assets' );

/** Reset to the shipped copy. */
function stocksystem_home_handle_reset() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'دسترسی مجاز نیست.', 'stocksystem' ) );
	}
	check_admin_referer( 'stocksystem_home_reset' );
	delete_option( 'stocksystem_home' );
	wp_safe_redirect( add_query_arg( array( 'page' => 'stocksystem-home', 'reset' => '1' ), admin_url( 'themes.php' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_home_reset', 'stocksystem_home_handle_reset' );

function stocksystem_home_field( $name, $value, $label, $args = array() ) {
	$type        = isset( $args['type'] ) ? $args['type'] : 'text';
	$placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
	$help        = isset( $args['help'] ) ? $args['help'] : '';
	$class       = isset( $args['class'] ) ? $args['class'] : '';
	?>
	<div class="ss-field <?php echo esc_attr( $class ); ?>">
		<label>
			<span class="ss-field__label"><?php echo esc_html( $label ); ?></span>
			<?php if ( 'textarea' === $type ) : ?>
				<textarea name="stocksystem_home<?php echo esc_attr( $name ); ?>" rows="3" placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php else : ?>
				<input type="<?php echo esc_attr( $type ); ?>" name="stocksystem_home<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>"<?php echo 'url' === $type ? ' dir="ltr"' : ''; ?>>
			<?php endif; ?>
		</label>
		<?php if ( $help ) : ?>
			<span class="ss-field__help"><?php echo esc_html( $help ); ?></span>
		<?php endif; ?>
	</div>
	<?php
}

function stocksystem_home_section_head( $slug, $title, $cfg = null, $movable = true ) {
	?>
	<div class="ss-card__head">
		<h2><?php echo esc_html( $title ); ?></h2>
		<?php if ( $movable && $cfg ) : ?>
			<span class="ss-card__controls">
				<input type="hidden" name="stocksystem_home[<?php echo esc_attr( $slug ); ?>][show]" value="0">
				<label><input type="checkbox" name="stocksystem_home[<?php echo esc_attr( $slug ); ?>][show]" value="1" <?php checked( ! empty( $cfg['show'] ) ); ?>> <?php esc_html_e( 'نمایش این بخش', 'stocksystem' ); ?></label>
				<label><?php esc_html_e( 'ترتیب', 'stocksystem' ); ?>
					<input type="number" min="1" max="9" class="small-text" name="stocksystem_home[<?php echo esc_attr( $slug ); ?>][order]" value="<?php echo esc_attr( $cfg['order'] ); ?>">
				</label>
			</span>
		<?php endif; ?>
	</div>
	<?php
}

function stocksystem_home_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$c          = stocksystem_home();
	$icons      = stocksystem_home_icons();
	$categories = stocksystem_nav_categories();
	$sources    = array(
		'on_sale'      => __( 'کالاهای تخفیف‌دار (اگر نبود: جدیدترین‌ها)', 'stocksystem' ),
		'newest'       => __( 'جدیدترین کالاها', 'stocksystem' ),
		'best_selling' => __( 'پرفروش‌ترین‌ها', 'stocksystem' ),
		'featured'     => __( 'کالاهای «ویژه» (ستاره‌دار در فهرست محصولات)', 'stocksystem' ),
		'manual'       => __( 'انتخاب دستی کالاها', 'stocksystem' ),
	);
	$products = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => 300, 'status' => 'publish', 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
	$blog_cats = get_categories( array( 'hide_empty' => false ) );
	?>
	<div class="wrap ss-home">
		<h1><?php esc_html_e( 'صفحهٔ اصلی', 'stocksystem' ); ?></h1>
		<p class="description"><?php esc_html_e( 'متن‌ها، لینک‌ها، تصاویر و بخش‌های صفحهٔ اصلی. فیلد متنی را خالی بگذارید تا متن پیش‌فرض قالب نمایش داده شود.', 'stocksystem' ); ?></p>

		<?php if ( isset( $_GET['reset'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'صفحهٔ اصلی به متن‌های پیش‌فرض برگشت.', 'stocksystem' ); ?></p></div>
		<?php endif; ?>
		<?php settings_errors(); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'stocksystem_home_group' ); ?>

			<!-- Hero -->
			<section class="ss-card">
				<?php stocksystem_home_section_head( 'hero', __( 'بالای صفحه (هیرو)', 'stocksystem' ), null, false ); ?>
				<div class="ss-grid">
					<?php
					stocksystem_home_field( '[hero][eyebrow]', $c['hero']['eyebrow'], __( 'برچسب کوچک بالای تیتر', 'stocksystem' ), array( 'placeholder' => sprintf( __( 'استوک تست‌شده · %s', 'stocksystem' ), stocksystem_business( 'warranty_text' ) ), 'help' => __( 'خالی = «استوک تست‌شده · متن گارانتی» با متن گارانتی از بخش سفارشی‌سازی.', 'stocksystem' ) ) );
					stocksystem_home_field( '[hero][title]', $c['hero']['title'], __( 'تیتر اصلی', 'stocksystem' ) );
					stocksystem_home_field( '[hero][desc]', $c['hero']['desc'], __( 'توضیح زیر تیتر', 'stocksystem' ), array( 'type' => 'textarea', 'class' => 'ss-field--wide' ) );
					stocksystem_home_field( '[hero][cta1_label]', $c['hero']['cta1_label'], __( 'دکمهٔ اصلی — متن', 'stocksystem' ) );
					stocksystem_home_field( '[hero][cta1_url]', $c['hero']['cta1_url'], __( 'دکمهٔ اصلی — لینک', 'stocksystem' ), array( 'type' => 'url', 'placeholder' => __( 'خالی = اولین دستهٔ محصولات', 'stocksystem' ), 'help' => __( 'آدرس کامل یا مسیر داخلی مثل /repair/', 'stocksystem' ) ) );
					stocksystem_home_field( '[hero][cta2_label]', $c['hero']['cta2_label'], __( 'دکمهٔ دوم — متن', 'stocksystem' ) );
					stocksystem_home_field( '[hero][cta2_url]', $c['hero']['cta2_url'], __( 'دکمهٔ دوم — لینک', 'stocksystem' ), array( 'type' => 'url', 'placeholder' => __( 'خالی = صفحهٔ وضعیت کالای استوک', 'stocksystem' ) ) );
					for ( $i = 0; $i < 3; $i++ ) {
						stocksystem_home_field( '[hero][pillars][' . $i . ']', isset( $c['hero']['pillars'][ $i ] ) ? $c['hero']['pillars'][ $i ] : '', sprintf( /* translators: %d: number */ __( 'ارزش برند %d (زیر دکمه‌ها؛ خالی = حذف)', 'stocksystem' ), $i + 1 ), array( 'placeholder' => '' ) );
					}
					?>
				</div>

				<h3><?php esc_html_e( 'تصاویر هیرو', 'stocksystem' ); ?></h3>
				<p class="description"><?php esc_html_e( 'تا سه تصویر. اگر انتخاب نکنید، عکس جدیدترین کالاهای دستهٔ اول نمایش داده می‌شود. عکس با زمینهٔ شفاف (PNG) بهتر دیده می‌شود.', 'stocksystem' ); ?></p>
				<div class="ss-media-row">
					<?php for ( $i = 0; $i < 3; $i++ ) : $img = (int) $c['hero']['images'][ $i ]; ?>
						<div class="ss-media" data-empty-label="<?php esc_attr_e( 'انتخاب تصویر', 'stocksystem' ); ?>">
							<div class="ss-media__preview"><?php echo $img ? wp_get_attachment_image( $img, 'thumbnail' ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
							<input type="hidden" name="stocksystem_home[hero][images][<?php echo (int) $i; ?>]" value="<?php echo esc_attr( $img ); ?>">
							<button type="button" class="button ss-media__pick"><?php echo $img ? esc_html__( 'تغییر تصویر', 'stocksystem' ) : esc_html__( 'انتخاب تصویر', 'stocksystem' ); ?></button>
							<button type="button" class="button-link ss-media__clear"<?php echo $img ? '' : ' hidden'; ?>><?php esc_html_e( 'حذف', 'stocksystem' ); ?></button>
						</div>
					<?php endfor; ?>
				</div>
			</section>

			<!-- Categories -->
			<section class="ss-card">
				<?php stocksystem_home_section_head( 'categories', __( 'دسته‌بندی‌ها', 'stocksystem' ), $c['categories'] ); ?>
				<div class="ss-grid">
					<?php
					stocksystem_home_field( '[categories][heading]', $c['categories']['heading'], __( 'عنوان بخش', 'stocksystem' ) );
					stocksystem_home_field( '[categories][link_label]', $c['categories']['link_label'], __( 'متن لینک گوشه', 'stocksystem' ) );
					stocksystem_home_field( '[categories][link_url]', $c['categories']['link_url'], __( 'لینک گوشه', 'stocksystem' ), array( 'type' => 'url', 'placeholder' => __( 'خالی = صفحهٔ فروشگاه', 'stocksystem' ) ) );
					?>
				</div>
				<fieldset class="ss-checks">
					<legend><?php esc_html_e( 'دسته‌هایی که نمایش داده شوند', 'stocksystem' ); ?></legend>
					<p class="description"><?php esc_html_e( 'هیچ‌کدام انتخاب نشود = همهٔ دسته‌های اصلی. ترتیب همان ترتیب دسته‌ها در ووکامرس است (محصولات ← دسته‌ها ← کشیدن و رها کردن).', 'stocksystem' ); ?></p>
					<?php foreach ( $categories as $category ) : ?>
						<?php if ( empty( $category->id ) ) { continue; } ?>
						<label><input type="checkbox" name="stocksystem_home[categories][ids][]" value="<?php echo esc_attr( $category->id ); ?>" <?php checked( in_array( (int) $category->id, array_map( 'intval', $c['categories']['ids'] ), true ) ); ?>> <?php echo esc_html( $category->name ); ?></label>
					<?php endforeach; ?>
				</fieldset>
			</section>

			<!-- Featured products -->
			<section class="ss-card">
				<?php stocksystem_home_section_head( 'featured', __( 'پیشنهاد کالا', 'stocksystem' ), $c['featured'] ); ?>
				<div class="ss-grid">
					<?php
					stocksystem_home_field( '[featured][heading]', $c['featured']['heading'], __( 'عنوان بخش', 'stocksystem' ) );
					stocksystem_home_field( '[featured][link_label]', $c['featured']['link_label'], __( 'متن لینک گوشه', 'stocksystem' ) );
					stocksystem_home_field( '[featured][link_url]', $c['featured']['link_url'], __( 'لینک گوشه', 'stocksystem' ), array( 'type' => 'url', 'placeholder' => __( 'خالی = فروشگاه، فقط تخفیف‌دارها', 'stocksystem' ) ) );
					?>
					<div class="ss-field">
						<label>
							<span class="ss-field__label"><?php esc_html_e( 'کالاها از کجا بیایند؟', 'stocksystem' ); ?></span>
							<select name="stocksystem_home[featured][source]" class="ss-source">
								<?php foreach ( $sources as $value => $label ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $c['featured']['source'], $value ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<div class="ss-field">
						<label>
							<span class="ss-field__label"><?php esc_html_e( 'تعداد کالا', 'stocksystem' ); ?></span>
							<select name="stocksystem_home[featured][count]">
								<?php foreach ( array( 4, 8, 12 ) as $n ) : ?>
									<option value="<?php echo (int) $n; ?>" <?php selected( (int) $c['featured']['count'], $n ); ?>><?php echo esc_html( stocksystem_to_persian_digits( $n ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<div class="ss-field ss-field--wide ss-manual" hidden>
						<label>
							<span class="ss-field__label"><?php esc_html_e( 'کالاهای انتخابی', 'stocksystem' ); ?></span>
							<select name="stocksystem_home[featured][product_ids][]" multiple size="8">
								<?php foreach ( $products as $p ) : ?>
									<option value="<?php echo esc_attr( $p->get_id() ); ?>" <?php selected( in_array( $p->get_id(), array_map( 'intval', $c['featured']['product_ids'] ), true ) ); ?>><?php echo esc_html( $p->get_name() ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<span class="ss-field__help"><?php esc_html_e( 'با نگه داشتن Ctrl (یا ⌘ در مک) چند کالا انتخاب کنید. به ترتیب فهرست نمایش داده می‌شوند.', 'stocksystem' ); ?></span>
					</div>
				</div>
			</section>

			<!-- Trust band -->
			<section class="ss-card">
				<?php stocksystem_home_section_head( 'trust', __( 'نوار اعتماد (چهار مزیت)', 'stocksystem' ), $c['trust'] ); ?>
				<p class="description"><?php esc_html_e( 'عنوان یا توضیح خالی، متن پیش‌فرض را نشان می‌دهد: عنوان اول = متن گارانتی و توضیح چهارم = آدرس فروشگاه (از بخش سفارشی‌سازی).', 'stocksystem' ); ?></p>
				<div class="ss-trust">
					<?php foreach ( $c['trust']['items'] as $i => $item ) : ?>
						<div class="ss-trust__item">
							<input type="hidden" name="stocksystem_home[trust][items][<?php echo (int) $i; ?>][enabled]" value="0">
							<label class="ss-trust__enable"><input type="checkbox" name="stocksystem_home[trust][items][<?php echo (int) $i; ?>][enabled]" value="1" <?php checked( ! empty( $item['enabled'] ) ); ?>> <?php echo esc_html( sprintf( /* translators: %d: item number */ __( 'مزیت %d', 'stocksystem' ), $i + 1 ) ); ?></label>
							<label>
								<span class="ss-field__label"><?php esc_html_e( 'آیکون', 'stocksystem' ); ?></span>
								<select name="stocksystem_home[trust][items][<?php echo (int) $i; ?>][icon]">
									<?php foreach ( $icons as $key => $icon ) : ?>
										<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $item['icon'], $key ); ?>><?php echo esc_html( $icon['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</label>
							<?php
							stocksystem_home_field( '[trust][items][' . $i . '][title]', $item['title'], __( 'عنوان', 'stocksystem' ) );
							stocksystem_home_field( '[trust][items][' . $i . '][desc]', $item['desc'], __( 'توضیح', 'stocksystem' ) );
							?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

			<!-- Blog -->
			<section class="ss-card">
				<?php stocksystem_home_section_head( 'blog', __( 'مقالات بلاگ', 'stocksystem' ), $c['blog'] ); ?>
				<div class="ss-grid">
					<?php
					stocksystem_home_field( '[blog][heading]', $c['blog']['heading'], __( 'عنوان بخش', 'stocksystem' ) );
					stocksystem_home_field( '[blog][link_label]', $c['blog']['link_label'], __( 'متن لینک گوشه', 'stocksystem' ) );
					?>
					<div class="ss-field">
						<label>
							<span class="ss-field__label"><?php esc_html_e( 'تعداد مقاله', 'stocksystem' ); ?></span>
							<select name="stocksystem_home[blog][count]">
								<?php foreach ( array( 3, 6 ) as $n ) : ?>
									<option value="<?php echo (int) $n; ?>" <?php selected( (int) $c['blog']['count'], $n ); ?>><?php echo esc_html( stocksystem_to_persian_digits( $n ) ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
					<div class="ss-field">
						<label>
							<span class="ss-field__label"><?php esc_html_e( 'فقط از این دسته', 'stocksystem' ); ?></span>
							<select name="stocksystem_home[blog][category_id]">
								<option value="0"><?php esc_html_e( 'همهٔ دسته‌ها (جدیدترین‌ها)', 'stocksystem' ); ?></option>
								<?php foreach ( $blog_cats as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat->term_id ); ?>" <?php selected( (int) $c['blog']['category_id'], $cat->term_id ); ?>><?php echo esc_html( $cat->name ); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</div>
				</div>
			</section>

			<p class="submit">
				<?php submit_button( __( 'ذخیرهٔ تغییرات', 'stocksystem' ), 'primary', 'submit', false ); ?>
				<a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'دیدن صفحهٔ اصلی', 'stocksystem' ); ?></a>
			</p>
		</form>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ss-reset" onsubmit="return confirm('<?php echo esc_js( __( 'همهٔ متن‌ها و تنظیمات صفحهٔ اصلی به حالت اولیه برگردد؟', 'stocksystem' ) ); ?>');">
			<input type="hidden" name="action" value="stocksystem_home_reset">
			<?php wp_nonce_field( 'stocksystem_home_reset' ); ?>
			<button type="submit" class="button-link button-link-delete"><?php esc_html_e( 'بازگشت به متن‌های پیش‌فرض', 'stocksystem' ); ?></button>
		</form>
	</div>
	<?php
}
