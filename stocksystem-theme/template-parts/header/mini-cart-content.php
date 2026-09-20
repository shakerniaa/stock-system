<?php
/**
 * Mini-cart drawer contents (header link, rows, footer). Rendered once
 * inside the drawer and again as a WooCommerce cart fragment
 * (inc/mini-cart.php) so it refreshes after every add / remove without a
 * page reload. Design: 11 Desktop States §02.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart         = function_exists( 'WC' ) ? WC()->cart : null;
$items        = $cart ? $cart->get_cart() : array();
$count        = $cart ? $cart->get_cart_contents_count() : 0;
$cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$checkout_url = function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' );
$shop_url     = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>
<div id="mini-cart-content" class="mini-cart-panel__content">
	<div class="mini-cart-panel__header">
		<span>
			<?php esc_html_e( 'سبد خرید', 'stocksystem' ); ?>
			(<?php echo esc_html( stocksystem_to_persian_digits( $count ) ); ?>)
		</span>
		<?php if ( ! empty( $items ) ) : ?>
			<a href="<?php echo esc_url( $cart_url ); ?>"><?php esc_html_e( 'مشاهدهٔ سبد', 'stocksystem' ); ?></a>
		<?php endif; ?>
	</div>

	<div class="mini-cart-panel__body" id="mini-cart-panel-body">
		<?php if ( empty( $items ) ) : ?>
			<div class="mini-cart-panel__empty">
				<p><?php esc_html_e( 'سبد شما خالی است.', 'stocksystem' ); ?></p>
				<a class="btn btn--outline btn--block" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'مشاهدهٔ محصولات', 'stocksystem' ); ?></a>
			</div>
		<?php else : ?>
			<?php foreach ( $items as $cart_item_key => $item ) : ?>
				<?php
				$product = $item['data'];
				if ( ! $product || ! $product->exists() ) {
					continue;
				}
				$permalink = $product->is_visible() ? $product->get_permalink( $item ) : '';
				?>
				<div class="mini-cart-panel__row">
					<span class="mini-cart-panel__thumb"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
					<span class="mini-cart-panel__info">
						<?php if ( $permalink ) : ?>
							<a class="mini-cart-panel__name" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
						<?php else : ?>
							<span class="mini-cart-panel__name"><?php echo esc_html( $product->get_name() ); ?></span>
						<?php endif; ?>
						<span class="mini-cart-panel__meta"><?php echo esc_html( stocksystem_to_persian_digits( $item['quantity'] ) ); ?> <?php esc_html_e( 'عدد', 'stocksystem' ); ?></span>
						<button type="button" class="mini-cart-panel__remove" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product-name="<?php echo esc_attr( $product->get_name() ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'حذف %s از سبد', 'stocksystem' ), $product->get_name() ) ); ?>">
							<?php esc_html_e( 'حذف', 'stocksystem' ); ?>
						</button>
					</span>
					<span class="mini-cart-panel__price"><?php echo esc_html( stocksystem_format_number( (float) $product->get_price() * (int) $item['quantity'] ) ); ?></span>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $items ) ) : ?>
		<div class="mini-cart-panel__footer">
			<span class="mini-cart-panel__total">
				<?php esc_html_e( 'جمع کل', 'stocksystem' ); ?>
				<strong><?php echo esc_html( stocksystem_format_number( (float) $cart->get_cart_contents_total() ) ); ?> <small><?php esc_html_e( 'تومان', 'stocksystem' ); ?></small></strong>
			</span>
			<a class="btn btn--primary btn--block" href="<?php echo esc_url( $checkout_url ); ?>"><?php esc_html_e( 'ادامهٔ خرید', 'stocksystem' ); ?></a>
		</div>
	<?php endif; ?>
</div>
