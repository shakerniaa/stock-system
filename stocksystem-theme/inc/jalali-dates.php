<?php
/**
 * Jalali (Solar Hijri) dates everywhere WordPress formats a date for
 * people to read — the parts the theme does not render itself:
 * WooCommerce emails, WooCommerce's own templates, the admin order and
 * post lists, plugins. The theme's templates already call
 * stocksystem_jdate() directly (inc/persian-numerals.php).
 *
 * Only display formats change: a format is converted when it holds a
 * month/weekday name (F M l D), is a slash date (Y/m/d), or is the site's
 * own date format. Machine formats
 * ('Y-m-d', 'c', 'U', 'Y-m-d H:i:s' …) stay Gregorian, so date inputs,
 * sorting, schema, REST and feeds keep working unchanged.
 *
 * Steps aside when a Jalali plugin already does this (WP-Parsidate, or
 * Persian WooCommerce with its «تاریخ شمسی» option on), or when the
 * 'stocksystem_jalali_dates' filter returns false.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formats a timestamp like wp_date(), but with the Jalali calendar and
 * Persian names/digits. Every PHP date character is supported: Jalali
 * ones are computed here, the rest come from DateTime::format().
 *
 * @param string            $format    PHP date format.
 * @param int               $timestamp Unix timestamp.
 * @param DateTimeZone|null $timezone  Defaults to the site time zone.
 */
function stocksystem_jalali_format( $format, $timestamp, $timezone = null ) {
	$timezone = $timezone instanceof DateTimeZone ? $timezone : wp_timezone();
	$datetime = new DateTime( '@' . (int) $timestamp );
	$datetime->setTimezone( $timezone );

	list( $gy, $gm, $gd )  = array_map( 'intval', explode( ' ', $datetime->format( 'Y n j' ) ) );
	list( $jy, $jm, $jd ) = stocksystem_gregorian_to_jalali( $gy, $gm, $gd );

	$months   = array( 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند' );
	$weekdays = array( 'یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه' ); // Indexed by PHP 'w' (0 = Sunday).
	$is_am    = 'am' === $datetime->format( 'a' );

	$map = array(
		'd' => sprintf( '%02d', $jd ),
		'j' => (string) $jd,
		'S' => '',
		'l' => $weekdays[ (int) $datetime->format( 'w' ) ],
		'D' => $weekdays[ (int) $datetime->format( 'w' ) ],
		'm' => sprintf( '%02d', $jm ),
		'n' => (string) $jm,
		'F' => $months[ $jm - 1 ],
		'M' => $months[ $jm - 1 ],
		't' => (string) ( $jm <= 6 ? 31 : ( $jm < 12 ? 30 : ( stocksystem_jalali_is_leap( $jy ) ? 30 : 29 ) ) ),
		'L' => stocksystem_jalali_is_leap( $jy ) ? '1' : '0',
		'Y' => (string) $jy,
		'o' => (string) $jy,
		'y' => sprintf( '%02d', $jy % 100 ),
		'a' => $is_am ? 'ق.ظ' : 'ب.ظ',
		'A' => $is_am ? 'قبل از ظهر' : 'بعد از ظهر',
	);

	$out    = '';
	$chars  = preg_split( '//u', (string) $format, -1, PREG_SPLIT_NO_EMPTY );
	$length = count( $chars );

	for ( $i = 0; $i < $length; $i++ ) {
		$char = $chars[ $i ];

		if ( '\\' === $char ) {
			$i++;
			$out .= $i < $length ? $chars[ $i ] : '';
			continue;
		}

		if ( isset( $map[ $char ] ) ) {
			$out .= $map[ $char ];
		} elseif ( ctype_alpha( $char ) ) {
			$out .= $datetime->format( $char );
		} else {
			$out .= $char;
		}
	}

	return stocksystem_to_persian_digits( $out );
}

/** Jalali leap year, 33-year cycle (1399, 1403, 1408 … are leap). */
function stocksystem_jalali_is_leap( $jy ) {
	return in_array( (int) $jy % 33, array( 1, 5, 9, 13, 17, 22, 26, 30 ), true );
}

/** Whether a date format is meant for people (month name, or the site's own format). */
function stocksystem_jalali_is_display_format( $format ) {
	$format = (string) $format;

	if ( '' === $format || in_array( $format, array( 'r', 'c', 'U', DATE_RSS, DATE_RFC2822, DATE_ATOM, DATE_W3C, DATE_COOKIE ), true ) ) {
		return false;
	}

	if ( $format === get_option( 'date_format' ) ) {
		return true;
	}

	// A month or weekday name (F M l D), or a slash date like the admin
	// lists' «Y/m/d» — not escaped with a backslash. Dash dates (Y-m-d)
	// are left alone: that is the machine/storage style.
	if ( preg_match( '/(?<!\\\\)[FMlD]/', $format ) ) {
		return true;
	}

	return false !== strpos( $format, '/' ) && (bool) preg_match( '/(?<!\\\\)[Yy]/', $format );
}

function stocksystem_jalali_dates_enabled() {
	static $enabled = null;

	if ( null === $enabled ) {
		$enabled = ! defined( 'WP_PARSI_VER' ) && ! function_exists( 'parsidate' );

		// Persian WooCommerce's own Jalali option already converts every date.
		if ( $enabled && class_exists( 'Persian_Woocommerce_Date' ) && function_exists( 'PW' ) ) {
			$enabled = 'yes' !== PW()->get_options( 'enable_jalali_datepicker', 'yes' );
		}
		$enabled = (bool) apply_filters( 'stocksystem_jalali_dates', $enabled );
	}

	return $enabled;
}

add_filter(
	'wp_date',
	function ( $date, $format, $timestamp, $timezone ) {
		static $busy = false;

		if ( $busy || ! is_string( $date ) || ! stocksystem_jalali_dates_enabled() || ! stocksystem_jalali_is_display_format( $format ) ) {
			return $date;
		}

		if ( is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $date;
		}

		$busy = true;
		$date = stocksystem_jalali_format( $format, $timestamp, $timezone );
		$busy = false;

		return $date;
	},
	20,
	4
);
