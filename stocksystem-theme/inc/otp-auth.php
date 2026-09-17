<?php
/**
 * OTP (SMS one-time-code) login/registration — decision #3: "پیش‌فرض:
 * ورود با کد پیامکی (OTP) — بدون نیاز به رمز عبور برای مشتری تازه.
 * اختیاری: کاربر می‌تواند در تنظیمات حساب رمز عبور تعیین کند." WordPress
 * core is password-based, so this is a from-scratch mu-plugin-style
 * layer, exactly as the decisions doc anticipated needing.
 *
 * No SMS gateway has been chosen yet (open item in README) — sending is
 * abstracted behind stocksystem_send_otp_sms() so swapping in a real
 * provider later is a one-function change. Until then it emails the
 * store admin (same fallback pattern as inc/product-notify.php) and, in
 * WP_DEBUG only, returns the code directly to the browser for testing.
 *
 * Users are identified by phone number (stored as user_login, sanitized
 * to digits). A placeholder email is generated if the customer doesn't
 * provide a real one at registration — WordPress requires a unique,
 * non-empty user email.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STOCKSYSTEM_OTP_TTL', 5 * MINUTE_IN_SECONDS );
define( 'STOCKSYSTEM_OTP_PLACEHOLDER_EMAIL_DOMAIN', 'customers.stocksystem.local' );

function stocksystem_normalize_phone( $phone ) {
	$digits = preg_replace( '/\D/', '', (string) $phone );
	// Accept "989..." or "0912..." and normalize to the local "09..." form.
	if ( 0 === strpos( $digits, '98' ) && 12 === strlen( $digits ) ) {
		$digits = '0' . substr( $digits, 2 );
	}
	return $digits;
}

function stocksystem_is_valid_phone( $phone ) {
	return (bool) preg_match( '/^09\d{9}$/', $phone );
}

/**
 * Sends the OTP. Swap this for a real SMS provider integration when one
 * is chosen — everything else in this file is provider-agnostic.
 */
function stocksystem_send_otp_sms( $phone, $code ) {
	wp_mail(
		get_option( 'admin_email' ),
		__( 'کد ورود پیامکی', 'stocksystem' ),
		sprintf(
			/* translators: 1: phone number, 2: OTP code */
			__( 'کد ورود برای %1$s: %2$s (به مدت ۵ دقیقه معتبر است)', 'stocksystem' ),
			$phone,
			$code
		)
	);
}

function stocksystem_ajax_request_otp() {
	check_ajax_referer( 'stocksystem_otp', 'nonce' );

	$phone = stocksystem_normalize_phone( $_POST['phone'] ?? '' );

	if ( ! stocksystem_is_valid_phone( $phone ) ) {
		wp_send_json_error( array( 'message' => __( 'شمارهٔ موبایل معتبر نیست.', 'stocksystem' ) ) );
	}

	// Basic rate limit: one request per 60 seconds per phone.
	if ( get_transient( 'stocksystem_otp_throttle_' . $phone ) ) {
		wp_send_json_error( array( 'message' => __( 'کمی صبر کنید و دوباره تلاش کنید.', 'stocksystem' ) ) );
	}

	$code = (string) wp_rand( 1000, 9999 );
	set_transient( 'stocksystem_otp_' . $phone, wp_hash_password( $code ), STOCKSYSTEM_OTP_TTL );
	set_transient( 'stocksystem_otp_throttle_' . $phone, 1, 60 );

	stocksystem_send_otp_sms( $phone, $code );

	$response = array( 'message' => __( 'کد ورود پیامک شد.', 'stocksystem' ) );
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$response['debug_code'] = $code; // Never exposed outside WP_DEBUG.
	}

	wp_send_json_success( $response );
}
add_action( 'wp_ajax_stocksystem_request_otp', 'stocksystem_ajax_request_otp' );
add_action( 'wp_ajax_nopriv_stocksystem_request_otp', 'stocksystem_ajax_request_otp' );

function stocksystem_ajax_verify_otp() {
	check_ajax_referer( 'stocksystem_otp', 'nonce' );

	$phone = stocksystem_normalize_phone( $_POST['phone'] ?? '' );
	$code  = sanitize_text_field( wp_unslash( $_POST['code'] ?? '' ) );
	$name  = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );

	if ( ! stocksystem_is_valid_phone( $phone ) || '' === $code ) {
		wp_send_json_error( array( 'message' => __( 'اطلاعات وارد‌شده نامعتبر است.', 'stocksystem' ) ) );
	}

	$stored_hash = get_transient( 'stocksystem_otp_' . $phone );

	if ( ! $stored_hash || ! wp_check_password( $code, $stored_hash ) ) {
		wp_send_json_error( array( 'message' => __( 'کد وارد‌شده نادرست یا منقضی‌شده است.', 'stocksystem' ) ) );
	}

	delete_transient( 'stocksystem_otp_' . $phone );

	$user = stocksystem_find_or_create_user_by_phone( $phone, $name );

	if ( is_wp_error( $user ) ) {
		wp_send_json_error( array( 'message' => $user->get_error_message() ) );
	}

	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );
	do_action( 'wp_login', $user->user_login, $user );

	wp_send_json_success(
		array(
			'redirect' => wc_get_page_permalink( 'myaccount' ),
		)
	);
}
add_action( 'wp_ajax_stocksystem_verify_otp', 'stocksystem_ajax_verify_otp' );
add_action( 'wp_ajax_nopriv_stocksystem_verify_otp', 'stocksystem_ajax_verify_otp' );

/**
 * Finds the customer account for a phone number, or creates one on
 * first login — this is what makes OTP double as both login and
 * registration in a single flow (decision #3 doesn't ask for a
 * separate signup step for the OTP path).
 */
