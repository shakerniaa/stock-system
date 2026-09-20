<?php
/**
 * Front-end performance. Nothing here changes what a visitor sees — it only
 * removes weight and render-blocking work the theme does not use:
 *
 *   • WordPress extras a classic shop theme never needs: emoji scripts/styles,
 *     block-editor CSS on pages without blocks, head links (generator, RSD,
 *     WLW, shortlink, oEmbed);
 *   • jQuery Migrate (WooCommerce 11 and the theme don't use the old APIs);
 *   • jQuery itself is deferred (WooCommerce's scripts already are) so it no
 *     longer blocks rendering — scripts that need it are deferred with it,
 *     and WordPress keeps the order;
 *   • the three most used Peyda weights are preloaded (no font-swap flash on
 *     the first paint);
 *   • the hero's first image is marked as the priority image.
 *
 * Minified/bundled CSS and JS are handled in inc/enqueue.php (assets/dist).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * WordPress extras
 * ---------------------------------------------------------------------- */

function stocksystem_perf_cleanup_head() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );

	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'template_redirect', 'rest_output_link_header', 11 );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'stocksystem_perf_cleanup_head' );

/** Block-editor CSS only where the page really has blocks (blog posts written in the editor). */
function stocksystem_perf_dequeue_block_css() {
	if ( is_singular() && has_blocks( get_post() ) ) {
		return;
	}

	foreach ( array( 'wp-block-library', 'wp-block-library-theme', 'global-styles', 'classic-theme-styles', 'wc-blocks-style', 'wc-blocks-vendors-style' ) as $handle ) {
		wp_dequeue_style( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'stocksystem_perf_dequeue_block_css', 100 );
add_action( 'wp_footer', 'stocksystem_perf_dequeue_block_css', 1 );

// Global-styles / SVG duotone filters are only for block themes.
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );

/* -------------------------------------------------------------------------
 * jQuery: no Migrate, deferred
 * ---------------------------------------------------------------------- */

add_action(
	'wp_default_scripts',
	function ( $scripts ) {
		if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
			return;
		}
		$scripts->registered['jquery']->deps = array_values( array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) ) );
	}
);

function stocksystem_perf_defer_jquery() {
	if ( is_admin() || is_customize_preview() ) {
		return;
	}

	// WordPress only defers a script when everything that depends on it is deferred
	// too, so the theme's own jQuery-dependent scripts are marked as well.
	foreach ( array( 'jquery-core', 'stocksystem-product-configurator', 'stocksystem-checkout' ) as $handle ) {
		wp_script_add_data( $handle, 'strategy', 'defer' );
	}
}
add_action( 'wp_enqueue_scripts', 'stocksystem_perf_defer_jquery', 100 );

/* -------------------------------------------------------------------------
 * Fonts
 * ---------------------------------------------------------------------- */

function stocksystem_perf_preload_fonts() {
	foreach ( array( 'Regular', 'SemiBold', 'ExtraBold' ) as $weight ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( STOCKSYSTEM_URI . '/assets/fonts/Peyda-' . $weight . '.woff2' )
		);
	}
}
add_action( 'wp_head', 'stocksystem_perf_preload_fonts', 1 );

/* -------------------------------------------------------------------------
 * Images
 * ---------------------------------------------------------------------- */

/**
 * Attributes for a theme-rendered <img>: the first $eager images of a kind on a
 * page load normally (they are usually above the fold), the rest are lazy.
 *
 * @param string $kind  Counter name ('product-card', 'blog-card', …).
 * @param int    $eager How many to leave eager.
 * @return array loading/decoding attributes for wp_get_attachment_image() & co.
 */
function stocksystem_image_attrs( $kind, $eager = 4 ) {
	static $seen = array();

	$seen[ $kind ] = isset( $seen[ $kind ] ) ? $seen[ $kind ] + 1 : 1;

	if ( $seen[ $kind ] > $eager ) {
		return array( 'loading' => 'lazy', 'decoding' => 'async', 'fetchpriority' => 'low' );
	}

	// WordPress would mark the first image on the page "high"; on the home page that is the hero's.
	return array( 'decoding' => 'async', 'fetchpriority' => ( 1 === $seen[ $kind ] && ! is_front_page() ) ? 'high' : 'auto' );
}

// New uploads: WordPress generates its sizes as WebP (about a third smaller than
// JPEG/PNG) when the server's image library supports it; the original file is kept.
add_filter(
	'image_editor_output_format',
	function ( $formats ) {
		if ( function_exists( 'wp_image_editor_supports' ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) ) ) {
			$formats['image/jpeg'] = 'image/webp';
			$formats['image/png']  = 'image/webp';
		}

		return $formats;
	}
);

add_filter(
	'wp_editor_set_quality',
	function ( $quality, $mime_type ) {
		return 'image/webp' === $mime_type ? 82 : $quality;
	},
	10,
	2
);

// Templates print product/post images through wp_kses_post(), which silently
// dropped srcset, sizes, loading and decoding — so every image was fetched at
// one size and none were lazy. Allow those (all inert) on <img>.
add_filter(
	'wp_kses_allowed_html',
	function ( $tags, $context ) {
		if ( 'post' === $context && isset( $tags['img'] ) ) {
			$tags['img'] = array_merge(
				$tags['img'],
				array(
					'srcset'        => true,
					'sizes'         => true,
					'loading'       => true,
					'decoding'      => true,
					'fetchpriority' => true,
				)
			);
		}

		return $tags;
	},
	10,
	2
);

/* -------------------------------------------------------------------------
 * Database
 * ---------------------------------------------------------------------- */

/**
 * get_option() for the theme's own settings. A *missing* option is never in
 * WordPress's autoload cache, so each one cost a query on every page; creating
 * it once (empty, autoloaded) puts all of them into the single autoload read.
 */
function stocksystem_get_option( $name ) {
	$value = get_option( $name, null );

	if ( null === $value ) {
		add_option( $name, array(), '', 'yes' );
		$value = array();
	}

	return $value;
}
