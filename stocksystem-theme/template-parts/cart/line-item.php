<?php
/**
 * One cart line item card. Source: 04 Cart Checkout.dc.html (the one
 * piece of that file decision #2 keeps — its line-item/summary card
 * patterns, not its single-page structure).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_item_key = $args['cart_item_key'];
$cart_item     = $args['cart_item'];
$product       = $cart_item['data'];

if ( ! $product || ! $product->exists() || $cart_item['quantity'] <= 0 ) {
	return;
}

$product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
?>
<div class="cart-line" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>">
	<span class="cart-line__thumb">
		<?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_thumbnail', $product->get_image(), $cart_item, $cart_item_key ) ); ?>
	</span>

	<span class="cart-line__info">
		<?php if ( $product_permalink ) : ?>
			<a class="cart-line__name" href="<?php echo esc_url( $product_permalink ); ?>"><?php echo wp_kses_post( $product->get_name() ); ?></a>
		<?php else : ?>
			<span class="cart-line__name"><?php echo wp_kses_post( $product->get_name() ); ?></span>
		<?php endif; ?>

		<?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>

		<?php
		$grading = stocksystem_product_grading_note( $product );
		if ( $grading ) :
			?>
			<span class="cart-line__grading">
				<span class="cart-line__grading-dot" style="background-color:<?php echo esc_attr( $grading['color'] ); ?>"></span>
				<?php echo esc_html( $grading['label'] ); ?>
			</span>
		<?php endif; ?>

		<a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="cart-line__remove" aria-label="<?php esc_attr_e( 'حذف از سبد', 'stocksystem' ); ?>">
			<?php esc_html_e( 'حذف', 'stocksystem' ); ?>
		</a>
	</span>

	<span class="cart-line__controls">
		<?php
		if ( $product->is_sold_individually() ) {
			echo '<input type="hidden" name="cart[' . esc_attr( $cart_item_key ) . '][qty]" value="1">';
		} else {
			woocommerce_quantity_input(
				array(
					'input_name'   => "cart[{$cart_item_key}][qty]",
					'input_value'  => $cart_item['quantity'],
					'max_value'    => $product->get_max_purchase_quantity(),
					'min_value'    => '0',
					'product_name' => $product->get_name(),
				),
				$product
			);
		}
		?>
		<span class="cart-line__price"><?php echo wp_kses_post( WC()->cart->get_product_subtotal( $product, $cart_item['quantity'] ) ); ?></span>
	</span>
</div>
