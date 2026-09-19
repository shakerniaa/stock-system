<?php
/**
 * Account details form — restyled, plus a phone field (the real identity
 * field for OTP accounts) and a fix for a real gap the OTP flow opens up:
 * WooCommerce always demands the *current* password before accepting a
 * new one, but an OTP-created account's password is random gibberish
 * nobody — including the account's owner — ever knew. If they haven't
 * set a real password yet (`_otp_temp_password` still set, see
 * inc/otp-auth.php), that value is submitted as a hidden field instead
 * of asking them to type a password they were never given.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_edit_account_form' );

$otp_temp_password = get_user_meta( $user->ID, '_otp_temp_password', true );
?>

<div class="account-panel__header">
	<h2><?php esc_html_e( 'جزئیات حساب', 'stocksystem' ); ?></h2>
</div>

<form class="woocommerce-EditAccountForm edit-account account-edit-form" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?>>

	<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

	<p class="form-row form-row-first">
		<label for="account_first_name"><?php esc_html_e( 'نام', 'stocksystem' ); ?></label>
		<input type="text" class="input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>">
	</p>
	<p class="form-row form-row-last">
		<label for="account_last_name"><?php esc_html_e( 'نام خانوادگی', 'stocksystem' ); ?></label>
		<input type="text" class="input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>">
	</p>

	<p class="form-row form-row-wide">
		<label for="account_phone"><?php esc_html_e( 'شمارهٔ موبایل', 'stocksystem' ); ?></label>
		<input type="tel" class="input-text ltr" name="account_phone" id="account_phone" autocomplete="tel" pattern="09[0-9]{9}" value="<?php echo esc_attr( get_user_meta( $user->ID, 'billing_phone', true ) ); ?>">
	</p>

	<p class="form-row form-row-wide">
		<label for="account_email"><?php esc_html_e( 'ایمیل', 'stocksystem' ); ?> <span class="account-edit-form__optional">(<?php esc_html_e( 'اختیاری', 'stocksystem' ); ?>)</span></label>
		<input type="email" class="input-text ltr" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( str_ends_with( $user->user_email, STOCKSYSTEM_OTP_PLACEHOLDER_EMAIL_DOMAIN ) ? '' : $user->user_email ); ?>">
	</p>

	<?php do_action( 'woocommerce_edit_account_form_fields' ); ?>

	<fieldset class="account-edit-form__password">
		<legend>
			<?php echo $otp_temp_password ? esc_html__( 'تعیین رمز عبور (اختیاری)', 'stocksystem' ) : esc_html__( 'تغییر رمز عبور', 'stocksystem' ); ?>
		</legend>

		<?php if ( $otp_temp_password ) : ?>
			<p class="account-edit-form__password-hint"><?php esc_html_e( 'چون تا الان با کد پیامکی وارد شده‌اید، برای اولین‌بار نیازی به رمز فعلی نیست.', 'stocksystem' ); ?></p>
			<input type="hidden" name="password_current" value="<?php echo esc_attr( $otp_temp_password ); ?>">
		<?php else : ?>
			<p class="form-row form-row-wide">
				<label for="password_current"><?php esc_html_e( 'رمز فعلی', 'stocksystem' ); ?></label>
				<input type="password" class="input-text" name="password_current" id="password_current" autocomplete="current-password">
			</p>
		<?php endif; ?>

		<p class="form-row form-row-wide">
			<label for="password_1"><?php esc_html_e( 'رمز جدید', 'stocksystem' ); ?></label>
			<input type="password" class="input-text" name="password_1" id="password_1" autocomplete="new-password">
		</p>
		<p class="form-row form-row-wide">
			<label for="password_2"><?php esc_html_e( 'تکرار رمز جدید', 'stocksystem' ); ?></label>
			<input type="password" class="input-text" name="password_2" id="password_2" autocomplete="new-password">
		</p>
	</fieldset>

	<?php do_action( 'woocommerce_edit_account_form' ); ?>

	<p>
		<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
		<button type="submit" class="btn btn--primary" name="save_account_details" value="<?php esc_attr_e( 'ذخیرهٔ تغییرات', 'stocksystem' ); ?>"><?php esc_html_e( 'ذخیرهٔ تغییرات', 'stocksystem' ); ?></button>
		<input type="hidden" name="action" value="save_account_details">
	</p>

	<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
</form>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
