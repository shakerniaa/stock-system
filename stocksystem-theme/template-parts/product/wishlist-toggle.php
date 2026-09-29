<?php
/**
 * Wishlist heart toggle — top-inline-start corner of the card media,
 * mirroring the badges on the other corner. Reused on the single
 * product page too.
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

$is_saved = is_user_logged_in() && stocksystem_is_in_wishlist( $product->get_id() );
?>
<button
	type="button"
	class="wishlist-toggle<?php echo $is_saved ? ' is-active' : ''; ?>"
	data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
	data-nonce="<?php echo esc_attr( wp_create_nonce( 'stocksystem_wishlist' ) ); ?>"
	aria-pressed="<?php echo $is_saved ? 'true' : 'false'; ?>"
>
	<svg width="18" height="18" viewBox="0 0 24 24" fill="<?php echo $is_saved ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 5.6a5.5 5.5 0 0 0-7.8 0L12 6.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 22l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8z"></path></svg>
	<span class="screen-reader-text"><?php esc_html_e( 'افزودن به علاقه‌مندی‌ها', 'stocksystem' ); ?></span>
</button>
