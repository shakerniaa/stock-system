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

$categories  = stocksystem_nav_categories();
$primary_cat = ! empty( $categories ) ? $categories[0]->url : ( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) );

$hero_images = array();
if ( function_exists( 'wc_get_products' ) ) {
	$hero_products = wc_get_products( array( 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish' ) );
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
	<div class="container home-hero__grid">
		<div class="home-hero__copy">
			<span class="home-hero__eyebrow">
				<?php
				printf(
					/* translators: %s: warranty text from Customizer */
					esc_html__( 'استوک اروپایی · تست‌شده · %s', 'stocksystem' ),
					esc_html( stocksystem_business( 'warranty_text' ) )
				);
				?>
			</span>
			<h1 class="home-hero__title"><?php esc_html_e( 'لپ‌تاپ و کامپیوتر حرفه‌ای، با قیمتی که منطقی است', 'stocksystem' ); ?></h1>
			<p class="home-hero__desc"><?php esc_html_e( 'هر دستگاه پیش از فروش تست سخت‌افزاری کامل می‌شود و برگهٔ وضعیت دارد: سلامت باتری، ساعت کارکرد و وضعیت بدنه — بدون ابهام.', 'stocksystem' ); ?></p>
			<div class="home-hero__ctas">
				<a class="btn btn--primary" href="<?php echo esc_url( $primary_cat ); ?>"><?php esc_html_e( 'مشاهدهٔ لپ‌تاپ‌ها', 'stocksystem' ); ?></a>
				<a class="btn btn--outline-on-dark" href="<?php echo esc_url( home_url( '/stock-condition/' ) ); ?>"><?php esc_html_e( 'وضعیت کالای استوک چیست؟', 'stocksystem' ); ?></a>
			</div>
			<div class="home-hero__pillars">
				<span class="home-hero__pillar-bar" aria-hidden="true"></span>
				<span class="home-hero__pillar-list">
					<span><?php esc_html_e( 'اعتماد', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'کیفیت', 'stocksystem' ); ?></span>
					<span><?php esc_html_e( 'تکنولوژی', 'stocksystem' ); ?></span>
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
