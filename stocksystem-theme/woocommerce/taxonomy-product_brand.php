<?php
/**
 * Brand archive — extends the category archive layout with a brand-intro
 * block (logo tile, description, live stats). Source: 16 Shop Pages.dc.html
 * §16-B.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$term    = get_queried_object();
$battery = get_term_meta( $term->term_id, 'avg_battery_health', true );
$return_rate = get_term_meta( $term->term_id, 'return_rate_percent', true );

// The stored term count is always 0 for this taxonomy; count published products.
$brand_counts = stocksystem_brand_product_counts();
$brand_count  = isset( $brand_counts[ $term->term_id ] ) ? $brand_counts[ $term->term_id ] : 0;
?>

<div class="archive-header">
	<div class="container">
		<?php woocommerce_breadcrumb(); ?>
	</div>
</div>

<div class="container">
	<div class="brand-intro">
		<span class="brand-intro__logo ltr"><?php echo esc_html( $term->name ); ?></span>
		<div class="brand-intro__body">
			<h1 class="brand-intro__title"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( $term->description ) : ?>
				<p class="brand-intro__description"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
			<div class="brand-intro__stats">
				<span class="brand-intro__stat brand-intro__stat--accent">
					<?php
					printf(
						/* translators: %s: in-stock device count, Persian digits */
						esc_html__( '%s دستگاه موجود', 'stocksystem' ),
						esc_html( stocksystem_to_persian_digits( $brand_count ) )
					);
					?>
				</span>
				<?php if ( '' !== $battery ) : ?>
					<span class="brand-intro__stat">
						<?php
						printf(
							/* translators: %s: average battery health percent, Persian digits */
							esc_html__( 'میانگین سلامت باتری %s٪', 'stocksystem' ),
							esc_html( stocksystem_to_persian_digits( $battery ) )
						);
						?>
					</span>
				<?php endif; ?>
				<?php if ( '' !== $return_rate ) : ?>
					<span class="brand-intro__stat">
						<?php
						printf(
							/* translators: %s: return rate percent, Persian digits */
							esc_html__( 'نرخ مرجوعی %s٪', 'stocksystem' ),
							esc_html( stocksystem_to_persian_digits( $return_rate ) )
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>
	</div>

	<?php get_template_part( 'template-parts/archive/results', null, array( 'show_stock_facets' => false, 'exclude' => array( 'brand' ) ) ); ?>
</div>

<?php get_footer( 'shop' ); ?>
