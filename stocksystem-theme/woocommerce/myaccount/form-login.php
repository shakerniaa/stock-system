<?php
/**
 * Login/registration — decision #3: OTP is the default and unified
 * login+signup mechanism (a phone number that doesn't have an account
 * yet just gets one created on successful code verification — see
 * stocksystem_find_or_create_user_by_phone() in inc/otp-auth.php), so
 * this deliberately isn't the design's three separate login/register/
 * forgot-password cards: one OTP card serves both returning and new
 * customers. Password login stays available as a secondary option
 * (business customers who set one), and "forgot password" is only
 * relevant to that path — linked out to WooCommerce's own
 * lost-password endpoint rather than duplicated here.
 * Source: 16 Shop Pages.dc.html §16-C.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_user_logged_in() ) {
	return;
}
?>
<div class="auth-page">
	<div class="auth-card">
		<span class="auth-card__eyebrow"><?php esc_html_e( 'ورود یا ثبت‌نام', 'stocksystem' ); ?></span>
		<h1 class="auth-card__title"><?php esc_html_e( 'ورود به حساب', 'stocksystem' ); ?></h1>
		<p class="auth-card__desc"><?php esc_html_e( 'شمارهٔ موبایل را وارد کنید؛ کد ورود پیامک می‌شود. اگر حساب نداشته باشید، همین‌جا برایتان ساخته می‌شود.', 'stocksystem' ); ?></p>

		<form class="otp-form" id="otp-form" data-nonce="<?php echo esc_attr( wp_create_nonce( 'stocksystem_otp' ) ); ?>">
			<div class="otp-form__step" id="otp-step-phone">
				<label for="otp-phone"><?php esc_html_e( 'شمارهٔ موبایل', 'stocksystem' ); ?></label>
				<input type="tel" id="otp-phone" name="phone" class="ltr" placeholder="0912 345 6789" required pattern="09[0-9]{9}">

				<label for="otp-name" class="otp-form__optional-label">
					<?php esc_html_e( 'نام و نام خانوادگی', 'stocksystem' ); ?>
					<span><?php esc_html_e( '(فقط برای حساب‌های تازه)', 'stocksystem' ); ?></span>
				</label>
				<input type="text" id="otp-name" name="name" placeholder="<?php esc_attr_e( 'مثلاً رضا کاظمی', 'stocksystem' ); ?>">

				<button type="button" class="btn btn--primary btn--block" id="otp-request-btn"><?php esc_html_e( 'دریافت کد ورود', 'stocksystem' ); ?></button>
			</div>

			<div class="otp-form__step" id="otp-step-code" hidden>
				<p class="otp-form__sent-to"></p>
				<label for="otp-code"><?php esc_html_e( 'کد ۴ رقمی', 'stocksystem' ); ?></label>
				<input type="text" id="otp-code" name="code" class="ltr" inputmode="numeric" maxlength="4" pattern="[0-9]{4}">
				<button type="button" class="btn btn--primary btn--block" id="otp-verify-btn"><?php esc_html_e( 'ورود', 'stocksystem' ); ?></button>
				<button type="button" class="otp-form__back" id="otp-back-btn"><?php esc_html_e( '← اصلاح شماره', 'stocksystem' ); ?></button>
			</div>

			<p class="otp-form__error" id="otp-error" role="alert" hidden></p>
		</form>

		<span class="auth-card__divider"><span></span><?php esc_html_e( 'یا', 'stocksystem' ); ?><span></span></span>

		<button type="button" class="btn btn--outline btn--block" id="password-login-toggle"><?php esc_html_e( 'ورود با رمز عبور', 'stocksystem' ); ?></button>

		<div class="password-login" id="password-login-form" hidden>
			<?php do_action( 'woocommerce_before_customer_login_form' ); ?>
			<form class="woocommerce-form woocommerce-form-login login" method="post">
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'شمارهٔ موبایل یا ایمیل', 'stocksystem' ); ?></label>
					<input type="text" class="input-text" name="username" id="username" autocomplete="username">
				</p>
				<p class="form-row form-row-wide">
					<label for="password"><?php esc_html_e( 'رمز عبور', 'stocksystem' ); ?></label>
					<input type="password" class="input-text" name="password" id="password" autocomplete="current-password">
				</p>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<p class="form-row">
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button type="submit" class="btn btn--primary btn--block" name="login" value="<?php esc_attr_e( 'ورود', 'stocksystem' ); ?>"><?php esc_html_e( 'ورود', 'stocksystem' ); ?></button>
				</p>

				<p class="form-row auth-card__lost-password">
					<a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php esc_html_e( 'رمز عبور را فراموش کرده‌اید؟', 'stocksystem' ); ?></a>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>
			</form>
			<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
		</div>
	</div>
</div>
