<?php
/**
 * Review author line: name, verified-buyer label, Jalali date.
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;

global $comment;
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

if ( '0' === $comment->comment_approved ) {
	?>
	<p class="meta"><em class="woocommerce-review__awaiting-approval"><?php esc_html_e( 'نظر شما پس از تأیید نمایش داده می‌شود.', 'stocksystem' ); ?></em></p>
	<?php
} else {
	?>
	<p class="meta product-review__meta">
		<strong class="woocommerce-review__author"><?php comment_author(); ?></strong>
		<?php if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) : ?>
			<em class="woocommerce-review__verified verified"><?php esc_html_e( 'خریدار تأییدشده', 'stocksystem' ); ?></em>
		<?php endif; ?>
		<time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( stocksystem_jdate( 'j F Y', get_comment_time( 'U', true ) ) ); ?></time>
	</p>
	<?php
}
