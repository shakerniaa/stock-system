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

$cfg   = stocksystem_home( 'featured' ); // Appearance → «صفحهٔ اصلی».
$count = (int) $cfg['count'];
$base  = array( 'limit' => $count, 'status' => 'publish', 'orderby' => 'date', 'order' => 'DESC' );

switch ( $cfg['source'] ) {
	case 'manual':
		$products = array();
		foreach ( array_slice( $cfg['product_ids'], 0, $count ) as $manual_id ) {
			$manual_product = wc_get_product( $manual_id );
			if ( $manual_product && 'publish' === $manual_product->get_status() ) {
				$products[] = $manual_product;
			}
		}
		break;
	case 'best_selling':
		$products = wc_get_products( array_merge( $base, array( 'orderby' => 'popularity', 'order' => 'DESC' ) ) );
		break;
	case 'featured':
		$products = wc_get_products( array_merge( $base, array( 'featured' => true ) ) );
		break;
	case 'newest':
		$products = wc_get_products( $base );
		break;
	default: // on_sale.
		$products = wc_get_products( array_merge( $base, array( 'on_sale' => true ) ) );
}

if ( empty( $products ) ) {
	$products = wc_get_products( $base ); // Nothing matched: show the newest instead of an empty section.
}

if ( empty( $products ) ) {
	return;
}

$shop_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$more_link = stocksystem_home_link( $cfg['link_url'], add_query_arg( 'on_sale', '1', $shop_url ) );
?>
<section class="home-featured">
	<div class="container">
		<div class="home-featured__header">
			<div class="home-featured__heading">
				<h2><?php echo esc_html( $cfg['heading'] ); ?></h2>
				<span class="home-featured__rule" aria-hidden="true"></span>
			</div>
			<a href="<?php echo esc_url( $more_link ); ?>"><?php echo esc_html( $cfg['link_label'] ); ?></a>
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
