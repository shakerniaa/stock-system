<?php
/**
 * Texts that JavaScript shows (toasts, form errors, button states).
 *
 * They are written as translatable strings here so the «متن‌های سایت» admin
 * page (inc/site-texts.php) lists and edits them like every other text, then
 * handed to the front end as `window.stocksystemText`; `window.stocksystemT
 * ( key, fallback )` reads one (fallback = the built-in text, so scripts keep
 * working if this list is ever out of sync). `%s` is replaced by the caller.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_js_strings() {
	return array(
		// Shared.
		'network_error'          => __( 'ارتباط با سرور برقرار نشد. اینترنت خود را بررسی کنید و دوباره تلاش کنید.', 'stocksystem' ),
		'generic_error'          => __( 'مشکلی پیش آمد، دوباره تلاش کنید', 'stocksystem' ),
		'offline_error'          => __( 'اتصال برقرار نشد. اتصال اینترنت را بررسی کنید.', 'stocksystem' ),

		// Login with one-time code.
		'otp_get_code'           => __( 'دریافت کد ورود', 'stocksystem' ),
		'otp_sending'            => __( 'در حال ارسال…', 'stocksystem' ),
		'otp_resend'             => __( 'ارسال مجدد کد', 'stocksystem' ),
		'otp_resend_wait'        => __( 'ارسال مجدد کد (%s)', 'stocksystem' ),
		'otp_sent_before'        => __( 'کد ۴ رقمی به شمارهٔ', 'stocksystem' ),
		'otp_sent_after'         => __( 'پیامک شد.', 'stocksystem' ),
		'otp_phone_invalid'      => __( 'شمارهٔ موبایل معتبر نیست. آن را به شکل ۰۹۱۲۳۴۵۶۷۸۹ (۱۱ رقم) وارد کنید.', 'stocksystem' ),
		'otp_new_code'           => __( 'کد جدید ارسال شد. فقط کد آخر معتبر است.', 'stocksystem' ),
		'otp_code_incomplete'    => __( 'کد ۴ رقمی را کامل وارد کنید.', 'stocksystem' ),
		'otp_login'              => __( 'ورود', 'stocksystem' ),
		'otp_verifying'          => __( 'در حال بررسی…', 'stocksystem' ),
		'otp_done'               => __( 'ورود انجام شد…', 'stocksystem' ),
		'otp_use_password'       => __( 'ورود با رمز عبور', 'stocksystem' ),
		'otp_cancel_password'    => __( 'انصراف از ورود با رمز', 'stocksystem' ),

		// Cart / mini-cart.
		'added_to_cart'          => __( 'به سبد اضافه شد', 'stocksystem' ),
		'cart_item_removed'      => __( '«%s» از سبد حذف شد', 'stocksystem' ),
		'cart_removed'           => __( 'از سبد حذف شد', 'stocksystem' ),
		'cart_remove_failed'     => __( 'حذف انجام نشد؛ دوباره تلاش کنید.', 'stocksystem' ),

		// Wishlist.
		'wishlist_added'         => __( 'به علاقه‌مندی‌ها اضافه شد', 'stocksystem' ),
		'wishlist_removed'       => __( 'از علاقه‌مندی‌ها حذف شد', 'stocksystem' ),
		'wishlist_remove_failed' => __( 'حذف انجام نشد، دوباره تلاش کنید.', 'stocksystem' ),
		'wishlist_count'         => __( '%s کالا ذخیره شده', 'stocksystem' ),

		// Checkout / product page.
		'recipient_incomplete'   => __( 'مشخصات و نشانی گیرندهٔ سفارش را کامل کنید.', 'stocksystem' ),
		'addons_total'           => __( 'قیمت با افزودنی‌های انتخابی: %s تومان', 'stocksystem' ),
	);
}

/** Ships the map to the front end before any theme script runs. */
function stocksystem_print_js_strings() {
	$json = wp_json_encode( stocksystem_js_strings(), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP );
	?>
<script id="stocksystem-text">window.stocksystemText=<?php echo $json; // phpcs:ignore WordPress.Security.EscapeOutput ?>;window.stocksystemT=function(k,f){var s=window.stocksystemText;return s&&s[k]?s[k]:f;};</script>
	<?php
}
add_action( 'wp_head', 'stocksystem_print_js_strings', 1 );

/**
 * Texts that CSS prints (`content:`) — form validation messages. Exposed as
 * custom properties so they can be edited like every other text.
 */
function stocksystem_css_strings() {
	return array(
		'msg-invalid'        => __( 'مقدار واردشده معتبر نیست؛ آن را بررسی کنید.', 'stocksystem' ),
		'msg-required'       => __( 'این فیلد الزامی است.', 'stocksystem' ),
		'msg-email'          => __( 'ایمیل معتبر نیست؛ نمونه: name@example.com', 'stocksystem' ),
		'msg-phone'          => __( 'شمارهٔ موبایل باید ۱۱ رقم و با ۰۹ شروع شود.', 'stocksystem' ),
		'msg-postcode'       => __( 'کد پستی باید ۱۰ رقم باشد.', 'stocksystem' ),
		'msg-checkout-error' => __( 'ثبت سفارش انجام نشد — موارد زیر نیاز به اصلاح دارند', 'stocksystem' ),
	);
}

function stocksystem_print_css_strings() {
	$css = '';
	foreach ( stocksystem_css_strings() as $name => $text ) {
		$css .= '--' . $name . ":'" . addcslashes( $text, "'\\\n\r" ) . "';";
	}
	echo '<style id="stocksystem-text-css">:root{' . $css . '}</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
add_action( 'wp_head', 'stocksystem_print_css_strings', 1 );
