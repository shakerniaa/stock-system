<?php
/**
 * Stock System theme bootstrap.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// In WP_DEBUG the asset version changes every request, so a CSS/JS edit is
// never hidden behind a browser cache keyed on an unchanged ?ver=0.1.0
// (that stale-cache trap made a finished CSS change look like it hadn't
// applied). Production keeps the fixed theme version for real caching.
define( 'STOCKSYSTEM_VERSION', ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? '0.1.0.' . time() : '0.1.0' );
define( 'STOCKSYSTEM_DIR', get_template_directory() );
define( 'STOCKSYSTEM_URI', get_template_directory_uri() );

require STOCKSYSTEM_DIR . '/inc/setup.php';
require STOCKSYSTEM_DIR . '/inc/enqueue.php';
require STOCKSYSTEM_DIR . '/inc/customizer-settings.php';
require STOCKSYSTEM_DIR . '/inc/home-settings.php';
require STOCKSYSTEM_DIR . '/inc/site-texts.php';
require STOCKSYSTEM_DIR . '/inc/nav-settings.php';
require STOCKSYSTEM_DIR . '/inc/menus.php';
require STOCKSYSTEM_DIR . '/inc/js-strings.php';
require STOCKSYSTEM_DIR . '/inc/persian-numerals.php';
require STOCKSYSTEM_DIR . '/inc/taxonomies.php';
require STOCKSYSTEM_DIR . '/inc/term-meta-fields.php';
require STOCKSYSTEM_DIR . '/inc/nav-data.php';
require STOCKSYSTEM_DIR . '/inc/product-card.php';
require STOCKSYSTEM_DIR . '/inc/archive-filters.php';
require STOCKSYSTEM_DIR . '/inc/product-addons.php';
require STOCKSYSTEM_DIR . '/inc/product-configurator.php';
require STOCKSYSTEM_DIR . '/inc/product-test-report.php';
require STOCKSYSTEM_DIR . '/inc/product-notify.php';
require STOCKSYSTEM_DIR . '/inc/order-statuses.php';
require STOCKSYSTEM_DIR . '/inc/wallet.php';
require STOCKSYSTEM_DIR . '/inc/checkout-fields.php';
require STOCKSYSTEM_DIR . '/inc/otp-auth.php';
require STOCKSYSTEM_DIR . '/inc/account-endpoints.php';
require STOCKSYSTEM_DIR . '/inc/wishlist.php';
require STOCKSYSTEM_DIR . '/inc/woocommerce.php';
require STOCKSYSTEM_DIR . '/inc/mini-cart.php';
require STOCKSYSTEM_DIR . '/inc/content-pages.php';
require STOCKSYSTEM_DIR . '/inc/blog.php';
require STOCKSYSTEM_DIR . '/inc/repair.php';
require STOCKSYSTEM_DIR . '/inc/support-pages.php';
