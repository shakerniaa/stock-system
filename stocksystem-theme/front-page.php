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

<?php get_template_part( 'template-parts/home/hero' ); ?>
<?php get_template_part( 'template-parts/home/categories' ); ?>
<?php get_template_part( 'template-parts/home/featured' ); ?>
<?php get_template_part( 'template-parts/home/trust-band' ); ?>
<?php get_template_part( 'template-parts/home/blog-teaser' ); ?>

<?php get_footer(); ?>
