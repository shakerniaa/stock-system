<?php
/**
 * Buy box — out of stock (state 2). The full phone-number "notify me"
 * form (15-C) lives on the single product page; the card just links
 * there — too little room for a real form at card width.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = $args['product'];
?>
<span class="product-card__unavailable"><?php esc_html_e( 'فعلاً موجود نیست', 'stocksystem' ); ?></span>

<a href="<?php echo esc_url( $product->get_permalink() . '#notify-me' ); ?>" class="btn btn--outline btn--block">
	<?php esc_html_e( 'خبرم کن', 'stocksystem' ); ?>
</a>
