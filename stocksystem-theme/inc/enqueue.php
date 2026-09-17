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
	wp_enqueue_style( 'stocksystem-toast', STOCKSYSTEM_URI . '/assets/css/components/toast.css', array( 'stocksystem-base' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-header', STOCKSYSTEM_URI . '/assets/css/components/header.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-footer', STOCKSYSTEM_URI . '/assets/css/components/footer.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-product-card', STOCKSYSTEM_URI . '/assets/css/components/product-card.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-archive', STOCKSYSTEM_URI . '/assets/css/components/archive.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
	wp_enqueue_style( 'stocksystem-style', get_stylesheet_uri(), array( 'stocksystem-header', 'stocksystem-footer', 'stocksystem-product-card', 'stocksystem-archive' ), STOCKSYSTEM_VERSION );

	wp_enqueue_script( 'stocksystem-breakpoints', STOCKSYSTEM_URI . '/assets/js/breakpoints.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-navigation', STOCKSYSTEM_URI . '/assets/js/navigation.js', array( 'stocksystem-breakpoints' ), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-toast', STOCKSYSTEM_URI . '/assets/js/toast.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-product-card', STOCKSYSTEM_URI . '/assets/js/product-card.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-archive-filters', STOCKSYSTEM_URI . '/assets/js/archive-filters.js', array(), STOCKSYSTEM_VERSION, true );
	wp_enqueue_script( 'stocksystem-faq-accordion', STOCKSYSTEM_URI . '/assets/js/faq-accordion.js', array(), STOCKSYSTEM_VERSION, true );

	wp_enqueue_script( 'stocksystem-wishlist', STOCKSYSTEM_URI . '/assets/js/wishlist.js', array(), STOCKSYSTEM_VERSION, true );
	wp_localize_script(
		'stocksystem-wishlist',
		'stocksystemAjax',
		array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'accountUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ),
		)
	);

	wp_enqueue_style( 'stocksystem-blog', STOCKSYSTEM_URI . '/assets/css/components/blog.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );

	if ( is_front_page() ) {
		wp_enqueue_style( 'stocksystem-home', STOCKSYSTEM_URI . '/assets/css/components/home.css', array( 'stocksystem-product-card', 'stocksystem-blog' ), STOCKSYSTEM_VERSION );
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

	if ( is_page_template( 'page-templates/about-contact.php' ) ) {
		wp_enqueue_style( 'stocksystem-about', STOCKSYSTEM_URI . '/assets/css/components/about.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	}

	if ( is_page_template( 'page-templates/repair.php' ) ) {
		wp_enqueue_style( 'stocksystem-repair', STOCKSYSTEM_URI . '/assets/css/components/repair.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	}

	if ( is_page_template( 'page-templates/stock-condition.php' ) ) {
		wp_enqueue_style( 'stocksystem-product-page', STOCKSYSTEM_URI . '/assets/css/components/product-page.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
		wp_enqueue_style( 'stocksystem-grading', STOCKSYSTEM_URI . '/assets/css/components/grading.css', array( 'stocksystem-product-page' ), STOCKSYSTEM_VERSION );
	}

	if ( is_page_template( array( 'page-templates/terms.php', 'page-templates/privacy.php', 'page-templates/faq.php' ) ) ) {
		wp_enqueue_style( 'stocksystem-support', STOCKSYSTEM_URI . '/assets/css/components/support.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	}

	if ( is_page_template( 'page-templates/faq.php' ) ) {
		wp_enqueue_script( 'stocksystem-faq-filter', STOCKSYSTEM_URI . '/assets/js/faq-filter.js', array(), STOCKSYSTEM_VERSION, true );
	}

	if ( is_404() ) {
		wp_enqueue_style( 'stocksystem-error-404', STOCKSYSTEM_URI . '/assets/css/components/error-404.css', array( 'stocksystem-buttons' ), STOCKSYSTEM_VERSION );
	}

	$is_account_area = ( function_exists( 'is_account_page' ) && is_account_page() )
		|| is_page_template( 'page-templates/order-tracking.php' );

	if ( $is_account_area ) {
		wp_enqueue_style( 'stocksystem-account', STOCKSYSTEM_URI . '/assets/css/components/account.css', array( 'stocksystem-product-card' ), STOCKSYSTEM_VERSION );
		if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
			wp_enqueue_script( 'stocksystem-otp-login', STOCKSYSTEM_URI . '/assets/js/otp-login.js', array( 'stocksystem-wishlist' ), STOCKSYSTEM_VERSION, true );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'stocksystem_enqueue_assets' );
