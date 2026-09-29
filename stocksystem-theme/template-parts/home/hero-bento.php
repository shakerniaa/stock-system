<?php
/**
 * Hero 1b — bento grid: one large banner plus up to four tiles, all
 * visible at once so no campaign is hidden behind a slide.
 * Source: stocksystem-dev-kit/HomePage.dc.html §1b.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$title    = trim( (string) stocksystem_hero( 'bento_title' ) );

if ( '' === $title ) {
	get_template_part( 'template-parts/home/hero', 'classic' );
	return;
}

$tiles = array();
foreach ( (array) stocksystem_hero( 'bento_tiles' ) as $tile ) {
	if ( '' === trim( (string) $tile['title'] ) ) {
		continue;
	}
	$tiles[] = $tile;
}

$main_image = (int) stocksystem_hero( 'bento_image' );
$main_cta   = trim( (string) stocksystem_hero( 'bento_cta' ) );
?>
<section class="hero-bento">
	<div class="container hero-bento__grid">
		<div class="hero-bento__main" style="<?php echo esc_attr( stocksystem_hero_theme_vars( stocksystem_hero( 'bento_theme' ) ) ); ?>">
			<span class="hero-bento__stripe" aria-hidden="true"></span>
			<div class="hero-bento__main-copy">
				<?php if ( '' !== trim( (string) stocksystem_hero( 'bento_eyebrow' ) ) ) : ?>
					<span class="hero-bento__eyebrow"><?php echo esc_html( stocksystem_hero( 'bento_eyebrow' ) ); ?></span>
				<?php endif; ?>
				<h1 class="hero-bento__title"><?php echo esc_html( $title ); ?></h1>
				<?php if ( '' !== trim( (string) stocksystem_hero( 'bento_body' ) ) ) : ?>
					<p class="hero-bento__body"><?php echo esc_html( stocksystem_hero( 'bento_body' ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $main_cta ) : ?>
				<a class="btn hero-bento__cta" href="<?php echo esc_url( stocksystem_home_link( stocksystem_hero( 'bento_cta_url' ), $shop_url ) ); ?>"><?php echo esc_html( $main_cta ); ?></a>
			<?php endif; ?>
			<?php if ( $main_image ) : ?>
				<?php
				echo wp_get_attachment_image(
					$main_image,
					'large',
					false,
					array( 'class' => 'hero-bento__img', 'decoding' => 'async', 'loading' => 'eager', 'fetchpriority' => 'high' )
				);
				?>
			<?php endif; ?>
		</div>

		<?php foreach ( $tiles as $tile ) : ?>
			<?php
			$tile_url   = stocksystem_home_link( $tile['url'], $shop_url );
			$tile_image = (int) $tile['image'];
			?>
			<a
				class="hero-bento__tile"
				href="<?php echo esc_url( $tile_url ); ?>"
				style="--tile-bg:<?php echo esc_attr( $tile['bg'] ); ?>;--tile-fg:<?php echo esc_attr( $tile['fg'] ); ?>"
			>
				<?php if ( '' !== trim( (string) $tile['eyebrow'] ) ) : ?>
					<span class="hero-bento__tile-eyebrow"><?php echo esc_html( $tile['eyebrow'] ); ?></span>
				<?php endif; ?>
				<span class="hero-bento__tile-title"><?php echo esc_html( $tile['title'] ); ?></span>
				<?php if ( '' !== trim( (string) $tile['body'] ) ) : ?>
					<span class="hero-bento__tile-body"><?php echo esc_html( $tile['body'] ); ?></span>
				<?php endif; ?>
				<?php if ( '' !== trim( (string) $tile['cta'] ) ) : ?>
					<span class="hero-bento__tile-cta"><?php echo esc_html( $tile['cta'] ); ?></span>
				<?php endif; ?>
				<?php if ( $tile_image ) : ?>
					<?php echo wp_get_attachment_image( $tile_image, 'medium', false, array( 'class' => 'hero-bento__tile-img', 'decoding' => 'async', 'loading' => 'lazy', 'alt' => '' ) ); ?>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
	</div>
</section>
