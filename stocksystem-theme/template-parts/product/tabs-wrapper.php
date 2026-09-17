<?php
/**
 * Wraps WooCommerce's native product-data tabs (filtered in
 * inc/woocommerce.php) so they inherit this theme's styling. Native
 * tabs.js (part of WooCommerce's single-product script) handles the
 * click-to-switch behavior already — no custom JS needed here.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="product-tabs">
	<?php woocommerce_output_product_data_tabs(); ?>
</div>
