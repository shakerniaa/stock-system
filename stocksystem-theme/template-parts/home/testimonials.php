<?php
/**
 * Optional customer testimonials — «استوک سیستم ← بخش‌های تازهٔ صفحهٔ اصلی».
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg = stocksystem_opt( 'homex' );

if ( empty( $cfg['testi_items'] ) ) {
	return;
}
?>
<section class="home-x home-testi">
	<div class="container">
		<div class="home-x__head"><h2><?php echo esc_html( $cfg['testi_title'] ); ?></h2></div>
		<div class="home-testi__grid">
			<?php foreach ( $cfg['testi_items'] as $item ) : ?>
				<?php $stars = max( 1, min( 5, (int) ( '' === $item['stars'] ? 5 : $item['stars'] ) ) ); ?>
				<figure class="home-testi__card">
					<span class="home-testi__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: 1-5 */ __( '%s از ۵ ستاره', 'stocksystem' ), stocksystem_to_persian_digits( $stars ) ) ); ?>"><?php echo esc_html( str_repeat( '★', $stars ) ); ?><span class="is-off"><?php echo esc_html( str_repeat( '★', 5 - $stars ) ); ?></span></span>
					<blockquote class="home-testi__text"><?php echo esc_html( $item['text'] ); ?></blockquote>
					<figcaption class="home-testi__who"><strong><?php echo esc_html( $item['name'] ); ?></strong><?php echo esc_html( $item['role'] ); ?></figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
