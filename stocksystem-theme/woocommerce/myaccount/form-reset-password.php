<?php
/**
 * Set a new password (arrived from the emailed reset link).
 *
 * @package StockSystem
 *
 * @var array $args key, login
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="auth-page">
	<div class="auth-card">
		<?php do_action( 'woocommerce_before_reset_password_form' ); ?>

		<span class="auth-card__eyebrow"><?php esc_html_e( 'بازیابی رمز', 'stocksystem' ); ?></span>
		<h1 class="auth-card__title"><?php esc_html_e( 'رمز عبور جدید', 'stocksystem' ); ?></h1>
		<p class="auth-card__desc"><?php echo esc_html( apply_filters( 'woocommerce_reset_password_message', __( 'یک رمز عبور جدید انتخاب کنید و آن را یک بار دیگر تکرار کنید.', 'stocksystem' ) ) ); ?></p>

		<form method="post" class="auth-form woocommerce-ResetPassword lost_reset_password">
			<div class="auth-field">
				<label for="password_1"><?php esc_html_e( 'رمز عبور جدید', 'stocksystem' ); ?></label>
				<input type="password" name="password_1" id="password_1" autocomplete="new-password" required aria-required="true">
			</div>
			<div class="auth-field">
				<label for="password_2"><?php esc_html_e( 'تکرار رمز عبور جدید', 'stocksystem' ); ?></label>
				<input type="password" name="password_2" id="password_2" autocomplete="new-password" required aria-required="true">
			</div>

			<input type="hidden" name="reset_key" value="<?php echo esc_attr( $args['key'] ); ?>">
			<input type="hidden" name="reset_login" value="<?php echo esc_attr( $args['login'] ); ?>">

			<?php do_action( 'woocommerce_resetpassword_form' ); ?>

			<input type="hidden" name="wc_reset_password" value="true">
			<?php wp_nonce_field( 'reset_password', 'woocommerce-reset-password-nonce' ); ?>
			<button type="submit" class="btn btn--primary btn--block" value="<?php esc_attr_e( 'ذخیرهٔ رمز جدید', 'stocksystem' ); ?>"><?php esc_html_e( 'ذخیرهٔ رمز جدید', 'stocksystem' ); ?></button>
		</form>

		<?php do_action( 'woocommerce_after_reset_password_form' ); ?>
	</div>
</div>
