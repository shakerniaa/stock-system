<?php
/**
 * Custom My Account endpoints — wallet (decision #10) and wishlist
 * (16-D). Order tracking is deliberately NOT one of these: the design
 * explicitly says it "works without logging in" ("پیگیری سفارش بدون نیاز
 * به ورود کار می‌کند"), and WooCommerce's account shortcode gates every
 * endpoint behind login except its own hardcoded lost-password/reset-
 * password cases — fighting that isn't worth it. Order tracking is its
 * own standalone page template instead (page-templates/order-tracking.php).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_add_account_endpoints() {
	add_rewrite_endpoint( 'wallet', EP_ROOT | EP_PAGES );
	add_rewrite_endpoint( 'wishlist', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'stocksystem_add_account_endpoints' );

function stocksystem_account_query_vars( $vars ) {
	$vars['wallet']   = 'wallet';
	$vars['wishlist'] = 'wishlist';
	return $vars;
}
add_filter( 'woocommerce_get_query_vars', 'stocksystem_account_query_vars' );

/**
 * Flush rewrite rules once so /my-account/wallet/ etc. resolve — only
 * ever on activation, never on every request.
 */
function stocksystem_flush_rewrites_for_endpoints() {
	stocksystem_add_account_endpoints();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'stocksystem_flush_rewrites_for_endpoints' );

/**
 * Menu order: سفارش‌های من, کیف پول, علاقه‌مندی‌ها, نشانی‌ها, اطلاعات
 * حساب, خروج. Drops "downloads" (no digital products in this catalog).
 */
function stocksystem_account_menu_items( $items ) {
	unset( $items['downloads'] );

	$logout = $items['customer-logout'] ?? null;
	unset( $items['customer-logout'] );

	$new_items = array();
	foreach ( $items as $key => $label ) {
		$new_items[ $key ] = $label;
		if ( 'orders' === $key ) {
			$new_items['wallet']   = __( 'کیف پول و اعتبار', 'stocksystem' );
			$new_items['wishlist'] = __( 'علاقه‌مندی‌ها', 'stocksystem' );
		}
	}

	if ( $logout ) {
		$new_items['customer-logout'] = $logout;
	}

	return $new_items;
}
add_filter( 'woocommerce_account_menu_items', 'stocksystem_account_menu_items' );

/** Sidebar icon per menu key — template-parts/account/navigation.php. */
function stocksystem_account_menu_icons() {
	return array(
		'dashboard'        => '<path d="M4 13h6V4H4v9zM14 20h6v-9h-6v9zM14 4v5h6V4h-6zM4 20h6v-5H4v5z"></path>',
		'orders'           => '<path d="M4 6h16l-1.5 10.5a2 2 0 0 1-2 1.7H7.5a2 2 0 0 1-2-1.7L4 6z"></path><path d="M9 6V4.5a3 3 0 0 1 6 0V6"></path>',
		'wallet'           => '<rect x="3" y="6" width="18" height="12" rx="2"></rect><path d="M16 12h2"></path>',
		'wishlist'         => '<path d="M12 20s-7-4.6-7-9.4A3.9 3.9 0 0 1 12 8a3.9 3.9 0 0 1 7 2.6C19 15.4 12 20 12 20z"></path>',
		'edit-address'     => '<path d="M12 21s7-5.4 7-11a7 7 0 0 0-14 0c0 5.6 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle>',
		'edit-account'     => '<circle cx="12" cy="8" r="3.5"></circle><path d="M5 20a7 7 0 0 1 14 0"></path>',
		'customer-logout'  => '<path d="M10 5H6a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"></path><path d="M15 8l4 4-4 4M19 12H9"></path>',
	);
}

/* ---- Wallet endpoint content ---- */
function stocksystem_wallet_endpoint_content() {
	get_template_part( 'template-parts/account/wallet' );
}
add_action( 'woocommerce_account_wallet_endpoint', 'stocksystem_wallet_endpoint_content' );

/* ---- Wishlist endpoint content ---- */
function stocksystem_wishlist_endpoint_content() {
	get_template_part( 'template-parts/account/wishlist' );
}
add_action( 'woocommerce_account_wishlist_endpoint', 'stocksystem_wishlist_endpoint_content' );

/* ---- Wallet top-up request (no payment gateway chosen yet — same
   honest "request via contact" pattern as inc/product-notify.php). ---- */
function stocksystem_handle_wallet_topup_request() {
	if ( empty( $_POST['stocksystem_wallet_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['stocksystem_wallet_nonce'] ), 'stocksystem_wallet_topup' ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر است.', 'stocksystem' ) );
	}

	if ( ! is_user_logged_in() ) {
		wp_die( esc_html__( 'برای این کار باید وارد حساب شوید.', 'stocksystem' ) );
	}

	$amount = ! empty( $_POST['amount'] ) ? absint( $_POST['amount'] ) : 0;
	$user   = wp_get_current_user();

	if ( $amount > 0 ) {
		wp_mail(
			get_option( 'admin_email' ),
			__( 'درخواست شارژ کیف پول', 'stocksystem' ),
			sprintf(
				/* translators: 1: user display name, 2: phone, 3: amount in toman */
				__( "کاربر: %1\$s\nتلفن: %2\$s\nمبلغ درخواستی: %3\$s تومان", 'stocksystem' ),
				$user->display_name,
				get_user_meta( $user->ID, 'billing_phone', true ),
				number_format( $amount )
			)
		);
	}

	wp_safe_redirect( add_query_arg( 'topup_requested', '1', wc_get_account_endpoint_url( 'wallet' ) ) );
	exit;
}
add_action( 'admin_post_stocksystem_wallet_topup', 'stocksystem_handle_wallet_topup_request' );
