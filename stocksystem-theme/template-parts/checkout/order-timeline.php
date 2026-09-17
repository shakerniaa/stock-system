<?php
/**
 * 5-step order status timeline, reused by the thank-you page and (later)
 * the My Account order-tracking page. Source: 12 Checkout Flow.dc.html
 * §STEP 04. Step data/logic in inc/order-statuses.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order = ! empty( $args['order'] ) ? $args['order'] : null;

if ( ! $order instanceof WC_Order ) {
	return;
}

if ( in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ) {
	return;
}

$steps = stocksystem_order_timeline_steps( $order );
?>
<div class="order-timeline">
	<span class="order-timeline__title"><?php esc_html_e( 'وضعیت سفارش', 'stocksystem' ); ?></span>
	<div class="order-timeline__track">
		<?php foreach ( $steps as $i => $step ) : ?>
			<div class="order-timeline__step<?php echo $step['done'] ? ' is-done' : ''; ?>">
				<span class="order-timeline__connector">
					<span class="order-timeline__line order-timeline__line--start<?php echo ( 0 === $i || $steps[ $i - 1 ]['done'] ) ? ' is-done' : ''; ?>"></span>
					<span class="order-timeline__dot"></span>
					<span class="order-timeline__line order-timeline__line--end<?php echo ( $i === count( $steps ) - 1 || ! $step['done'] ) ? '' : ' is-done'; ?>"></span>
				</span>
				<span class="order-timeline__label"><?php echo esc_html( $step['label'] ); ?></span>
				<span class="order-timeline__date">
					<?php
					if ( $step['date'] instanceof WC_DateTime ) {
						echo esc_html( stocksystem_to_persian_digits( $step['date']->date_i18n( 'j F · H:i' ) ) );
					} elseif ( $step['done'] ) {
						esc_html_e( 'انجام‌شده', 'stocksystem' );
					} else {
						esc_html_e( 'در انتظار', 'stocksystem' );
					}
					?>
				</span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
