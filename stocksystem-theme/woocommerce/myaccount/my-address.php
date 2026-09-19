<?php
/**
 * My Account → addresses (08 Account.dc.html «نشانی‌های من»): one card per
 * address WooCommerce keeps — billing (the default delivery address) and, when
 * enabled, a separate recipient/shipping address. The design's multi-address
 * book isn't built: WooCommerce stores one of each per customer.
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

$get_addresses = apply_filters(
	'woocommerce_my_account_get_addresses',
	( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() )
		? array(
			'billing'  => __( 'نشانی پیش‌فرض (صورتحساب و تحویل)', 'stocksystem' ),
			'shipping' => __( 'نشانی گیرندهٔ دیگر', 'stocksystem' ),
		)
		: array(
			'billing' => __( 'نشانی پیش‌فرض (صورتحساب و تحویل)', 'stocksystem' ),
		),
	$customer_id
);
?>
<div class="account-addresses">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'نشانی‌های من', 'stocksystem' ); ?></h2>
	</div>
	<p class="account-addresses__lead"><?php echo apply_filters( 'woocommerce_my_account_my_address_description', esc_html__( 'این نشانی‌ها هنگام تسویه‌حساب به‌طور پیش‌فرض پر می‌شوند.', 'stocksystem' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>

	<div class="account-addresses__list">
		<?php foreach ( $get_addresses as $name => $address_title ) : ?>
			<?php $address = wc_get_account_formatted_address( $name ); ?>
			<section class="account-address<?php echo 'billing' === $name ? ' is-default' : ''; ?>">
				<header class="account-address__head">
					<h3><?php echo esc_html( $address_title ); ?></h3>
					<?php if ( 'billing' === $name ) : ?>
						<span class="badge badge--new"><?php esc_html_e( 'پیش‌فرض', 'stocksystem' ); ?></span>
					<?php endif; ?>
					<a class="account-address__edit" href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>">
						<?php echo $address ? esc_html__( 'ویرایش', 'stocksystem' ) : esc_html__( '+ افزودن', 'stocksystem' ); ?>
					</a>
				</header>
				<address>
					<?php
					echo $address ? wp_kses_post( $address ) : esc_html__( 'هنوز نشانی‌ای ثبت نکرده‌اید.', 'stocksystem' );
					do_action( 'woocommerce_my_account_after_my_address', $name );
					?>
				</address>
			</section>
		<?php endforeach; ?>
	</div>
</div>
