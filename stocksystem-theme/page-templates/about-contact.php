<?php
/**
 * Template Name: About & Contact
 * Source: 09 About Contact.dc.html.
 *
 * Decision fixes applied: address is Neyshabur (not Tehran), warranty
 * copy comes from the customizer (1 month, not the design's 18-month
 * placeholder). "Since <year>" and the usage numbers below are marketing
 * placeholders the client fills in during the content phase — left as
 * TODO business-copy, not invented data.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$hours = stocksystem_business( 'store_hours' );
?>

<section class="about-hero">
	<div class="container about-hero__grid">
		<div class="about-hero__copy">
			<!-- TODO: business-copy — real founding year -->
			<span class="about-hero__eyebrow ltr"><?php esc_html_e( 'از سال ۱۳۹۴', 'stocksystem' ); ?></span>
			<h1 class="about-hero__title"><?php esc_html_e( 'یک کارگاه واقعی، نه فقط یک فروشگاه اینترنتی', 'stocksystem' ); ?></h1>
			<p class="about-hero__desc"><?php esc_html_e( 'کار ما با تعمیر شروع شد. همان دانش فنی باعث شد بتوانیم کالای استوک را تست کنیم، درجه بدهیم و با گارانتی بفروشیم. اگر بخواهید، می‌توانید پیش از خرید بیایید و دستگاه را روشن کنید.', 'stocksystem' ); ?></p>
			<span class="about-hero__values">
				<span class="about-hero__values-bar" aria-hidden="true"></span>
				<span class="about-hero__values-list">
					<span><?php esc_html_e( 'اعتماد', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'کیفیت', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'تکنولوژی', 'stocksystem' ); ?></span>
				</span>
			</span>
		</div>
		<div class="about-hero__media" aria-hidden="true">
			<?php esc_html_e( 'عکس فروشگاه یا میز کار', 'stocksystem' ); ?>
		</div>
	</div>
</section>

<section class="about-stats">
	<div class="container about-stats__grid">
		<span class="about-stats__item">
			<span class="about-stats__value"><?php echo esc_html( stocksystem_to_persian_digits( '10' ) ); ?></span>
			<span class="about-stats__label"><?php esc_html_e( 'سال سابقهٔ فنی', 'stocksystem' ); ?></span>
		</span>
		<span class="about-stats__item">
			<span class="about-stats__value"><?php echo esc_html( stocksystem_to_persian_digits( '100' ) ); ?>٪</span>
			<span class="about-stats__label"><?php esc_html_e( 'تست پیش از فروش', 'stocksystem' ); ?></span>
		</span>
		<span class="about-stats__item">
			<span class="about-stats__value"><?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?></span>
			<span class="about-stats__label"><?php esc_html_e( 'روی هر دستگاه', 'stocksystem' ); ?></span>
		</span>
	</div>
</section>

<section class="about-contact">
	<div class="container about-contact__grid">
		<div class="about-contact__info">
			<h2><?php esc_html_e( 'فروشگاه و تماس', 'stocksystem' ); ?></h2>

			<span class="about-contact__row">
				<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s7-5.4 7-11a7 7 0 0 0-14 0c0 5.6 7 11 7 11z"></path><circle cx="12" cy="10" r="2.5"></circle></svg>
				<span class="about-contact__row-text">
					<span class="about-contact__row-label"><?php esc_html_e( 'نشانی', 'stocksystem' ); ?></span>
					<span class="about-contact__row-value"><?php echo esc_html( stocksystem_business( 'address' ) ); ?></span>
				</span>
			</span>

			<span class="about-contact__row">
				<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
				<span class="about-contact__row-text">
					<span class="about-contact__row-label"><?php esc_html_e( 'تلفن و واتساپ', 'stocksystem' ); ?></span>
					<a class="about-contact__phone ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php echo esc_html( stocksystem_business( 'phone' ) ); ?></a>
				</span>
			</span>

			<?php if ( $hours ) : ?>
				<span class="about-contact__row">
					<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5V12l3 2"></path></svg>
					<span class="about-contact__row-text">
						<span class="about-contact__row-label"><?php esc_html_e( 'ساعت کاری', 'stocksystem' ); ?></span>
						<span class="about-contact__row-value"><?php echo esc_html( $hours ); ?></span>
					</span>
				</span>
			<?php endif; ?>

			<a class="btn btn--phone about-contact__cta ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
				<?php esc_html_e( 'تماس فوری', 'stocksystem' ); ?>
			</a>
		</div>

		<div class="about-contact__map" aria-hidden="true">
			<?php esc_html_e( 'نقشه — پس از دریافت آدرس دقیق از کارفرما جاسازی می‌شود', 'stocksystem' ); ?>
		</div>
	</div>
</section>

<?php get_footer(); ?>
