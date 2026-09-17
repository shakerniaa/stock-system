<?php
/**
 * Account dashboard (base /my-account/ endpoint). Source: 08
 * Account.dc.html stat-card row — trimmed to the two stats this build
 * actually has real data for (active orders, wallet balance). The
 * design's warranty-count and club-points cards aren't implemented
 * features (see PROGRESS.md), so they're left out rather than faked.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $current_user;
wp_get_current_user();

$active_statuses = array( 'processing', 'qc-packing', 'with-courier' );
$active_orders    = wc_get_orders(
	array(
		'customer' => $current_user->ID,
		'status'   => $active_statuses,
		'limit'    => -1,
		'return'   => 'ids',
	)
);

$recent_orders = wc_get_orders(
	array(
		'customer' => $current_user->ID,
		'limit'    => 3,
		'orderby'  => 'date',
		'order'    => 'DESC',
	)
);
?>
<div class="account-dashboard">
	<p class="account-dashboard__greeting">
		<?php
		printf(
			/* translators: %s: customer display name */
			esc_html__( 'سلام %s، به حساب کاربری خود خوش آمدید.', 'stocksystem' ),
			'<strong>' . esc_html( $current_user->display_name ) . '</strong>'
		);
		?>
	</p>

	<div class="account-dashboard__stats">
		<div class="account-dashboard__stat">
			<span><?php esc_html_e( 'سفارش‌های جاری', 'stocksystem' ); ?></span>
			<strong><?php echo esc_html( stocksystem_to_persian_digits( count( $active_orders ) ) ); ?></strong>
		</div>
		<div class="account-dashboard__stat">
			<span><?php esc_html_e( 'اعتبار کیف پول', 'stocksystem' ); ?></span>
			<strong><?php echo esc_html( stocksystem_format_number( stocksystem_get_wallet_balance( $current_user->ID ) ) ); ?></strong>
		</div>
	</div>

	<?php if ( ! empty( $recent_orders ) ) : ?>
		<div class="account-dashboard__recent">
			<div class="account-panel__header">
				<h2><?php esc_html_e( 'سفارش‌های اخیر', 'stocksystem' ); ?></h2>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>"><?php esc_html_e( 'مشاهدهٔ همه ←', 'stocksystem' ); ?></a>
			</div>
			<?php foreach ( $recent_orders as $order ) : ?>
				<a class="account-dashboard__order-row" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
					<span class="ltr">#<?php echo esc_html( $order->get_order_number() ); ?></span>
					<span><?php echo esc_html( stocksystem_to_persian_digits( $order->get_date_created()->date_i18n( 'j F' ) ) ); ?></span>
					<span><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
					<span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<p class="account-dashboard__links">
		<?php
		printf(
			/* translators: 1: edit account link, 2: logout link */
			esc_html__( 'می‌توانید از %1$s جزئیات حساب را ویرایش کنید، یا %2$s شوید.', 'stocksystem' ),
			'<a href="' . esc_url( wc_get_account_endpoint_url( 'edit-account' ) ) . '">' . esc_html__( 'اینجا', 'stocksystem' ) . '</a>',
			'<a href="' . esc_url( wc_logout_url() ) . '">' . esc_html__( 'خارج', 'stocksystem' ) . '</a>'
		);
		?>
	</p>
</div>
