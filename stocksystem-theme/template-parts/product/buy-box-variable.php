<?php
/**
 * Buy box — variable product, two renderings of the same underlying
 * WC_Product_Variable depending on how much its variations' prices
 * spread:
 *  - state 6 "N configurations": attribute chips + "select options"
 *  - state 10 "price range": "X تا Y" + "view options"
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Product_Variable $product */
$product     = $args['product'];
$has_range   = stocksystem_variable_has_price_range( $product );
?>

<?php if ( $has_range ) : ?>
	<?php $prices = $product->get_variation_prices( true ); ?>
	<span class="product-card__price-range">
		<?php echo esc_html( stocksystem_format_number( min( $prices['price'] ) ) ); ?>
		<span class="product-card__price-range-sep"><?php esc_html_e( 'تا', 'stocksystem' ); ?></span>
		<?php echo esc_html( stocksystem_format_number( max( $prices['price'] ) ) ); ?>
	</span>
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn--outline btn--block">
		<?php esc_html_e( 'مشاهدهٔ گزینه‌ها', 'stocksystem' ); ?>
	</a>
<?php else : ?>
	<?php
	$attributes = $product->get_variation_attributes();
	$first_axis = ! empty( $attributes ) ? reset( $attributes ) : array();
	?>
	<?php if ( ! empty( $first_axis ) ) : ?>
		<span class="product-card__chips">
			<?php foreach ( array_slice( $first_axis, 0, 4 ) as $value ) : ?>
				<span class="product-card__chip ltr"><?php echo esc_html( $value ); ?></span>
			<?php endforeach; ?>
		</span>
	<?php endif; ?>
	<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn--outline btn--block">
		<?php esc_html_e( 'انتخاب گزینه‌ها', 'stocksystem' ); ?>
	</a>
<?php endif; ?>
