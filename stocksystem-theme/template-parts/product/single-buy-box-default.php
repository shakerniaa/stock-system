<?php
/**
 * Default buy-box body: simple/variable/grouped products all flow
 * through WooCommerce's own add-to-cart template for their type
 * (simple.php, our overridden variable.php, or grouped.php) — this part
 * just adds the price line above it for types that don't render their
 * own (variable products show price inside the configurator itself).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
?>
<?php if ( ! $product->is_type( 'variable' ) ) : ?>
	<span class="buy-box__price">
		<?php echo wp_kses_post( $product->get_price_html() ); ?>
		<?php
		if ( $product->is_on_sale() && ! has_term( 'clearance', 'product_tag', $product->get_id() ) ) {
			$percent = stocksystem_product_discount_percent( $product );
			if ( $percent > 0 ) {
				printf(
					'<span class="buy-box__discount-pill">٪%s تخفیف</span>',
					esc_html( stocksystem_to_persian_digits( $percent ) )
				);
			}
		}
		?>
	</span>
<?php endif; ?>

<?php woocommerce_template_single_add_to_cart(); ?>

<a class="btn btn--outline btn--block buy-box__consult" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
	<?php esc_html_e( 'استعلام تلفنی و مشاورهٔ خرید', 'stocksystem' ); ?>
</a>

<div class="mobile-buy-bar" data-mobile-buy-bar>
	<a class="mobile-buy-bar__call" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>" aria-label="<?php esc_attr_e( 'تماس برای مشاوره', 'stocksystem' ); ?>">
		<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
	</a>
	<button type="button" class="btn btn--primary mobile-buy-bar__cta"><?php esc_html_e( 'افزودن به سبد', 'stocksystem' ); ?></button>
</div>
