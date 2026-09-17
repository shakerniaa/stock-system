<?php
/**
 * "مدل‌های مشابه" — related products, using the same card component as
 * every other listing on the site rather than WooCommerce's default
 * related-products markup (removed in inc/woocommerce.php).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];

if ( ! $current_product instanceof WC_Product || ! function_exists( 'wc_get_related_products' ) ) {
	return;
}

$related_ids = wc_get_related_products( $current_product->get_id(), 4 );

if ( empty( $related_ids ) ) {
	return;
}
?>
<section class="related-products">
	<div class="container">
		<h2 class="related-products__title"><?php esc_html_e( 'مدل‌های مشابه', 'stocksystem' ); ?></h2>
		<div class="product-grid">
			<?php
			foreach ( $related_ids as $related_id ) :
				global $product;
				$product = wc_get_product( $related_id );
				if ( $product ) {
					get_template_part( 'template-parts/product/card' );
				}
			endforeach;
			?>
		</div>
	</div>
</section>
