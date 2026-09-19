<?php
/**
 * Payment fragment — the main column of the payment step: shipping
 * choices, then payment methods, each as a selectable card
 * (12 Checkout Flow.dc.html STEP 03). The place-order button lives with the
 * order summary instead (checkout/review-order.php), as in the design.
 *
 * Root keeps WooCommerce's #payment / .woocommerce-checkout-payment so its
 * AJAX fragment refresh still targets it.
 *
 * @package StockSystem
 *
 * @var WC_Checkout $checkout
 * @var array       $available_gateways
 * @var string      $order_button_text
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
<div id="payment" class="woocommerce-checkout-payment checkout-payment">
	<?php if ( WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
		<?php get_template_part( 'template-parts/checkout/shipping-options' ); ?>
		<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
	<?php endif; ?>

	<?php if ( WC()->cart && WC()->cart->needs_payment() ) : ?>
		<section class="checkout-methods" aria-labelledby="checkout-methods-title">
			<span id="checkout-methods-title" class="checkout-page__section-title"><?php esc_html_e( 'روش پرداخت', 'stocksystem' ); ?></span>
			<ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					}
				} else {
					echo '<li class="checkout-methods__empty">';
					echo esc_html(
						apply_filters(
							'woocommerce_no_available_payment_methods_message',
							WC()->customer->get_billing_country()
								? __( 'در حال حاضر روش پرداختی برای این سفارش فعال نیست. لطفاً با ما تماس بگیرید.', 'stocksystem' )
								: __( 'برای دیدن روش‌های پرداخت، ابتدا اطلاعات خود را کامل کنید.', 'stocksystem' )
						)
					);
					echo '</li>';
				}
				?>
			</ul>
		</section>
	<?php endif; ?>
</div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
