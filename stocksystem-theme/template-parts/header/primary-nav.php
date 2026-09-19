<?php
/**
 * Category nav bar + mega menu trigger. Source: 01 Home.dc.html,
 * 11 Desktop States.dc.html §01.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories  = stocksystem_nav_categories();
$shop_url    = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<nav class="primary-nav" aria-label="<?php esc_attr_e( 'دسته‌های محصول', 'stocksystem' ); ?>">
	<div class="container primary-nav__inner">
		<button
			type="button"
			class="primary-nav__all-categories"
			id="mega-menu-toggle"
			aria-haspopup="true"
			aria-expanded="false"
			aria-controls="mega-menu"
		>
			<?php esc_html_e( 'همهٔ دسته‌ها', 'stocksystem' ); ?>
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 9.5l6 6 6-6"></path></svg>
		</button>

		<span class="primary-nav__separator" aria-hidden="true"></span>

		<ul class="primary-nav__links">
			<?php foreach ( $categories as $category ) : ?>
				<li>
					<a
						href="<?php echo esc_url( $category->url ); ?>"
						<?php echo ( ! empty( $category->id ) && is_tax( 'product_cat', $category->id ) ) ? 'aria-current="page"' : ''; ?>
					><?php echo esc_html( $category->name ); ?></a>
				</li>
			<?php endforeach; ?>
			<li><a href="<?php echo esc_url( home_url( '/repair/' ) ); ?>"<?php echo is_page( 'repair' ) ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'تعمیرات تخصصی', 'stocksystem' ); ?></a></li>
			<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"<?php echo ( is_home() || is_singular( 'post' ) || is_category() ) ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'بلاگ', 'stocksystem' ); ?></a></li>
		</ul>
	</div>

	<?php get_template_part( 'template-parts/header/mega-menu' ); ?>
</nav>
