<?php
/**
 * 404 — three real ways out (search, shop, contact) rather than a dead
 * end, same "never just an apology" principle as
 * template-parts/archive/empty-results.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$categories = array_slice( stocksystem_nav_categories(), 0, 4 );
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>

<section class="error-404">
	<div class="container error-404__inner">
		<span class="error-404__code" aria-hidden="true">۴۰۴</span>
		<h1><?php esc_html_e( 'این صفحه پیدا نشد', 'stocksystem' ); ?></h1>
		<p><?php esc_html_e( 'لینکی که دنبالش بودید ممکن است حذف شده یا آدرسش تغییر کرده باشد.', 'stocksystem' ); ?></p>

		<div class="error-404__search"><?php get_search_form(); ?></div>

		<div class="error-404__actions">
			<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn--primary"><?php esc_html_e( 'مشاهدهٔ فروشگاه', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'بازگشت به خانه', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="btn btn--outline"><?php esc_html_e( 'تماس با ما', 'stocksystem' ); ?></a>
		</div>

		<?php if ( ! empty( $categories ) ) : ?>
			<div class="error-404__categories">
				<span class="error-404__categories-title"><?php esc_html_e( 'یا از یکی از دسته‌ها شروع کنید', 'stocksystem' ); ?></span>
				<span class="error-404__categories-list">
					<?php foreach ( $categories as $category ) : ?>
						<a href="<?php echo esc_url( $category->url ); ?>"><?php echo esc_html( $category->name ); ?></a>
					<?php endforeach; ?>
				</span>
			</div>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
