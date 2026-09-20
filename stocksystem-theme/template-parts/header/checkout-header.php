<?php
/**
 * Distraction-free checkout header: logo + secure-connection note, no
 * nav/search/cart (12 Checkout Flow.dc.html uses this on all four steps).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="checkout-header">
	<div class="container checkout-header__inner">
		<a class="checkout-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( stocksystem_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="130" height="36">
		</a>
		<span class="checkout-header__secure">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 7.8-8 9-4.8-1.2-8-4.5-8-9V6z"></path><path d="M9 12l2.2 2.2L15.5 10"></path></svg>
			<span class="checkout-header__secure-full"><?php esc_html_e( 'اتصال امن · اطلاعات کارت نزد درگاه بانکی', 'stocksystem' ); ?></span>
			<span class="checkout-header__secure-short"><?php esc_html_e( 'اتصال امن', 'stocksystem' ); ?></span>
		</span>
	</div>
</div>
