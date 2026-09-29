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

// Either the hand-written rows or real product reviews, depending on the
// «منبع نظرها» setting — see stocksystem_testimonial_items().
$items = function_exists( 'stocksystem_testimonial_items' ) ? stocksystem_testimonial_items() : ( empty( $cfg['testi_items'] ) ? array() : $cfg['testi_items'] );

if ( empty( $items ) ) {
	return;
}
?>
<section class="home-x home-testi">
	<div class="container">
		<div class="home-x__head"><h2><?php echo esc_html( $cfg['testi_title'] ); ?></h2></div>
		<div class="home-testi__grid">
			<?php foreach ( $items as $item ) : ?>
				<?php $stars = max( 1, min( 5, (int) ( '' === $item['stars'] ? 5 : $item['stars'] ) ) ); ?>
				<figure class="home-testi__card">
					<span class="home-testi__stars" role="img" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: 1-5 */ __( '%s از ۵ ستاره', 'stocksystem' ), stocksystem_to_persian_digits( $stars ) ) ); ?>"><?php echo esc_html( str_repeat( '★', $stars ) ); ?><span class="is-off"><?php echo esc_html( str_repeat( '★', 5 - $stars ) ); ?></span></span>
					<blockquote class="home-testi__text"><?php echo esc_html( $item['text'] ); ?></blockquote>
					<figcaption class="home-testi__who">
					<strong><?php echo esc_html( $item['name'] ); ?></strong>
					<?php if ( ! empty( $item['url'] ) ) : ?>
						<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['role'] ); ?></a>
					<?php else : ?>
						<?php echo esc_html( $item['role'] ); ?>
					<?php endif; ?>
				</figcaption>
				</figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>
