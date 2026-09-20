<?php
/**
 * Mini-cart + header cart-count badges, kept in sync through WooCommerce's
 * cart-fragments API: after an AJAX add / remove, WooCommerce re-renders
 * each selector below and the frontend swaps it into the page.
 *
 * WooCommerce no longer enqueues wc-cart-fragments by default (it is meant
 * for the mini-cart widget/block), so it is enqueued here explicitly.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_enqueue_cart_fragments() {
	wp_enqueue_script( 'wc-cart-fragments' );
}
add_action( 'wp_enqueue_scripts', 'stocksystem_enqueue_cart_fragments', 20 );

function stocksystem_cart_count_badge( $class, $id = '' ) {
	$count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;

	return sprintf(
		'<span class="%1$s"%2$s%3$s>%4$s</span>',
		esc_attr( $class ),
		$id ? ' id="' . esc_attr( $id ) . '"' : '',
		0 === $count ? ' hidden' : '',
		esc_html( stocksystem_to_persian_digits( $count ) )
	);
}

function stocksystem_cart_fragments( $fragments ) {
	ob_start();
	get_template_part( 'template-parts/header/mini-cart-content' );
	$fragments['#mini-cart-content'] = ob_get_clean();

	$fragments['#mini-cart-count']           = stocksystem_cart_count_badge( 'site-masthead__cart-count', 'mini-cart-count' );
	$fragments['.mobile-header__cart-count'] = stocksystem_cart_count_badge( 'mobile-header__cart-count' );

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'stocksystem_cart_fragments' );
