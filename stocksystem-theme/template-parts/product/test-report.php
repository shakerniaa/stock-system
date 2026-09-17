<?php
/**
 * Device test-report panel — 4 stats. Source: 03 Product.dc.html.
 * Skipped entirely if the admin hasn't filled any of it in yet.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product = ! empty( $args['product'] ) ? $args['product'] : $GLOBALS['product'];
$report  = stocksystem_get_test_report( $product->get_id() );

if ( ! $report ) {
	return;
}
?>
<div class="test-report">
	<div class="test-report__header">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 9h6M7 13h8"></path></svg>
		<span class="test-report__title"><?php esc_html_e( 'برگهٔ تست این دستگاه', 'stocksystem' ); ?></span>
		<?php if ( $report['date'] ) : ?>
			<span class="test-report__date">
				<?php
				printf(
					/* translators: %s: test date */
					esc_html__( 'تاریخ تست: %s', 'stocksystem' ),
					esc_html( $report['date'] )
				);
				?>
			</span>
		<?php endif; ?>
	</div>

	<div class="test-report__grid">
		<?php if ( '' !== $report['battery'] ) : ?>
			<div class="test-report__stat">
				<span class="test-report__stat-label"><?php esc_html_e( 'سلامت باتری', 'stocksystem' ); ?></span>
				<span class="test-report__stat-value test-report__stat-value--good">
					<?php echo esc_html( stocksystem_to_persian_digits( $report['battery'] ) ); ?>٪
				</span>
			</div>
		<?php endif; ?>

		<?php if ( '' !== $report['runtime'] ) : ?>
			<div class="test-report__stat">
				<span class="test-report__stat-label"><?php esc_html_e( 'ساعت کارکرد', 'stocksystem' ); ?></span>
				<span class="test-report__stat-value"><?php echo esc_html( stocksystem_format_number( $report['runtime'] ) ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $report['body'] ) : ?>
			<div class="test-report__stat">
				<span class="test-report__stat-label"><?php esc_html_e( 'وضعیت بدنه', 'stocksystem' ); ?></span>
				<span class="test-report__stat-value"><?php echo esc_html( $report['body'] ); ?></span>
			</div>
		<?php endif; ?>

		<?php if ( $report['pixels'] ) : ?>
			<div class="test-report__stat">
				<span class="test-report__stat-label"><?php esc_html_e( 'پیکسل سوخته', 'stocksystem' ); ?></span>
				<span class="test-report__stat-value<?php echo ( 'ندارد' === $report['pixels'] ) ? ' test-report__stat-value--good' : ''; ?>"><?php echo esc_html( $report['pixels'] ); ?></span>
			</div>
		<?php endif; ?>
	</div>
</div>
