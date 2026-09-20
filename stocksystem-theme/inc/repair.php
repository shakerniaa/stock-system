<?php
/**
 * Repair services list + request-form handler. Source: 06 Repair.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service list (name/icon/turnaround/warranty/price-from). Same
 * "fallback placeholder data, filterable" pattern as
 * stocksystem_nav_categories() in inc/nav-data.php — real prices are
 * catalog-like data the client confirms in the content phase, not a
 * single Customizer field like warranty/address/phone. TODO:
 * business-copy — confirm real turnaround/price numbers with the client.
 */
function stocksystem_repair_services_default() {
	$services = array(
		array(
			'name'       => __( 'تعویض نمایشگر لپ‌تاپ', 'stocksystem' ),
			'icon_key'   => 'display',
			'icon'       => '<rect x="2.5" y="4" width="19" height="12.5" rx="2"></rect><path d="M8 20h8"></path>',
			'turnaround' => __( '۲۴ تا ۴۸ ساعت', 'stocksystem' ),
			'warranty'   => __( '۶ ماه', 'stocksystem' ),
			'price_from' => 4200000,
		),
		array(
			'name'       => __( 'تعمیر مادربرد در سطح قطعه', 'stocksystem' ),
			'icon_key'   => 'board',
			'icon'       => '<rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M9 9h6v6H9z"></path>',
			'turnaround' => __( '۳ تا ۷ روز', 'stocksystem' ),
			'warranty'   => __( '۳ ماه', 'stocksystem' ),
			'price_from' => 5500000,
		),
		array(
			'name'       => __( 'سرویس حرارتی و تعویض خمیر', 'stocksystem' ),
			'icon_key'   => 'thermal',
			'icon'       => '<path d="M12 3v6M12 15v6M5 12h14"></path><circle cx="12" cy="12" r="2.5"></circle>',
			'turnaround' => __( 'همان روز', 'stocksystem' ),
			'warranty'   => __( '۳ ماه', 'stocksystem' ),
			'price_from' => 1200000,
		),
		array(
			'name'       => __( 'بازیابی اطلاعات SSD و HDD', 'stocksystem' ),
			'icon_key'   => 'drive',
			'icon'       => '<ellipse cx="12" cy="6" rx="7" ry="3"></ellipse><path d="M5 6v12c0 1.7 3.1 3 7 3s7-1.3 7-3V6"></path>',
			'turnaround' => __( '۲ تا ۱۰ روز', 'stocksystem' ),
			'warranty'   => '',
			'price_from' => null,
		),
	);

	return $services;
}

/**
 * Services shown on the page: the rows saved in Users → «تعمیرات تخصصی»
 * (inc/admin-pages.php), else the defaults above. Still filterable.
 */
function stocksystem_repair_services() {
	$icons    = stocksystem_service_icons();
	$services = array();

	foreach ( stocksystem_opt( 'repair', 'services' ) as $row ) {
		$services[] = array(
			'name'       => $row['name'],
			'icon'       => isset( $icons[ $row['icon'] ] ) ? $icons[ $row['icon'] ]['svg'] : $icons['wrench']['svg'],
			'turnaround' => $row['turnaround'],
			'warranty'   => $row['warranty'],
			'price_from' => ( '' === $row['price_from'] || null === $row['price_from'] ) ? null : (int) $row['price_from'],
		);
	}

	return apply_filters( 'stocksystem_repair_services', $services );
}

/**
 * 4-step process shown next to the hero and again above the form.
 */
function stocksystem_repair_steps_default() {
	return array(
		__( 'ثبت درخواست یا تماس', 'stocksystem' ),
		__( 'تشخیص رایگان و اعلام هزینه', 'stocksystem' ),
		__( 'تعمیر پس از تأیید شما', 'stocksystem' ),
		__( 'تحویل با گارانتی کتبی', 'stocksystem' ),
	);
}

function stocksystem_repair_steps() {
	return array_values( array_filter( wp_list_pluck( stocksystem_opt( 'repair', 'steps' ), 'text' ) ) );
}

/**
 * Repair-request form handler — same "email the store, no CRM/ticketing
 * system chosen yet" honesty as the notify-me and quote-request handlers
 * in inc/product-notify.php and the wallet top-up request in
 * inc/wallet.php.
 */
function stocksystem_handle_repair_request_submission() {
	if ( empty( $_POST['stocksystem_repair_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['stocksystem_repair_nonce'] ), 'stocksystem_repair_request' ) ) {
		wp_die( esc_html__( 'درخواست نامعتبر است.', 'stocksystem' ) );
	}

	$name        = ! empty( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone       = ! empty( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
	$device      = ! empty( $_POST['device'] ) ? sanitize_text_field( wp_unslash( $_POST['device'] ) ) : '';
	$service     = ! empty( $_POST['service'] ) ? sanitize_text_field( wp_unslash( $_POST['service'] ) ) : '';
	$description = ! empty( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';
	$redirect    = ! empty( $_POST['_wp_http_referer'] ) ? wp_unslash( $_POST['_wp_http_referer'] ) : home_url( '/repair/' );

	if ( $name && preg_match( '/^09\d{9}$/', $phone ) ) {
		wp_mail(
			stocksystem_notify_email( 'repair' ),
			stocksystem_opt( 'messages', 'repair_subject' ),
			sprintf(
				/* translators: 1: name, 2: phone, 3: device model, 4: service type, 5: problem description */
				__( "نام: %1\$s\nتلفن: %2\$s\nمدل دستگاه: %3\$s\nنوع خدمت: %4\$s\nشرح مشکل: %5\$s", 'stocksystem' ),
				$name,
				$phone,
				$device,
				$service,
				$description
			)
		);
		$redirect = add_query_arg( 'repair_request', 'success', $redirect );
	} else {
		$redirect = add_query_arg( 'repair_request', 'invalid', $redirect );
	}

	wp_safe_redirect( $redirect . '#repair-request' );
	exit;
}
add_action( 'admin_post_stocksystem_repair_request', 'stocksystem_handle_repair_request_submission' );
add_action( 'admin_post_nopriv_stocksystem_repair_request', 'stocksystem_handle_repair_request_submission' );
