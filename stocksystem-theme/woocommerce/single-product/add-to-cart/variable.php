<?php
/**
 * Variable-product add-to-cart form — overrides WooCommerce's default
 * <select>-per-attribute UI with the RAM/storage pill-tile configurator
 * (15-A), while keeping every structural piece wc-add-to-cart-variation.js
 * depends on (hidden native <select>s, data-product_variations, the
 * .single_variation wrapper, .variation_id input) so pricing/stock stay
 * 100% server-authoritative — this file only changes how the choice gets
 * made, not how the result gets priced.
 *
 * Add-ons (Windows licence, case+mouse, warranty extension) are a
 * separate layer bolted on after the native form pieces — see
 * inc/product-addons.php for the cart-side surcharge logic.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$attributes         = $product->get_variation_attributes();
$available_variations = $product->is_type( 'variable' ) ? $product->get_available_variations() : false;
$variations_attr    = wc_esc_json( wp_json_encode( $available_variations ) );
$addons             = stocksystem_get_product_addons( $product->get_id() );
$cheapest_price     = stocksystem_configurator_cheapest_price( $product );
$is_hardware_config = stocksystem_is_hardware_configurator_product( $product );

do_action( 'woocommerce_before_add_to_cart_form' );
?>
<form class="variations_form cart configurator" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'این محصول در حال حاضر قابل خرید نیست.', 'stocksystem' ) ) ); ?></p>
	<?php else : ?>

		<div class="configurator__grid">
			<div class="configurator__options">
				<?php foreach ( $attributes as $attribute_name => $raw_options ) : ?>
					<?php
					$tile_options = stocksystem_configurator_attribute_options( $product, $attribute_name );
					$select_name  = 'attribute_' . sanitize_title( $attribute_name );
					$group_id     = 'configurator-' . sanitize_title( $attribute_name );
					?>
					<div class="configurator__attribute" data-attribute-select="<?php echo esc_attr( $select_name ); ?>">
						<span class="configurator__attribute-label"><?php echo esc_html( wc_attribute_label( $attribute_name ) ); ?></span>

						<div class="configurator__tiles" role="radiogroup" aria-label="<?php echo esc_attr( wc_attribute_label( $attribute_name ) ); ?>">
							<?php foreach ( $tile_options as $option ) : ?>
								<label class="configurator__tile<?php echo ! $option['available'] ? ' is-unavailable' : ''; ?>">
									<input
										type="radio"
										name="<?php echo esc_attr( $group_id ); ?>"
										value="<?php echo esc_attr( $option['value'] ); ?>"
										data-delta="<?php echo esc_attr( $option['delta'] ); ?>"
										<?php disabled( ! $option['available'] ); ?>
									>
									<span class="configurator__tile-label"><?php echo esc_html( $option['label'] ); ?></span>
									<span class="configurator__tile-delta">
										<?php
										if ( ! $option['available'] ) {
											esc_html_e( 'ناموجود', 'stocksystem' );
										} elseif ( $option['delta'] > 0 ) {
											echo '+' . esc_html( stocksystem_format_number( $option['delta'] ) );
										} else {
											esc_html_e( 'قیمت پایه', 'stocksystem' );
										}
										?>
									</span>
								</label>
							<?php endforeach; ?>
						</div>

						<!-- Hidden native select — required by wc-add-to-cart-variation.js
						     for server-authoritative price/stock lookup; the tiles above
						     drive it via assets/js/product-configurator.js. -->
						<span class="configurator__native-select screen-reader-text">
							<?php
							wc_dropdown_variation_attribute_options(
								array(
									'options'   => $raw_options,
									'attribute' => $attribute_name,
									'product'   => $product,
								)
							);
							?>
						</span>
					</div>
				<?php endforeach; ?>

				<?php if ( ! empty( $addons ) ) : ?>
					<div class="configurator__attribute">
						<span class="configurator__attribute-label"><?php esc_html_e( 'افزودنی‌ها', 'stocksystem' ); ?></span>
						<div class="configurator__addons">
							<?php foreach ( $addons as $addon ) : ?>
								<label class="configurator__addon">
									<input type="checkbox" name="stocksystem_addons[]" value="<?php echo esc_attr( $addon['id'] ); ?>" data-price="<?php echo esc_attr( $addon['price'] ); ?>">
									<span class="configurator__addon-box" aria-hidden="true">
										<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7"></path></svg>
									</span>
									<span class="configurator__addon-label"><?php echo esc_html( $addon['label'] ); ?></span>
									<span class="configurator__addon-price">+<?php echo esc_html( stocksystem_format_number( $addon['price'] ) ); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endif; ?>

				<button type="button" class="configurator__reset reset_variations"><?php esc_html_e( 'پاک کردن انتخاب‌ها', 'stocksystem' ); ?></button>
			</div>

			<div class="configurator__summary<?php echo $is_hardware_config ? ' configurator__summary--custom-meta' : ''; ?>" data-base-price="<?php echo esc_attr( $cheapest_price ); ?>" data-hardware-config="<?php echo $is_hardware_config ? '1' : '0'; ?>">
				<?php do_action( 'woocommerce_single_variation' ); ?>

				<?php if ( $is_hardware_config ) : ?>
					<div class="configurator__meta">
						<span class="configurator__stock-line" data-upgraded-text="<?php esc_attr_e( 'ارتقا در کارگاه انجام می‌شود · موجود', 'stocksystem' ); ?>"></span>
						<span class="configurator__lead-line" data-upgraded-text="<?php esc_attr_e( 'آماده‌سازی ۱ تا ۲ روز کاری', 'stocksystem' ); ?>" data-default-text="<?php esc_attr_e( 'ارسال در همان روز کاری', 'stocksystem' ); ?>"></span>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php do_action( 'woocommerce_after_variations_form' ); ?>
	<?php endif; ?>
</form>
<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
