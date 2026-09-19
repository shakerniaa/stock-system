<?php
/**
 * One review card: initial-letter avatar (no external Gravatar request),
 * author + verified label + Jalali date, stars, text.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$author  = get_comment_author();
$initial = mb_substr( trim( $author ), 0, 1 );
?>
<li <?php comment_class( 'product-review' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="comment_container product-review__inner">
		<span class="product-review__avatar" aria-hidden="true"><?php echo esc_html( $initial ? $initial : '؟' ); ?></span>

		<div class="comment-text product-review__body">
			<?php
			do_action( 'woocommerce_review_before_comment_meta', $comment );
			do_action( 'woocommerce_review_meta', $comment );
			do_action( 'woocommerce_review_before_comment_text', $comment );
			do_action( 'woocommerce_review_comment_text', $comment );
			do_action( 'woocommerce_review_after_comment_text', $comment );
			?>
		</div>
	</div>
