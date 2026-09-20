<?php
/**
 * Mini-cart drawer — floats from the header, stays open 4s after an
 * "add to cart" (assets/js/navigation.js), 360px per spec.
 * Source: 11 Desktop States.dc.html §02.
 *
 * The contents live in mini-cart-content.php and are refreshed through the
 * cart-fragments API (inc/mini-cart.php) after every add / remove.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="mini-cart" class="mini-cart-panel" role="dialog" aria-label="<?php esc_attr_e( 'سبد خرید', 'stocksystem' ); ?>" hidden>
	<?php get_template_part( 'template-parts/header/mini-cart-content' ); ?>
</div>
