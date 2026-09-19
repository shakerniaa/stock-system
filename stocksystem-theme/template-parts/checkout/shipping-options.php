<?php
/**
 * Shipping-method picker for the payment step — one radio card per rate,
 * replacing the <tr> rows of WooCommerce's cart-shipping.php (which only
 * make sense inside the default review table). Field names/classes are the
 * ones WooCommerce's checkout.js listens to, so changing the choice still
 * fires update_checkout.
 *
 * Rendered inside checkout/payment.php, which sits in a refreshed fragment.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$packages = WC()->shipping()->get_packages();
?>
<section class="checkout-shipping" aria-labelledby="checkout-shipping-title">
	<span id="checkout-shipping-title" class="checkout-page__section-title"><?php esc_html_e( 'روش ارسال', 'stocksystem' ); ?></span>

	<?php foreach ( $packages as $index => $package ) : ?>
		<?php
		$methods = ! empty( $package['rates'] ) ? $package['rates'] : array();
		$chosen  = isset( WC()->session->chosen_shipping_methods[ $index ] ) ? WC()->session->chosen_shipping_methods[ $index ] : '';
		$multi   = count( $methods ) > 1;
		?>

		<?php if ( $methods ) : ?>
			<ul class="checkout-shipping__list">
				<?php foreach ( $methods as $method ) : ?>
					<?php
					$input_id = sprintf( 'shipping_method_%1$d_%2$s', $index, sanitize_title( $method->id ) );
					$cost     = (float) $method->get_cost();
					if ( WC()->cart->display_prices_including_tax() ) {
						$cost += (float) $method->get_shipping_tax();
					}
					?>
					<li class="checkout-shipping__option">
						<?php if ( $multi ) : ?>
							<input type="radio" name="shipping_method[<?php echo esc_attr( $index ); ?>]" data-index="<?php echo esc_attr( $index ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method" <?php checked( $method->id, $chosen ); ?>>
						<?php else : ?>
							<input type="hidden" name="shipping_method[<?php echo esc_attr( $index ); ?>]" data-index="<?php echo esc_attr( $index ); ?>" id="<?php echo esc_attr( $input_id ); ?>" value="<?php echo esc_attr( $method->id ); ?>" class="shipping_method">
						<?php endif; ?>
						<label for="<?php echo esc_attr( $input_id ); ?>">
							<span class="checkout-shipping__name"><?php echo esc_html( $method->get_label() ); ?></span>
							<span class="checkout-shipping__price<?php echo $cost > 0 ? '' : ' is-free'; ?>">
								<?php echo $cost > 0 ? wp_kses_post( wc_price( $cost ) ) : esc_html__( 'رایگان', 'stocksystem' ); ?>
							</span>
						</label>
						<?php do_action( 'woocommerce_after_shipping_rate', $method, $index ); ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php else : ?>
			<p class="checkout-shipping__empty">
				<?php
				echo wp_kses_post(
					apply_filters(
						'woocommerce_no_shipping_available_html',
						__( 'برای این نشانی روش ارسالی تعریف نشده است. نشانی را بررسی کنید یا با ما تماس بگیرید.', 'stocksystem' )
					)
				);
				?>
			</p>
		<?php endif; ?>
	<?php endforeach; ?>
</section>
