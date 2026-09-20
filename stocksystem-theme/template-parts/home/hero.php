<?php
/**
 * Homepage hero — 7/5 split. Source: 01 Home.dc.html.
 *
 * Decision #5 fix: the design's badge hardcodes "گارانتی ۱۸ ماه"; pulled
 * from the Customizer warranty setting instead (default: 1 month).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$hero        = stocksystem_home( 'hero' ); // Appearance → «صفحهٔ اصلی».
$categories  = stocksystem_nav_categories();
$primary_cat = stocksystem_home_link( $hero['cta1_url'], ! empty( $categories ) ? $categories[0]->url : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ) );
$secondary   = stocksystem_home_link( $hero['cta2_url'], home_url( '/stock-condition/' ) );

// Images chosen in the admin win; otherwise draw from the latest products.
$hero_images = array();
foreach ( $hero['images'] as $hero_image_id ) {
	if ( $hero_image_id && wp_attachment_is_image( $hero_image_id ) ) {
		$hero_images[] = array(
			'src' => wp_get_attachment_image_url( $hero_image_id, 'medium' ),
			'alt' => trim( (string) get_post_meta( $hero_image_id, '_wp_attachment_image_alt', true ) ),
		);
	}
}

if ( empty( $hero_images ) && function_exists( 'wc_get_products' ) ) {
	// The hero shows devices, so draw from the primary (first) category —
	// laptops — rather than whatever product was added last.
	$hero_args = array( 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish' );
	$hero_term = ! empty( $categories ) ? get_term_by( 'name', $categories[0]->name, 'product_cat' ) : null;
	if ( $hero_term ) {
		$hero_args['category'] = array( $hero_term->slug );
	}
	$hero_products = wc_get_products( $hero_args );
	foreach ( $hero_products as $hero_product ) {
		if ( $hero_product->get_image_id() ) {
			$hero_images[] = array(
				'src' => wp_get_attachment_image_url( $hero_product->get_image_id(), 'medium' ),
				'alt' => $hero_product->get_name(),
			);
		}
	}
}
if ( empty( $hero_images ) ) {
	$hero_images = array(
		array( 'src' => STOCKSYSTEM_URI . '/assets/images/products/dell-inspiron-3520.png', 'alt' => 'Dell Inspiron 3520' ),
		array( 'src' => STOCKSYSTEM_URI . '/assets/images/products/elitebook-840-g8.png', 'alt' => 'HP EliteBook 840 G8' ),
		array( 'src' => STOCKSYSTEM_URI . '/assets/images/products/surface-laptop-4.png', 'alt' => 'Surface Laptop 4' ),
	);
}
?>
<section class="home-hero">
	<span class="home-hero__decor" aria-hidden="true">
		<span class="home-hero__decor-band"></span>
		<span class="home-hero__decor-ring"></span>
		<span class="home-hero__decor-ring"></span>
	</span>
	<div class="container home-hero__grid">
		<div class="home-hero__copy">
			<span class="home-hero__eyebrow">
				<?php
				// Empty in the admin = «استوک اروپایی · تست‌شده · <warranty text from Customizer>».
				echo esc_html( '' !== $hero['eyebrow'] ? $hero['eyebrow'] : sprintf( /* translators: %s: warranty text */ __( 'استوک اروپایی · تست‌شده · %s', 'stocksystem' ), stocksystem_business( 'warranty_text' ) ) );
				?>
			</span>
			<h1 class="home-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>
			<p class="home-hero__desc"><?php echo esc_html( $hero['desc'] ); ?></p>
			<div class="home-hero__ctas">
				<a class="btn btn--primary" href="<?php echo esc_url( $primary_cat ); ?>"><?php echo esc_html( $hero['cta1_label'] ); ?></a>
				<a class="btn btn--outline-on-dark" href="<?php echo esc_url( $secondary ); ?>"><?php echo esc_html( $hero['cta2_label'] ); ?></a>
			</div>
			<div class="home-hero__pillars">
				<span class="home-hero__pillar-bar" aria-hidden="true"></span>
				<span class="home-hero__pillar-list">
					<?php foreach ( $hero['pillars'] as $pillar ) : ?>
						<?php if ( '' !== $pillar ) : ?>
							<span><?php echo esc_html( $pillar ); ?></span>
						<?php endif; ?>
					<?php endforeach; ?>
				</span>
			</div>
		</div>

		<div class="home-hero__media">
			<?php foreach ( $hero_images as $image ) : ?>
				<img src="<?php echo esc_url( $image['src'] ); ?>" alt="<?php echo esc_attr( $image['alt'] ); ?>">
			<?php endforeach; ?>
		</div>
	</div>
</section>
