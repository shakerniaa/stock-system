<?php
/**
 * Mini-cart drawer — floats from the header, stays open 4s after an
 * "add to cart" (assets/js/navigation.js), 360px per spec.
 * Source: 11 Desktop States.dc.html §02.
 *
 * WooCommerce should refresh #mini-cart-panel-body via the cart fragments
 * API (woocommerce_add_to_cart_fragments) once wired up; this renders the
 * initial server state.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_cart = function_exists( 'WC' ) && WC()->cart;
$items    = $has_cart ? WC()->cart->get_cart() : array();
$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
?>
<div id="mini-cart" class="mini-cart-panel" role="dialog" aria-label="<?php esc_attr_e( 'سبد خرید', 'stocksystem' ); ?>" hidden>
	<div class="mini-cart-panel__header">
		<span>
			<?php esc_html_e( 'سبد خرید', 'stocksystem' ); ?>
			(<?php echo esc_html( stocksystem_to_persian_digits( $has_cart ? WC()->cart->get_cart_contents_count() : 0 ) ); ?>)
		</span>
		<a href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'مشاهدهٔ سبد', 'stocksystem' ); ?></a>
	</div>

	<div class="mini-cart-panel__body" id="mini-cart-panel-body">
		<?php if ( empty( $items ) ) : ?>
			<p class="mini-cart-panel__empty"><?php esc_html_e( 'سبد شما خالی است.', 'stocksystem' ); ?></p>
		<?php else : ?>
			<?php foreach ( $items as $item ) : ?>
				<?php $product = $item['data']; ?>
				<div class="mini-cart-panel__row">
					<span class="mini-cart-panel__thumb"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
					<span class="mini-cart-panel__info">
						<span class="mini-cart-panel__name"><?php echo esc_html( $product->get_name() ); ?></span>
						<span class="mini-cart-panel__meta"><?php echo esc_html( stocksystem_to_persian_digits( $item['quantity'] ) ); ?> <?php esc_html_e( 'عدد', 'stocksystem' ); ?></span>
					</span>
					<span class="mini-cart-panel__price"><?php echo esc_html( stocksystem_format_number( $product->get_price() ) ); ?></span>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $items ) ) : ?>
		<div class="mini-cart-panel__footer">
			<span class="mini-cart-panel__total">
				<?php esc_html_e( 'جمع کل', 'stocksystem' ); ?>
				<strong><?php echo esc_html( stocksystem_format_number( $has_cart ? WC()->cart->get_cart_contents_total() : 0 ) ); ?></strong>
			</span>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( $checkout_url ); ?>"><?php esc_html_e( 'ادامهٔ خرید', 'stocksystem' ); ?></a>
		</div>
	<?php endif; ?>
</div>
