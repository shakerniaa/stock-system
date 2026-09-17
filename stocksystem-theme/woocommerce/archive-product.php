<?php
/**
 * Shop root — a hub, not a bare grid: category tiles, budget links,
 * brand tiles, then bestsellers. README: "the shop root ... entry
 * paths, not a bare grid." Source: 16 Shop Pages.dc.html §16-A.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$categories = stocksystem_nav_categories();
$ranges     = stocksystem_price_ranges();
$brands     = stocksystem_nav_brands();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$total_count = array_sum( wp_list_pluck( $categories, 'count' ) );

$bestsellers = function_exists( 'wc_get_products' ) ? wc_get_products(
	array(
		'limit'   => 4,
		'orderby' => 'popularity',
		'status'  => 'publish',
	)
) : array();
?>

<div class="archive-header">
	<div class="container">
		<?php woocommerce_breadcrumb(); ?>
		<h1 class="archive-header__title"><?php esc_html_e( 'فروشگاه استوک سیستم', 'stocksystem' ); ?></h1>
		<p class="archive-header__count">
			<?php
			printf(
				/* translators: 1: total device count, 2: category count, both Persian digits */
				esc_html__( '%1$s دستگاه تست‌شده در %2$s دسته · همه با برگهٔ وضعیت', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( $total_count ) ),
				esc_html( stocksystem_to_persian_digits( count( $categories ) ) )
			);
			?>
		</p>
	</div>
</div>

<div class="container shop-hub">
	<section class="shop-hub__section">
		<h2><?php esc_html_e( 'با دسته شروع کنید', 'stocksystem' ); ?></h2>
		<div class="shop-hub__category-tiles">
			<?php foreach ( $categories as $category ) : ?>
				<a class="shop-hub__category-tile" href="<?php echo esc_url( $category->url ); ?>">
					<span class="shop-hub__category-icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="11" rx="1.5"></rect><path d="M2 19h20"></path></svg>
					</span>
					<span class="shop-hub__category-name"><?php echo esc_html( $category->name ); ?></span>
					<span class="shop-hub__category-count"><?php echo esc_html( stocksystem_to_persian_digits( $category->count ) ); ?> <?php esc_html_e( 'کالا', 'stocksystem' ); ?></span>
				</a>
			<?php endforeach; ?>

			<?php if ( has_term( 'clearance', 'product_tag' ) ) : ?>
				<a class="shop-hub__category-tile shop-hub__category-tile--clearance" href="<?php echo esc_url( add_query_arg( 'product_tag', 'clearance', $shop_url ) ); ?>">
					<span class="shop-hub__category-icon" aria-hidden="true">
						<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"><path d="M12 3l2.5 5.5L20 9l-4 4 1 6-5-3-5 3 1-6-4-4 5.5-.5z"></path></svg>
					</span>
					<span class="shop-hub__category-name"><?php esc_html_e( 'حراج ویژه', 'stocksystem' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</section>

	<div class="shop-hub__split">
		<section class="shop-hub__section">
			<h2><?php esc_html_e( 'با بودجه شروع کنید', 'stocksystem' ); ?></h2>
			<div class="shop-hub__budget-list">
				<?php foreach ( $ranges as $range ) : ?>
					<a class="shop-hub__budget-row" href="<?php echo esc_url( add_query_arg( array( 'min_price' => $range['min'], 'max_price' => $range['max'] ), $shop_url ) ); ?>">
						<span><?php echo esc_html( $range['label'] ); ?> <?php esc_html_e( 'تومان', 'stocksystem' ); ?></span>
						<span aria-hidden="true">←</span>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="shop-hub__section">
			<h2><?php esc_html_e( 'با برند شروع کنید', 'stocksystem' ); ?></h2>
			<div class="shop-hub__brand-tiles">
				<?php foreach ( $brands as $brand ) : ?>
					<a class="shop-hub__brand-tile ltr" href="<?php echo esc_url( $brand->url ); ?>"><?php echo esc_html( $brand->name ); ?></a>
				<?php endforeach; ?>
				<a class="shop-hub__brand-tile shop-hub__brand-tile--all" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'همهٔ برندها ←', 'stocksystem' ); ?></a>
			</div>
		</section>
	</div>

	<?php if ( ! empty( $bestsellers ) ) : ?>
		<section class="shop-hub__section">
			<span class="shop-hub__section-heading-row">
				<h2><?php esc_html_e( 'پرفروش‌های این هفته', 'stocksystem' ); ?></h2>
				<a href="<?php echo esc_url( add_query_arg( 'orderby', 'popularity', $shop_url ) ); ?>"><?php esc_html_e( 'مشاهدهٔ همه ←', 'stocksystem' ); ?></a>
			</span>
			<div class="product-grid">
				<?php
				foreach ( $bestsellers as $bestseller ) :
					global $product;
					$product = $bestseller;
					get_template_part( 'template-parts/product/card' );
				endforeach;
				?>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php get_footer( 'shop' ); ?>
