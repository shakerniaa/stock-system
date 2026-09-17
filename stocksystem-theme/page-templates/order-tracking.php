<?php
/**
 * Template Name: پیگیری سفارش
 *
 * Standalone, guest-accessible order tracking — README: "پیگیری سفارش
 * بدون نیاز به ورود کار می‌کند." Deliberately not a My Account endpoint;
 * WooCommerce's account shortcode gates every endpoint behind login
 * except its own hardcoded lost-password case, so fighting that isn't
 * worth it — a plain page template sidesteps it entirely.
 * Source: 16 Shop Pages.dc.html §16-D.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$order        = null;
$lookup_error = '';

if ( ! empty( $_POST['stocksystem_track_nonce'] ) && wp_verify_nonce( wp_unslash( $_POST['stocksystem_track_nonce'] ), 'stocksystem_track_order' ) ) {
	$order_number = sanitize_text_field( wp_unslash( $_POST['order_number'] ?? '' ) );
	$phone        = stocksystem_normalize_phone( $_POST['phone'] ?? '' );
	$order_id     = (int) preg_replace( '/\D/', '', $order_number );

	$candidate = $order_id ? wc_get_order( $order_id ) : false;

	if ( $candidate && stocksystem_normalize_phone( $candidate->get_billing_phone() ) === $phone ) {
		$order = $candidate;
	} else {
		$lookup_error = __( 'سفارشی با این مشخصات پیدا نشد. شمارهٔ سفارش و موبایل را بررسی کنید.', 'stocksystem' );
	}
}
?>

<div class="container order-tracking-page">
	<h1 class="order-tracking-page__title"><?php esc_html_e( 'پیگیری سفارش', 'stocksystem' ); ?></h1>

	<div class="order-tracking-page__grid">
		<div class="order-tracking-form-panel">
			<p><?php esc_html_e( 'بدون ورود به حساب: شمارهٔ سفارش و شمارهٔ موبایل ثبت‌شده کافی است.', 'stocksystem' ); ?></p>

			<form method="post">
				<?php wp_nonce_field( 'stocksystem_track_order', 'stocksystem_track_nonce' ); ?>
				<label for="track-order-number"><?php esc_html_e( 'شمارهٔ سفارش', 'stocksystem' ); ?></label>
				<input type="text" id="track-order-number" name="order_number" class="ltr" placeholder="SS-48121" required value="<?php echo esc_attr( wp_unslash( $_POST['order_number'] ?? '' ) ); ?>">

				<label for="track-order-phone"><?php esc_html_e( 'شمارهٔ موبایل', 'stocksystem' ); ?></label>
				<input type="tel" id="track-order-phone" name="phone" class="ltr" placeholder="09xx xxx xxxx" required pattern="09[0-9]{9}" value="<?php echo esc_attr( wp_unslash( $_POST['phone'] ?? '' ) ); ?>">

				<button type="submit" class="btn btn--dark btn--block"><?php esc_html_e( 'پیگیری', 'stocksystem' ); ?></button>

				<?php if ( $lookup_error ) : ?>
					<p class="order-tracking-form-panel__error"><?php echo esc_html( $lookup_error ); ?></p>
				<?php endif; ?>

				<p class="order-tracking-form-panel__hint"><?php esc_html_e( 'کد رهگیری پستی پس از تحویل به شرکت حمل، پیامک می‌شود.', 'stocksystem' ); ?></p>
			</form>
		</div>

		<?php if ( $order ) : ?>
			<div class="order-tracking-result">
				<div class="order-tracking-result__header">
					<span class="ltr"><?php echo esc_html( $order->get_order_number() ); ?></span>
					<span class="order-tracking-result__status"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span>
				</div>

				<?php get_template_part( 'template-parts/checkout/order-timeline', null, array( 'order' => $order ) ); ?>

				<div class="order-confirmation-items">
					<span class="order-confirmation-items__title"><?php esc_html_e( 'کالاهای سفارش', 'stocksystem' ); ?></span>
					<?php foreach ( $order->get_items() as $item ) : ?>
						<?php $product = $item->get_product(); ?>
						<div class="order-confirmation-items__row">
							<?php if ( $product ) : ?>
								<?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
							<?php endif; ?>
							<span class="order-confirmation-items__info">
								<span class="order-confirmation-items__name"><?php echo esc_html( $item->get_name() ); ?></span>
							</span>
							<span class="order-confirmation-items__price"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
