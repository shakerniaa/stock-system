<?php
/**
 * Masthead — logo, search, phone, account, cart trigger.
 * Source: 01 Home.dc.html, 11 Desktop States.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
$cart_count = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
$account_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/account/' );
$is_logged_in = is_user_logged_in();
?>
<div class="site-masthead">
	<div class="container site-masthead__inner">
		<a class="site-masthead__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<img
				src="<?php echo esc_url( STOCKSYSTEM_URI . '/assets/images/logo-lockup-dark.png' ); ?>"
				alt="<?php bloginfo( 'name' ); ?>"
				width="150"
				height="42"
			>
		</a>

		<div class="site-masthead__search-wrap">
			<form class="site-masthead__search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label for="site-search-input" class="screen-reader-text"><?php esc_html_e( 'جست‌وجوی مدل، برند یا مشخصات', 'stocksystem' ); ?></label>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M16.5 16.5L21 21"></path></svg>
				<input
					type="search"
					id="site-search-input"
					name="s"
					autocomplete="off"
					placeholder="<?php esc_attr_e( 'جست‌وجوی مدل، برند یا مشخصات…', 'stocksystem' ); ?>"
					aria-expanded="false"
					aria-controls="search-suggestions"
					aria-autocomplete="list"
				>
				<input type="hidden" name="post_type" value="product">
				<button type="submit" class="screen-reader-text"><?php esc_html_e( 'جست‌وجو', 'stocksystem' ); ?></button>
			</form>
			<?php get_template_part( 'template-parts/header/search-suggestions', null, array( 'id' => 'search-suggestions' ) ); ?>
		</div>

		<div class="site-masthead__actions">
			<a class="site-masthead__phone ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
				<?php echo esc_html( stocksystem_business( 'phone' ) ); ?>
			</a>

			<span class="site-masthead__divider" aria-hidden="true"></span>

			<a
				class="site-masthead__icon-btn"
				id="mini-cart-toggle"
				href="<?php echo esc_url( $cart_url ); ?>"
				aria-haspopup="true"
				aria-expanded="false"
				aria-controls="mini-cart"
			>
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16l-1.5 10.5a2 2 0 0 1-2 1.7H7.5a2 2 0 0 1-2-1.7L4 6z"></path><path d="M9 6V4.5a3 3 0 0 1 6 0V6"></path></svg>
				<span class="site-masthead__cart-count" id="mini-cart-count"<?php echo 0 === $cart_count ? ' hidden' : ''; ?>>
					<?php echo esc_html( stocksystem_to_persian_digits( $cart_count ) ); ?>
				</span>
				<span class="screen-reader-text"><?php esc_html_e( 'سبد خرید', 'stocksystem' ); ?></span>
			</a>

			<a class="site-masthead__account-btn" href="<?php echo esc_url( $account_url ); ?>">
				<?php echo $is_logged_in ? esc_html__( 'حساب کاربری', 'stocksystem' ) : esc_html__( 'ورود / ثبت‌نام', 'stocksystem' ); ?>
			</a>
		</div>
	</div>
</div>

<?php get_template_part( 'template-parts/header/mini-cart' ); ?>
