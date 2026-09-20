<?php
/**
 * Optional promo banner — «استوک سیستم ← بخش‌های تازهٔ صفحهٔ اصلی».
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg   = stocksystem_opt( 'homex' );
$image = stocksystem_opt_image( 'homex', 'banner_image', 'large' );
$link  = stocksystem_home_link( $cfg['banner_link'] );

if ( '' === $cfg['banner_title'] && '' === $cfg['banner_text'] && ! $image ) {
	return;
}
?>
<section class="home-x home-banner">
	<div class="container">
		<div class="home-banner__box<?php echo $image ? '' : ' home-banner__box--no-image'; ?>">
			<div>
				<?php if ( '' !== $cfg['banner_title'] ) : ?><h2 class="home-banner__title"><?php echo esc_html( $cfg['banner_title'] ); ?></h2><?php endif; ?>
				<?php if ( '' !== $cfg['banner_text'] ) : ?><p class="home-banner__text"><?php echo esc_html( $cfg['banner_text'] ); ?></p><?php endif; ?>
				<?php if ( $link ) : ?><a class="btn btn--primary" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $cfg['banner_btn'] ); ?></a><?php endif; ?>
			</div>
			<?php if ( $image ) : ?>
				<img class="home-banner__image" src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $cfg['banner_title'] ); ?>" loading="lazy">
			<?php endif; ?>
		</div>
	</div>
</section>
