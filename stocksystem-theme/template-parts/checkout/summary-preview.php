<?php
/**
 * Read-only order summary shown next to the address form (checkout step
 * "اطلاعات و ارسال") — shipping cost isn't known yet at this point, so
 * it's shown as a placeholder until step "پرداخت" resolves it via
 * WooCommerce's own totals. Source: 12 Checkout Flow.dc.html §STEP 01.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart = WC()->cart;
?>
<div class="checkout-summary-preview">
	<span class="checkout-summary__title"><?php esc_html_e( 'خلاصهٔ سفارش', 'stocksystem' ); ?></span>

	<div class="checkout-summary-preview__items">
		<?php foreach ( $cart->get_cart() as $cart_item ) : ?>
			<?php $product = $cart_item['data']; ?>
			<div class="checkout-summary-preview__item">
				<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
				<span class="checkout-summary-preview__item-info">
					<span class="checkout-summary-preview__item-name"><?php echo esc_html( $product->get_name() ); ?></span>
					<span class="checkout-summary-preview__item-meta">
						<?php echo esc_html( stocksystem_to_persian_digits( $cart_item['quantity'] ) ); ?> <?php esc_html_e( 'عدد', 'stocksystem' ); ?> ·
						<?php echo esc_html( stocksystem_format_number( $product->get_price() ) ); ?> <?php esc_html_e( 'تومان', 'stocksystem' ); ?>
					</span>
				</span>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="checkout-summary__lines">
		<span><span><?php esc_html_e( 'جمع کالاها', 'stocksystem' ); ?></span><span><?php wc_cart_totals_subtotal_html(); ?></span></span>
		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<span class="checkout-summary__discount"><span><?php echo esc_html( wc_cart_totals_coupon_label( $coupon ) ); ?></span><span>−<?php wc_cart_totals_coupon_html( $coupon ); ?></span></span>
		<?php endforeach; ?>
		<span><span><?php esc_html_e( 'هزینهٔ ارسال', 'stocksystem' ); ?></span><span class="checkout-summary__muted"><?php esc_html_e( 'در گام بعد', 'stocksystem' ); ?></span></span>
	</div>

	<div class="checkout-summary__total">
		<span><?php esc_html_e( 'مبلغ قابل پرداخت', 'stocksystem' ); ?></span>
		<span class="checkout-summary__total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<span class="checkout-summary__terms"><?php esc_html_e( 'با ادامهٔ خرید، قوانین و شرایط استوک سیستم را می‌پذیرید.', 'stocksystem' ); ?></span>
</div>
