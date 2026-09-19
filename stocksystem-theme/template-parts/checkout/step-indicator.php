<?php
/**
 * 4-stage progress header shared by cart, checkout, and the thank-you
 * page — decision #2's unified wizard: سبد → اطلاعات و ارسال → پرداخت →
 * پیگیری سفارش. Source: 12 Checkout Flow.dc.html (stepper markup).
 *
 * $args:
 *   current (int, 1-4) — the stage this page represents.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current = ! empty( $args['current'] ) ? (int) $args['current'] : 1;

$stages = array(
	1 => __( 'سبد خرید', 'stocksystem' ),
	2 => __( 'اطلاعات و ارسال', 'stocksystem' ),
	3 => __( 'پرداخت', 'stocksystem' ),
	4 => __( 'پیگیری سفارش', 'stocksystem' ),
);
?>
<div class="checkout-stepper-band">
<div class="container">
<div class="checkout-stepper">
	<?php foreach ( $stages as $number => $label ) : ?>
		<span class="checkout-stepper__stage<?php echo $number < $current ? ' is-done' : ( $number === $current ? ' is-current' : '' ); ?>">
			<span class="checkout-stepper__circle">
				<?php if ( $number < $current ) : ?>
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6L9 17l-5-5"></path></svg>
				<?php else : ?>
					<?php echo esc_html( stocksystem_to_persian_digits( $number ) ); ?>
				<?php endif; ?>
			</span>
			<span class="checkout-stepper__label"><?php echo esc_html( $label ); ?></span>
			<?php if ( $number < count( $stages ) ) : ?><span class="checkout-stepper__line" aria-hidden="true"></span><?php endif; ?>
		</span>
	<?php endforeach; ?>
</div>
</div>
</div>
