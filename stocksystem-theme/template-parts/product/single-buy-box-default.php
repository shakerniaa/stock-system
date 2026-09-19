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
