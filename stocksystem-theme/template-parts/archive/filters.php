<?php
/**
 * Archive filter sidebar — brand / grading / CPU / RAM checkboxes, price
 * range, in-stock toggle, and (search only) battery health + runtime
 * hours. One <form method="get"> so it works with JS (auto-submit on
 * change, assets/js/archive-filters.js) or without it (submit button).
 * Source: 02 Category.dc.html, 13 Search Results.dc.html §13-B.
 *
 * $args:
 *   show_stock_facets (bool) — battery/runtime chips, search only
 *   exclude (string[])       — facet keys to hide (e.g. 'brand' on a
 *                               brand archive, since the context already
 *                               is one brand)
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$show_stock_facets = ! empty( $args['show_stock_facets'] );
$exclude           = ! empty( $args['exclude'] ) ? (array) $args['exclude'] : array();
?>
<form class="archive-filters" method="get" id="archive-filters">
	<?php if ( is_search() ) : ?>
		<input type="hidden" name="s" value="<?php echo esc_attr( get_search_query() ); ?>">
		<input type="hidden" name="post_type" value="product">
	<?php endif; ?>

	<?php foreach ( stocksystem_archive_taxonomy_facets() as $param => $facet ) : ?>
		<?php
		if ( in_array( $param, $exclude, true ) || ! taxonomy_exists( $facet['taxonomy'] ) ) {
			continue;
		}
		$terms = get_terms( array( 'taxonomy' => $facet['taxonomy'], 'hide_empty' => false ) );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}
		$checked_values = isset( $_GET[ $param ] ) ? array_map( 'sanitize_title', (array) wp_unslash( $_GET[ $param ] ) ) : array();

		// Counts are products in this archive's scope, not the taxonomy's
		// stored term count (stale/zero for brands). A checked value stays
		// listed even at zero so it can be unchecked.
		$facet_terms = array();
		foreach ( $terms as $term ) {
			$term_count = stocksystem_facet_term_count( $term );
			if ( $term_count > 0 || in_array( $term->slug, $checked_values, true ) ) {
				$facet_terms[] = array( $term, $term_count );
			}
		}
		if ( empty( $facet_terms ) ) {
			continue;
		}
		?>
		<fieldset class="archive-filters__group">
			<legend class="archive-filters__legend"><?php echo esc_html( $facet['label'] ); ?></legend>
			<?php foreach ( $facet_terms as $facet_term ) : ?>
				<?php list( $term, $term_count ) = $facet_term; ?>
				<label class="archive-filters__checkbox">
					<input
						type="checkbox"
						name="<?php echo esc_attr( $param ); ?>[]"
						value="<?php echo esc_attr( $term->slug ); ?>"
						<?php checked( in_array( $term->slug, $checked_values, true ) ); ?>
					>
					<span class="archive-filters__checkbox-box" aria-hidden="true">
						<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"></path></svg>
					</span>
					<span class="archive-filters__checkbox-label"><?php echo esc_html( $term->name ); ?></span>
					<span class="archive-filters__checkbox-count"><?php echo esc_html( stocksystem_to_persian_digits( $term_count ) ); ?></span>
				</label>
			<?php endforeach; ?>
		</fieldset>
	<?php endforeach; ?>

	<fieldset class="archive-filters__group">
		<legend class="archive-filters__legend"><?php esc_html_e( 'محدودهٔ قیمت (تومان)', 'stocksystem' ); ?></legend>
		<span class="archive-filters__price-inputs">
			<label class="screen-reader-text" for="filter-min-price"><?php esc_html_e( 'حداقل قیمت', 'stocksystem' ); ?></label>
			<input type="number" min="0" step="100000" inputmode="numeric" id="filter-min-price" name="min_price" placeholder="<?php esc_attr_e( 'از', 'stocksystem' ); ?>" value="<?php echo esc_attr( isset( $_GET['min_price'] ) ? sanitize_text_field( wp_unslash( $_GET['min_price'] ) ) : '' ); ?>">
			<label class="screen-reader-text" for="filter-max-price"><?php esc_html_e( 'حداکثر قیمت', 'stocksystem' ); ?></label>
			<input type="number" min="0" step="100000" inputmode="numeric" id="filter-max-price" name="max_price" placeholder="<?php esc_attr_e( 'تا', 'stocksystem' ); ?>" value="<?php echo esc_attr( isset( $_GET['max_price'] ) ? sanitize_text_field( wp_unslash( $_GET['max_price'] ) ) : '' ); ?>">
		</span>
	</fieldset>

	<?php if ( $show_stock_facets ) : ?>
		<fieldset class="archive-filters__group">
			<legend class="archive-filters__legend"><?php esc_html_e( 'سلامت باتری', 'stocksystem' ); ?></legend>
			<span class="archive-filters__chip-radios">
				<?php
				$battery_options = array(
					'85' => __( 'بالای ۸۵٪', 'stocksystem' ),
					'70' => __( '۷۰ تا ۸۵٪', 'stocksystem' ),
					'0'  => __( 'زیر ۷۰٪', 'stocksystem' ),
				);
				$current_battery = isset( $_GET['battery_min'] ) ? sanitize_text_field( wp_unslash( $_GET['battery_min'] ) ) : '';
				foreach ( $battery_options as $value => $label ) :
					?>
					<label class="archive-filters__chip-radio">
						<input type="radio" name="battery_min" value="<?php echo esc_attr( $value ); ?>" <?php checked( $current_battery, $value ); ?>>
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</span>
		</fieldset>

		<fieldset class="archive-filters__group">
			<legend class="archive-filters__legend"><?php esc_html_e( 'ساعت کارکرد', 'stocksystem' ); ?></legend>
			<span class="archive-filters__chip-radios">
				<?php
				$runtime_options = array(
					'low'  => __( 'زیر ۲٬۰۰۰', 'stocksystem' ),
					'mid'  => __( '۲ تا ۵ هزار', 'stocksystem' ),
					'high' => __( 'بالای ۵ هزار', 'stocksystem' ),
				);
				$current_runtime = isset( $_GET['runtime'] ) ? sanitize_key( wp_unslash( $_GET['runtime'] ) ) : '';
				foreach ( $runtime_options as $value => $label ) :
					?>
					<label class="archive-filters__chip-radio">
						<input type="radio" name="runtime" value="<?php echo esc_attr( $value ); ?>" <?php checked( $current_runtime, $value ); ?>>
						<span><?php echo esc_html( $label ); ?></span>
					</label>
				<?php endforeach; ?>
			</span>
		</fieldset>
	<?php endif; ?>

	<fieldset class="archive-filters__group archive-filters__group--toggle">
		<label class="archive-filters__toggle">
			<input type="checkbox" name="in_stock" value="1" <?php checked( ! empty( $_GET['in_stock'] ) ); ?>>
			<span class="archive-filters__toggle-track" aria-hidden="true"><span class="archive-filters__toggle-thumb"></span></span>
			<?php esc_html_e( 'فقط کالاهای موجود', 'stocksystem' ); ?>
		</label>
	</fieldset>

	<button type="submit" class="btn btn--primary btn--block"><?php esc_html_e( 'اعمال فیلتر', 'stocksystem' ); ?></button>
</form>
