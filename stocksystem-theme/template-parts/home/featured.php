<?php
/**
 * "پیشنهاد این هفته" — on-sale products, rendered with the standard
 * product card so it stays consistent with every other listing.
 * Source: 01 Home.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wc_get_products' ) ) {
	return;
}

$products = wc_get_products(
	array(
		'limit'    => 4,
		'on_sale'  => true,
		'status'   => 'publish',
		'orderby'  => 'date',
		'order'    => 'DESC',
	)
);

if ( empty( $products ) ) {
	$products = wc_get_products( array( 'limit' => 4, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish' ) );
}

if ( empty( $products ) ) {
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="home-featured">
	<div class="container">
		<div class="home-featured__header">
			<div class="home-featured__heading">
				<h2><?php esc_html_e( 'پیشنهاد این هفته', 'stocksystem' ); ?></h2>
				<span class="home-featured__rule" aria-hidden="true"></span>
			</div>
			<a href="<?php echo esc_url( add_query_arg( 'on_sale', '1', $shop_url ) ); ?>"><?php esc_html_e( 'همهٔ تخفیف‌ها ←', 'stocksystem' ); ?></a>
		</div>

		<div class="product-grid">
			<?php
			foreach ( $products as $featured_product ) :
				global $product;
				$product = $featured_product;
				get_template_part( 'template-parts/product/card' );
			endforeach;
			?>
		</div>
	</div>
</section>
