<?php
/**
 * Asset registration.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Built assets. dev-tools/build-assets.mjs turns the readable sources in
 * assets/css and assets/js into assets/dist (minified, with the files every
 * page needs merged into global.min.css / global.min.js). When the manifest is
 * there — and SCRIPT_DEBUG is off — those are served; otherwise the sources.
 * Handles that live inside a bundle stay registered (as empty ones) so the
 * dependency lists elsewhere keep working.
 */
function stocksystem_dist_manifest() {
	static $manifest = null;

	if ( null === $manifest ) {
		$manifest = false;
		$file     = STOCKSYSTEM_DIR . '/assets/dist/manifest.json';

		if ( ! ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) && is_readable( $file ) ) {
			$data = json_decode( (string) file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			if ( is_array( $data ) ) {
				$manifest = $data;
			}

			// While developing (WP_DEBUG) never serve a build older than the sources.
			if ( $manifest && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$newest = 0;
				foreach ( glob( STOCKSYSTEM_DIR . '/assets/{css,css/components,js}/*.{css,js}', GLOB_BRACE ) as $source ) {
					$newest = max( $newest, (int) filemtime( $source ) );
				}
				if ( $newest > (int) filemtime( $file ) ) {
					$manifest = false;
				}
			}
		}
	}

	return $manifest;
}

/** URL + cache-busting version of an asset ('css'|'js', path under assets/css|js), from dist when built. */
function stocksystem_asset( $type, $rel ) {
	$manifest = stocksystem_dist_manifest();
	$path     = 'assets/' . $type . '/' . $rel;

	if ( $manifest && isset( $manifest[ $type ][ $rel ] ) ) {
		$path = 'assets/dist/' . $manifest[ $type ][ $rel ];
	}

	$file = STOCKSYSTEM_DIR . '/' . $path;

	return array( STOCKSYSTEM_URI . '/' . $path, is_readable( $file ) ? (string) filemtime( $file ) : STOCKSYSTEM_VERSION );
}

function stocksystem_enqueue_style( $handle, $rel, $deps = array() ) {
	$manifest = stocksystem_dist_manifest();

	if ( $manifest && in_array( $rel, $manifest['global_css'], true ) ) {
		list( $url, $ver ) = array( STOCKSYSTEM_URI . '/assets/dist/global.min.css', (string) filemtime( STOCKSYSTEM_DIR . '/assets/dist/global.min.css' ) );
		wp_enqueue_style( 'stocksystem-global', $url, array(), $ver );
		wp_register_style( $handle, false, array( 'stocksystem-global' ) );
		wp_enqueue_style( $handle );
		return;
	}

	list( $url, $ver ) = stocksystem_asset( 'css', $rel );
	wp_enqueue_style( $handle, $url, $deps, $ver );
}

function stocksystem_enqueue_script( $handle, $rel, $deps = array() ) {
	$manifest = stocksystem_dist_manifest();

	if ( $manifest && in_array( $rel, $manifest['global_js'], true ) ) {
		$url = STOCKSYSTEM_URI . '/assets/dist/global.min.js';
		wp_enqueue_script( 'stocksystem-global', $url, array(), (string) filemtime( STOCKSYSTEM_DIR . '/assets/dist/global.min.js' ), true );
		wp_register_script( $handle, false, array( 'stocksystem-global' ), false, true );
		wp_enqueue_script( $handle );
		return;
	}

	list( $url, $ver ) = stocksystem_asset( 'js', $rel );
	wp_enqueue_script( $handle, $url, $deps, $ver, true );
}

