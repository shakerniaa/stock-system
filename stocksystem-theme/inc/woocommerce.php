<?php
/**
 * WooCommerce integration glue.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// We ship our own component CSS; don't load WooCommerce's default styles.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// README: "'Low stock' is not a separate WooCommerce state — it is the
// low-stock threshold; set it to 2." Seed it once on activation only —
// never overwrite a value the store admin has since changed.
function stocksystem_set_default_low_stock_threshold() {
	if ( false === get_option( 'woocommerce_notify_low_stock_amount', false ) ) {
		update_option( 'woocommerce_notify_low_stock_amount', 2 );
	}
}
add_action( 'after_switch_theme', 'stocksystem_set_default_low_stock_threshold' );

/**
 * Product tabs (03 Product.dc.html shows "مشخصات فنی / گارانتی و خدمات /
 * نظرات"): the design's first cut was spec-focused and dropped WooCommerce's
 * long-form description tab entirely — but that tab is the only place a
 * merchant can write free-form copy about a product (condition notes, what's
 * in the box, usage notes …), so it's kept and relabelled instead of removed.
 * It only appears when the product's main content editor has something in it
 * (WooCommerce's own rule), so an empty description doesn't add an empty tab.
 * Order: توضیحات (10, native) → گارانتی و خدمات (15) → مشخصات فنی (20,
 * native "additional information", relabelled) → نظرات (30, native).
 */
function stocksystem_product_tabs( $tabs ) {
	if ( isset( $tabs['description'] ) ) {
		$tabs['description']['title'] = __( 'توضیحات محصول', 'stocksystem' );
	}

	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'مشخصات فنی', 'stocksystem' );
	}

	$tabs['stocksystem_warranty'] = array(
		'title'    => __( 'گارانتی و خدمات', 'stocksystem' ),
		'priority' => 15,
		'callback' => 'stocksystem_warranty_tab_content',
	);

	// «نظرات (۲)» — WooCommerce prints the count in Latin digits.
	if ( isset( $tabs['reviews'] ) ) {
		$tabs['reviews']['title'] = stocksystem_to_persian_digits( $tabs['reviews']['title'] );
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'stocksystem_product_tabs' );

// The description tab's own <h2> heading — defaults to WooCommerce's English
// "Description" unless a translation pack overrides it; matched to the tab
// title above instead of relying on that.
add_filter(
	'woocommerce_product_description_heading',
	function () {
		return __( 'توضیحات محصول', 'stocksystem' );
	}
);

function stocksystem_warranty_tab_content() {
	?>
	<h2><?php esc_html_e( 'گارانتی و خدمات', 'stocksystem' ); ?></h2>
	<ul class="warranty-tab-list">
		<li><?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?></li>
		<li><?php esc_html_e( 'تحویل حضوری رایگان در نیشابور · ارسال با پست/تیپاکس به سراسر کشور', 'stocksystem' ); ?></li>
		<li><?php esc_html_e( '۷ روز ضمانت بازگشت بدون قید و شرط', 'stocksystem' ); ?></li>
	</ul>
	<?php
}

// Our own template-parts/product/related.php reuses the standard product
// card grid instead of WooCommerce's default related-products markup, so
// every product listing on the site looks consistent.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

// template-parts/product/tabs-wrapper.php already calls
// woocommerce_output_product_data_tabs() manually inside the gallery
// column (03 Product.dc.html's layout puts tabs there, not after the
// buy box) — without this, WooCommerce's own default hook on this same
// action renders the tabs a second time right after the buy box. Only
// visible as an obvious duplicate once gallery and buy box sit side by
// side (tablet+) instead of stacked, which is what surfaced it.
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

// The coupon field lives in the cart's order summary (design), so drop
// WooCommerce's default "have a coupon? click here" banner above checkout.
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

// The design labels the card button «افزودن به سبد» (WooCommerce's fa_IR
// string adds «خرید», which wraps to two lines in the 2-up mobile cards).
add_filter(
	'woocommerce_product_add_to_cart_text',
	function ( $text, $product ) {
		if ( $product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			return __( 'افزودن به سبد', 'stocksystem' );
		}
		return $text;
	},
	10,
	2
);

