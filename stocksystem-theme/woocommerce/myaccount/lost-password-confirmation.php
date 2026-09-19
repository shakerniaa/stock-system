<?php
/**
 * Lost-password confirmation — shown after the reset link is requested.
 * The wording never confirms whether an account exists.
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="auth-page">
	<div class="auth-card auth-card--center">
		<span class="auth-card__icon" aria-hidden="true">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
		</span>

		<?php do_action( 'woocommerce_before_lost_password_confirmation_message' ); ?>

		<h1 class="auth-card__title"><?php esc_html_e( 'لینک بازیابی ارسال شد', 'stocksystem' ); ?></h1>
		<p class="auth-card__desc"><?php echo esc_html( apply_filters( 'woocommerce_lost_password_confirmation_message', __( 'اگر حسابی با این مشخصات ثبت شده باشد، لینک تغییر رمز به ایمیل آن ارسال شده است. رسیدن ایمیل ممکن است چند دقیقه طول بکشد؛ پوشهٔ اسپم را هم ببینید و پیش از درخواست دوباره حدود ۱۰ دقیقه صبر کنید.', 'stocksystem' ) ) ); ?></p>

		<p class="auth-card__note">
			<?php esc_html_e( 'اگر چیزی نرسید، احتمالاً مشخصات در حساب ثبت نشده است. با پشتیبانی تماس بگیرید:', 'stocksystem' ); ?>
			<a class="ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php echo esc_html( stocksystem_business( 'phone' ) ); ?></a>
		</p>

		<?php do_action( 'woocommerce_after_lost_password_confirmation_message' ); ?>

		<a class="auth-card__back" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">← <?php esc_html_e( 'بازگشت به صفحهٔ ورود', 'stocksystem' ); ?></a>
	</div>
</div>
