<?php
/**
 * Hero 1e — campaign hero: short brand message, live countdown, and a
 * row of deal cards with their remaining stock.
 * Source: stocksystem-dev-kit/HomePage.dc.html §1e.
 *
 * The design's "۷۸٪ فروخته شد" bar is only drawn for products that
 * actually manage stock — see stocksystem_hero_deal_products(), which
 * computes it from stock + total sales rather than inventing a number.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cards = stocksystem_hero_deal_products( max( 2, (int) stocksystem_hero( 'deal_count' ) ) );

if ( empty( $cards ) ) {
	get_template_part( 'template-parts/home/hero', 'classic' );
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$end      = trim( (string) stocksystem_hero( 'deal_end' ) );
$stamp    = $end ? strtotime( $end ) : 0;
?>
<section class="hero-deal">
	<div class="hero-deal__head">
		<div class="container hero-deal__head-inner">
			<div class="hero-deal__copy">
				<h1 class="hero-deal__title"><?php echo esc_html( stocksystem_hero( 'deal_title' ) ); ?></h1>
				<?php if ( '' !== trim( (string) stocksystem_hero( 'deal_body' ) ) ) : ?>
					<p class="hero-deal__body"><?php echo esc_html( stocksystem_hero( 'deal_body' ) ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( $stamp > time() ) : ?>
				<div class="hero-deal__countdown ltr" data-hero-countdown="<?php echo esc_attr( $stamp ); ?>">
					<?php
					$units = array(
						'hours'   => __( 'ساعت', 'stocksystem' ),
						'minutes' => __( 'دقیقه', 'stocksystem' ),
						'seconds' => __( 'ثانیه', 'stocksystem' ),
					);
					foreach ( $units as $unit => $label ) :
						?>
						<span class="hero-deal__unit">
							<span class="hero-deal__unit-value" data-countdown-unit="<?php echo esc_attr( $unit ); ?>">&mdash;</span>
							<span class="hero-deal__unit-label"><?php echo esc_html( $label ); ?></span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="container hero-deal__cards">
		<?php foreach ( $cards as $card ) : ?>
			<?php $product = $card['product']; ?>
			<article class="hero-deal__card">
				<a class="hero-deal__card-media" href="<?php echo esc_url( $product->get_permalink() ); ?>">
					<?php echo $product->get_image( 'medium', array( 'decoding' => 'async', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- WooCommerce-escaped markup ?>
					<?php if ( $card['discount'] > 0 ) : ?>
						<span class="hero-deal__off">
							<?php printf( /* translators: %s: discount percent */ esc_html__( '٪%s−', 'stocksystem' ), esc_html( stocksystem_to_persian_digits( $card['discount'] ) ) ); ?>
						</span>
					<?php endif; ?>
				</a>
				<div class="hero-deal__card-body">
					<a class="hero-deal__card-name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
					<span class="hero-deal__card-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>

					<?php if ( null !== $card['sold'] ) : ?>
						<span class="hero-deal__bar" aria-hidden="true">
							<span class="hero-deal__bar-fill" style="width:<?php echo (int) $card['sold']; ?>%"></span>
						</span>
					<?php endif; ?>

					<?php if ( null !== $card['left'] ) : ?>
						<span class="hero-deal__left">
							<?php
							if ( $card['left'] > 0 ) {
								printf(
									/* translators: %s: remaining stock count */
									esc_html__( '%s عدد باقی مانده', 'stocksystem' ),
									esc_html( stocksystem_format_number( $card['left'] ) )
								);
							} else {
								esc_html_e( 'تمام شد', 'stocksystem' );
							}
							?>
						</span>
					<?php endif; ?>
				</div>
			</article>
		<?php endforeach; ?>
	</div>

	<?php if ( '' !== trim( (string) stocksystem_hero( 'deal_cta' ) ) ) : ?>
		<div class="container hero-deal__more">
			<a class="btn btn--outline" href="<?php echo esc_url( stocksystem_home_link( stocksystem_hero( 'deal_cta_url' ), add_query_arg( 'on_sale', '1', $shop_url ) ) ); ?>">
				<?php echo esc_html( stocksystem_hero( 'deal_cta' ) ); ?>
			</a>
		</div>
	<?php endif; ?>
</section>
