<?php
/**
 * Site footer — 4-column layout, newsletter, trust badges, copyright.
 * Source: 01 Home.dc.html "data-proto-footer".
 *
 * Decision #5/#7 fixes applied here: warranty and address/phone come from
 * the customizer (Neyshabur / 1-month), not the design's Tehran/18-month
 * placeholder copy. Decision #6: the installment link is dropped.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = stocksystem_nav_categories();
$hours      = stocksystem_business( 'store_hours' );
?>
<footer id="colophon" class="site-footer">
	<div class="container site-footer__columns">
		<div class="site-footer__brand">
			<img src="<?php echo esc_url( STOCKSYSTEM_URI . '/assets/images/logo-lockup-dark.png' ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="150" height="40">
			<p><?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?> — <?php esc_html_e( 'فروش لپ‌تاپ و کامپیوتر استوک اروپایی با تست کامل سخت‌افزاری و برگهٔ وضعیت دستگاه.', 'stocksystem' ); ?></p>
			<a class="site-footer__phone ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
				<?php echo esc_html( stocksystem_business( 'phone' ) ); ?>
			</a>
			<span class="site-footer__address">
				<?php echo esc_html( stocksystem_business( 'address' ) ); ?>
				<?php if ( $hours ) : ?>
					· <?php echo esc_html( $hours ); ?>
				<?php endif; ?>
			</span>
		</div>

		<div class="site-footer__nav-col">
			<span class="site-footer__nav-title"><?php esc_html_e( 'دسته‌های کالا', 'stocksystem' ); ?></span>
			<?php foreach ( $categories as $category ) : ?>
				<a href="<?php echo esc_url( $category->url ); ?>"><?php echo esc_html( $category->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="site-footer__nav-col">
			<span class="site-footer__nav-title"><?php esc_html_e( 'خدمات و راهنما', 'stocksystem' ); ?></span>
			<a href="<?php echo esc_url( home_url( '/repair/' ) ); ?>"><?php esc_html_e( 'تعمیرات تخصصی', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/stock-condition/' ) ); ?>"><?php esc_html_e( 'وضعیت کالای استوک', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'شرایط گارانتی و مرجوعی', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( stocksystem_order_tracking_url() ); ?>"><?php esc_html_e( 'پیگیری سفارش', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'بلاگ', 'stocksystem' ); ?></a>
		</div>

		<div class="site-footer__newsletter">
			<span class="site-footer__nav-title"><?php esc_html_e( 'از موجودی‌های تازه باخبر شوید', 'stocksystem' ); ?></span>
			<p><?php esc_html_e( 'هفته‌ای یک ایمیل، فقط دستگاه‌های تازه‌رسیده و قیمت‌های اصلاح‌شده.', 'stocksystem' ); ?></p>
			<form class="site-footer__newsletter-form" method="post" action="">
				<label for="footer-newsletter-email" class="screen-reader-text"><?php esc_html_e( 'ایمیل شما', 'stocksystem' ); ?></label>
				<input type="email" id="footer-newsletter-email" name="email" required placeholder="<?php esc_attr_e( 'ایمیل شما', 'stocksystem' ); ?>">
				<button type="submit" class="btn btn--primary"><?php esc_html_e( 'ثبت', 'stocksystem' ); ?></button>
			</form>
		</div>
	</div>

	<div class="container site-footer__bottom">
		<span class="site-footer__copyright">
			&copy; <?php echo esc_html( stocksystem_jdate( 'Y' ) ); ?>
			<?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'تمام حقوق محفوظ است.', 'stocksystem' ); ?>
		</span>
	</div>
</footer>
