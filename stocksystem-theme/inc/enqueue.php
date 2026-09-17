<?php
/**
 * Asset registration.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_enqueue_assets() {
	wp_enqueue_style( 'stocksystem-fonts', STOCKSYSTEM_URI . '/assets/css/fonts.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-tokens', STOCKSYSTEM_URI . '/assets/css/tokens.css', array(), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-base', STOCKSYSTEM_URI . '/assets/css/base.css', array( 'stocksystem-tokens', 'stocksystem-fonts' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-buttons', STOCKSYSTEM_URI . '/assets/css/components/buttons.css', array( 'stocksystem-base' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-header', STOCKSYSTEM_URI . '/assets/css/components/header.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-footer', STOCKSYSTEM_URI . '/assets/css/components/footer.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-product-card', STOCKSYSTEM_URI . '/assets/css/components/product-card.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-archive', STOCKSYSTEM_URI . '/assets/css/components/archive.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-style', get_stylesheet_uri(), array( 'stocksystem-header', 'stocksystem-footer', 'stocksystem-product-card', 'stocksystem-archive' ), STOCKSYSTEM_VERSION );

	wp_enqueue_script( 'stocksystem-breakpoints', STOCKSYSTEM_URI . '/assets/js/breakpoints.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-navigation', STOCKSYSTEM_URI . '/assets/js/navigation.js', array( 'stocksystem-breakpoints' ), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-product-card', STOCKSYSTEM_URI . '/assets/js/product-card.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-archive-filters', STOCKSYSTEM_URI . '/assets/js/archive-filters.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-faq-accordion', STOCKSYSTEM_URI . '/assets/js/faq-accordion.js', array(), STOCKSYSTEM_VERSION, true );

	if ( is_front_page() ) {
		wp_enqueue_style( 'stocksystem-home', STOCKSYSTEM_URI . '/assets/css/components/home.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'stocksystem-product-page', STOCKSYSTEM_URI . '/assets/css/components/product-page.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
		wp_enqueue_script( 'stocksystem-product-configurator', STOCKSYSTEM_URI . '/assets/js/product-configurator.js', array( 'jquery', 'wc-add-to-cart-variation' ), STOCKSYSTEM_VERSION, true );
		wp_enqueue_script( 'stocksystem-product-page', STOCKSYSTEM_URI . '/assets/js/product-page.js', array(), STOCKSYSTEM_VERSION, true );
	}

	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		wp_enqueue_style( 'stocksystem-checkout', STOCKSYSTEM_URI . '/assets/css/components/checkout.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
		wp_enqueue_script( 'stocksystem-product-page', STOCKSYSTEM_URI . '/assets/js/product-page.js', array(), STOCKSYSTEM_VERSION, true );
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_script( 'stocksystem-checkout', STOCKSYSTEM_URI . '/assets/js/checkout.js', array( 'jquery' ), STOCKSYSTEM_VERSION, true );
	}
}
add_action( 'wp_enqueue_scripts', 'stocksystem_enqueue_assets' );
