<?php
/**
 * Wishlist — 16-D. README: "Wishlist in stock sales means something
 * different: because every device is unique, a saved item might sell
 * out. So stock status is shown inside the list too." That's already
 * handled for free — the wishlist just renders each product through the
 * normal product card, which already carries its own stock badge.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_get_wishlist( $user_id = null ) {
	$user_id = $user_id ? $user_id : get_current_user_id();
	if ( ! $user_id ) {
		return array();
	}
	$ids = get_user_meta( $user_id, '_wishlist', true );
	return is_array( $ids ) ? array_map( 'absint', $ids ) : array();
}

function stocksystem_is_in_wishlist( $product_id, $user_id = null ) {
	return in_array( (int) $product_id, stocksystem_get_wishlist( $user_id ), true );
}

function stocksystem_ajax_toggle_wishlist() {
	check_ajax_referer( 'stocksystem_wishlist', 'nonce' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( array( 'message' => __( 'برای افزودن به علاقه‌مندی‌ها وارد حساب شوید.', 'stocksystem' ), 'require_login' => true ) );
	}

	$product_id = ! empty( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'محصول نامعتبر است.', 'stocksystem' ) ) );
	}

	$user_id = get_current_user_id();
	$ids     = stocksystem_get_wishlist( $user_id );

	if ( in_array( $product_id, $ids, true ) ) {
		$ids     = array_values( array_diff( $ids, array( $product_id ) ) );
		$in_list = false;
	} else {
		$ids[]   = $product_id;
		$in_list = true;
	}

	update_user_meta( $user_id, '_wishlist', $ids );

	wp_send_json_success(
		array(
			'in_wishlist' => $in_list,
			'count'       => count( $ids ),
		)
	);
}
add_action( 'wp_ajax_stocksystem_toggle_wishlist', 'stocksystem_ajax_toggle_wishlist' );
