<?php
/**
 * Product-tag archive (clearance, featured, new arrival, bestseller, free
 * shipping). WooCommerce's loader falls back to archive-product.php for tags,
 * which is the shop *hub* — so the "حراج ویژه" tile and any tag link showed
 * the hub instead of the tagged products. Same layout as a category page.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$term  = get_queried_object();
$count = (int) $GLOBALS['wp_query']->found_posts;
?>

<div class="archive-header">
	<div class="container">
		<?php woocommerce_breadcrumb(); ?>
		<h1 class="archive-header__title"><?php echo esc_html( $term->name ); ?></h1>
		<p class="archive-header__count">
			<?php
			printf(
				/* translators: %s: product count, Persian digits */
				esc_html__( '%s کالا — همه با گارانتی و برگهٔ تست', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( $count ) )
			);
			?>
		</p>
		<?php if ( $term->description ) : ?>
			<p class="archive-header__desc"><?php echo esc_html( $term->description ); ?></p>
		<?php endif; ?>
	</div>
</div>

<div class="container">
	<?php get_template_part( 'template-parts/archive/results', null, array( 'show_stock_facets' => false ) ); ?>
</div>

<?php get_footer( 'shop' ); ?>
