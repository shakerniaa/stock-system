<?php
/**
 * Template Name: Privacy Policy
 * Source: 14 Support Pages.dc.html §14-D.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'حریم خصوصی', 'stocksystem' ); ?></span>
</div>

<header class="legal-page__header">
	<div class="container">
		<h1><?php esc_html_e( 'سیاست حفظ حریم خصوصی', 'stocksystem' ); ?></h1>
		<p class="legal-page__meta"><?php printf( /* translators: %s: Jalali date */ esc_html__( 'آخرین بازنگری: %s', 'stocksystem' ), esc_html( stocksystem_jdate( 'j F Y', (int) get_post_modified_time( 'U', true ) ) ) ); ?></p>
	</div>
</header>

<section class="legal-page">
	<div class="container legal-page__single">
		<div class="legal-page__section">
			<h2><?php esc_html_e( 'چه داده‌ای جمع می‌کنیم', 'stocksystem' ); ?></h2>
			<p><?php esc_html_e( 'برای ثبت سفارش فقط نام، شمارهٔ موبایل و نشانی تحویل لازم است. کد ملی تنها در صدور فاکتور رسمی درخواستی گرفته می‌شود.', 'stocksystem' ); ?></p>
			<ul class="legal-page__list">
				<li><?php esc_html_e( 'اطلاعات کارت بانکی هرگز روی سرور ما ذخیره نمی‌شود.', 'stocksystem' ); ?></li>
				<li><?php esc_html_e( 'سریال دستگاه برای اعتبارسنجی گارانتی نگهداری می‌شود.', 'stocksystem' ); ?></li>
				<li><?php esc_html_e( 'کد پیامکی ورود (OTP) فقط برای تأیید شمارهٔ موبایل استفاده و پس از استفاده باطل می‌شود.', 'stocksystem' ); ?></li>
			</ul>
		</div>

		<div class="legal-page__section">
			<h2><?php esc_html_e( 'حذف حساب و داده‌ها', 'stocksystem' ); ?></h2>
			<div class="legal-page__callout">
				<p>
					<?php
					printf(
						/* translators: %s: support phone number */
						esc_html__( 'برای درخواست حذف حساب و داده‌های خود، از طریق تلفن پشتیبانی (%s) یا ایمیل با ما تماس بگیرید. سوابق فاکتور به حکم قانون تا ۱۰ سال نگهداری می‌شود و مشمول این درخواست نیست.', 'stocksystem' ),
						'<a class="ltr" href="tel:' . esc_attr( stocksystem_business( 'phone' ) ) . '">' . esc_html( stocksystem_business( 'phone' ) ) . '</a>'
					);
					?>
				</p>
			</div>
		</div>

		<div class="legal-page__section">
			<h2><?php esc_html_e( 'اشتراک‌گذاری با اشخاص ثالث', 'stocksystem' ); ?></h2>
			<p><?php esc_html_e( 'اطلاعات تحویل سفارش (نام، تلفن، نشانی) فقط با شرکت پستی/تیپاکس طرف قرارداد برای ارسال بسته به اشتراک گذاشته می‌شود؛ هیچ داده‌ای برای تبلیغات به شخص ثالث فروخته یا اجاره داده نمی‌شود.', 'stocksystem' ); ?></p>
		</div>
	</div>
</section>

<?php get_footer(); ?>
