<?php
/**
 * "دسته‌بندی خدمات و محصولات" — category tiles. Source: 01 Home.dc.html.
 * Uses the same taxonomy data as the header mega menu / footer, rather
 * than a separately hardcoded category list, so the site has one source
 * of truth for the category set.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = stocksystem_nav_categories();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<section class="home-categories">
	<div class="container">
		<div class="home-categories__header">
			<h2><?php esc_html_e( 'دسته‌بندی خدمات و محصولات', 'stocksystem' ); ?></h2>
			<a href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'مشاهدهٔ همه ←', 'stocksystem' ); ?></a>
		</div>

		<div class="home-categories__grid">
			<?php foreach ( $categories as $category ) : ?>
				<a class="home-categories__tile" href="<?php echo esc_url( $category->url ); ?>">
					<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo stocksystem_category_icon( $category->name ); // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG paths ?></svg>
					<span class="home-categories__name"><?php echo esc_html( $category->name ); ?></span>
					<span class="home-categories__count"><?php echo esc_html( stocksystem_to_persian_digits( $category->count ) ); ?> <?php esc_html_e( 'کالا', 'stocksystem' ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
