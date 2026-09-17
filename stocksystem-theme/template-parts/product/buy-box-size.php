<?php
/**
 * Buy box — size-choice variable product (state 8). Unavailable sizes
 * (out-of-stock variation) render struck-through, dashed, non-interactive.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Product_Variable $product */
$product = $args['product'];

$size_taxonomy = '';
foreach ( $product->get_attributes() as $attribute ) {
	if ( $attribute->is_taxonomy() ) {
		$slug = $attribute->get_taxonomy();
		if ( false !== strpos( $slug, 'size' ) ) {
			$size_taxonomy = $slug;
			break;
		}
	}
}

$terms = $size_taxonomy ? wc_get_product_terms( $product->get_id(), $size_taxonomy ) : array();

// Map each size term to whether any in-stock variation offers it.
$available_sizes = array();
if ( $size_taxonomy ) {
	foreach ( $product->get_available_variations() as $variation_data ) {
		$key = 'attribute_' . sanitize_title( $size_taxonomy );
		if ( ! empty( $variation_data['attributes'][ $key ] ) ) {
			$available_sizes[ $variation_data['attributes'][ $key ] ] = true;
		}
	}
}
?>
<?php if ( ! empty( $terms ) ) : ?>
	<span class="product-card__size-chips">
		<?php foreach ( $terms as $i => $term ) : ?>
			<?php $available = empty( $available_sizes ) || ! empty( $available_sizes[ $term->slug ] ); ?>
			<span class="product-card__size-chip ltr<?php echo 0 === $i ? ' is-selected' : ''; ?><?php echo $available ? '' : ' is-unavailable'; ?>">
				<?php echo esc_html( $term->name ); ?>
			</span>
		<?php endforeach; ?>
	</span>
<?php endif; ?>

<span class="product-card__price">
	<span class="product-card__price-current"><?php echo esc_html( stocksystem_format_number( $product->get_price() ) ); ?></span>
</span>
