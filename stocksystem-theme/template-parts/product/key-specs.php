<?php
/**
 * "مشخصات کلیدی" — up to 6 headline specs shown inside the buy box,
 * above the fold, so a shopper doesn't have to scroll to the specs tab
 * to see CPU/RAM/storage at a glance. Same framed-panel idiom as
 * template-parts/product/test-report.php, so the two read as siblings.
 * Skipped entirely if the product has no attributes filled in yet.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
$specs   = stocksystem_get_key_specs( $product );

if ( empty( $specs ) ) {
	return;
}
?>
<div class="key-specs">
	<div class="key-specs__header">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 9h10M7 13h10M7 17h6"></path></svg>
		<span class="key-specs__title"><?php esc_html_e( 'مشخصات کلیدی', 'stocksystem' ); ?></span>
	</div>
	<div class="key-specs__grid">
		<?php foreach ( $specs as $spec ) : ?>
			<div class="key-specs__item">
				<span class="key-specs__label"><?php echo esc_html( $spec['label'] ); ?></span>
				<span class="key-specs__value"><?php echo esc_html( $spec['value'] ); ?></span>
			</div>
		<?php endforeach; ?>
	</div>
</div>
