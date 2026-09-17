<?php
/**
 * Stock System theme bootstrap.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'STOCKSYSTEM_VERSION', '0.1.0' );
define( 'STOCKSYSTEM_DIR', get_template_directory() );
define( 'STOCKSYSTEM_URI', get_template_directory_uri() );

require STOCKSYSTEM_DIR . '/inc/setup.php';
require STOCKSYSTEM_DIR . '/inc/enqueue.php';
require STOCKSYSTEM_DIR . '/inc/customizer-settings.php';
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
