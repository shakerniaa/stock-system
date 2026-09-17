<?php
/**
 * Zero-results state — three ways out (never just an apology), plus a
 * "nearby products" grid so the visit doesn't dead-end. Source:
 * 13 Search Results.dc.html §13-C.
 *
 * Decision #1 note: the design fills the primary CTA here with orange;
 * kept teal instead for the same reason as everywhere else in the theme
 * — orange is reserved for phone/badges/short-term emphasis, not a
 * filled primary action.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$query = is_search() ? get_search_query() : '';
$has_active_filters = ! empty( stocksystem_active_filter_chips() );

$nearby = array();
if ( function_exists( 'wc_get_products' ) ) {
	$nearby = wc_get_products(
		array(
			'limit'   => 4,
			'orderby' => 'popularity',
			'status'  => 'publish',
		)
	);
}
?>
<div class="empty-results">
	<span class="empty-results__icon" aria-hidden="true">
		<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"></circle><path d="M20 20l-3.5-3.5"></path><path d="M8.5 11h5"></path></svg>
	</span>

	<div class="empty-results__text">
		<?php if ( $query ) : ?>
			<h1><?php printf( esc_html__( 'نتیجه‌ای برای «%s» پیدا نشد', 'stocksystem' ), esc_html( $query ) ); ?></h1>
		<?php else : ?>
			<h1><?php esc_html_e( 'با این فیلترها کالایی پیدا نشد', 'stocksystem' ); ?></h1>
		<?php endif; ?>
		<p><?php esc_html_e( 'این مدل فعلاً در انبار استوک سیستم نیست. می‌توانید اعلان موجودی فعال کنید یا یکی از گزینه‌های زیر را امتحان کنید.', 'stocksystem' ); ?></p>
	</div>

	<div class="empty-results__actions">
		<a href="#notify-availability" class="btn btn--primary"><?php esc_html_e( 'فعال‌کردن اعلان موجودی', 'stocksystem' ); ?></a>
		<?php if ( $has_active_filters ) : ?>
			<a href="<?php echo esc_url( stocksystem_clear_all_filters_url() ); ?>" class="btn btn--outline"><?php esc_html_e( 'حذف فیلترها و جستجوی دوباره', 'stocksystem' ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'درخواست کالا از کارشناس', 'stocksystem' ); ?></a>
	</div>

	<?php if ( ! empty( $nearby ) ) : ?>
		<div class="empty-results__nearby">
			<span class="empty-results__nearby-title"><?php esc_html_e( 'کالاهای پرطرفدار این هفته', 'stocksystem' ); ?></span>
			<div class="product-grid">
				<?php
				foreach ( $nearby as $nearby_product ) :
					global $product;
					$product = $nearby_product;
					get_template_part( 'template-parts/product/card' );
				endforeach;
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
