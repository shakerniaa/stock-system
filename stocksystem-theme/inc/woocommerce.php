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
 * نظرات"): drop the long-form description tab (this design is spec-
 * focused, not copy-focused), relabel additional-information to
 * "مشخصات فنی", and add a warranty/shipping/returns tab sourced from the
 * same Customizer settings as the buy box — leave the reviews tab alone,
 * WooCommerce's own fa_IR translation already labels/counts it.
 */
function stocksystem_product_tabs( $tabs ) {
	unset( $tabs['description'] );

	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'مشخصات فنی', 'stocksystem' );
	}

	$tabs['stocksystem_warranty'] = array(
		'title'    => __( 'گارانتی و خدمات', 'stocksystem' ),
		'priority' => 15,
		'callback' => 'stocksystem_warranty_tab_content',
	);

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'stocksystem_product_tabs' );

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
		if ( $product && $product->is_type( 'simple' ) ) {
			return __( 'افزودن به سبد', 'stocksystem' );
		}
		return $text;
	},
	10,
	2
);

// The theme has no sidebar.php; WooCommerce's default sidebar hook would
// call get_sidebar() and log a "theme without sidebar.php" deprecation.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

// Swap WooCommerce's own wrappers for ours so archive/single templates sit
// inside the same .container frame as the rest of the theme.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

function stocksystem_wc_wrapper_start() {
	echo '<main id="primary" class="site-main container">';
}
add_action( 'woocommerce_before_main_content', 'stocksystem_wc_wrapper_start', 10 );

function stocksystem_wc_wrapper_end() {
	echo '</main>';
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
		return 'IRR' === $currency ? 'تومان' : $symbol;
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
