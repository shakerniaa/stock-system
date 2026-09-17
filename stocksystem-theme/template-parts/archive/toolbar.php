<?php
/**
 * Archive toolbar: result count + WooCommerce's native catalog-ordering
 * dropdown (handles the orderby query var and its own submit for us) +
 * mobile "filters" button that opens the sidebar as a bottom sheet.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="archive-toolbar">
	<div class="archive-toolbar__count">
		<?php woocommerce_result_count(); ?>
	</div>
	<div class="archive-toolbar__actions">
		<?php woocommerce_catalog_ordering(); ?>
		<button type="button" class="btn btn--outline archive-toolbar__filter-btn" id="mobile-filters-toggle" aria-haspopup="true" aria-expanded="false" aria-controls="mobile-filters-sheet">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"></path></svg>
			<?php esc_html_e( 'فیلترها', 'stocksystem' ); ?>
		</button>
	</div>
</div>
