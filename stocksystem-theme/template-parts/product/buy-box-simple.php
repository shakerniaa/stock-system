<?php
/**
 * Buy box — simple product (states 1/3/4/5/12/13/14: in stock, low stock,
 * clearance, % discount, labelled, new arrival, featured — same markup,
 * different badges/border via card.php modifier classes).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = $args['product'];
$badges  = ! empty( $args['badges'] ) ? $args['badges'] : array();
$is_low_stock = in_array( 'badge--warning', wp_list_pluck( $badges, 'class' ), true );
?>
<span class="product-card__price">
	<?php if ( $product->is_on_sale() ) : ?>
		<span class="product-card__price-current product-card__price-current--sale"><?php echo esc_html( stocksystem_format_number( $product->get_sale_price() ) ); ?></span>
		<span class="product-card__price-was"><?php echo esc_html( stocksystem_format_number( $product->get_regular_price() ) ); ?></span>
	<?php else : ?>
		<span class="product-card__price-current"><?php echo esc_html( stocksystem_format_number( $product->get_price() ) ); ?></span>
	<?php endif; ?>
</span>

<a
	href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
	data-quantity="1"
	data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
	data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
	rel="nofollow"
	class="btn btn--primary btn--block ajax_add_to_cart add_to_cart_button"
>
	<?php echo $is_low_stock ? esc_html__( 'آخرین فرصت', 'stocksystem' ) : esc_html( $product->add_to_cart_text() ); ?>
</a>
