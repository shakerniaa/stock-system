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
			<?php
			// Editable in Appearance → Menus (location «نوار اصلی»); these are the defaults.
			$extra_links = stocksystem_menu_links(
				'header_extra',
				array(
					array( 'title' => __( 'تعمیرات تخصصی', 'stocksystem' ), 'url' => home_url( '/repair/' ), 'current' => is_page( 'repair' ) ),
					array( 'title' => __( 'بلاگ', 'stocksystem' ), 'url' => home_url( '/blog/' ), 'current' => ( is_home() || is_singular( 'post' ) || is_category() ) ),
				)
			);
			foreach ( $extra_links as $extra_link ) :
				?>
				<li><a href="<?php echo esc_url( $extra_link['url'] ); ?>"<?php echo ! empty( $extra_link['current'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $extra_link['title'] ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php get_template_part( 'template-parts/header/mega-menu' ); ?>
</nav>
