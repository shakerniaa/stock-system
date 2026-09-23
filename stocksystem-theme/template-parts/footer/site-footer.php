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
$footer_cfg = stocksystem_nav_settings()['footer']; // Appearance → «منو و فوتر».

$social_icons = array(
	'instagram' => array( __( 'اینستاگرام', 'stocksystem' ), '<rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.5" cy="6.5" r=".8"></circle>' ),
	'telegram'  => array( __( 'تلگرام', 'stocksystem' ), '<path d="M21.5 3.5L2.5 11l6.5 2.4L11 20l3-4.2 4.5 3.2z"></path><path d="M9 13.400l8-6"></path>' ),
	'whatsapp'  => array( __( 'واتساپ', 'stocksystem' ), '<path d="M3 21l1.6-4.6A8.500 8.500 0 1 1 8 19.500z"></path><path d="M9 9c.5 2 2 3.500 4 4l1-1 2 1c-.5 1.500-2 2-3.500 1.500C10 13.500 8 11.500 7.500 9.500 7.500 8.500 8.200 8 9 9z"></path>' ),
	'aparat'    => array( __( 'آپارات', 'stocksystem' ), '<circle cx="12" cy="12" r="9"></circle><path d="M10 8.500l5 3.500-5 3.500z"></path>' ),
	'linkedin'  => array( __( 'لینکدین', 'stocksystem' ), '<rect x="3" y="3" width="18" height="18" rx="3"></rect><path d="M8 11v5M8 8v.01M12 16v-5M12 13c0-2 4-2.500 4 0v3"></path>' ),
);
$socials = array();
foreach ( $social_icons as $network => $icon ) {
	if ( ! empty( $footer_cfg[ $network ] ) ) {
		$socials[ $network ] = array( 'label' => $icon[0], 'svg' => $icon[1], 'url' => stocksystem_home_link( $footer_cfg[ $network ] ) );
	}
}

$newsletter_status = isset( $_GET['newsletter'] ) ? sanitize_key( wp_unslash( $_GET['newsletter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
?>
<footer id="colophon" class="site-footer">
	<div class="container site-footer__columns">
		<div class="site-footer__brand">
			<img src="<?php echo esc_url( stocksystem_logo_url() ); ?>" alt="<?php bloginfo( 'name' ); ?>" width="150" height="40">
			<p><?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?> — <?php esc_html_e( 'فروش لپ‌تاپ و کامپیوتر استوک با تست کامل سخت‌افزاری و برگهٔ وضعیت دستگاه.', 'stocksystem' ); ?></p>
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
			<?php if ( $socials ) : ?>
				<span class="site-footer__socials">
					<?php foreach ( $socials as $social ) : ?>
						<a href="<?php echo esc_url( $social['url'] ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( $social['label'] ); ?>">
							<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $social['svg']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG paths ?></svg>
						</a>
					<?php endforeach; ?>
				</span>
			<?php endif; ?>
		</div>

		<div class="site-footer__nav-col">
			<span class="site-footer__nav-title"><?php esc_html_e( 'دسته‌های کالا', 'stocksystem' ); ?></span>
			<?php foreach ( $categories as $category ) : ?>
				<a href="<?php echo esc_url( $category->url ); ?>"><?php echo esc_html( $category->name ); ?></a>
			<?php endforeach; ?>
		</div>

		<div class="site-footer__nav-col">
			<span class="site-footer__nav-title"><?php esc_html_e( 'خدمات و راهنما', 'stocksystem' ); ?></span>
			<?php
			// Editable in Appearance → Menus (location «فوتر — ستون خدمات و راهنما»).
			$service_links = stocksystem_menu_links(
				'footer_services',
				array(
					array( 'title' => __( 'تعمیرات تخصصی', 'stocksystem' ), 'url' => home_url( '/repair/' ) ),
					array( 'title' => __( 'وضعیت کالای استوک', 'stocksystem' ), 'url' => home_url( '/stock-condition/' ) ),
					array( 'title' => __( 'شرایط گارانتی و مرجوعی', 'stocksystem' ), 'url' => home_url( '/terms/' ) ),
					array( 'title' => __( 'پیگیری سفارش', 'stocksystem' ), 'url' => stocksystem_order_tracking_url() ),
					array( 'title' => __( 'بلاگ', 'stocksystem' ), 'url' => home_url( '/blog/' ) ),
				)
			);
			foreach ( $service_links as $service_link ) :
				?>
				<a href="<?php echo esc_url( $service_link['url'] ); ?>"><?php echo esc_html( $service_link['title'] ); ?></a>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $footer_cfg['newsletter_show'] ) ) : ?>
		<div class="site-footer__newsletter" id="footer-newsletter">
			<span class="site-footer__nav-title"><?php esc_html_e( 'از موجودی‌های تازه باخبر شوید', 'stocksystem' ); ?></span>
			<p><?php esc_html_e( 'هفته‌ای یک ایمیل، فقط دستگاه‌های تازه‌رسیده و قیمت‌های اصلاح‌شده.', 'stocksystem' ); ?></p>
			<form class="site-footer__newsletter-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="stocksystem_newsletter">
				<?php wp_nonce_field( 'stocksystem_newsletter', 'stocksystem_newsletter_nonce' ); ?>
				<input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="site-footer__hp" aria-hidden="true">
				<label for="footer-newsletter-email" class="screen-reader-text"><?php esc_html_e( 'ایمیل شما', 'stocksystem' ); ?></label>
				<input type="email" id="footer-newsletter-email" name="email" required dir="ltr" placeholder="<?php esc_attr_e( 'ایمیل شما', 'stocksystem' ); ?>">
				<button type="submit" class="btn btn--primary"><?php esc_html_e( 'ثبت', 'stocksystem' ); ?></button>
			</form>
			<?php if ( 'ok' === $newsletter_status ) : ?>
				<p class="site-footer__newsletter-msg is-ok" role="status"><?php esc_html_e( 'ثبت شد؛ ممنون که همراه ما هستید.', 'stocksystem' ); ?></p>
			<?php elseif ( 'invalid' === $newsletter_status ) : ?>
				<p class="site-footer__newsletter-msg is-error" role="alert"><?php esc_html_e( 'ایمیل معتبر نیست؛ نمونه: name@example.com', 'stocksystem' ); ?></p>
			<?php elseif ( 'error' === $newsletter_status ) : ?>
				<p class="site-footer__newsletter-msg is-error" role="alert"><?php esc_html_e( 'ثبت انجام نشد؛ صفحه را تازه کنید و دوباره تلاش کنید.', 'stocksystem' ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>

	<div class="container site-footer__bottom">
		<span class="site-footer__copyright">
			&copy; <?php echo esc_html( stocksystem_jdate( 'Y' ) ); ?>
			<?php bloginfo( 'name' ); ?> — <?php esc_html_e( 'تمام حقوق محفوظ است.', 'stocksystem' ); ?>
		</span>
		<?php if ( '' !== trim( (string) $footer_cfg['badges_html'] ) ) : ?>
			<span class="site-footer__badges"><?php echo $footer_cfg['badges_html']; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin code (e-Namad etc.), unfiltered_html only ?></span>
		<?php endif; ?>
	</div>
</footer>
