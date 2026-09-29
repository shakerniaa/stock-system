<?php
/**
 * "افزودن به مقایسه" toggle. Rendered on the product card and in the
 * single product's buy box; assets/js/compare.js gives it its state.
 *
 * Ships without an is-active class on purpose: the selection lives in
 * localStorage, so the server can't know it, and marking state here
 * would be wrong for anyone served a cached page. The JS sets it on
 * load instead.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];

if ( ! $product instanceof WC_Product ) {
	return;
}

$style = ! empty( $args['style'] ) ? $args['style'] : 'icon';
?>
<button
	type="button"
	class="compare-toggle compare-toggle--<?php echo esc_attr( $style ); ?>"
	data-compare-toggle="<?php echo esc_attr( $product->get_id() ); ?>"
	aria-pressed="false"
>
	<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
		<path d="M4 7h7M4 12h7M4 17h7"></path><path d="M16 5l4 4-4 4"></path><path d="M20 9h-6"></path>
	</svg>
	<span class="compare-toggle__label"><?php esc_html_e( 'افزودن به مقایسه', 'stocksystem' ); ?></span>
</button>
