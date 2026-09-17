<?php
/**
 * Topbar — thin strip above the header. Source: 01 Home.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$threshold = stocksystem_business( 'free_shipping_threshold' );
?>
<div class="site-topbar">
	<div class="container site-topbar__inner">
		<span class="site-topbar__message">
			<?php if ( $threshold ) : ?>
				<?php
				printf(
					/* translators: %s: free shipping threshold, already formatted with Persian digits */
					esc_html__( 'ارسال رایگان سفارش‌های بالای %s تومان · ۷ روز ضمانت بازگشت', 'stocksystem' ),
					esc_html( stocksystem_format_number( $threshold ) )
				);
				?>
			<?php else : ?>
				<?php esc_html_e( 'تحویل حضوری رایگان در نیشابور · ارسال با پست به سراسر کشور · ۷ روز ضمانت بازگشت', 'stocksystem' ); ?>
			<?php endif; ?>
		</span>
		<span class="site-topbar__links">
			<a href="<?php echo esc_url( stocksystem_order_tracking_url() ); ?>"><?php esc_html_e( 'پیگیری سفارش', 'stocksystem' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'تماس با ما', 'stocksystem' ); ?></a>
		</span>
	</div>
</div>
