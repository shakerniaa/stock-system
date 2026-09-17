<?php
/**
 * No-price / B2B quote-request buy box (state 11). Source:
 * 15 Product States.dc.html §15-C.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
$result  = isset( $_GET['quote_request'] ) ? sanitize_key( wp_unslash( $_GET['quote_request'] ) ) : '';
?>
<span class="buy-box__quote-label"><?php esc_html_e( 'قیمت با استعلام', 'stocksystem' ); ?></span>

<p class="buy-box__unavailable-note">
	<?php esc_html_e( 'قیمت بستگی به تعداد، پیکربندی و شرایط پرداخت دارد. کارشناس فروش سازمانی ظرف یک روز کاری تماس می‌گیرد.', 'stocksystem' ); ?>
</p>

<?php if ( 'success' === $result ) : ?>
	<p class="buy-box__notify-success"><?php esc_html_e( 'درخواست شما ثبت شد — به‌زودی با شما تماس می‌گیریم.', 'stocksystem' ); ?></p>
<?php else : ?>
	<form id="request-quote" class="quote-request-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="stocksystem_quote_request">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php wp_nonce_field( 'stocksystem_quote_request', 'stocksystem_quote_nonce' ); ?>
		<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">

		<label class="screen-reader-text" for="quote-org-name"><?php esc_html_e( 'نام سازمان', 'stocksystem' ); ?></label>
		<input type="text" id="quote-org-name" name="org_name" placeholder="<?php esc_attr_e( 'نام سازمان', 'stocksystem' ); ?>" required>

		<label class="screen-reader-text" for="quote-device-count"><?php esc_html_e( 'تعداد دستگاه', 'stocksystem' ); ?></label>
		<input type="text" id="quote-device-count" name="device_count" placeholder="<?php esc_attr_e( 'تعداد دستگاه', 'stocksystem' ); ?>">

		<label class="screen-reader-text" for="quote-phone"><?php esc_html_e( 'شماره تماس', 'stocksystem' ); ?></label>
		<input type="tel" id="quote-phone" name="phone" class="ltr" placeholder="09xx xxx xxxx" required>

		<button type="submit" class="btn btn--dark btn--block"><?php esc_html_e( 'درخواست استعلام قیمت', 'stocksystem' ); ?></button>

		<?php if ( 'invalid' === $result ) : ?>
			<span class="notify-me-form__error"><?php esc_html_e( 'لطفاً همهٔ فیلدهای لازم را پر کنید.', 'stocksystem' ); ?></span>
		<?php endif; ?>
	</form>
<?php endif; ?>
