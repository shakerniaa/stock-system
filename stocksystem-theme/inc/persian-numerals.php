<?php
/**
 * Persian-Indic numeral formatting for all user-facing numbers.
 * Thousands separator is U+066C (٬) per DECISIONS-v1.1.md — not "," or
 * the Arabic decimal separator "٫", both of which appear inconsistently
 * across the .dc.html files and are wrong.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Convert a Latin numeral string to Persian-Indic digits.
 */
function stocksystem_to_persian_digits( $value ) {
	$latin   = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' );
	$persian = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );

	return str_replace( $latin, $persian, (string) $value );
}

/**
 * Format an integer amount with U+066C thousands grouping and
 * Persian-Indic digits. Use for prices, quantities, and any other
 * user-facing number; leave SKUs, phone numbers, and model names as
 * Latin strings inside dir="ltr" spans instead.
 */
function stocksystem_format_number( $amount ) {
	$grouped = number_format( (float) $amount, 0, '.', "\u{066C}" );

	return stocksystem_to_persian_digits( $grouped );
}
