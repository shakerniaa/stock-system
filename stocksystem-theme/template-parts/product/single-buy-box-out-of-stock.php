<?php
/**
 * Out-of-stock buy box — last known price + notify-me phone form.
 * Source: 15 Product States.dc.html §15-C.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
$waiting = stocksystem_notify_subscriber_count( $product->get_id() );

$notify_result = isset( $_GET['notify_me'] ) ? sanitize_key( wp_unslash( $_GET['notify_me'] ) ) : '';
?>
<span class="buy-box__last-price">
	<?php
	printf(
		/* translators: %s: last known price */
		esc_html__( 'آخرین قیمت: %s تومان', 'stocksystem' ),
		esc_html( stocksystem_format_number( $product->get_price() ) )
	);
	?>
</span>

<p class="buy-box__unavailable-note">
	<?php esc_html_e( 'این مدل ماهی چند بار شارژ می‌شود. شمارهٔ موبایل بگذارید تا لحظهٔ رسیدن پیامک شود.', 'stocksystem' ); ?>
</p>

<?php if ( 'success' === $notify_result ) : ?>
	<p class="buy-box__notify-success"><?php esc_html_e( 'ثبت شد — به‌محضی که موجود شد پیامک می‌دهیم.', 'stocksystem' ); ?></p>
<?php else : ?>
	<form id="notify-me" class="notify-me-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="stocksystem_notify_me">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>">
		<?php wp_nonce_field( 'stocksystem_notify_me', 'stocksystem_notify_nonce' ); ?>
		<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">

		<label class="screen-reader-text" for="notify-me-phone"><?php esc_html_e( 'شماره موبایل', 'stocksystem' ); ?></label>
		<input
			type="tel"
			id="notify-me-phone"
			name="phone"
			class="ltr"
			pattern="09[0-9]{9}"
			placeholder="09xx xxx xxxx"
			required
		>
		<button type="submit" class="btn btn--dark btn--block"><?php esc_html_e( 'اطلاع از موجود شدن', 'stocksystem' ); ?></button>

		<?php if ( 'invalid' === $notify_result ) : ?>
			<span class="notify-me-form__error"><?php esc_html_e( 'شمارهٔ موبایل درست نیست.', 'stocksystem' ); ?></span>
		<?php endif; ?>
	</form>
<?php endif; ?>

<?php if ( $waiting > 0 ) : ?>
	<span class="buy-box__waiting-count">
		<?php
		printf(
			/* translators: %s: waiting subscriber count, Persian digits */
			esc_html__( '%s نفر منتظر این مدل هستند', 'stocksystem' ),
			esc_html( stocksystem_to_persian_digits( $waiting ) )
		);
		?>
	</span>
<?php endif; ?>
