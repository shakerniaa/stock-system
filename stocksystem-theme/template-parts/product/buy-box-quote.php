<?php
/**
 * Buy box — no-price / quote-request product (state 11). The real quote
 * form (organization name, device count — see 15-C) lives on the single
 * product page; the card links there.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = $args['product'];
?>
<span class="product-card__quote-label"><?php esc_html_e( 'قیمت با استعلام', 'stocksystem' ); ?></span>

<a href="<?php echo esc_url( $product->get_permalink() . '#request-quote' ); ?>" class="btn btn--dark btn--block">
	<?php esc_html_e( 'درخواست قیمت', 'stocksystem' ); ?>
</a>
