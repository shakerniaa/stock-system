<?php
/**
 * Single product page — gallery + test-report panel + tabs on one side,
 * buy box on the other. Source: 03 Product.dc.html, 15 Product
 * States.dc.html §15-A/15-C.
 *
 * Decision corrections applied: warranty/shipping copy comes from
 * Customizer settings (1 month, Neyshabur pickup + nationwide post), not
 * the design's 18-month/Tehran-same-day placeholder text (decisions #4,
 * #5). Installment banner (14 Support Pages calculator's per-product
 * teaser) is dropped per decision #6.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

if ( post_password_required() ) {
	echo get_the_password_form();
	return;
}

$variant = stocksystem_product_card_variant( $product );
$badges  = stocksystem_product_badges( $product, $variant );
?>
<?php
// Outside the grid, like WooCommerce's own template: it prints the (usually
// empty) notices wrapper, which as a grid child would occupy the first cell,
// pushing the gallery into the second column and the buy box to a new row.
do_action( 'woocommerce_before_single_product' );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product-layout', $product ); ?>>

	<div class="single-product-layout__gallery-col">
		<?php woocommerce_show_product_images(); ?>
		<?php get_template_part( 'template-parts/product/test-report', null, array( 'product' => $product ) ); ?>
		<?php get_template_part( 'template-parts/product/tabs-wrapper', null, array( 'product' => $product ) ); ?>
	</div>

	<div class="single-product-layout__buy-col">
		<div class="buy-box">
			<div class="buy-box__header">
				<?php get_template_part( 'template-parts/product/wishlist-toggle', null, array( 'product' => $product ) ); ?>

				<?php if ( ! empty( $badges ) ) : ?>
					<span class="buy-box__badges">
						<?php foreach ( $badges as $badge ) : ?>
							<span class="badge <?php echo esc_attr( $badge['class'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
						<?php endforeach; ?>
						<span class="badge badge--neutral"><?php esc_html_e( 'تست‌شده و گارانتی‌دار', 'stocksystem' ); ?></span>
					</span>
				<?php endif; ?>

				<h1 class="buy-box__title"><?php the_title(); ?></h1>

				<?php if ( $product->get_sku() ) : ?>
					<span class="buy-box__sku ltr">SKU: <?php echo esc_html( $product->get_sku() ); ?></span>
				<?php endif; ?>
			</div>

			<div class="buy-box__panel">
				<?php
				switch ( $variant ) {
					case 'out-of-stock':
						get_template_part( 'template-parts/product/single-buy-box-out-of-stock', null, array( 'product' => $product ) );
						break;
					case 'quote':
						get_template_part( 'template-parts/product/single-buy-box-quote', null, array( 'product' => $product ) );
						break;
					default:
						get_template_part( 'template-parts/product/single-buy-box-default', null, array( 'product' => $product ) );
						break;
				}
				?>
			</div>

			<?php get_template_part( 'template-parts/product/key-specs', null, array( 'product' => $product ) ); ?>

			<div class="buy-box__trust">
				<span class="buy-box__trust-item">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l8 3v6c0 4.5-3.2 7.8-8 9-4.8-1.2-8-4.5-8-9V6z"></path><path d="M9 12l2.2 2.2L15.5 10"></path></svg>
					<?php echo esc_html( stocksystem_business( 'warranty_text' ) ); ?>
				</span>
				<span class="buy-box__trust-item">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v9H3zM14 10h4l3 3v3h-7z"></path><circle cx="7" cy="18" r="1.6"></circle><circle cx="17" cy="18" r="1.6"></circle></svg>
					<?php esc_html_e( 'تحویل حضوری رایگان در نیشابور · ارسال با پست/تیپاکس به سراسر کشور', 'stocksystem' ); ?>
				</span>
				<span class="buy-box__trust-item">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 14l-4-4 4-4"></path><path d="M5 10h9a5 5 0 0 1 0 10h-3"></path></svg>
					<?php esc_html_e( '۷ روز ضمانت بازگشت بدون قید و شرط', 'stocksystem' ); ?>
				</span>
			</div>
		</div>
	</div>

	<?php do_action( 'woocommerce_after_single_product_summary' ); ?>

	<?php get_template_part( 'template-parts/product/related', null, array( 'product' => $product ) ); ?>

	<?php do_action( 'woocommerce_after_single_product' ); ?>
</div>
