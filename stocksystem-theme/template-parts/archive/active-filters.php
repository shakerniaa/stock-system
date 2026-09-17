<?php
/**
 * Active filter chip row, each with a "×" that links to the same archive
 * with just that one filter removed. Source: 02 Category.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$chips = stocksystem_active_filter_chips();

if ( empty( $chips ) ) {
	return;
}
?>
<div class="active-filters">
	<span class="active-filters__label"><?php esc_html_e( 'فیلتر فعال:', 'stocksystem' ); ?></span>
	<?php foreach ( $chips as $chip ) : ?>
		<a class="active-filters__chip" href="<?php echo esc_url( $chip['remove_url'] ); ?>">
			<?php echo esc_html( $chip['label'] ); ?>
			<span aria-hidden="true">×</span>
			<span class="screen-reader-text"><?php esc_html_e( 'حذف این فیلتر', 'stocksystem' ); ?></span>
		</a>
	<?php endforeach; ?>
	<a class="active-filters__clear" href="<?php echo esc_url( stocksystem_clear_all_filters_url() ); ?>"><?php esc_html_e( 'حذف همه', 'stocksystem' ); ?></a>
</div>
