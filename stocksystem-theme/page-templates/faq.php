<?php
/**
 * Template Name: FAQ
 * Source: 14 Support Pages.dc.html §14-B.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$categories = stocksystem_faq_categories();
$items      = stocksystem_general_faq_items();
?>

<section class="faq-hero">
	<div class="container">
		<h1><?php esc_html_e( 'چه سوالی دارید؟', 'stocksystem' ); ?></h1>
		<p><?php esc_html_e( 'پرتکرارترین سوال‌های خریداران استوک سیستم.', 'stocksystem' ); ?></p>
		<?php // Revealed by faq-filter.js — without JS there is nothing to search with. ?>
		<label class="faq-search" hidden>
			<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path></svg>
			<span class="screen-reader-text"><?php esc_html_e( 'جست‌وجو در سوال‌ها', 'stocksystem' ); ?></span>
			<input type="search" class="faq-search__input" placeholder="<?php esc_attr_e( 'جست‌وجو در سوال‌ها…', 'stocksystem' ); ?>" autocomplete="off">
		</label>
		<p class="faq-empty" hidden><?php esc_html_e( 'سوالی با این عبارت پیدا نشد. می‌توانید مستقیم با ما تماس بگیرید.', 'stocksystem' ); ?></p>
	</div>
</section>

<section class="faq-page">
	<div class="container faq-page__grid">
		<div>
			<div class="faq-filter">
				<button type="button" class="faq-filter__chip is-current" data-category=""><?php esc_html_e( 'همه', 'stocksystem' ); ?></button>
				<?php foreach ( $categories as $key => $label ) : ?>
					<button type="button" class="faq-filter__chip" data-category="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></button>
				<?php endforeach; ?>
			</div>

			<?php
			get_template_part(
				'template-parts/global/faq-accordion',
				null,
				array(
					'items'      => $items,
					'id_prefix'  => 'general-faq',
					'open_first' => true,
				)
			);
			?>
		</div>

		<aside class="faq-page__sidebar">
			<span class="faq-page__sidebar-title"><?php esc_html_e( 'جواب سوالتان را نگرفتید؟', 'stocksystem' ); ?></span>
			<p><?php esc_html_e( 'کارشناس فنی ما پاسخگوست. برای سوال فنی، مدل دستگاه را آماده داشته باشید.', 'stocksystem' ); ?></p>
			<a class="btn btn--phone" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php echo esc_html( stocksystem_business( 'phone' ) ); ?></a>
		</aside>
	</div>
</section>

<?php get_footer(); ?>
