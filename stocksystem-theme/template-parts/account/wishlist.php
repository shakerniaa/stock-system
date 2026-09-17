<?php
/**
 * "علاقه‌مندی‌ها" — My Account → Wishlist. Reuses the standard product
 * card (same one everywhere else) rather than the design's bespoke
 * table — its stock badges already satisfy the README's requirement
 * that stock status show inside the list, and the heart toggle already
 * on every card doubles as the "remove" control.
 * Source: 16 Shop Pages.dc.html §16-D.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_ids = stocksystem_get_wishlist();
?>
<div class="account-wishlist">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'علاقه‌مندی‌ها', 'stocksystem' ); ?></h2>
		<span class="account-panel__count">
			<?php
			printf(
				/* translators: %s: saved item count, Persian digits */
				esc_html__( '%s کالا ذخیره شده', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( count( $product_ids ) ) )
			);
			?>
		</span>
	</div>

	<?php if ( empty( $product_ids ) ) : ?>
		<p class="account-panel__empty"><?php esc_html_e( 'هنوز کالایی به علاقه‌مندی‌ها اضافه نکرده‌اید.', 'stocksystem' ); ?></p>
	<?php else : ?>
		<div class="product-grid">
			<?php
			foreach ( $product_ids as $product_id ) :
				global $product;
				$product = wc_get_product( $product_id );
				if ( $product ) {
					get_template_part( 'template-parts/product/card' );
				}
			endforeach;
			?>
		</div>
	<?php endif; ?>
</div>
