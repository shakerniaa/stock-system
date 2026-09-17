<?php
/**
 * Trust band — 4 reassurance points. Source: 01 Home.dc.html.
 *
 * Decision fixes: warranty copy from Customizer (not the design's
 * hardcoded "۱۸ ماه گارانتی"), shipping copy is Neyshabur pickup +
 * nationwide post (not the design's Tehran-same-day/48h placeholder —
 * decision #4/#7).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<section class="home-trust">
	<div class="container home-trust__grid">
		<span class="home-trust__item">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 7.8-8 9-4.8-1.2-8-4.5-8-9V6z"></path><path d="M9 12l2.2 2.2L15.5 10"></path></svg>
			<span class="home-trust__text">
				<span class="home-trust__title"><?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?></span>
				<span class="home-trust__desc"><?php esc_html_e( 'قطعات اصلی و تعمیر در فروشگاه', 'stocksystem' ); ?></span>
			</span>
		</span>
		<span class="home-trust__item">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="14" rx="2"></rect><path d="M7 9h6M7 13h4"></path></svg>
			<span class="home-trust__text">
				<span class="home-trust__title"><?php esc_html_e( 'برگهٔ تست هر دستگاه', 'stocksystem' ); ?></span>
				<span class="home-trust__desc"><?php esc_html_e( 'سلامت باتری، ساعت کارکرد، وضعیت بدنه', 'stocksystem' ); ?></span>
			</span>
		</span>
		<span class="home-trust__item">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"></path><circle cx="7" cy="18" r="1.6"></circle><circle cx="17" cy="18" r="1.6"></circle></svg>
			<span class="home-trust__text">
				<span class="home-trust__title"><?php esc_html_e( 'ارسال سریع', 'stocksystem' ); ?></span>
				<span class="home-trust__desc"><?php esc_html_e( 'تحویل حضوری رایگان در نیشابور · ارسال با پست به سراسر کشور', 'stocksystem' ); ?></span>
			</span>
		</span>
		<span class="home-trust__item">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 7v10a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7"></path><path d="M4 7l8-4 8 4"></path><path d="M10 19v-5h4v5"></path></svg>
			<span class="home-trust__text">
				<span class="home-trust__title"><?php esc_html_e( 'فروشگاه فیزیکی', 'stocksystem' ); ?></span>
				<span class="home-trust__desc"><?php echo esc_html( stocksystem_business( 'address' ) ); ?></span>
			</span>
		</span>
	</div>
</section>
