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
<?php
// A failed password login reloads the page: reopen that form (its errors
// would otherwise sit inside a collapsed block nobody can see).
$password_open = ! empty( $_POST['login'] ) || ! empty( $_POST['username'] );
?>
<div class="auth-page">
	<div class="auth-card">
		<?php do_action( 'woocommerce_before_customer_login_form' ); ?>

		<span class="auth-card__eyebrow"><?php esc_html_e( 'ورود یا ثبت‌نام', 'stocksystem' ); ?></span>
		<h1 class="auth-card__title"><?php esc_html_e( 'ورود به حساب', 'stocksystem' ); ?></h1>
		<p class="auth-card__desc"><?php esc_html_e( 'شمارهٔ موبایل را وارد کنید تا کد ورود برایتان پیامک شود. اگر حساب نداشته باشید، خودکار ساخته می‌شود.', 'stocksystem' ); ?></p>

		<form class="otp-form" id="otp-form" novalidate data-nonce="<?php echo esc_attr( wp_create_nonce( 'stocksystem_otp' ) ); ?>">
			<div class="otp-form__step" id="otp-step-phone">
				<div class="otp-form__field">
					<label for="otp-phone"><?php esc_html_e( 'شمارهٔ موبایل', 'stocksystem' ); ?></label>
					<input type="tel" id="otp-phone" name="phone" class="ltr" placeholder="0912 345 6789" inputmode="numeric" autocomplete="tel" aria-describedby="otp-phone-error">
					<p class="otp-form__error" id="otp-phone-error" role="alert" hidden></p>
				</div>

				<button type="button" class="btn btn--primary btn--block" id="otp-request-btn"><?php esc_html_e( 'دریافت کد ورود', 'stocksystem' ); ?></button>
			</div>

			<div class="otp-form__step" id="otp-step-code" hidden>
				<p class="otp-form__sent-to" aria-live="polite"></p>

				<div class="otp-form__field">
					<label for="otp-code"><?php esc_html_e( 'کد ۴ رقمی', 'stocksystem' ); ?></label>
					<input type="text" id="otp-code" name="code" class="ltr otp-form__code" inputmode="numeric" autocomplete="one-time-code" maxlength="4" placeholder="••••" aria-describedby="otp-code-error">
					<p class="otp-form__error" id="otp-code-error" role="alert" hidden></p>
				</div>

				<button type="button" class="btn btn--primary btn--block" id="otp-verify-btn"><?php esc_html_e( 'ورود', 'stocksystem' ); ?></button>

				<div class="otp-form__actions">
					<button type="button" class="otp-form__link" id="otp-resend-btn" disabled><?php esc_html_e( 'ارسال مجدد کد', 'stocksystem' ); ?></button>
					<button type="button" class="otp-form__link" id="otp-back-btn"><?php esc_html_e( 'اصلاح شماره', 'stocksystem' ); ?></button>
				</div>
				<p class="otp-form__info" id="otp-info" role="status" hidden></p>
			</div>
		</form>

		<span class="auth-card__divider"><span></span><?php esc_html_e( 'یا', 'stocksystem' ); ?><span></span></span>

		<button type="button" class="btn btn--outline btn--block" id="password-login-toggle"><?php echo $password_open ? esc_html__( 'انصراف از ورود با رمز', 'stocksystem' ) : esc_html__( 'ورود با رمز عبور', 'stocksystem' ); ?></button>

		<div class="password-login" id="password-login-form"<?php echo $password_open ? '' : ' hidden'; ?>>
			<form class="woocommerce-form woocommerce-form-login login" method="post">
				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<p class="form-row form-row-wide">
					<label for="username"><?php esc_html_e( 'شمارهٔ موبایل یا ایمیل', 'stocksystem' ); ?></label>
					<input type="text" class="input-text" name="username" id="username" autocomplete="username" value="<?php echo ! empty( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>">
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
