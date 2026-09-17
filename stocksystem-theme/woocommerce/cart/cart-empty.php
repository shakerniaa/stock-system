<?php
/**
 * Empty cart state. Source: 10 Mobile Flows.dc.html §B2.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

$suggestion = function_exists( 'wc_get_products' ) ? wc_get_products( array( 'limit' => 1, 'orderby' => 'popularity', 'status' => 'publish' ) ) : array();
?>
<div class="cart-empty">
	<span class="cart-empty__icon" aria-hidden="true">
		<svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16l-1.5 10.5a2 2 0 0 1-2 1.7H7.5a2 2 0 0 1-2-1.7L4 6z"></path><path d="M9 6V4.5a3 3 0 0 1 6 0V6"></path></svg>
	</span>
	<h1 class="cart-empty__title"><?php esc_html_e( 'سبد شما خالی است', 'stocksystem' ); ?></h1>
	<p class="cart-empty__desc"><?php echo esc_html( sprintf(
		/* translators: %s: store phone number */
		__( 'اگر مدل مشخصی در ذهن دارید، با شمارهٔ %s تماس بگیرید — موجودی انبار هر روز به‌روز می‌شود.', 'stocksystem' ),
		stocksystem_business( 'phone' )
	) ); ?></p>
	<div class="cart-empty__actions">
		<a href="<?php echo esc_url( $shop_url ); ?>" class="btn btn--primary btn--block"><?php esc_html_e( 'مشاهدهٔ لپ‌تاپ‌ها', 'stocksystem' ); ?></a>
		<a href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>" class="btn btn--outline btn--block ltr"><?php esc_html_e( 'استعلام تلفنی', 'stocksystem' ); ?></a>
	</div>

	<?php if ( ! empty( $suggestion ) ) : ?>
		<div class="cart-empty__suggestion">
			<span class="cart-empty__suggestion-title"><?php esc_html_e( 'پیشنهاد این هفته', 'stocksystem' ); ?></span>
			<div class="product-grid product-grid--single">
				<?php
				global $product;
				$product = $suggestion[0];
				get_template_part( 'template-parts/product/card' );
				?>
			</div>
		</div>
	<?php endif; ?>
</div>
