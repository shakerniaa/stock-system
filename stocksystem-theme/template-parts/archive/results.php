<?php
/**
 * Shared results layout: active-filter chips, 9/3 filter-sidebar + grid,
 * toolbar (count/sort/mobile filter button), pagination. Reused by
 * taxonomy-product_cat.php, taxonomy-product_brand.php, and search.php.
 *
 * $args passed straight through to template-parts/archive/filters.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Nothing matched and there are no filters to relax: show just the empty
// state, full width (13 Search Results §13-C has no sidebar).
if ( ! have_posts() && empty( stocksystem_active_filter_chips() ) ) {
	?>
	<div class="archive-layout archive-layout--empty">
		<?php get_template_part( 'template-parts/archive/empty-results' ); ?>
	</div>
	<?php
	return;
}
?>
<div class="archive-layout">
	<?php get_template_part( 'template-parts/archive/active-filters' ); ?>

	<div class="archive-layout__row">
		<aside id="archive-filters-sidebar" class="archive-layout__sidebar">
			<div class="archive-layout__sidebar-header">
				<span><?php esc_html_e( 'فیلترها', 'stocksystem' ); ?></span>
				<button type="button" id="mobile-filters-close">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
					<span class="screen-reader-text"><?php esc_html_e( 'بستن فیلترها', 'stocksystem' ); ?></span>
				</button>
			</div>
			<?php get_template_part( 'template-parts/archive/filters', null, $args ); ?>
		</aside>

		<div class="archive-layout__main">
			<?php get_template_part( 'template-parts/archive/toolbar' ); ?>
			<?php get_template_part( 'template-parts/archive/product-grid' ); ?>
		</div>
	</div>

	<div id="mobile-filters-backdrop" class="mobile-filters-backdrop" hidden></div>
</div>
