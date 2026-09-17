<?php
/**
 * Order confirmation — stage 4 of the wizard. Source: 12 Checkout
 * Flow.dc.html §STEP 04.
 *
 * Decision #7 fix: the design's support phone number on this specific
 * page (021-9100 2233) is flagged wrong in DECISIONS-v1.1.md — unified
 * with stocksystem_business('phone') like everywhere else.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $order ) {
	return;
}

$is_paid  = $order->is_paid();
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<div class="checkout-page">
	<div class="container">
		<?php get_template_part( 'template-parts/checkout/step-indicator', null, array( 'current' => 4 ) ); ?>

		<div class="order-confirmation-banner<?php echo $is_paid ? '' : ' is-pending'; ?>">
			<span class="order-confirmation-banner__icon" aria-hidden="true">
				<?php if ( $is_paid ) : ?>
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
				<?php else : ?>
					<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4l3 2"></path></svg>
				<?php endif; ?>
			</span>
			<span class="order-confirmation-banner__text">
				<span class="order-confirmation-banner__title">
					<?php echo $is_paid ? esc_html__( 'پرداخت با موفقیت انجام شد', 'stocksystem' ) : esc_html__( 'سفارش شما ثبت شد', 'stocksystem' ); ?>
				</span>
				<span class="order-confirmation-banner__desc">
					<?php
					printf(
						/* translators: %s: masked phone number */
						esc_html__( 'سفارش شما ثبت شد و پیامک تأیید به شمارهٔ %s ارسال گردید.', 'stocksystem' ),
						esc_html( $order->get_billing_phone() )
					);
					?>
				</span>
			</span>
			<span class="order-confirmation-banner__number">
				<span class="ltr">ORDER NO.</span>
				<span class="ltr"><?php echo esc_html( $order->get_order_number() ); ?></span>
			</span>
		</div>

		<div class="checkout-page__grid">
			<div class="checkout-page__main">
				<div class="order-confirmation-stats">
					<div class="order-confirmation-stats__item">
						<span><?php esc_html_e( 'مبلغ پرداخت‌شده', 'stocksystem' ); ?></span>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</div>
					<div class="order-confirmation-stats__item">
						<span><?php esc_html_e( 'روش ارسال', 'stocksystem' ); ?></span>
						<strong><?php echo esc_html( $order->get_shipping_method() ? $order->get_shipping_method() : '—' ); ?></strong>
					</div>
					<div class="order-confirmation-stats__item">
						<span><?php esc_html_e( 'روش پرداخت', 'stocksystem' ); ?></span>
						<strong><?php echo esc_html( $order->get_payment_method_title() ); ?></strong>
					</div>
					<div class="order-confirmation-stats__item">
						<span><?php esc_html_e( 'تاریخ ثبت', 'stocksystem' ); ?></span>
						<strong><?php echo esc_html( stocksystem_to_persian_digits( $order->get_date_created()->date_i18n( 'j F' ) ) ); ?></strong>
					</div>
				</div>

				<?php get_template_part( 'template-parts/checkout/order-timeline', null, array( 'order' => $order ) ); ?>

				<div class="order-confirmation-items">
					<span class="order-confirmation-items__title"><?php esc_html_e( 'کالاهای سفارش', 'stocksystem' ); ?></span>
					<?php foreach ( $order->get_items() as $item ) : ?>
						<?php $product = $item->get_product(); ?>
						<div class="order-confirmation-items__row">
							<?php if ( $product ) : ?>
								<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
							<?php endif; ?>
							<span class="order-confirmation-items__info">
								<span class="order-confirmation-items__name"><?php echo esc_html( $item->get_name() ); ?></span>
								<span class="order-confirmation-items__meta">
									<?php
									printf(
										/* translators: %s: quantity, Persian digits */
										esc_html__( '%s عدد', 'stocksystem' ),
										esc_html( stocksystem_to_persian_digits( $item->get_quantity() ) )
									);
									?>
								</span>
							</span>
							<span class="order-confirmation-items__price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<aside class="order-confirmation-aside">
				<div class="order-confirmation-aside__panel">
					<span class="order-confirmation-aside__title"><?php esc_html_e( 'گام بعدی', 'stocksystem' ); ?></span>
					<a href="<?php echo esc_url( stocksystem_order_tracking_url() ); ?>" class="btn btn--primary btn--block"><?php esc_html_e( 'پیگیری سفارش', 'stocksystem' ); ?></a>
					<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="btn btn--outline btn--block"><?php esc_html_e( 'مشاهدهٔ جزئیات سفارش', 'stocksystem' ); ?></a>
					<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn--outline btn--block"><?php esc_html_e( 'بازگشت به فروشگاه', 'stocksystem' ); ?></a>
				</div>

				<div class="order-confirmation-aside__notice">
					<span class="order-confirmation-aside__notice-title"><?php esc_html_e( '۷ روز مهلت بازگشت', 'stocksystem' ); ?></span>
					<p><?php esc_html_e( 'اگر دستگاه با توضیحات گرید مطابق نبود، تا ۷ روز پس از تحویل بدون قید و شرط مرجوع می‌شود.', 'stocksystem' ); ?></p>
					<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'شرایط مرجوعی', 'stocksystem' ); ?></a>
				</div>

				<div class="order-confirmation-aside__support">
					<span class="order-confirmation-aside__title"><?php esc_html_e( 'پشتیبانی سفارش', 'stocksystem' ); ?></span>
					<p>
						<?php if ( stocksystem_business( 'store_hours' ) ) : ?>
							<?php echo esc_html( stocksystem_business( 'store_hours' ) ); ?> ·
						<?php endif; ?>
						<span class="ltr"><?php echo esc_html( stocksystem_business( 'phone' ) ); ?></span>
					</p>
				</div>
			</aside>
		</div>
	</div>
</div>
