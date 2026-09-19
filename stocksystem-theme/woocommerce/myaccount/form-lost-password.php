<?php
/**
 * Lost password — 16 Shop Pages.dc.html §16-C "بازیابی رمز". Same card as
 * the login (OTP is the primary way in, so this page also points back at
 * it: most customers have no password to forget).
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="auth-page">
	<div class="auth-card">
		<?php do_action( 'woocommerce_before_lost_password_form' ); ?>

		<span class="auth-card__eyebrow"><?php esc_html_e( 'بازیابی رمز', 'stocksystem' ); ?></span>
		<h1 class="auth-card__title"><?php esc_html_e( 'رمز را فراموش کرده‌اید؟', 'stocksystem' ); ?></h1>
		<p class="auth-card__desc"><?php esc_html_e( 'شمارهٔ موبایل یا ایمیل حسابتان را وارد کنید. لینک تغییر رمز برایتان فرستاده می‌شود و تا ۱۵ دقیقه معتبر است.', 'stocksystem' ); ?></p>

		<form method="post" class="auth-form woocommerce-ResetPassword lost_reset_password">
			<div class="auth-field">
				<label for="user_login"><?php esc_html_e( 'موبایل یا ایمیل', 'stocksystem' ); ?></label>
				<input class="ltr" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true" value="<?php echo ! empty( $_POST['user_login'] ) ? esc_attr( wp_unslash( $_POST['user_login'] ) ) : ''; ?>">
			</div>

			<?php do_action( 'woocommerce_lostpassword_form' ); ?>

			<input type="hidden" name="wc_reset_password" value="true">
			<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
			<button type="submit" class="btn btn--primary btn--block" value="<?php esc_attr_e( 'ارسال لینک بازیابی', 'stocksystem' ); ?>"><?php esc_html_e( 'ارسال لینک بازیابی', 'stocksystem' ); ?></button>
		</form>

		<p class="auth-card__note">
			<?php esc_html_e( 'با شمارهٔ موبایل و کد پیامکی وارد می‌شوید؟ برای ورود به رمز نیازی ندارید.', 'stocksystem' ); ?>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php esc_html_e( 'ورود با کد پیامکی', 'stocksystem' ); ?></a>
		</p>

		<a class="auth-card__back" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>">← <?php esc_html_e( 'بازگشت به صفحهٔ ورود', 'stocksystem' ); ?></a>

		<?php do_action( 'woocommerce_after_lost_password_form' ); ?>
	</div>
</div>
