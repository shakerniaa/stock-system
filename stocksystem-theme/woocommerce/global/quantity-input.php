<?php
/**
 * Quantity stepper — WooCommerce's default is a bare number input; this
 * adds real −/+ buttons around it (design source: 03 Product.dc.html).
 * Argument handling mirrors WooCommerce core's own template so cart/
 * checkout quantity updates keep working unmodified.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$defaults = array(
	'input_id'     => uniqid( 'quantity_' ),
	'input_name'   => 'quantity',
	'input_value'  => '1',
	'classes'      => apply_filters( 'woocommerce_quantity_input_classes', array( 'input-text', 'qty', 'text' ), $product ?? null ),
	'max_value'    => -1,
	'min_value'    => 0,
	'step'         => 1,
	'pattern'      => apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '[0-9.]*' ),
	'inputmode'    => apply_filters( 'woocommerce_quantity_input_inputmode', has_filter( 'woocommerce_stock_amount', 'intval' ) ? 'numeric' : 'decimal' ),
	'product_name' => '',
	'placeholder'  => '',
);

$args = wp_parse_args( $args, $defaults );

if ( empty( $args['rules'] ) ) {
	$args['rules'] = '';
}
?>
<div class="quantity-stepper">
	<button type="button" class="quantity-stepper__btn quantity-stepper__btn--minus" aria-label="<?php esc_attr_e( 'کم کردن تعداد', 'stocksystem' ); ?>">−</button>
	<label class="screen-reader-text" for="<?php echo esc_attr( $args['input_id'] ); ?>">
		<?php echo $args['product_name'] ? esc_html( $args['product_name'] ) : esc_html__( 'تعداد', 'stocksystem' ); ?>
	</label>
	<input
		type="number"
		id="<?php echo esc_attr( $args['input_id'] ); ?>"
		class="<?php echo esc_attr( implode( ' ', (array) $args['classes'] ) ); ?>"
		name="<?php echo esc_attr( $args['input_name'] ); ?>"
		value="<?php echo esc_attr( $args['input_value'] ); ?>"
		<?php if ( '' !== $args['placeholder'] ) : ?>placeholder="<?php echo esc_attr( $args['placeholder'] ); ?>"<?php endif; ?>
		<?php if ( -1 !== (int) $args['max_value'] ) : ?>max="<?php echo esc_attr( $args['max_value'] ); ?>"<?php endif; ?>
		min="<?php echo esc_attr( $args['min_value'] ); ?>"
		step="<?php echo esc_attr( $args['step'] ); ?>"
		inputmode="<?php echo esc_attr( $args['inputmode'] ); ?>"
	/>
	<button type="button" class="quantity-stepper__btn quantity-stepper__btn--plus" aria-label="<?php esc_attr_e( 'افزودن به تعداد', 'stocksystem' ); ?>">+</button>
</div>
