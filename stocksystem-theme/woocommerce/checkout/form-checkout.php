<?php
/**
 * Checkout — stages 2 ("اطلاعات و ارسال") and 3 ("پرداخت") of decision
 * #2's wizard, as a JS progressive-reveal on ONE page/ONE form (not
 * separate URLs) — WooCommerce's checkout AJAX (update_checkout,
 * payment method switching) is wired to the whole form regardless of
 * which step is visually hidden via CSS, so nothing about WC's own
 * mechanics needs to change, only what's shown at once.
 *
 * Simplification: WooCommerce couples shipping-*method* selection with
 * the order-review/payment block (review-order.php), not with the
 * billing address form — splitting that apart would mean overriding
 * review-order.php too and fighting the AJAX refresh cycle. So "step 2"
 * here is address + order notes, and shipping method choice appears
 * alongside payment in "step 3" — still a coherent flow (you provide an
 * address, then see shipping options/cost together with how you'll pay).
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
	<div class="container">
		<?php get_template_part( 'template-parts/checkout/step-indicator', null, array( 'current' => 2 ) ); ?>
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
				<div class="checkout-page__grid">
					<div class="checkout-page__main">
						<span class="checkout-page__section-title"><?php esc_html_e( 'روش ارسال و پرداخت', 'stocksystem' ); ?></span>
						<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
						<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>
						<div id="order_review" class="woocommerce-checkout-review-order">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
						<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
						<button type="button" class="btn btn--outline" id="checkout-back-to-info">← <?php esc_html_e( 'بازگشت به اطلاعات', 'stocksystem' ); ?></button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
