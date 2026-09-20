<?php
/**
 * Optional brand logos strip — «استوک سیستم ← بخش‌های تازهٔ صفحهٔ اصلی».
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg = stocksystem_opt( 'homex' );

if ( empty( $cfg['brands_items'] ) ) {
	return;
}
?>
<section class="home-x home-brands">
	<div class="container">
		<div class="home-x__head"><h2><?php echo esc_html( $cfg['brands_title'] ); ?></h2></div>
		<div class="home-brands__row">
			<?php foreach ( $cfg['brands_items'] as $item ) : ?>
				<?php
				$logo = ! empty( $item['logo'] ) ? wp_get_attachment_image_url( (int) $item['logo'], 'medium' ) : '';
				$url  = stocksystem_home_link( $item['url'] );
				$tag  = $url ? 'a' : 'span';
				?>
				<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="home-brands__item"<?php echo $url ? ' href="' . esc_url( $url ) . '"' : ''; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<?php if ( $logo ) : ?>
						<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $item['name'] ); ?>" loading="lazy">
					<?php else : ?>
						<span class="ltr"><?php echo esc_html( $item['name'] ); ?></span>
					<?php endif; ?>
				</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			<?php endforeach; ?>
		</div>
	</div>
</section>
