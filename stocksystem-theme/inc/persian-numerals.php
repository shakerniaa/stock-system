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


/**
 * Gregorian -> Jalali (Solar Hijri) date parts. Standard arithmetic
 * conversion (valid across the 1200s–1400s range), no extension needed.
 *
 * @return int[] [ year, month, day ]
 */
function stocksystem_gregorian_to_jalali( $gy, $gm, $gd ) {
	$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
	$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
	$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
	$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
	$days %= 12053;
	$jy   += 4 * intdiv( $days, 1461 );
	$days %= 1461;

	if ( $days > 365 ) {
		$jy  += intdiv( $days - 1, 365 );
		$days = ( $days - 1 ) % 365;
	}

	if ( $days < 186 ) {
		$jm = 1 + intdiv( $days, 31 );
		$jd = 1 + ( $days % 31 );
	} else {
		$jm = 7 + intdiv( $days - 186, 30 );
		$jd = 1 + ( ( $days - 186 ) % 30 );
	}

	return array( $jy, $jm, $jd );
}

/**
 * Jalali date/time string with Persian-Indic digits — the site's
 * user-facing calendar (design: «۲۳ شهریور ۱۴۰۵»), not WordPress's
 * Gregorian date with translated month names. Supports the format
 * tokens actually used in the theme: j d n m F Y y H i G.
 *
 * @param string   $format    Format string (see above).
 * @param int|null $timestamp Unix timestamp; defaults to now.
 */
function stocksystem_jdate( $format = 'j F Y', $timestamp = null ) {
	$timestamp = null === $timestamp ? time() : (int) $timestamp;
	list( $gy, $gm, $gd, $hh, $ii ) = array_map( 'intval', explode( ' ', wp_date( 'Y n j G i', $timestamp ) ) );
	list( $jy, $jm, $jd )           = stocksystem_gregorian_to_jalali( $gy, $gm, $gd );

	$months = array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );

	$map = array(
		'j' => (string) $jd,
		'd' => sprintf( '%02d', $jd ),
		'n' => (string) $jm,
		'm' => sprintf( '%02d', $jm ),
		'F' => $months[ $jm - 1 ],
		'Y' => (string) $jy,
		'y' => sprintf( '%02d', $jy % 100 ),
		'G' => (string) $hh,
		'H' => sprintf( '%02d', $hh ),
		'i' => sprintf( '%02d', $ii ),
	);

	$out = '';
	foreach ( preg_split( '//u', $format, -1, PREG_SPLIT_NO_EMPTY ) as $char ) {
		$out .= $map[ $char ] ?? $char;
	}

	return stocksystem_to_persian_digits( $out );
}
