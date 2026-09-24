<?php
/**
 * Mobile drawer nav — slides in from the inline-end edge (RTL: right).
 * Source: 10 Mobile Flows.dc.html §B1.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories   = stocksystem_nav_categories();
$account_url  = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'myaccount' ) : home_url( '/account/' );
$is_logged_in = is_user_logged_in();
?>
<div class="mobile-drawer-backdrop" id="mobile-drawer-backdrop" hidden></div>
<div id="mobile-drawer" class="mobile-drawer" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'منوی اصلی', 'stocksystem' ); ?>" hidden>
	<div class="mobile-drawer__header">
		<img src="<?php echo esc_url( stocksystem_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="74" height="30">
		<button type="button" class="mobile-drawer__close" id="mobile-drawer-close">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'بستن منو', 'stocksystem' ); ?></span>
		</button>
	</div>

	<nav class="mobile-drawer__nav" aria-label="<?php esc_attr_e( 'دسته‌های محصول', 'stocksystem' ); ?>">
		<?php foreach ( $categories as $category ) : ?>
			<a href="<?php echo esc_url( $category->url ); ?>">
				<?php echo esc_html( $category->name ); ?>
				<span class="mobile-drawer__chevron" aria-hidden="true">‹</span>
			</a>
		<?php endforeach; ?>
		<a href="<?php echo esc_url( home_url( '/repair/' ) ); ?>"><?php esc_html_e( 'تعمیرات تخصصی', 'stocksystem' ); ?></a>

		<span class="mobile-drawer__divider" aria-hidden="true"></span>

		<a href="<?php echo esc_url( home_url( '/stock-condition/' ) ); ?>"><?php esc_html_e( 'وضعیت کالای استوک', 'stocksystem' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'بلاگ', 'stocksystem' ); ?></a>
		<a href="<?php echo esc_url( stocksystem_order_tracking_url() ); ?>"><?php esc_html_e( 'پیگیری سفارش', 'stocksystem' ); ?></a>
		<a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'دربارهٔ ما', 'stocksystem' ); ?></a>
	</nav>

	<div class="mobile-drawer__footer">
		<a class="btn btn--phone ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
			<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
			<?php echo esc_html( stocksystem_business( 'phone' ) ); ?>
		</a>
		<a class="btn btn--outline-on-dark" href="<?php echo esc_url( $account_url ); ?>">
			<?php echo $is_logged_in ? esc_html__( 'حساب کاربری', 'stocksystem' ) : esc_html__( 'ورود / ثبت‌نام', 'stocksystem' ); ?>
		</a>
	</div>
</div>