function stocksystem_find_or_create_user_by_phone( $phone, $name = '' ) {
	$existing = get_users(
		array(
			'meta_key'   => 'billing_phone',
			'meta_value' => $phone,
			'number'     => 1,
			'fields'     => 'all',
		)
	);

	if ( ! empty( $existing ) ) {
		return $existing[0];
	}

	// Fall back to matching by user_login too, in case the account
	// predates billing_phone meta being set (e.g. created via checkout).
	$by_login = get_user_by( 'login', $phone );
	if ( $by_login ) {
		return $by_login;
	}

	$placeholder_email = $phone . '@' . STOCKSYSTEM_OTP_PLACEHOLDER_EMAIL_DOMAIN;

	// A random password the customer never sees or needs — they log in via
	// OTP. Stored (in the clear, deliberately) so the account-details form
	// can silently satisfy WooCommerce's "enter your current password"
	// requirement the first time they choose to set a real one; see
	// woocommerce/myaccount/form-edit-account.php. It stops mattering, and
	// gets cleared, the moment they set a real password.
	$temp_password = wp_generate_password( 24 );

	$user_id = wc_create_new_customer( $placeholder_email, $phone, $temp_password );

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	// wc_create_new_customer() doesn't accept a display_name arg — set it
	// directly, since WooCommerce's own account-details form later
	// requires display_name to be non-empty.
	wp_update_user(
		array(
			'ID'           => $user_id,
			'display_name' => $name ? $name : $phone,
		)
	);

	update_user_meta( $user_id, 'billing_phone', $phone );
	update_user_meta( $user_id, '_otp_temp_password', $temp_password );
	if ( $name ) {
		$name_parts = explode( ' ', $name, 2 );
		update_user_meta( $user_id, 'billing_first_name', $name_parts[0] );
		update_user_meta( $user_id, 'first_name', $name_parts[0] );
		if ( ! empty( $name_parts[1] ) ) {
			update_user_meta( $user_id, 'billing_last_name', $name_parts[1] );
			update_user_meta( $user_id, 'last_name', $name_parts[1] );
		}
	}

	return get_user_by( 'id', $user_id );
}

/**
 * Whether this user has set an optional password for the "ورود با رمز
 * عبور" path (account-details page sets this flag when they do).
 */
function stocksystem_user_has_password_login( $user_id ) {
	return (bool) get_user_meta( $user_id, '_has_set_password', true );
}

/**
 * Once an OTP-created account's random placeholder password gets
 * replaced with a real one (see form-edit-account.php override), stop
 * treating it as a placeholder — from here on WooCommerce's normal
 * "enter your current password to change it" rule applies, as it
 * should for a password that's actually theirs.
 */
function stocksystem_clear_otp_temp_password_flag( $user_id ) {
	if ( get_user_meta( $user_id, '_otp_temp_password', true ) ) {
		delete_user_meta( $user_id, '_otp_temp_password' );
		update_user_meta( $user_id, '_has_set_password', true );
	}
}
add_action( 'woocommerce_save_account_details', 'stocksystem_clear_otp_temp_password_flag' );

/**
 * form-edit-account.php intentionally drops two fields WooCommerce's own
 * save handler (WC_Form_Handler::save_account_details(), hooked on
 * wp_loaded at priority 20) hard-requires directly in $_POST, with no
 * fallback to the existing value:
 *
 *  - account_email: real email is optional for an OTP account (the
 *    placeholder "{phone}@customers.stocksystem.local" satisfies
 *    WordPress's own non-empty-email rule) — shown blank in the UI so
 *    the placeholder doesn't look like a real address, so submits empty
 *    if untouched.
 *  - account_display_name: this form uses first/last name instead and
 *    has no separate "display name" field at all, so it's never in
 *    $_POST to begin with.
 *
 * Both fixed the same way: filled in from the current account
 * (email) or first+last name (display name) before WooCommerce reads
 * $_POST, running at an earlier wp_loaded priority.
 */
function stocksystem_backfill_account_details_post() {
	if ( ! is_user_logged_in() || empty( $_POST['save_account_details'] ) ) {
		return;
	}

	$current_user = wp_get_current_user();

	if ( isset( $_POST['account_email'] ) && '' === trim( $_POST['account_email'] ) ) {
		$_POST['account_email'] = $current_user->user_email;
	}

	if ( empty( $_POST['account_display_name'] ) ) {
		$first = ! empty( $_POST['account_first_name'] ) ? wp_unslash( $_POST['account_first_name'] ) : $current_user->first_name;
		$last  = ! empty( $_POST['account_last_name'] ) ? wp_unslash( $_POST['account_last_name'] ) : $current_user->last_name;
		$name  = trim( $first . ' ' . $last );

		$_POST['account_display_name'] = $name ? $name : $current_user->display_name;
	}
}
add_action( 'wp_loaded', 'stocksystem_backfill_account_details_post', 5 );

/**
 * The phone field in the customized form-edit-account.php isn't one
 * WooCommerce's own save handler knows about.
 */
function stocksystem_save_account_phone( $user_id ) {
	if ( ! isset( $_POST['account_phone'] ) ) {
		return;
	}

	$phone = stocksystem_normalize_phone( $_POST['account_phone'] );

	if ( '' === $phone ) {
		return;
	}

	if ( ! stocksystem_is_valid_phone( $phone ) ) {
		wc_add_notice( __( 'شمارهٔ موبایل معتبر نیست.', 'stocksystem' ), 'error' );
		return;
	}

	update_user_meta( $user_id, 'billing_phone', $phone );
}
add_action( 'woocommerce_save_account_details', 'stocksystem_save_account_phone' );
