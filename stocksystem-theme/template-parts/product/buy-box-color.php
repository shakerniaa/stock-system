<?php
/**
 * Buy box — color-choice variable product (state 7). Swatches read a hex
 * value from term meta key "swatch_color" on the color attribute's terms;
 * falls back to a neutral grey dot until that meta is set (Products →
 * Attributes → the color taxonomy's terms — no color-swatch plugin is
 * assumed here, just a plain term-meta field).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Product_Variable $product */
$product = $args['product'];

$color_taxonomy = '';
foreach ( $product->get_attributes() as $attribute ) {
	if ( $attribute->is_taxonomy() ) {
		$slug = $attribute->get_taxonomy();
		if ( false !== strpos( $slug, 'color' ) || false !== strpos( $slug, 'colour' ) ) {
			$color_taxonomy = $slug;
			break;
		}
	}
}

$terms = $color_taxonomy ? wc_get_product_terms( $product->get_id(), $color_taxonomy ) : array();
?>
<?php if ( ! empty( $terms ) ) : ?>
	<span class="product-card__swatches">
		<?php foreach ( $terms as $i => $term ) : ?>
			<?php $hex = get_term_meta( $term->term_id, 'swatch_color', true ) ?: '#DDE7E6'; ?>
			<span class="product-card__swatch<?php echo 0 === $i ? ' is-selected' : ''; ?>" style="background-color:<?php echo esc_attr( $hex ); ?>"></span>
		<?php endforeach; ?>
		<span class="product-card__swatch-label"><?php echo esc_html( $terms[0]->name ); ?></span>
	</span>
<?php endif; ?>

<span class="product-card__price">
	<span class="product-card__price-current"><?php echo esc_html( stocksystem_format_number( $product->get_price() ) ); ?></span>
</span>

<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn--primary btn--block">
	<?php esc_html_e( 'انتخاب گزینه‌ها', 'stocksystem' ); ?>
</a>
