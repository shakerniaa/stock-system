<?php
/**
 * Buy box — model-choice grouped product (state 9). Each child (simple)
 * product is a model row with its own stock badge.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Product_Grouped $product */
$product     = $args['product'];
$child_ids   = $product->get_children();
$prices      = array();
?>
<span class="product-card__model-rows">
	<?php foreach ( array_slice( $child_ids, 0, 3 ) as $i => $child_id ) : ?>
		<?php
		$child = wc_get_product( $child_id );
		if ( ! $child ) {
			continue;
		}
		$prices[] = (float) $child->get_price();
		$in_stock = $child->is_in_stock();
		$qty      = $child->get_stock_quantity();
		?>
		<a href="<?php echo esc_url( $child->get_permalink() ); ?>" class="product-card__model-row<?php echo 0 === $i ? ' is-primary' : ''; ?>">
			<span class="ltr"><?php echo esc_html( $child->get_name() ); ?></span>
			<?php if ( ! $in_stock ) : ?>
				<span class="product-card__model-stock product-card__model-stock--out"><?php esc_html_e( 'ناموجود', 'stocksystem' ); ?></span>
			<?php elseif ( null !== $qty && $qty <= stocksystem_low_stock_threshold( $child ) ) : ?>
				<span class="product-card__model-stock product-card__model-stock--low"><?php echo esc_html( stocksystem_to_persian_digits( $qty ) ); ?> <?php esc_html_e( 'عدد', 'stocksystem' ); ?></span>
			<?php else : ?>
				<span class="product-card__model-stock product-card__model-stock--in"><?php esc_html_e( 'موجود', 'stocksystem' ); ?></span>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
</span>

<?php if ( ! empty( $prices ) ) : ?>
	<span class="product-card__price">
		<span class="product-card__price-from"><?php esc_html_e( 'از', 'stocksystem' ); ?></span>
		<span class="product-card__price-current"><?php echo esc_html( stocksystem_format_number( min( $prices ) ) ); ?></span>
	</span>
<?php endif; ?>

<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="btn btn--outline btn--block">
	<?php esc_html_e( 'مشاهدهٔ مدل‌ها', 'stocksystem' ); ?>
</a>
