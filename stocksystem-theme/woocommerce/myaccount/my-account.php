<?php
/**
 * My Account page shell — 3/9 sidebar + content grid.
 * Source: 08 Account.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="account-page">
	<div class="container account-page__grid">
		<?php do_action( 'woocommerce_account_navigation' ); ?>

		<div class="woocommerce-MyAccount-content account-page__content">
			<?php do_action( 'woocommerce_account_content' ); ?>
		</div>
	</div>
</div>
