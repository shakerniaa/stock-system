<?php
/**
 * "خبرم کن" out-of-stock notify-me form handler. README: "Out-of-stock
 * products swap the buy button for a 'notify me' phone-number form."
 * No SMS/marketing plugin decision has been made yet, so this just
 * records the phone number against the product (as post meta) and
 * emails the store — swap stocksystem_notify_subscriber_added() for a
 * real SMS/CRM integration once one is chosen.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_handle_notify_me_submission() {
	if ( empty( $_POST['stocksystem_notify_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['stocksystem_notify_nonce'] ), 'stocksystem_notify_me' ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر است.', 'stocksystem' ) );
	}

	$product_id = ! empty( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$phone      = ! empty( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$redirect   = ! empty( $_POST['_wp_http_referer'] ) ? wp_unslash( $_POST['_wp_http_referer'] ) : home_url( '/' );

	if ( $product_id && preg_match( '/^09\d{9}$/', $phone ) ) {
		stocksystem_notify_subscriber_added( $product_id, $phone );
		$redirect = add_query_arg( 'notify_me', 'success', $redirect );
	} else {
		$redirect = add_query_arg( 'notify_me', 'invalid', $redirect );
	}

	wp_safe_redirect( $redirect . '#notify-me' );
	exit;
}
add_action( 'admin_post_stocksystem_notify_me', 'stocksystem_handle_notify_me_submission' );
add_action( 'admin_post_nopriv_stocksystem_notify_me', 'stocksystem_handle_notify_me_submission' );

function stocksystem_notify_subscriber_added( $product_id, $phone ) {
	$subscribers = get_post_meta( $product_id, '_notify_subscribers', true );
	$subscribers = is_array( $subscribers ) ? $subscribers : array();

	if ( ! in_array( $phone, $subscribers, true ) ) {
		$subscribers[] = $phone;
		update_post_meta( $product_id, '_notify_subscribers', $subscribers );
	}

	$admin_email = stocksystem_notify_email( 'notify' );
	wp_mail(
		$admin_email,
		sprintf(
			/* translators: %s: product name */
			__( 'درخواست اطلاع‌رسانی موجودی: %s', 'stocksystem' ),
			get_the_title( $product_id )
		),
		sprintf(
			/* translators: 1: phone number, 2: product name */
			__( 'شماره %1$s برای اطلاع از موجود شدن «%2$s» ثبت شد.', 'stocksystem' ),
			$phone,
			get_the_title( $product_id )
		)
	);
}

/**
 * Waiting-subscriber count for the "۴۷ نفر منتظر این مدل هستند" line.
 */
function stocksystem_notify_subscriber_count( $product_id ) {
	$subscribers = get_post_meta( $product_id, '_notify_subscribers', true );
	return is_array( $subscribers ) ? count( $subscribers ) : 0;
}

/**
 * "درخواست استعلام قیمت" quote-request form handler (no-price / B2B
 * products, state 11). Same no-plugin-yet caveat as notify-me above —
 * emails the store rather than filing into a real CRM/quoting system.
 */
function stocksystem_handle_quote_request_submission() {
	if ( empty( $_POST['stocksystem_quote_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['stocksystem_quote_nonce'] ), 'stocksystem_quote_request' ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر است.', 'stocksystem' ) );
	}

	$product_id     = ! empty( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$org_name       = ! empty( $_POST['org_name'] ) ? sanitize_text_field( wp_unslash( $_POST['org_name'] ) ) : '';
	$device_count   = ! empty( $_POST['device_count'] ) ? sanitize_text_field( wp_unslash( $_POST['device_count'] ) ) : '';
	$phone          = ! empty( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$redirect       = ! empty( $_POST['_wp_http_referer'] ) ? wp_unslash( $_POST['_wp_http_referer'] ) : home_url( '/' );

	if ( $product_id && $org_name && $phone ) {
		wp_mail(
			stocksystem_notify_email( 'request' ),
			sprintf(
				/* translators: %s: product name */
				__( 'درخواست استعلام قیمت: %s', 'stocksystem' ),
				get_the_title( $product_id )
			),
			sprintf(
				/* translators: 1: org name, 2: device count, 3: phone, 4: product name */
				__( "سازمان: %1\$s\nتعداد دستگاه: %2\$s\nتلفن: %3\$s\nمحصول: %4\$s", 'stocksystem' ),
				$org_name,
				$device_count,
				$phone,
				get_the_title( $product_id )
			)
		);
		$redirect = add_query_arg( 'quote_request', 'success', $redirect );
	} else {
		$redirect = add_query_arg( 'quote_request', 'invalid', $redirect );
	}

	wp_safe_redirect( $redirect . '#request-quote' );
	exit;
}
add_action( 'admin_post_stocksystem_quote_request', 'stocksystem_handle_quote_request_submission' );
add_action( 'admin_post_nopriv_stocksystem_quote_request', 'stocksystem_handle_quote_request_submission' );