// Same short label on the product page's own button.
add_filter(
	'woocommerce_product_single_add_to_cart_text',
	function ( $text, $product ) {
		if ( $product && $product->is_type( array( 'simple', 'grouped' ) ) ) {
			return __( 'افزودن به سبد', 'stocksystem' );
		}
		return $text;
	},
	10,
	2
);

// Reviews use an initial-letter avatar instead of a Gravatar request per review.
remove_action( 'woocommerce_review_before', 'woocommerce_review_display_gravatar', 10 );

// The theme has no sidebar.php; WooCommerce's default sidebar hook would
// call get_sidebar() and log a "theme without sidebar.php" deprecation.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Swap WooCommerce's own wrappers for ours so archive/single templates sit
// inside the same .container frame as the rest of the theme.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

function stocksystem_wc_wrapper_start() {
	echo '<div class="site-main container">';
}
add_action( 'woocommerce_before_main_content', 'stocksystem_wc_wrapper_start', 10 );

function stocksystem_wc_wrapper_end() {
	echo '</div>';
}
add_action( 'woocommerce_after_main_content', 'stocksystem_wc_wrapper_end', 10 );

/**
 * Store currency. WooCommerce has no native "Toman" currency code — the
 * standard approach (same one most Iranian WooCommerce stores use) is
 * IRR (Rial) with the symbol overridden to "تومان" and prices entered
 * directly in Toman units; WooCommerce never does unit conversion, it
 * only ever displays whatever symbol/decimals you configure. Seeded
 * once on activation, never overwritten if the store admin changes it.
 */
function stocksystem_set_default_currency_options() {
	$defaults = array(
		'woocommerce_currency'            => 'IRR',
		'woocommerce_currency_pos'        => 'right_space',
		'woocommerce_price_num_decimals'  => '0',
		'woocommerce_price_thousand_sep'  => ',',
		'woocommerce_price_decimal_sep'   => '.',
	);

	foreach ( $defaults as $option => $value ) {
		if ( false === get_option( $option, false ) ) {
			update_option( $option, $value );
		}
	}
}
add_action( 'after_switch_theme', 'stocksystem_set_default_currency_options' );

add_filter(
	'woocommerce_currency_symbol',
	function ( $symbol, $currency ) {
		return 'IRR' === $currency ? __( 'تومان', 'stocksystem' ) : $symbol;
	},
	10,
	2
);

/**
 * Persian-Indic digits on every native WooCommerce price render (cart,
 * checkout, variation panels, order review) — not just the hand-built
 * product card markup, which calls stocksystem_format_number() directly
 * instead. The ASCII comma thousands separator (owner's choice: ۳,۳۵۰,۰۰۰) comes from the
 * woocommerce_price_thousand_sep option seeded above, editable like any
 * other WooCommerce setting rather than forced here.
 */
add_filter(
	'formatted_woocommerce_price',
	function ( $formatted_price ) {
		return stocksystem_to_persian_digits( $formatted_price );
	},
	20
);

// WooCommerce stores its default checkout/registration privacy sentences in
// English when the option was first written before the fa_IR pack loaded;
// swap in Persian only while the store still holds that untouched default.
add_filter(
	'woocommerce_get_privacy_policy_text',
	function ( $text, $type ) {
		if ( 0 !== strpos( wp_strip_all_tags( (string) $text ), 'Your personal data will be used' ) ) {
			return $text;
		}

		if ( 'registration' === $type ) {
			return __( 'اطلاعات شخصی شما برای پشتیبانی از تجربهٔ شما در این سایت، مدیریت دسترسی به حساب کاربری و سایر مقاصد ذکرشده در [privacy_policy] استفاده می‌شود.', 'stocksystem' );
		}

		return __( 'اطلاعات شخصی شما برای پردازش سفارش، بهبود تجربهٔ شما در این سایت و سایر مقاصد ذکرشده در [privacy_policy] استفاده می‌شود.', 'stocksystem' );
	},
	10,
	2
);

// «۴ عدد در انبار» — WooCommerce prints the raw integer.
add_filter( 'woocommerce_format_stock_quantity', 'stocksystem_to_persian_digits' );