function stocksystem_enqueue_assets() {
	stocksystem_enqueue_style( 'stocksystem-fonts', 'fonts.css', array() );
	stocksystem_enqueue_style( 'stocksystem-tokens', 'tokens.css', array() );
	stocksystem_enqueue_style( 'stocksystem-base', 'base.css', array( 'stocksystem-tokens', 'stocksystem-fonts' ) );
	stocksystem_enqueue_style( 'stocksystem-buttons', 'components/buttons.css', array( 'stocksystem-base' ) );
	stocksystem_enqueue_style( 'stocksystem-toast', 'components/toast.css', array( 'stocksystem-base' ) );
	stocksystem_enqueue_style( 'stocksystem-notices', 'components/notices.css', array( 'stocksystem-base' ) );
	stocksystem_enqueue_style( 'stocksystem-header', 'components/header.css', array( 'stocksystem-buttons' ) );
	stocksystem_enqueue_style( 'stocksystem-footer', 'components/footer.css', array( 'stocksystem-buttons' ) );
	stocksystem_enqueue_style( 'stocksystem-product-card', 'components/product-card.css', array( 'stocksystem-buttons' ) );
	stocksystem_enqueue_style( 'stocksystem-archive', 'components/archive.css', array( 'stocksystem-product-card' ) );
	if ( stocksystem_dist_manifest() ) {
		wp_register_style( 'stocksystem-style', false, array( 'stocksystem-global' ) ); // style.css is only the theme header comment.
		wp_enqueue_style( 'stocksystem-style' );
	} else {
		wp_enqueue_style( 'stocksystem-style', get_stylesheet_uri(), array( 'stocksystem-header', 'stocksystem-footer', 'stocksystem-product-card', 'stocksystem-archive' ), STOCKSYSTEM_VERSION );
	}

	stocksystem_enqueue_script( 'stocksystem-breakpoints', 'breakpoints.js', array() );
	stocksystem_enqueue_script( 'stocksystem-navigation', 'navigation.js', array( 'stocksystem-breakpoints' ) );
	stocksystem_enqueue_script( 'stocksystem-typing-state', 'typing-state.js', array() );
	stocksystem_enqueue_script( 'stocksystem-toast', 'toast.js', array() );
	stocksystem_enqueue_script( 'stocksystem-product-card', 'product-card.js', array() );
	stocksystem_enqueue_script( 'stocksystem-archive-filters', 'archive-filters.js', array() );
	stocksystem_enqueue_script( 'stocksystem-faq-accordion', 'faq-accordion.js', array() );

	stocksystem_enqueue_script( 'stocksystem-wishlist', 'wishlist.js', array() );
	wp_localize_script(
		stocksystem_dist_manifest() ? 'stocksystem-global' : 'stocksystem-wishlist',
		'stocksystemAjax',
		array(
			'url'        => admin_url( 'admin-ajax.php' ),
			'accountUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/my-account/' ),
		)
	);

	stocksystem_enqueue_style( 'stocksystem-blog', 'components/blog.css', array( 'stocksystem-buttons' ) );

	if ( is_front_page() ) {
		stocksystem_enqueue_style( 'stocksystem-home', 'components/home.css', array( 'stocksystem-product-card', 'stocksystem-blog' ) );
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		stocksystem_enqueue_style( 'stocksystem-product-page', 'components/product-page.css', array( 'stocksystem-product-card' ) );
		stocksystem_enqueue_script( 'stocksystem-product-configurator', 'product-configurator.js', array( 'jquery', 'wc-add-to-cart-variation' ) );
		stocksystem_enqueue_script( 'stocksystem-product-page', 'product-page.js', array() );
	}

	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		stocksystem_enqueue_style( 'stocksystem-checkout', 'components/checkout.css', array( 'stocksystem-product-card' ) );
		stocksystem_enqueue_script( 'stocksystem-product-page', 'product-page.js', array() );
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		stocksystem_enqueue_script( 'stocksystem-cart', 'cart.js', array() );
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		stocksystem_enqueue_script( 'stocksystem-checkout', 'checkout.js', array( 'jquery' ) );
	}

	if ( is_page_template( 'page-templates/about-contact.php' ) ) {
		stocksystem_enqueue_style( 'stocksystem-about', 'components/about.css', array( 'stocksystem-buttons' ) );
	}

	if ( is_page_template( 'page-templates/repair.php' ) ) {
		stocksystem_enqueue_style( 'stocksystem-repair', 'components/repair.css', array( 'stocksystem-buttons' ) );
	}

	if ( is_page_template( 'page-templates/stock-condition.php' ) ) {
		stocksystem_enqueue_style( 'stocksystem-product-page', 'components/product-page.css', array( 'stocksystem-product-card' ) );
		stocksystem_enqueue_style( 'stocksystem-grading', 'components/grading.css', array( 'stocksystem-product-page' ) );
	}

	if ( is_page_template( array( 'page-templates/terms.php', 'page-templates/privacy.php', 'page-templates/faq.php' ) ) || ( is_page() && ! is_page_template() && ! is_front_page() ) ) {
		stocksystem_enqueue_style( 'stocksystem-support', 'components/support.css', array( 'stocksystem-buttons' ) );
	}

	if ( is_page_template( 'page-templates/faq.php' ) ) {
		stocksystem_enqueue_script( 'stocksystem-faq-filter', 'faq-filter.js', array() );
	}

	if ( is_404() ) {
		stocksystem_enqueue_style( 'stocksystem-error-404', 'components/error-404.css', array( 'stocksystem-buttons' ) );
	}

	$is_account_area = ( function_exists( 'is_account_page' ) && is_account_page() )
		|| is_page_template( 'page-templates/order-tracking.php' );

	if ( $is_account_area ) {
		// checkout.css carries the .order-timeline the order cards reuse.
		stocksystem_enqueue_style( 'stocksystem-checkout', 'components/checkout.css', array( 'stocksystem-product-card' ) );
		stocksystem_enqueue_style( 'stocksystem-account', 'components/account.css', array( 'stocksystem-product-card', 'stocksystem-checkout' ) );
		stocksystem_enqueue_script( 'stocksystem-account-nav', 'account-nav.js', array() );
		if ( function_exists( 'is_account_page' ) && is_account_page() && ! is_user_logged_in() ) {
			stocksystem_enqueue_script( 'stocksystem-otp-login', 'otp-login.js', array( 'stocksystem-wishlist' ) );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'stocksystem_enqueue_assets' );
