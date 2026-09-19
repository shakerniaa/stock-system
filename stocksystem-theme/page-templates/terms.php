<?php
/**
 * Template Name: Terms & Conditions
 * Source: 14 Support Pages.dc.html §14-A.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sections = stocksystem_terms_sections();
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'قوانین و مقررات', 'stocksystem' ); ?></span>
</div>

<header class="legal-page__header">
	<div class="container">
		<h1><?php esc_html_e( 'قوانین و مقررات فروش', 'stocksystem' ); ?></h1>
		<p class="legal-page__meta"><?php printf( /* translators: %s: Jalali date */ esc_html__( 'آخرین بازنگری: %s', 'stocksystem' ), esc_html( stocksystem_jdate( 'j F Y', (int) get_post_modified_time( 'U', true ) ) ) ); ?></p>
	</div>
</header>

<section class="legal-page">
	<div class="container legal-page__grid">
		<aside class="legal-page__toc">
			<span class="legal-page__toc-title"><?php esc_html_e( 'در این صفحه', 'stocksystem' ); ?></span>
			<?php foreach ( $sections as $section ) : ?>
				<a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['title'] ); ?></a>
			<?php endforeach; ?>
			<span class="legal-page__toc-contact">
				<span><?php esc_html_e( 'سوالی دارید؟', 'stocksystem' ); ?></span>
				<a class="btn btn--phone" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php esc_html_e( 'تماس با پشتیبانی', 'stocksystem' ); ?></a>
			</span>
		</aside>

		<article class="legal-page__content">
			<p class="legal-page__intro"><?php esc_html_e( 'ثبت سفارش در استوک سیستم به‌معنای پذیرش بندهای زیر است. این متن برای خریدار نوشته شده، نه برای واحد حقوقی؛ هر جا شرطی به سود ما و به زیان شماست، صریح گفته‌ایم.', 'stocksystem' ); ?></p>

			<?php foreach ( $sections as $section ) : ?>
				<div class="legal-page__section" id="<?php echo esc_attr( $section['id'] ); ?>">
					<h2><?php echo esc_html( $section['title'] ); ?></h2>

					<?php if ( 'warranty' === $section['id'] ) : ?>
						<p>
							<?php
							printf(
								/* translators: %s: warranty text, e.g. "گارانتی ۱ ماهه سخت‌افزار" */
								esc_html__( 'تمام دستگاه‌ها مشمول %s هستند. ضربه، نفوذ مایعات و باز شدن دستگاه توسط فرد غیرمجاز، گارانتی را باطل می‌کند.', 'stocksystem' ),
								esc_html( stocksystem_business( 'warranty_text' ) )
							);
							?>
						</p>
					<?php elseif ( 'shipping' === $section['id'] ) : ?>
						<p>
							<?php
							printf(
								/* translators: %s: store address */
								esc_html__( 'تحویل حضوری در فروشگاه استوک سیستم رایگان است (%s). ارسال به سراسر کشور با پست یا تیپاکس انجام می‌شود؛ هزینه و بازهٔ ارسال بر اساس مقصد، هنگام تسویه‌حساب محاسبه و نمایش داده می‌شود.', 'stocksystem' ),
								esc_html( stocksystem_business( 'address' ) )
							);
							?>
						</p>
					<?php else : ?>
						<p><?php echo esc_html( $section['body'] ); ?></p>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</article>
	</div>
</section>

<?php get_footer(); ?>