/**
 * Order tracking is a standalone guest-accessible page (page-templates/
 * order-tracking.php), not a My Account endpoint — see that file's
 * header comment for why. The page itself is auto-created on theme
 * activation, same pattern as WooCommerce's own shop/cart/checkout
 * pages, with its ID cached in an option.
 */
function stocksystem_order_tracking_url() {
	$page_id = get_option( 'stocksystem_order_tracking_page_id' );

	if ( $page_id && get_post_status( $page_id ) ) {
		return get_permalink( $page_id );
	}

	return home_url( '/' );
}

function stocksystem_create_order_tracking_page() {
	if ( get_option( 'stocksystem_order_tracking_page_id' ) ) {
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'     => __( 'پیگیری سفارش', 'stocksystem' ),
			'post_status'    => 'publish',
			'post_type'      => 'page',
			'page_template'  => 'page-templates/order-tracking.php',
			'comment_status' => 'closed',
		)
	);

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'stocksystem_order_tracking_page_id', $page_id );
	}
}
add_action( 'after_switch_theme', 'stocksystem_create_order_tracking_page' );

/**
 * Coupon helpers for the cart / checkout summaries. wc_cart_totals_coupon_html()
 * prints "-amount [Remove]" (its own sign and an English-labelled link), which
 * doubles the minus sign in our rows and duplicates the removal control shown
 * with the applied code — so the summaries print just the amount.
 */
function stocksystem_coupon_discount_html( $coupon ) {
	$amount = WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax );

	if ( $coupon->get_free_shipping() && empty( $amount ) ) {
		return esc_html__( 'ارسال رایگان', 'stocksystem' );
	}

	return '−' . wc_price( $amount );
}

function stocksystem_remove_coupon_url( $code ) {
	return add_query_arg( 'remove_coupon', rawurlencode( $code ), wc_get_cart_url() );
}

// Coupon codes are stored lowercase by WooCommerce; show them the way they are written on flyers.
add_filter(
	'woocommerce_cart_totals_coupon_label',
	function ( $label, $coupon ) {
		/* translators: %s: coupon code */
		return sprintf( __( 'کد تخفیف: %s', 'stocksystem' ), strtoupper( $coupon->get_code() ) );
	},
	10,
	2
);

// Site search means product search. The header forms send post_type=product,
// but the 404 page's search form, browser search shortcuts and old links send
// a bare ?s=… — which would otherwise list posts, pages and products as
// unstyled title lines.
add_action(
	'pre_get_posts',
	function ( $query ) {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}
		if ( ! $query->get( 'post_type' ) ) {
			$query->set( 'post_type', 'product' );
		}
	}
);

/**
 * Fresh WooCommerce (8.3+) creates the Cart and Checkout pages with its block
 * editor blocks, which bypass every template in woocommerce/cart and
 * woocommerce/checkout. This theme is built on the classic templates, so on
 * activation (and whenever an admin screen loads) a page that still holds block
 * markup is switched to the classic shortcode.
 */
function stocksystem_ensure_classic_cart_checkout() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return;
	}

	$pages = array(
		'cart'     => array( 'wp:woocommerce/cart', '[woocommerce_cart]' ),
		'checkout' => array( 'wp:woocommerce/checkout', '[woocommerce_checkout]' ),
	);

	foreach ( $pages as $key => $info ) {
		$page_id = (int) wc_get_page_id( $key );
		$page    = $page_id > 0 ? get_post( $page_id ) : null;

		if ( $page && false !== strpos( $page->post_content, $info[0] ) ) {
			wp_update_post( array( 'ID' => $page_id, 'post_content' => $info[1] ) );
		}
	}
}
add_action( 'after_switch_theme', 'stocksystem_ensure_classic_cart_checkout', 20 );
add_action(
	'admin_init',
	function () {
		if ( current_user_can( 'manage_options' ) && ! get_transient( 'stocksystem_classic_pages_checked' ) ) {
			stocksystem_ensure_classic_cart_checkout();
			set_transient( 'stocksystem_classic_pages_checked', 1, DAY_IN_SECONDS );
		}
	}
);
