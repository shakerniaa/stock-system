<?php
/**
 * Clearance countdown chip — bottom corner of the media well, only when a
 * product is tagged "clearance" AND has a WooCommerce sale end date set.
 * Source: 15 Product States.dc.html state 4.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : null;

if ( ! $product instanceof WC_Product || ! has_term( 'clearance', 'product_tag', $product->get_id() ) ) {
	return;
}

$sale_to = $product->get_date_on_sale_to();

if ( ! $sale_to ) {
	return;
}
?>
<span class="product-card__countdown ltr" data-countdown-to="<?php echo esc_attr( $sale_to->getTimestamp() ); ?>">
	&mdash;
</span>
