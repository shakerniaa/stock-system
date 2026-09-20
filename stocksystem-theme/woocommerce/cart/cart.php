<?php
/**
 * Cart — stage 1 of the decision #2 wizard. Source: 04 Cart
 * Checkout.dc.html (line-item/summary card patterns only — its overall
 * single-page structure is obsolete per decision #2).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<div class="checkout-page">
	<?php get_template_part( 'template-parts/checkout/step-indicator', null, array( 'current' => 1 ) ); ?>
	<div class="container">

		<?php do_action( 'woocommerce_before_cart' ); // Notices (coupon result, removed-item undo) sit inside the page frame. ?>

		<h1 class="checkout-page__title">
			<?php
			printf(
				/* translators: %s: cart item count, Persian digits */
				esc_html__( '%s کالا در سبد', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( WC()->cart->get_cart_contents_count() ) )
			);
			?>
		</h1>

		<div class="checkout-page__grid">
			<div class="checkout-page__main">
				<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
					<?php do_action( 'woocommerce_before_cart_table' ); ?>

					<div class="cart-lines">
						<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) : ?>
							<?php get_template_part( 'template-parts/cart/line-item', null, array( 'cart_item_key' => $cart_item_key, 'cart_item' => $cart_item ) ); ?>
						<?php endforeach; ?>
					</div>

					<?php do_action( 'woocommerce_after_cart_table' ); ?>

					<div class="cart-actions">
						<a href="<?php echo esc_url( $shop_url ); ?>" class="cart-actions__continue">← <?php esc_html_e( 'ادامهٔ خرید', 'stocksystem' ); ?></a>
						<button type="submit" class="btn btn--outline" name="update_cart" value="<?php esc_attr_e( 'به‌روزرسانی سبد', 'stocksystem' ); ?>"><?php esc_html_e( 'به‌روزرسانی سبد', 'stocksystem' ); ?></button>
					</div>

					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</form>
			</div>

			<aside class="checkout-summary">
				<span class="checkout-summary__title"><?php esc_html_e( 'خلاصهٔ سفارش', 'stocksystem' ); ?></span>

				<?php if ( wc_coupons_enabled() ) : ?>
					<form class="cart-coupon-form" method="post" action="<?php echo esc_url( wc_get_cart_url() ); ?>">
						<label class="screen-reader-text" for="coupon_code"><?php esc_html_e( 'کد تخفیف', 'stocksystem' ); ?></label>
						<input type="text" name="coupon_code" id="coupon_code" class="ltr" placeholder="<?php esc_attr_e( 'کد تخفیف', 'stocksystem' ); ?>">
						<button type="submit" class="btn btn--dark" name="apply_coupon" value="<?php esc_attr_e( 'اعمال کد', 'stocksystem' ); ?>"><?php esc_html_e( 'اعمال کد', 'stocksystem' ); ?></button>
					</form>
					<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
						<div class="cart-coupon-applied">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"></path></svg>
							<?php echo esc_html( wc_cart_totals_coupon_label( $coupon ) ); ?>
							<a href="<?php echo esc_url( stocksystem_remove_coupon_url( $code ) ); ?>"><?php esc_html_e( 'حذف', 'stocksystem' ); ?></a>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<div class="checkout-summary__lines">
					<span><span><?php esc_html_e( 'جمع کالاها', 'stocksystem' ); ?></span><span><?php wc_cart_totals_subtotal_html(); ?></span></span>
					<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
						<span class="checkout-summary__discount"><span><?php echo esc_html( wc_cart_totals_coupon_label( $coupon ) ); ?></span><span><?php echo wp_kses_post( stocksystem_coupon_discount_html( $coupon ) ); ?></span></span>
					<?php endforeach; ?>
					<span><span><?php esc_html_e( 'هزینهٔ ارسال', 'stocksystem' ); ?></span><span class="checkout-summary__muted"><?php esc_html_e( 'در تسویه‌حساب محاسبه می‌شود', 'stocksystem' ); ?></span></span>
				</div>

				<div class="checkout-summary__total">
					<span><?php esc_html_e( 'مبلغ قابل پرداخت', 'stocksystem' ); ?></span>
					<span class="checkout-summary__total-amount"><?php wc_cart_totals_order_total_html(); ?></span>
				</div>

				<a href="<?php echo esc_url( $checkout_url ); ?>" class="btn btn--primary btn--block"><?php esc_html_e( 'ادامه به اطلاعات و ارسال', 'stocksystem' ); ?></a>
				<span class="checkout-summary__terms"><?php esc_html_e( 'با ادامهٔ خرید، قوانین و شرایط استوک سیستم را می‌پذیرید.', 'stocksystem' ); ?></span>
			</aside>
		</div>

		<div class="mobile-summary-bar">
			<span class="mobile-summary-bar__total">
				<span class="mobile-summary-bar__label"><?php esc_html_e( 'قابل پرداخت', 'stocksystem' ); ?></span>
				<span class="mobile-summary-bar__amount"><?php wc_cart_totals_order_total_html(); ?></span>
			</span>
			<a href="<?php echo esc_url( $checkout_url ); ?>" class="btn btn--primary mobile-summary-bar__cta"><?php esc_html_e( 'ادامه به اطلاعات و ارسال', 'stocksystem' ); ?></a>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
