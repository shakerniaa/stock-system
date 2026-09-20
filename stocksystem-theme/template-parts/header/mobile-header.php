<?php
/**
 * Mobile header — hamburger, logo, search icon, cart icon, search bar.
 * Hidden ≥1024px via CSS (assets/css/components/header.css).
 * Source: 01 Home.dc.html "mobile home", 10 Mobile Flows.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
?>
<div class="mobile-header">
	<div class="mobile-header__row">
		<button
			type="button"
			class="mobile-header__hamburger"
			id="mobile-drawer-toggle"
			aria-haspopup="true"
			aria-expanded="false"
			aria-controls="mobile-drawer"
		>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'باز کردن منو', 'stocksystem' ); ?></span>
		</button>

		<a class="mobile-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img src="<?php echo esc_url( stocksystem_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="110" height="26">
		</a>

		<span class="mobile-header__row-actions">
			<a class="mobile-header__icon-btn" href="#site-search-input-mobile">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M16.5 16.5L21 21"></path></svg>
				<span class="screen-reader-text"><?php esc_html_e( 'جست‌وجو', 'stocksystem' ); ?></span>
			</a>
			<a class="mobile-header__icon-btn" id="mini-cart-toggle-mobile" href="<?php echo esc_url( $cart_url ); ?>" aria-haspopup="true" aria-expanded="false" aria-controls="mini-cart">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16l-1.5 10.5a2 2 0 0 1-2 1.7H7.5a2 2 0 0 1-2-1.7L4 6z"></path><path d="M9 6V4.5a3 3 0 0 1 6 0V6"></path></svg>
				<span class="mobile-header__cart-count"<?php echo 0 === $cart_count ? ' hidden' : ''; ?>><?php echo esc_html( stocksystem_to_persian_digits( $cart_count ) ); ?></span>
				<span class="screen-reader-text"><?php esc_html_e( 'سبد خرید', 'stocksystem' ); ?></span>
			</a>
		</span>
	</div>

	<div class="mobile-header__search-wrap">
		<form class="mobile-header__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label for="site-search-input-mobile" class="screen-reader-text"><?php esc_html_e( 'جست‌وجوی مدل، برند یا مشخصات', 'stocksystem' ); ?></label>
			<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M16.5 16.5L21 21"></path></svg>
			<input
				type="search"
				id="site-search-input-mobile"
				name="s"
				autocomplete="off"
				placeholder="<?php esc_attr_e( 'جست‌وجوی مدل، برند یا مشخصات…', 'stocksystem' ); ?>"
				aria-expanded="false"
				aria-controls="search-suggestions-mobile"
				aria-autocomplete="list"
			>
			<input type="hidden" name="post_type" value="product">
			<button type="submit" class="screen-reader-text"><?php esc_html_e( 'جست‌وجو', 'stocksystem' ); ?></button>
		</form>
		<?php get_template_part( 'template-parts/header/search-suggestions', null, array( 'id' => 'search-suggestions-mobile' ) ); ?>
	</div>
</div>

<?php get_template_part( 'template-parts/header/mobile-drawer' ); ?>
