<?php
/**
 * My Account → one order (08 Account.dc.html order detail, 16 §16-D):
 * summary strip, status timeline, item rows, delivery address and totals,
 * plus a pay / track / support action row. Replaces WooCommerce's sentence
 * with <mark> highlights and its bare details table. The default
 * `woocommerce_view_order` action (which prints that table) is deliberately
 * not fired.
 *
 * @package StockSystem
 *
 * @var int           $order_id
 * @var WC_Order|null $order
 * @var array         $notes    Customer-facing order notes.
 */

defined( 'ABSPATH' ) || exit;

$order = isset( $order ) && $order instanceof WC_Order ? $order : wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$notes      = $order->get_customer_order_notes();
$status     = $order->get_status();
$status_cls = 'is-active';
if ( 'completed' === $status ) {
	$status_cls = 'is-done';
} elseif ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
	$status_cls = 'is-bad';
} elseif ( in_array( $status, array( 'pending', 'on-hold' ), true ) ) {
	$status_cls = 'is-wait';
}

$created        = $order->get_date_created();
$shipping_total = (float) $order->get_shipping_total() + (float) $order->get_shipping_tax();
$states         = WC()->countries->get_states( $order->get_billing_country() );
$state_name     = is_array( $states ) && isset( $states[ $order->get_billing_state() ] ) ? $states[ $order->get_billing_state() ] : $order->get_billing_state();
$billing_line   = implode( '، ', array_filter( array( $state_name, $order->get_billing_city(), $order->get_billing_address_1(), $order->get_billing_address_2() ) ) );
$has_shipping   = $order->has_shipping_address() && $order->get_formatted_shipping_address() !== $order->get_formatted_billing_address();
?>
<div class="account-order-detail">
	<div class="account-panel__header">
		<h2>
			<?php esc_html_e( 'سفارش', 'stocksystem' ); ?>
			<span class="ltr"><?php echo esc_html( $order->get_order_number() ); ?></span>
		</h2>
		<span class="account-order__status <?php echo esc_attr( $status_cls ); ?>"><?php echo esc_html( wc_get_order_status_name( $status ) ); ?></span>
	</div>

	<article class="account-order">
		<header class="account-order__head">
			<span class="account-order__meta">
				<span><?php esc_html_e( 'تاریخ ثبت', 'stocksystem' ); ?></span>
				<strong>
					<?php if ( $created ) : ?>
						<bdi><?php echo esc_html( stocksystem_jdate( 'j F Y', $created->getTimestamp() ) ); ?></bdi> · <bdi><?php echo esc_html( stocksystem_jdate( 'H:i', $created->getTimestamp() ) ); ?></bdi>
					<?php else : ?>
						—
					<?php endif; ?>
				</strong>
			</span>
			<?php if ( $order->get_payment_method_title() ) : ?>
				<span class="account-order__meta">
					<span><?php esc_html_e( 'روش پرداخت', 'stocksystem' ); ?></span>
					<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
				</span>
			<?php endif; ?>
			<span class="account-order__meta">
				<span><?php echo $order->is_paid() ? esc_html__( 'پرداخت‌شده', 'stocksystem' ) : esc_html__( 'مبلغ سفارش', 'stocksystem' ); ?></span>
				<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
			</span>
		</header>

		<?php if ( $order->needs_payment() ) : ?>
			<div class="account-order-detail__pay">
				<span><?php esc_html_e( 'پرداخت این سفارش هنوز کامل نشده است.', 'stocksystem' ); ?></span>
				<a class="btn btn--primary" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'پرداخت سفارش', 'stocksystem' ); ?></a>
			</div>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/checkout/order-timeline', null, array( 'order' => $order ) ); ?>

		<div class="account-order__items">
			<span class="account-order-detail__section-title"><?php esc_html_e( 'کالاهای سفارش', 'stocksystem' ); ?></span>
			<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
				<?php $product = $item->get_product(); ?>
				<div class="account-order__item">
					<span class="account-order__thumb"><?php echo $product ? wp_kses_post( $product->get_image( 'thumbnail' ) ) : ''; ?></span>
					<span class="account-order__item-info">
						<?php if ( $product && $product->is_visible() ) : ?>
							<a class="account-order__item-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $item->get_name() ); ?></a>
						<?php else : ?>
							<span class="account-order__item-name"><?php echo esc_html( $item->get_name() ); ?></span>
						<?php endif; ?>
						<span class="account-order__item-meta">
							<?php
							printf(
								/* translators: %s: quantity, Persian digits */
								esc_html__( '%s عدد', 'stocksystem' ),
								esc_html( stocksystem_to_persian_digits( $item->get_quantity() ) )
							);
							?>
						</span>
						<?php echo wp_kses_post( wc_display_item_meta( $item, array( 'echo' => false, 'before' => '<span class="account-order__item-meta">', 'after' => '</span>', 'separator' => ' · ', 'label_before' => '', 'label_after' => ': ' ) ) ); ?>
					</span>
					<span class="account-order__item-price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</article>

	<div class="account-order-detail__grid">
		<section class="account-order-detail__card">
			<span class="account-order-detail__section-title"><?php esc_html_e( 'نشانی تحویل', 'stocksystem' ); ?></span>
			<?php if ( $has_shipping ) : ?>
				<address>
					<?php echo wp_kses_post( $order->get_formatted_shipping_address() ); ?>
					<?php if ( $order->get_shipping_phone() ) : ?>
						<br><span class="ltr"><?php echo esc_html( $order->get_shipping_phone() ); ?></span>
					<?php endif; ?>
				</address>
			<?php else : ?>
				<p><?php echo esc_html( $billing_line ? $billing_line : __( 'نشانی ثبت نشده است.', 'stocksystem' ) ); ?></p>
				<p class="account-order-detail__muted">
					<?php echo esc_html( trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ) ); ?>
					<?php if ( $order->get_billing_phone() ) : ?>
						· <span class="ltr"><?php echo esc_html( $order->get_billing_phone() ); ?></span>
					<?php endif; ?>
					<?php if ( $order->get_billing_postcode() ) : ?>
						· <?php esc_html_e( 'کد پستی', 'stocksystem' ); ?> <span class="ltr"><?php echo esc_html( $order->get_billing_postcode() ); ?></span>
					<?php endif; ?>
				</p>
			<?php endif; ?>
			<?php if ( $order->get_shipping_method() ) : ?>
				<p class="account-order-detail__muted"><?php esc_html_e( 'روش ارسال:', 'stocksystem' ); ?> <?php echo esc_html( $order->get_shipping_method() ); ?></p>
			<?php endif; ?>
			<?php if ( $order->get_customer_note() ) : ?>
				<p class="account-order-detail__muted"><?php esc_html_e( 'توضیحات شما:', 'stocksystem' ); ?> <?php echo esc_html( $order->get_customer_note() ); ?></p>
			<?php endif; ?>
		</section>

		<section class="account-order-detail__card account-order-detail__totals checkout-summary__lines">
			<span class="account-order-detail__section-title"><?php esc_html_e( 'خلاصهٔ مبلغ', 'stocksystem' ); ?></span>
			<span><span><?php esc_html_e( 'جمع کالاها', 'stocksystem' ); ?></span><span><?php echo wp_kses_post( wc_price( $order->get_subtotal() ) ); ?></span></span>
			<?php if ( $order->get_total_discount() > 0 ) : ?>
				<span class="checkout-summary__discount"><span><?php esc_html_e( 'تخفیف', 'stocksystem' ); ?></span><span>−<?php echo wp_kses_post( wc_price( $order->get_total_discount() ) ); ?></span></span>
			<?php endif; ?>
			<span><span><?php esc_html_e( 'ارسال', 'stocksystem' ); ?></span><span><?php echo $shipping_total > 0 ? wp_kses_post( wc_price( $shipping_total ) ) : esc_html__( 'رایگان', 'stocksystem' ); ?></span></span>
			<?php foreach ( $order->get_fees() as $fee ) : ?>
				<span><span><?php echo esc_html( $fee->get_name() ); ?></span><span><?php echo wp_kses_post( wc_price( $fee->get_total() ) ); ?></span></span>
			<?php endforeach; ?>
			<span class="order-tracking-result__total"><span><?php echo $order->is_paid() ? esc_html__( 'پرداخت‌شده', 'stocksystem' ) : esc_html__( 'مبلغ سفارش', 'stocksystem' ); ?></span><span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span></span>
		</section>
	</div>

	<?php if ( $notes ) : ?>
		<section class="account-order-detail__card">
			<span class="account-order-detail__section-title"><?php esc_html_e( 'به‌روزرسانی‌های سفارش', 'stocksystem' ); ?></span>
			<ol class="account-order-detail__notes">
				<?php foreach ( $notes as $note ) : ?>
					<li>
						<time><?php echo esc_html( stocksystem_jdate( 'j F Y · H:i', strtotime( $note->comment_date_gmt . ' UTC' ) ) ); ?></time>
						<div><?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?></div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<div class="account-order-detail__actions">
		<a class="btn btn--outline" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ) ); ?>">→ <?php esc_html_e( 'همهٔ سفارش‌ها', 'stocksystem' ); ?></a>
		<a class="btn btn--outline" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php esc_html_e( 'پشتیبانی این سفارش', 'stocksystem' ); ?></a>
	</div>
</div>
