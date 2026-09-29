<?php
/**
 * Hero 1a — full-bleed slider with titled tabs.
 * Source: stocksystem-dev-kit/HomePage.dc.html §1a.
 *
 * Every slide is one row of the «اسلایدها» repeater. Tabs show each
 * slide's short title rather than anonymous dots, so a visitor can see
 * what is queued. Autoplay pauses on hover and on focus-within, and is
 * skipped entirely for visitors who asked for reduced motion (handled
 * in assets/js/home-hero.js).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$slides = array();
foreach ( (array) stocksystem_hero( 'slider_slides' ) as $row ) {
	if ( '' === trim( (string) $row['title'] ) ) {
		continue; // A row with no title has nothing to show.
	}
	$slides[] = $row;
}

if ( empty( $slides ) ) {
	get_template_part( 'template-parts/home/hero', 'classic' );
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$autoplay = ! empty( stocksystem_hero( 'slider_autoplay' ) );
$interval = max( 2, (int) stocksystem_hero( 'slider_interval' ) );
?>
<section
	class="hero-slider"
	data-hero-slider
	data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>"
	data-interval="<?php echo esc_attr( $interval * 1000 ); ?>"
	aria-roledescription="carousel"
	aria-label="<?php esc_attr_e( 'پیشنهادهای ویژه', 'stocksystem' ); ?>"
>
	<div class="hero-slider__viewport">
		<div class="hero-slider__track" data-hero-track>
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php
				$image  = (int) $slide['image'];
				$cta_url  = stocksystem_home_link( $slide['cta_url'], $shop_url );
				$cta2_url = stocksystem_home_link( $slide['cta2_url'], home_url( '/stock-condition/' ) );
				// With no image and no price there is no media column, so
				// the copy takes the full width instead of sitting beside
				// an empty half.
				$has_media = $image || '' !== trim( (string) $slide['price'] );
				?>
				<article
					class="hero-slide"
					style="<?php echo esc_attr( stocksystem_hero_theme_vars( $slide['theme'] ) ); ?>"
					data-hero-slide
					role="group"
					aria-roledescription="<?php esc_attr_e( 'اسلاید', 'stocksystem' ); ?>"
					aria-label="<?php echo esc_attr( sprintf( '%s / %s', stocksystem_to_persian_digits( $index + 1 ), stocksystem_to_persian_digits( count( $slides ) ) ) ); ?>"
					<?php echo 0 !== $index ? ' aria-hidden="true"' : ''; ?>
				>
					<span class="hero-slide__stripe" aria-hidden="true"></span>
					<div class="container hero-slide__grid<?php echo $has_media ? '' : ' hero-slide__grid--no-media'; ?>">
						<div class="hero-slide__copy">
							<?php if ( '' !== trim( (string) $slide['eyebrow'] ) ) : ?>
								<span class="hero-slide__eyebrow"><?php echo esc_html( $slide['eyebrow'] ); ?></span>
							<?php endif; ?>
							<?php // Only the first slide carries the page's h1. ?>
							<?php if ( 0 === $index ) : ?>
								<h1 class="hero-slide__title"><?php echo esc_html( $slide['title'] ); ?></h1>
							<?php else : ?>
								<p class="hero-slide__title" role="heading" aria-level="2"><?php echo esc_html( $slide['title'] ); ?></p>
							<?php endif; ?>
							<?php if ( '' !== trim( (string) $slide['body'] ) ) : ?>
								<p class="hero-slide__body"><?php echo esc_html( $slide['body'] ); ?></p>
							<?php endif; ?>
							<div class="hero-slide__ctas">
								<?php if ( '' !== trim( (string) $slide['cta'] ) ) : ?>
									<a class="btn hero-slide__cta" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $slide['cta'] ); ?></a>
								<?php endif; ?>
								<?php if ( '' !== trim( (string) $slide['cta2'] ) ) : ?>
									<a class="btn btn--outline-on-dark" href="<?php echo esc_url( $cta2_url ); ?>"><?php echo esc_html( $slide['cta2'] ); ?></a>
								<?php endif; ?>
							</div>
						</div>

						<?php if ( $has_media ) : ?>
							<div class="hero-slide__media">
								<span class="hero-slide__ring" aria-hidden="true"></span>
								<?php if ( $image ) : ?>
									<?php
									// The first slide's image is the page's LCP element.
									echo wp_get_attachment_image(
										$image,
										'large',
										false,
										array(
											'class'         => 'hero-slide__img',
											'decoding'      => 'async',
											'loading'       => 0 === $index ? 'eager' : 'lazy',
											'fetchpriority' => 0 === $index ? 'high' : 'auto',
										)
									);
									?>
								<?php endif; ?>
								<?php if ( '' !== trim( (string) $slide['price'] ) ) : ?>
									<span class="hero-slide__price">
										<?php if ( '' !== trim( (string) $slide['price_label'] ) ) : ?>
											<span class="hero-slide__price-label"><?php echo esc_html( $slide['price_label'] ); ?></span>
										<?php endif; ?>
										<span class="hero-slide__price-value"><?php echo esc_html( $slide['price'] ); ?></span>
									</span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( count( $slides ) > 1 ) : ?>
			<button type="button" class="hero-slider__arrow hero-slider__arrow--prev" data-hero-prev aria-label="<?php esc_attr_e( 'اسلاید قبلی', 'stocksystem' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M9 5l7 7-7 7"></path></svg>
			</button>
			<button type="button" class="hero-slider__arrow hero-slider__arrow--next" data-hero-next aria-label="<?php esc_attr_e( 'اسلاید بعدی', 'stocksystem' ); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M15 5l-7 7 7 7"></path></svg>
			</button>
		<?php endif; ?>
	</div>

	<?php if ( count( $slides ) > 1 ) : ?>
		<div class="hero-slider__tabs container" role="tablist">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<button
					type="button"
					class="hero-slider__tab<?php echo 0 === $index ? ' is-active' : ''; ?>"
					data-hero-tab="<?php echo (int) $index; ?>"
					role="tab"
					aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
				>
					<span class="hero-slider__tab-num ltr"><?php echo esc_html( stocksystem_to_persian_digits( str_pad( $index + 1, 2, '0', STR_PAD_LEFT ) ) ); ?></span>
					<span class="hero-slider__tab-label"><?php echo esc_html( '' !== trim( (string) $slide['tab'] ) ? $slide['tab'] : $slide['title'] ); ?></span>
				</button>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</section>
