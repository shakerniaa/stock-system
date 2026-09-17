<?php
/**
 * Product grid + pagination for the main loop, using our own card
 * component instead of WooCommerce's content-product.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! have_posts() ) {
	get_template_part( 'template-parts/archive/empty-results' );
	return;
}
?>
<div class="product-grid">
	<?php
	while ( have_posts() ) :
		the_post();
		global $product;
		$product = wc_get_product( get_the_ID() );
		get_template_part( 'template-parts/product/card' );
	endwhile;
	?>
</div>

<?php woocommerce_pagination(); ?>
