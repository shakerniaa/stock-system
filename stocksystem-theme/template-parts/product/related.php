<?php
/**
 * "مدل‌های مشابه" — related products, using the same card component as
 * every other listing on the site rather than WooCommerce's default
 * related-products markup (removed in inc/woocommerce.php).
 *
 * Sourced in three tiers, because wc_get_related_products() alone left
 * most pages on this shop with an empty section: it only matches on a
 * shared category or tag, no product here carries a tag, and several
 * categories hold one product each — so a monitor or an all-in-one page
 * simply dead-ended with nothing to click.
 *
 *   1. Upsells the owner picked by hand (محصولات پیوند شده → Upsells).
 *   2. WooCommerce's own automatic relations (same category/tag).
 *   3. Best sellers, to fill the row rather than leave a gap.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];

if ( ! $current_product instanceof WC_Product || ! function_exists( 'wc_get_products' ) ) {
	return;
}

$limit       = 4;
$current_id  = $current_product->get_id();
$related_ids = array();

/** Appends ids that are publishable, visible and not the product itself. */
$collect = function ( $ids ) use ( &$related_ids, $current_id, $limit ) {
	foreach ( (array) $ids as $id ) {
		$id = (int) $id;

		if ( count( $related_ids ) >= $limit ) {
			return;
		}
		if ( $id === $current_id || in_array( $id, $related_ids, true ) ) {
			continue;
		}

		$candidate = wc_get_product( $id );
		// is_visible() also covers "hidden from catalogue" products, which
		// should not be advertised from another product's page.
		if ( $candidate && 'publish' === $candidate->get_status() && $candidate->is_visible() ) {
			$related_ids[] = $id;
		}
	}
};

// 1. Hand-picked upsells win: they are an explicit editorial choice.
$collect( $current_product->get_upsell_ids() );

// 2. WooCommerce's automatic relations.
if ( count( $related_ids ) < $limit && function_exists( 'wc_get_related_products' ) ) {
	$collect( wc_get_related_products( $current_id, $limit * 2, $related_ids ) );
}

// 3. Best sellers, so the row is never left short.
if ( count( $related_ids ) < $limit ) {
	$fallback = wc_get_products(
		array(
			'limit'   => $limit * 3,
			'status'  => 'publish',
			'orderby' => 'popularity',
			'order'   => 'DESC',
			'exclude' => array_merge( array( $current_id ), $related_ids ),
		)
	);
	$collect( wp_list_pluck( $fallback, 'id' ) );
}

if ( empty( $related_ids ) ) {
	return;
}
?>
<section class="related-products">
	<div class="container">
		<h2 class="related-products__title"><?php esc_html_e( 'مدل‌های مشابه', 'stocksystem' ); ?></h2>
		<div class="product-grid">
			<?php
			foreach ( $related_ids as $related_id ) :
				global $product;
				$product = wc_get_product( $related_id );
				if ( $product ) {
					get_template_part( 'template-parts/product/card' );
				}
			endforeach;
			?>
		</div>
	</div>
</section>
