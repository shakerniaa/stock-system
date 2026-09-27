<?php
/**
 * Grouped product specifications — replaces the flat WooCommerce
 * attribute table inside the «مشخصات فنی» tab. Rendered via
 * inc/product-specs.php's stocksystem_render_grouped_specs(), hooked
 * onto woocommerce_product_additional_information in place of
 * wc_display_product_attributes().
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
$groups  = stocksystem_get_grouped_product_specs( $product );

if ( empty( $groups ) ) {
	return;
}
?>
<div class="product-specs">
	<?php foreach ( $groups as $group ) : ?>
		<div class="product-specs__group">
			<div class="product-specs__group-title"><?php echo esc_html( $group['title'] ); ?></div>
			<div class="product-specs__rows">
				<?php foreach ( $group['rows'] as $row ) : ?>
					<div class="product-specs__row">
						<span class="product-specs__label"><?php echo esc_html( $row['label'] ); ?></span>
						<span class="product-specs__value"><?php echo esc_html( $row['value'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
