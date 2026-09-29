<?php
/**
 * Homepage. Source: 01 Home.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php
// Hero first; the rest in the order (and with the on/off switches) set in
// Appearance → «صفحهٔ اصلی» (inc/home-settings.php).
get_template_part( 'template-parts/home/hero' );

$stocksystem_home_parts = array(
	'categories' => 'template-parts/home/categories',
	'featured'   => 'template-parts/home/featured',
	'trust'      => 'template-parts/home/trust-band',
	'blog'       => 'template-parts/home/blog-teaser',
	'banner'     => 'template-parts/home/banner',
	'testimonials' => 'template-parts/home/testimonials',
	'brands'     => 'template-parts/home/brands-logos',
	'counters'   => 'template-parts/home/counters',
);

foreach ( stocksystem_home_section_order() as $stocksystem_home_slug ) {
	get_template_part( $stocksystem_home_parts[ $stocksystem_home_slug ] );
}

// Extra product rows (استوک سیستم ← «ردیف‌های محصول»), each with its own
// heading, source and count — see inc/home-rows.php.
get_template_part( 'template-parts/home/product-rows' );
?>

<?php get_footer(); ?>
