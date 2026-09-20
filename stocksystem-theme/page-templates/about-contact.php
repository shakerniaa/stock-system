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
$about = stocksystem_opt( 'about' ); // «استوک سیستم ← درباره ما و تماس».

// Real count, not the design's placeholder «۴۲۸».
$in_stock_count = function_exists( 'wc_get_products' )
	? count(
		wc_get_products(
			array(
				'status'       => 'publish',
				'stock_status' => 'instock',
				'limit'        => -1,
				'return'       => 'ids',
			)
		)
	)
	: 0;
?>

<section class="about-hero">
	<div class="container about-hero__grid">
		<div class="about-hero__copy">
			<?php if ( '' !== $about['eyebrow'] ) : ?>
				<span class="about-hero__eyebrow ltr"><?php echo esc_html( $about['eyebrow'] ); ?></span>
			<?php endif; ?>
			<h1 class="about-hero__title"><?php echo esc_html( $about['title'] ); ?></h1>
			<p class="about-hero__desc"><?php echo esc_html( $about['desc'] ); ?></p>
			<?php if ( ! empty( $about['values'] ) ) : ?>
				<span class="about-hero__values">
					<span class="about-hero__values-bar" aria-hidden="true"></span>
					<span class="about-hero__values-list">
						<?php foreach ( $about['values'] as $value ) : ?>
							<span><?php echo esc_html( $value['text'] ); ?></span>
						<?php endforeach; ?>
					</span>
				</span>
			<?php endif; ?>
		</div>
		<?php $about_image = stocksystem_opt_image( 'about', 'image', 'large' ); ?>
		<?php if ( $about_image ) : ?>
			<div class="about-hero__media about-hero__media--photo">
				<img src="<?php echo esc_url( $about_image ); ?>" alt="<?php echo esc_attr( $about['title'] ); ?>">
			</div>
		<?php else : ?>
			<div class="about-hero__media" aria-hidden="true">
				<?php esc_html_e( 'عکس فروشگاه یا میز کار', 'stocksystem' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php
// Stats: the rows written in the admin, with the live in-stock count after the
// first one and the warranty phrase last (each can be switched off there).
$stat_cards = array();
foreach ( $about['stats'] as $index => $stat ) {
	$stat_cards[] = array( 'value' => $stat['value'], 'label' => $stat['label'], 'text' => false );
	if ( 0 === $index && ! empty( $about['show_stock'] ) && $in_stock_count > 0 ) {
		$stat_cards[] = array( 'value' => stocksystem_to_persian_digits( $in_stock_count ), 'label' => __( 'کالای موجود و تست‌شده', 'stocksystem' ), 'text' => false );
	}
}
if ( ! empty( $about['show_stock'] ) && $in_stock_count > 0 && empty( $about['stats'] ) ) {
	$stat_cards[] = array( 'value' => stocksystem_to_persian_digits( $in_stock_count ), 'label' => __( 'کالای موجود و تست‌شده', 'stocksystem' ), 'text' => false );
}
if ( ! empty( $about['show_warr'] ) ) {
	$stat_cards[] = array( 'value' => stocksystem_business( 'warranty_text' ), 'label' => __( 'روی هر دستگاه', 'stocksystem' ), 'text' => true );
}
?>
<?php if ( $stat_cards ) : ?>
<section class="about-stats">
	<div class="container about-stats__grid">
		<?php foreach ( $stat_cards as $card ) : ?>
			<span class="about-stats__item">
				<span class="about-stats__value<?php echo $card['text'] ? ' about-stats__value--text' : ''; ?>"><?php echo esc_html( $card['value'] ); ?></span>
				<span class="about-stats__label"><?php echo esc_html( $card['label'] ); ?></span>
			</span>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<section class="about-contact">
	<div class="container about-contact__grid">
		<div class="about-contact__info">
			<h2><?php echo esc_html( $about['contact_title'] ); ?></h2>
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

			<?php foreach ( $about['extra_rows'] as $extra_row ) : ?>
				<span class="about-contact__row">
					<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 8v4M12 16h.01"></path></svg>
					<span class="about-contact__row-text">
						<span class="about-contact__row-label"><?php echo esc_html( $extra_row['label'] ); ?></span>
						<?php if ( '' !== $extra_row['url'] ) : ?>
							<a class="about-contact__phone" href="<?php echo esc_url( stocksystem_home_link( $extra_row['url'] ) ); ?>"><?php echo esc_html( $extra_row['value'] ); ?></a>
						<?php else : ?>
							<span class="about-contact__row-value"><?php echo esc_html( $extra_row['value'] ); ?></span>
						<?php endif; ?>
					</span>
				</span>
			<?php endforeach; ?>

			<a class="btn btn--phone about-contact__cta ltr" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4.5 5.5h4l2 4.5-2.5 1.5a10 10 0 0 0 4.5 4.5l1.5-2.5 4.5 2v4a1.5 1.5 0 0 1-1.7 1.5C10.8 19.8 4.2 13.2 3 6.2A1.5 1.5 0 0 1 4.5 5.5z"></path></svg>
				<?php echo esc_html( $about['cta_label'] ); ?>
			</a>
		</div>

		<?php if ( '' !== trim( $about['map'] ) ) : ?>
			<div class="about-contact__map about-contact__map--embed"><?php echo $about['map']; // phpcs:ignore WordPress.Security.EscapeOutput -- trusted admin embed code (unfiltered_html). ?></div>
		<?php else : ?>
			<div class="about-contact__map" aria-hidden="true">
				<?php esc_html_e( 'نقشه — پس از دریافت آدرس دقیق از کارفرما جاسازی می‌شود', 'stocksystem' ); ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
