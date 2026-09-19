<?php
/**
 * My Account → Orders. Source: 08 Account.dc.html — order cards with a
 * status chip, item rows and the same 5-step timeline the thank-you page
 * uses, instead of WooCommerce's default table.
 *
 * Deliberately not built from the design's card: the invoice download
 * button and warranty-claim link (no invoice/warranty feature exists yet).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter        = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_page  = max( 1, absint( get_query_var( 'orders' ) ) );
$active_states = array( 'pending', 'on-hold', 'processing', 'qc-packing', 'with-courier' );

$args = array(
	'customer' => get_current_user_id(),
	'paginate' => true,
	'page'     => $current_page,
	'limit'    => 10,
	'orderby'  => 'date',
	'order'    => 'DESC',
);
if ( 'active' === $filter ) {
	$args['status'] = $active_states;
} elseif ( 'done' === $filter ) {
	$args['status'] = array( 'completed' );
}

$result = wc_get_orders( $args );
$orders = $result->orders;
$base   = wc_get_account_endpoint_url( 'orders' );

$tabs = array(
	'all'    => __( 'همه', 'stocksystem' ),
	'active' => __( 'جاری', 'stocksystem' ),
	'done'   => __( 'تحویل‌شده', 'stocksystem' ),
);

$status_class = function ( $status ) use ( $active_states ) {
	if ( 'completed' === $status ) {
		return 'is-done';
	}
	if ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
		return 'is-bad';
	}
	return in_array( $status, array( 'pending', 'on-hold' ), true ) ? 'is-wait' : 'is-active';
};
?>
<div class="account-orders">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'سفارش‌های من', 'stocksystem' ); ?></h2>
	</div>

	<div class="account-orders__filters" role="tablist">
		<?php foreach ( $tabs as $key => $label ) : ?>
			<a class="account-orders__filter<?php echo $key === $filter || ( 'all' === $key && ! isset( $tabs[ $filter ] ) ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( 'all' === $key ? $base : add_query_arg( 'status', $key, $base ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endforeach; ?>
	</div>

	<?php if ( empty( $orders ) ) : ?>
		<div class="account-orders__empty">
			<p><?php esc_html_e( 'هنوز سفارشی ثبت نکرده‌اید.', 'stocksystem' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'مشاهدهٔ فروشگاه', 'stocksystem' ); ?></a>
		</div>
	<?php else : ?>
		<?php foreach ( $orders as $order ) : ?>
			<?php $order = wc_get_order( $order ); ?>
			<article class="account-order">
				<header class="account-order__head">
					<span class="account-order__meta">
						<span><?php esc_html_e( 'شمارهٔ سفارش', 'stocksystem' ); ?></span>
						<strong class="ltr"><?php echo esc_html( $order->get_order_number() ); ?></strong>
					</span>
					<span class="account-order__meta">
						<span><?php esc_html_e( 'تاریخ', 'stocksystem' ); ?></span>
						<strong><?php echo esc_html( stocksystem_jdate( 'j F Y', $order->get_date_created()->getTimestamp() ) ); ?></strong>
					</span>
					<span class="account-order__meta">
						<span><?php esc_html_e( 'مبلغ', 'stocksystem' ); ?></span>
						<strong><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></strong>
					</span>
					<span class="account-order__status <?php echo esc_attr( $status_class( $order->get_status() ) ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
				</header>

				<div class="account-order__items">
					<?php foreach ( $order->get_items() as $item ) : ?>
						<?php $product = $item->get_product(); ?>
						<div class="account-order__item">
							<span class="account-order__thumb"><?php echo $product ? wp_kses_post( $product->get_image( 'thumbnail' ) ) : ''; ?></span>
							<span class="account-order__item-info">
								<span class="account-order__item-name"><?php echo esc_html( $item->get_name() ); ?></span>
								<span class="account-order__item-meta">
									<?php
									printf(
										/* translators: %s: quantity, Persian digits */
										esc_html__( '%s عدد', 'stocksystem' ),
										esc_html( stocksystem_to_persian_digits( $item->get_quantity() ) )
									);
									?>
								</span>
							</span>
						</div>
					<?php endforeach; ?>
				</div>

				<?php get_template_part( 'template-parts/checkout/order-timeline', null, array( 'order' => $order ) ); ?>

				<footer class="account-order__actions">
					<a class="btn btn--primary" href="<?php echo esc_url( $order->get_view_order_url() ); ?>"><?php esc_html_e( 'جزئیات سفارش', 'stocksystem' ); ?></a>
					<?php if ( in_array( $order->get_status(), $active_states, true ) ) : ?>
						<a class="btn btn--outline" href="<?php echo esc_url( add_query_arg( 'order', $order->get_order_number(), stocksystem_order_tracking_url() ) ); ?>"><?php esc_html_e( 'پیگیری مرسوله', 'stocksystem' ); ?></a>
					<?php endif; ?>
				</footer>
			</article>
		<?php endforeach; ?>

		<?php if ( $result->max_num_pages > 1 ) : ?>
			<nav class="account-orders__pager" aria-label="<?php esc_attr_e( 'صفحه‌بندی سفارش‌ها', 'stocksystem' ); ?>">
				<?php if ( $current_page > 1 ) : ?>
					<a class="btn btn--outline" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1, wc_get_page_permalink( 'myaccount' ) ) ); ?>">→ <?php esc_html_e( 'قبلی', 'stocksystem' ); ?></a>
				<?php endif; ?>
				<?php if ( $current_page < $result->max_num_pages ) : ?>
					<a class="btn btn--outline" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1, wc_get_page_permalink( 'myaccount' ) ) ); ?>"><?php esc_html_e( 'بعدی', 'stocksystem' ); ?> ←</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>
	<?php endif; ?>
</div>
