<?php
/**
 * Theme setup: supports, menus, sidebars.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_setup() {
	load_theme_textdomain( 'stocksystem', STOCKSYSTEM_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 4,
				'min_columns'     => 1,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	// No registered nav menu locations: the primary/mobile nav and footer
	// nav are both driven by the product_cat taxonomy (see inc/nav-data.php)
	// rather than an editable WP menu, since the design ties them to the
	// catalog. Add a location back here if a later template needs one.
}
add_action( 'after_setup_theme', 'stocksystem_setup' );

function stocksystem_body_classes( $classes ) {
	$classes[] = 'ss-rtl';
	return $classes;
}
add_filter( 'body_class', 'stocksystem_body_classes' );
