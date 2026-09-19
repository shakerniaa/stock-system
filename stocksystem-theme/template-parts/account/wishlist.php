<?php
/**
 * "علاقه‌مندی‌ها" — My Account → Wishlist, as the design's list
 * (16 Shop Pages.dc.html §16-D): product / price / status columns with a
 * per-row action and a remove button. Every device is unique, so a saved
 * one may sell — the status column is the point of the page, and a sold
 * item's action becomes "see similar".
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_ids = stocksystem_get_wishlist();
$low_stock   = (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
$nonce       = wp_create_nonce( 'stocksystem_wishlist' );
?>
<div class="account-wishlist">
	<div class="account-panel__header">
		<h2><?php esc_html_e( 'علاقه‌مندی‌ها', 'stocksystem' ); ?></h2>
		<span class="account-panel__count" data-wishlist-count>
			<?php
			printf(
				/* translators: %s: saved item count, Persian digits */
				esc_html__( '%s کالا ذخیره شده', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( count( $product_ids ) ) )
			);
			?>
		</span>
	</div>

	<p class="account-panel__empty" data-wishlist-empty<?php echo empty( $product_ids ) ? '' : ' hidden'; ?>><?php esc_html_e( 'هنوز کالایی به علاقه‌مندی‌ها اضافه نکرده‌اید. با زدن قلب روی هر کالا اینجا ذخیره می‌شود.', 'stocksystem' ); ?></p>

	<?php if ( ! empty( $product_ids ) ) : ?>
		<div class="wishlist-table" role="table" aria-label="<?php esc_attr_e( 'علاقه‌مندی‌ها', 'stocksystem' ); ?>">
			<div class="wishlist-table__head" role="row">
				<span role="columnheader"><?php esc_html_e( 'کالا', 'stocksystem' ); ?></span>
				<span role="columnheader"><?php esc_html_e( 'قیمت', 'stocksystem' ); ?></span>
				<span role="columnheader"><?php esc_html_e( 'وضعیت', 'stocksystem' ); ?></span>
				<span role="columnheader"><span class="screen-reader-text"><?php esc_html_e( 'اقدام', 'stocksystem' ); ?></span></span>
			</div>

			<?php
			foreach ( $product_ids as $product_id ) :
				$product = wc_get_product( $product_id );
				if ( ! $product ) {
					continue;
				}

				$in_stock = $product->is_in_stock();
				$qty      = $product->managing_stock() ? $product->get_stock_quantity() : null;
				$is_low   = $in_stock && null !== $qty && $qty <= $low_stock;
				$grading  = stocksystem_product_grading_note( $product );
				$cats     = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );
				$similar  = ! empty( $cats ) ? get_term_link( (int) $cats[0], 'product_cat' ) : wc_get_page_permalink( 'shop' );
				?>
				<div class="wishlist-row<?php echo $in_stock ? '' : ' is-unavailable'; ?>" role="row" data-wishlist-row="<?php echo esc_attr( $product->get_id() ); ?>">
					<span class="wishlist-row__product" role="cell">
						<a class="wishlist-row__thumb" href="<?php echo esc_url( $product->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
							<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
						</a>
						<span class="wishlist-row__info">
							<a class="wishlist-row__name" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a>
							<?php if ( $grading ) : ?>
								<span class="wishlist-row__meta">
									<span class="product-card__grading-dot" style="background-color:<?php echo esc_attr( $grading['color'] ); ?>"></span>
									<?php echo esc_html( $grading['label'] ); ?>
								</span>
							<?php endif; ?>
						</span>
					</span>

					<span class="wishlist-row__price" role="cell">
						<?php if ( $product->is_on_sale() && ! $product->is_type( 'variable' ) ) : ?>
							<strong class="is-sale"><?php echo esc_html( stocksystem_format_number( $product->get_sale_price() ) ); ?></strong>
							<s><?php echo esc_html( stocksystem_format_number( $product->get_regular_price() ) ); ?></s>
						<?php else : ?>
							<strong><?php echo wp_kses_post( $product->is_type( 'variable' ) ? $product->get_price_html() : stocksystem_format_number( $product->get_price() ) ); ?></strong>
						<?php endif; ?>
					</span>

					<span class="wishlist-row__status" role="cell">
						<?php if ( ! $in_stock ) : ?>
							<span class="badge badge--error"><?php esc_html_e( 'ناموجود', 'stocksystem' ); ?></span>
						<?php elseif ( $is_low ) : ?>
							<span class="badge badge--warning">
								<?php
								/* translators: %s: units left, Persian digits */
								printf( esc_html__( 'تنها %s عدد', 'stocksystem' ), esc_html( stocksystem_to_persian_digits( $qty ) ) );
								?>
							</span>
						<?php else : ?>
							<span class="badge badge--success"><?php esc_html_e( 'موجود', 'stocksystem' ); ?></span>
						<?php endif; ?>
					</span>

					<span class="wishlist-row__actions" role="cell">
						<?php if ( ! $in_stock ) : ?>
							<a class="btn btn--outline" href="<?php echo esc_url( $similar ); ?>"><?php esc_html_e( 'مشابه‌ها را ببین', 'stocksystem' ); ?></a>
						<?php elseif ( $product->is_type( 'simple' ) && $product->is_purchasable() ) : ?>
							<a
								href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
								data-quantity="1"
								data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
								data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
								rel="nofollow"
								class="btn btn--primary ajax_add_to_cart add_to_cart_button"
							><?php esc_html_e( 'افزودن به سبد', 'stocksystem' ); ?></a>
						<?php else : ?>
							<a class="btn btn--outline" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php esc_html_e( 'مشاهده گزینه‌ها', 'stocksystem' ); ?></a>
						<?php endif; ?>

						<button
							type="button"
							class="wishlist-row__remove"
							data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product name */ __( 'حذف «%s» از علاقه‌مندی‌ها', 'stocksystem' ), $product->get_name() ) ); ?>"
						>
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>
						</button>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
