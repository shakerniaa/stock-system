<?php
/**
 * Account sidebar nav. Source: 08 Account.dc.html (icons kept minimal —
 * this theme only builds the menu items that are actually implemented;
 * the design's repair-requests/comparison/loyalty-points/tickets items
 * aren't part of this build's scope, see PROGRESS.md).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_account_navigation' );

$icons = stocksystem_account_menu_icons();
?>
<nav class="account-nav woocommerce-MyAccount-navigation">
	<ul>
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) : ?>
			<?php $icon = $icons[ $endpoint ] ?? $icons['dashboard']; ?>
			<li class="account-nav__item <?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?><?php echo 'customer-logout' === $endpoint ? ' account-nav__item--logout' : ''; ?>">
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>">
					<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $icon; /* phpcs:ignore -- fixed inline SVG paths, no user data */ ?></svg>
					<?php echo esc_html( $label ); ?>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
