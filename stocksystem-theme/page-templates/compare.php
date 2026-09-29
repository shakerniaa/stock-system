<?php
/**
 * Template Name: مقایسهٔ محصولات
 *
 * Renders server-side from ?ids=, so a comparison is shareable and
 * complete on first paint; assets/js/compare.js only keeps the
 * selection in sync afterwards.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$products = function_exists( 'stocksystem_compare_requested_products' ) ? stocksystem_compare_requested_products() : array();
$groups   = stocksystem_compare_table( $products );
$count    = count( $products );
?>

<div class="site-main container compare-page">
	<nav class="page-crumb" aria-label="<?php esc_attr_e( 'مسیر صفحه', 'stocksystem' ); ?>">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
		<span aria-hidden="true">/</span>
		<span><?php esc_html_e( 'مقایسهٔ محصولات', 'stocksystem' ); ?></span>
	</nav>

	<div class="compare-page__head">
		<h1 class="compare-page__title"><?php esc_html_e( 'مقایسهٔ محصولات', 'stocksystem' ); ?></h1>
		<?php if ( $count > 1 ) : ?>
			<label class="compare-page__diff">
				<input type="checkbox" data-compare-diff>
				<span><?php esc_html_e( 'فقط تفاوت‌ها را نشان بده', 'stocksystem' ); ?></span>
			</label>
		<?php endif; ?>
	</div>

	<?php if ( $count < 1 ) : ?>
		<p class="compare-page__empty">
			<?php esc_html_e( 'هنوز محصولی برای مقایسه انتخاب نکرده‌اید. از روی کارت هر محصول یا صفحهٔ آن، دکمهٔ «افزودن به مقایسه» را بزنید.', 'stocksystem' ); ?>
		</p>
		<p>
			<a class="btn btn--primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>">
				<?php esc_html_e( 'رفتن به فروشگاه', 'stocksystem' ); ?>
			</a>
		</p>
	<?php else : ?>
		<div class="compare-table-wrap">
			<table class="compare-table" data-compare-table>
				<thead>
					<tr>
						<th class="compare-table__corner" scope="col">
							<span class="screen-reader-text"><?php esc_html_e( 'ویژگی', 'stocksystem' ); ?></span>
						</th>
						<?php foreach ( $products as $product ) : ?>
							<th class="compare-table__product" scope="col">
								<button
									type="button"
									class="compare-table__remove"
									data-compare-remove="<?php echo esc_attr( $product->get_id() ); ?>"
									aria-label="<?php esc_attr_e( 'حذف از مقایسه', 'stocksystem' ); ?>"
								>
									<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
								</button>

								<a class="compare-table__media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
									<?php echo $product->get_image( 'medium', array( 'decoding' => 'async', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce-escaped markup ?>
								</a>

								<a class="compare-table__name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>

								<span class="compare-table__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>

								<a class="btn btn--primary compare-table__cta" href="<?php echo esc_url( $product->get_permalink() ); ?>">
									<?php esc_html_e( 'مشاهدهٔ محصول', 'stocksystem' ); ?>
								</a>
							</th>
						<?php endforeach; ?>
					</tr>
				</thead>

				<?php foreach ( $groups as $group ) : ?>
					<tbody class="compare-table__group">
						<tr class="compare-table__group-row">
							<th class="compare-table__group-title" colspan="<?php echo (int) ( $count + 1 ); ?>" scope="colgroup">
								<?php echo esc_html( $group['title'] ); ?>
							</th>
						</tr>
						<?php foreach ( $group['rows'] as $row ) : ?>
							<tr class="compare-table__row<?php echo $row['same'] ? ' is-same' : ''; ?>"<?php echo $row['same'] ? ' data-compare-same' : ''; ?>>
								<th class="compare-table__label" scope="row"><?php echo esc_html( $row['label'] ); ?></th>
								<?php foreach ( $row['cells'] as $cell ) : ?>
									<td class="compare-table__cell">
										<?php if ( '' === $cell ) : ?>
											<span class="compare-table__empty" aria-label="<?php esc_attr_e( 'ثبت نشده', 'stocksystem' ); ?>">—</span>
										<?php else : ?>
											<?php echo esc_html( $cell ); ?>
										<?php endif; ?>
									</td>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				<?php endforeach; ?>
			</table>
		</div>

		<?php if ( empty( $groups ) ) : ?>
			<p class="compare-page__empty">
				<?php esc_html_e( 'برای این محصول‌ها هنوز مشخصاتی ثبت نشده، پس چیزی برای مقایسه وجود ندارد.', 'stocksystem' ); ?>
			</p>
		<?php endif; ?>

		<p class="compare-page__note" data-compare-all-same hidden>
			<?php esc_html_e( 'این محصول‌ها در همهٔ مشخصات ثبت‌شده یکسان‌اند.', 'stocksystem' ); ?>
		</p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
