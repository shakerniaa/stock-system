<?php
/**
 * Product card — reused by archives, home "deal of the week", mega menu,
 * mini-cart, related products. Source: 15 Product States.dc.html §15-B.
 *
 * Expects global $product (standard inside the WooCommerce loop).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( ! $product instanceof WC_Product ) {
	return;
}

$variant   = stocksystem_product_card_variant( $product );
$badges    = stocksystem_product_badges( $product, $variant );
$ribbon    = stocksystem_product_ribbon( $product );
$is_featured = has_term( 'featured', 'product_tag', $product->get_id() );

$card_classes = array( 'product-card', 'product-card--' . $variant );
if ( $is_featured ) {
	$card_classes[] = 'is-featured';
}
if ( in_array( 'badge--warning', wp_list_pluck( $badges, 'class' ), true ) ) {
	$card_classes[] = 'has-low-stock';
}
?>
<article class="<?php echo esc_attr( implode( ' ', $card_classes ) ); ?>">
	<div class="product-card__media">
		<?php if ( $ribbon ) : ?>
			<span class="product-card__ribbon"><?php echo esc_html( $ribbon ); ?></span>
		<?php endif; ?>

		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo wp_kses_post(
				$product->get_image(
					'medium',
					array(
						'class' => 'product-card__image' . ( ! $product->is_in_stock() ? ' product-card__image--disabled' : '' ),
					)
				)
			);
			?>
		</a>

		<?php if ( ! empty( $badges ) ) : ?>
			<span class="product-card__badges">
				<?php foreach ( $badges as $badge ) : ?>
					<span class="badge <?php echo esc_attr( $badge['class'] ); ?>">
						<?php if ( ! empty( $badge['icon'] ) && 'star' === $badge['icon'] ) : ?>
							<svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.3 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8z"></path></svg>
						<?php endif; ?>
						<?php echo esc_html( $badge['label'] ); ?>
					</span>
				<?php endforeach; ?>
			</span>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/product/clearance-countdown', null, array( 'product' => $product ) ); ?>
	</div>

	<div class="product-card__body">
		<a class="product-card__title" href="<?php echo esc_url( $product->get_permalink() ); ?>">
			<?php echo esc_html( $product->get_name() ); ?>
		</a>

		<?php $excerpt = wp_strip_all_tags( $product->get_short_description() ); ?>
		<?php if ( $excerpt ) : ?>
			<span class="product-card__excerpt"><?php echo esc_html( wp_trim_words( $excerpt, 6, '…' ) ); ?></span>
		<?php endif; ?>

		<?php $grading = stocksystem_product_grading_note( $product ); ?>
		<?php if ( $grading ) : ?>
			<span class="product-card__grading">
				<span class="product-card__grading-dot" style="background-color:<?php echo esc_attr( $grading['color'] ); ?>"></span>
				<?php echo esc_html( $grading['label'] ); ?>
			</span>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/product/buy-box-' . $variant, null, array( 'product' => $product, 'badges' => $badges ) ); ?>
	</div>
</article>
