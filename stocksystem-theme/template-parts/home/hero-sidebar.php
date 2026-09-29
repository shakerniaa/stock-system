<?php
/**
 * Hero 1c — marketplace layout: always-open category column, banner in
 * the middle, "deal of the day" column on the other side.
 * Source: stocksystem-dev-kit/HomePage.dc.html §1c.
 *
 * The category column reuses stocksystem_nav_categories() (same source
 * as the mega menu) so it never drifts from the rest of the site, and
 * shows real published-product counts.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = stocksystem_nav_categories();
$counts     = stocksystem_category_product_counts();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$title      = trim( (string) stocksystem_hero( 'sidebar_title' ) );

if ( '' === $title ) {
	get_template_part( 'template-parts/home/hero', 'classic' );
	return;
}

// Deal column: an explicitly chosen product, else the first on-sale one.
$deal = null;
if ( ! empty( stocksystem_hero( 'sidebar_deal_on' ) ) && function_exists( 'wc_get_products' ) ) {
	$deal_id = (int) stocksystem_hero( 'sidebar_deal_id' );
	if ( $deal_id ) {
		$candidate = wc_get_product( $deal_id );
		$deal      = ( $candidate && 'publish' === $candidate->get_status() ) ? $candidate : null;
	}
	if ( ! $deal ) {
		$on_sale = wc_get_products( array( 'limit' => 1, 'status' => 'publish', 'on_sale' => true ) );
		$deal    = ! empty( $on_sale ) ? $on_sale[0] : null;
	}
}

$deal_end   = trim( (string) stocksystem_hero( 'sidebar_deal_end' ) );
$deal_stamp = $deal_end ? strtotime( $deal_end ) : 0;
$hero_image = (int) stocksystem_hero( 'sidebar_image' );
?>
<section class="hero-sidebar">
	<div class="container hero-sidebar__grid">
		<nav class="hero-sidebar__cats" aria-label="<?php esc_attr_e( 'دسته‌های محصول', 'stocksystem' ); ?>">
			<?php foreach ( $categories as $category ) : ?>
				<a class="hero-sidebar__cat" href="<?php echo esc_url( $category->url ); ?>">
					<span class="hero-sidebar__cat-dot" aria-hidden="true"></span>
					<span class="hero-sidebar__cat-name"><?php echo esc_html( $category->name ); ?></span>
					<?php if ( ! empty( $counts[ $category->id ] ) ) : ?>
						<span class="hero-sidebar__cat-count"><?php echo esc_html( stocksystem_format_number( $counts[ $category->id ] ) ); ?></span>
					<?php endif; ?>
					<svg class="hero-sidebar__cat-chevron" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"></path></svg>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="hero-sidebar__banner" style="<?php echo esc_attr( stocksystem_hero_theme_vars( stocksystem_hero( 'sidebar_theme' ) ) ); ?>">
			<span class="hero-sidebar__stripe" aria-hidden="true"></span>
			<?php if ( '' !== trim( (string) stocksystem_hero( 'sidebar_eyebrow' ) ) ) : ?>
				<span class="hero-sidebar__eyebrow"><?php echo esc_html( stocksystem_hero( 'sidebar_eyebrow' ) ); ?></span>
			<?php endif; ?>
			<h1 class="hero-sidebar__title"><?php echo esc_html( $title ); ?></h1>
			<?php if ( '' !== trim( (string) stocksystem_hero( 'sidebar_body' ) ) ) : ?>
				<p class="hero-sidebar__body"><?php echo esc_html( stocksystem_hero( 'sidebar_body' ) ); ?></p>
			<?php endif; ?>
			<?php if ( '' !== trim( (string) stocksystem_hero( 'sidebar_cta' ) ) ) : ?>
				<a class="btn hero-sidebar__cta" href="<?php echo esc_url( stocksystem_home_link( stocksystem_hero( 'sidebar_cta_url' ), $shop_url ) ); ?>"><?php echo esc_html( stocksystem_hero( 'sidebar_cta' ) ); ?></a>
			<?php endif; ?>
			<?php if ( $hero_image ) : ?>
				<?php echo wp_get_attachment_image( $hero_image, 'large', false, array( 'class' => 'hero-sidebar__img', 'decoding' => 'async', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $deal ) : ?>
			<aside class="hero-sidebar__deal">
				<div class="hero-sidebar__deal-card">
					<span class="hero-sidebar__deal-head">
						<span class="hero-sidebar__deal-title"><?php echo esc_html( stocksystem_hero( 'sidebar_deal_title' ) ); ?></span>
						<?php if ( $deal_stamp > time() ) : ?>
							<span class="hero-sidebar__deal-clock ltr" data-countdown-to="<?php echo esc_attr( $deal_stamp ); ?>">&mdash;</span>
						<?php endif; ?>
					</span>
					<a class="hero-sidebar__deal-media" href="<?php echo esc_url( $deal->get_permalink() ); ?>">
						<?php echo $deal->get_image( 'medium', array( 'decoding' => 'async', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce-escaped markup ?>
					</a>
					<a class="hero-sidebar__deal-name" href="<?php echo esc_url( $deal->get_permalink() ); ?>"><?php echo esc_html( $deal->get_name() ); ?></a>
					<span class="hero-sidebar__deal-price"><?php echo wp_kses_post( $deal->get_price_html() ); ?></span>
				</div>

				<div class="hero-sidebar__trust">
					<span class="hero-sidebar__trust-item">
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.4 8-8 9-4.6-1-8-4.5-8-9V6z"></path></svg>
						<?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?>
					</span>
					<span class="hero-sidebar__trust-item">
						<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 12a8 8 0 1 0 3-6.3M4 4v4h4"></path></svg>
						<?php esc_html_e( '۷ روز بازگشت', 'stocksystem' ); ?>
					</span>
				</div>
			</aside>
		<?php endif; ?>
	</div>
</section>
