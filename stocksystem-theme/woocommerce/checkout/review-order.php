<?php
/**
 * Order review fragment — the aside of the payment step: items, totals,
 * the place-order button, and the payment-security note
 * (12 Checkout Flow.dc.html STEP 03).
 *
 * Root keeps WooCommerce's .woocommerce-checkout-review-order-table class
 * so its AJAX fragment refresh still targets it. #place_order lives here
 * (not in payment.php) so the CTA sits under the total; WooCommerce's JS
 * finds it by id and swaps its label per gateway.
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;

$cart = WC()->cart;

$shipping_total = (float) $cart->get_shipping_total();
if ( $cart->display_prices_including_tax() ) {
	$shipping_total += (float) $cart->get_shipping_tax();
}

$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'ثبت سفارش', 'stocksystem' ) );
?>
<aside class="woocommerce-checkout-review-order-table checkout-summary checkout-review">
	<span class="checkout-summary__title"><?php esc_html_e( 'خلاصهٔ سفارش', 'stocksystem' ); ?></span>

	<div class="checkout-summary-preview__items">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) :
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! $_product instanceof WC_Product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}

			if ( ! apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				continue;
			}
			?>
			<div class="checkout-summary-preview__item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<?php echo wp_kses_post( $_product->get_image( 'thumbnail' ) ); ?>
				<span class="checkout-summary-preview__item-info">
					<span class="checkout-summary-preview__item-name"><?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?></span>
					<span class="checkout-summary-preview__item-meta">
						<?php echo esc_html( stocksystem_to_persian_digits( $cart_item['quantity'] ) ); ?> <?php esc_html_e( 'عدد', 'stocksystem' ); ?> ·
						<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_subtotal', $cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ) ); ?>
					</span>
				</span>
			</div>
		<?php endforeach; ?>

		<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>
	</div>

	<div class="checkout-summary__lines">
		<span class="cart-subtotal"><span><?php esc_html_e( 'جمع کالاها', 'stocksystem' ); ?></span><span><?php wc_cart_totals_subtotal_html(); ?></span></span>

		<?php foreach ( $cart->get_coupons() as $code => $coupon ) : ?>
			<span class="checkout-summary__discount cart-discount"><span><?php echo esc_html( wc_cart_totals_coupon_label( $coupon, false ) ); ?></span><span>−<?php wc_cart_totals_coupon_html( $coupon ); ?></span></span>
		<?php endforeach; ?>

		<?php if ( $cart->needs_shipping() && $cart->show_shipping() ) : ?>
			<span class="shipping"><span><?php esc_html_e( 'هزینهٔ ارسال', 'stocksystem' ); ?></span><span><?php echo $shipping_total > 0 ? wp_kses_post( wc_price( $shipping_total ) ) : esc_html__( 'رایگان', 'stocksystem' ); ?></span></span>
		<?php endif; ?>

		<?php foreach ( $cart->get_fees() as $fee ) : ?>
			<span class="fee"><span><?php echo esc_html( $fee->name ); ?></span><span><?php wc_cart_totals_fee_html( $fee ); ?></span></span>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! $cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( $cart->get_tax_totals() as $code => $tax ) : ?>
					<span class="tax-rate"><span><?php echo esc_html( $tax->label ); ?></span><span><?php echo wp_kses_post( $tax->formatted_amount ); ?></span></span>
				<?php endforeach; ?>
			<?php else : ?>
				<span class="tax-total"><span><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></span><span><?php wc_cart_totals_taxes_total_html(); ?></span></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<div class="checkout-summary__total order-total">
		<span><?php esc_html_e( 'مبلغ قابل پرداخت', 'stocksystem' ); ?></span>
		<span class="checkout-summary__total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	<div class="form-row place-order">
		<noscript>
			<?php esc_html_e( 'مرورگر شما جاوااسکریپت را پشتیبانی نمی‌کند؛ پیش از ثبت سفارش، دکمهٔ «به‌روزرسانی مجموع» را بزنید.', 'stocksystem' ); ?>
			<br><button type="submit" class="btn btn--outline" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'به‌روزرسانی مجموع', 'stocksystem' ); ?>"><?php esc_html_e( 'به‌روزرسانی مجموع', 'stocksystem' ); ?></button>
		</noscript>

		<?php wc_get_template( 'checkout/terms.php' ); ?>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<?php
		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'woocommerce_order_button_html',
			'<button type="submit" class="btn btn--primary btn--block" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">' . esc_html( $order_button_text ) . '</button>'
		);
		?>

		<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>

	<div class="checkout-summary__secure">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="10" width="16" height="10" rx="2"></rect><path d="M8 10V7a4 4 0 118 0v3"></path></svg>
		<span><?php esc_html_e( 'تراکنش روی بستر SSL و شاپرک انجام می‌شود. در صورت قطع ارتباط، مبلغ حداکثر ۷۲ ساعت کاری برمی‌گردد.', 'stocksystem' ); ?></span>
	</div>
</aside>
