<?php
/**
 * Checkout — stages 2 ("اطلاعات و ارسال") and 3 ("پرداخت") of decision
 * #2's wizard, as a JS progressive-reveal on ONE page/ONE form (not
 * separate URLs) — WooCommerce's checkout AJAX (update_checkout,
 * payment method switching) is wired to the whole form regardless of
 * which step is visually hidden via CSS, so nothing about WC's own
 * mechanics needs to change, only what's shown at once.
 *
 * Simplification: WooCommerce prices shipping from the address and only
 * refreshes rates through its order-review AJAX, so "step 2" here is
 * address + order notes, and the shipping-method choice sits with payment
 * in "step 3": checkout/payment.php (main column: shipping + payment
 * cards) and checkout/review-order.php (aside: summary + place order).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checkout = WC()->checkout();

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'برای تسویه‌حساب باید وارد حساب کاربری شوید.', 'stocksystem' ) ) );
	return;
}
?>

<div class="checkout-page">
	<?php get_template_part( 'template-parts/checkout/step-indicator', null, array( 'current' => 2 ) ); ?>
	<div class="container">
		<h1 class="checkout-page__title"><?php esc_html_e( 'تسویه‌حساب', 'stocksystem' ); ?></h1>

		<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

			<?php if ( $checkout->get_checkout_fields() ) : ?>
				<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

				<div id="checkout-step-info" class="checkout-step is-active">
					<div class="checkout-page__grid">
						<div class="checkout-page__main">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>
						<aside class="checkout-summary">
							<?php get_template_part( 'template-parts/checkout/summary-preview' ); ?>
							<button type="button" class="btn btn--primary btn--block" id="checkout-continue-to-payment"><?php esc_html_e( 'ادامه به پرداخت', 'stocksystem' ); ?></button>
						</aside>
					</div>
				</div>

				<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
			<?php endif; ?>

			<div id="checkout-step-payment" class="checkout-step" hidden>
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
				<div id="order_review" class="woocommerce-checkout-review-order">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>
				<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
				<button type="button" class="btn btn--outline checkout-back" id="checkout-back-to-info">← <?php esc_html_e( 'بازگشت به اطلاعات', 'stocksystem' ); ?></button>
			</div>
		</form>

		<div class="mobile-summary-bar" data-checkout-bar>
			<span class="mobile-summary-bar__total">
				<span class="mobile-summary-bar__label"><?php esc_html_e( 'قابل پرداخت', 'stocksystem' ); ?></span>
				<span class="mobile-summary-bar__amount" data-checkout-bar-total><?php wc_cart_totals_order_total_html(); ?></span>
			</span>
			<button
				type="button"
				class="btn btn--primary mobile-summary-bar__cta"
				data-checkout-bar-cta
				data-label-continue="<?php esc_attr_e( 'ادامه به پرداخت', 'stocksystem' ); ?>"
				data-label-pay="<?php esc_attr_e( 'ثبت و پرداخت', 'stocksystem' ); ?>"
			><?php esc_html_e( 'ادامه به پرداخت', 'stocksystem' ); ?></button>
		</div>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
