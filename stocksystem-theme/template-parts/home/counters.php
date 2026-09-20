<?php
/**
 * Optional counters — «استوک سیستم ← بخش‌های تازهٔ صفحهٔ اصلی».
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg = stocksystem_opt( 'homex' );

if ( empty( $cfg['count_items'] ) ) {
	return;
}
?>
<section class="home-x home-counters">
	<div class="container">
		<div class="home-counters__row">
			<?php foreach ( $cfg['count_items'] as $item ) : ?>
				<span>
					<span class="home-counters__value"><?php echo esc_html( $item['value'] ); ?></span>
					<span class="home-counters__label"><?php echo esc_html( $item['label'] ); ?></span>
				</span>
			<?php endforeach; ?>
		</div>
	</div>
</section>
