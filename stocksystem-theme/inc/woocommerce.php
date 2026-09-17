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
		'woocommerce_price_thousand_sep'  => "\u{066C}",
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
 * instead. The ٬ (U+066C) thousands separator itself comes from the
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

/**
 * order-tracking is a custom My Account endpoint (16-D), not a core
 * WooCommerce one — registered once that template lands. Falls back to
 * the account page so the topbar link never 404s in the meantime.
 */
function stocksystem_order_tracking_url() {
	if ( ! function_exists( 'wc_get_page_id' ) ) {
		return home_url( '/' );
	}

	$account_page_id = wc_get_page_id( 'myaccount' );

	if ( $account_page_id < 1 ) {
		return home_url( '/' );
	}

	return wc_get_endpoint_url( 'order-tracking', '', get_permalink( $account_page_id ) );
}
