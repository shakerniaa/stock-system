<?php
/**
 * Product reviews tab — summary (average + count), review cards, and the
 * review form. Same ids/classes/hooks as WooCommerce's template (#reviews,
 * #comments, #review_form, #commentform, select#rating) so its own
 * single-product.js still turns the rating <select> into clickable stars —
 * the stars are then drawn by product-page.css (WooCommerce's stylesheet is
 * disabled, which is why they used to render as a bare dropdown).
 *
 * @package StockSystem
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! comments_open() ) {
	return;
}

$count   = (int) $product->get_review_count();
$average = (float) $product->get_average_rating();
?>
<div id="reviews" class="woocommerce-Reviews product-reviews">
	<div id="comments" class="product-reviews__list-wrap">
		<div class="product-reviews__summary">
			<h2 class="woocommerce-Reviews-title"><?php esc_html_e( 'نظرات کاربران', 'stocksystem' ); ?></h2>
			<?php if ( $count && wc_review_ratings_enabled() ) : ?>
				<span class="product-reviews__average">
					<strong><?php echo esc_html( stocksystem_to_persian_digits( number_format( $average, 1 ) ) ); ?></strong>
					<?php echo wc_get_rating_html( $average, $count ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="product-reviews__count">
						<?php
						/* translators: %s: review count, Persian digits */
						printf( esc_html__( 'از %s نظر', 'stocksystem' ), esc_html( stocksystem_to_persian_digits( $count ) ) );
						?>
					</span>
				</span>
			<?php endif; ?>
		</div>

		<?php if ( have_comments() ) : ?>
			<ol class="commentlist">
				<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
			</ol>

			<?php
			if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
				echo '<nav class="woocommerce-pagination">';
				paginate_comments_links(
					apply_filters(
						'woocommerce_comment_pagination_args',
						array(
							'prev_text' => '&rarr;',
							'next_text' => '&larr;',
							'type'      => 'list',
						)
					)
				);
				echo '</nav>';
			endif;
			?>
		<?php else : ?>
			<p class="woocommerce-noreviews product-reviews__empty"><?php esc_html_e( 'هنوز نظری برای این دستگاه ثبت نشده است.', 'stocksystem' ); ?></p>
		<?php endif; ?>
	</div>

	<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>
		<div id="review_form_wrapper" class="product-reviews__form">
			<div id="review_form">
				<?php
				$commenter    = wp_get_current_commenter();
				$comment_form = array(
					'title_reply'         => have_comments() ? esc_html__( 'نظر خود را بنویسید', 'stocksystem' ) : esc_html__( 'اولین نفری باشید که نظر می‌دهد', 'stocksystem' ),
					/* translators: %s: comment author */
					'title_reply_to'      => esc_html__( 'پاسخ به %s', 'stocksystem' ),
					'title_reply_before'  => '<span id="reply-title" class="comment-reply-title" role="heading" aria-level="3">',
					'title_reply_after'   => '</span>',
					'comment_notes_after' => '',
					'label_submit'        => esc_html__( 'ثبت نظر', 'stocksystem' ),
					'class_submit'        => 'btn btn--primary',
					'logged_in_as'        => '',
					'comment_field'       => '',
				);

				$name_email_required = (bool) get_option( 'require_name_email', 1 );
				$fields              = array(
					'author' => array(
						'label'        => __( 'نام', 'stocksystem' ),
						'type'         => 'text',
						'value'        => $commenter['comment_author'],
						'required'     => $name_email_required,
						'autocomplete' => 'name',
					),
					'email'  => array(
						'label'        => __( 'ایمیل', 'stocksystem' ),
						'type'         => 'email',
						'value'        => $commenter['comment_author_email'],
						'required'     => $name_email_required,
						'autocomplete' => 'email',
					),
				);

				$comment_form['fields'] = array();

				foreach ( $fields as $key => $field ) {
					$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
					$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

					if ( $field['required'] ) {
						$field_html .= '&nbsp;<span class="required">*</span>';
					}

					$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . '></p>';

					$comment_form['fields'][ $key ] = $field_html;
				}

				$account_page_url = wc_get_page_permalink( 'myaccount' );
				if ( $account_page_url ) {
					$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf(
						/* translators: %s: link tags */
						esc_html__( 'برای ثبت نظر باید %1$sوارد حساب کاربری%2$s شوید.', 'stocksystem' ),
						'<a href="' . esc_url( $account_page_url ) . '">',
						'</a>'
					) . '</p>';
				}

				if ( wc_review_ratings_enabled() ) {
					$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">' . esc_html__( 'امتیاز شما', 'stocksystem' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
						<option value="">' . esc_html__( 'انتخاب کنید…', 'stocksystem' ) . '</option>
						<option value="5">' . esc_html__( 'عالی', 'stocksystem' ) . '</option>
						<option value="4">' . esc_html__( 'خوب', 'stocksystem' ) . '</option>
						<option value="3">' . esc_html__( 'متوسط', 'stocksystem' ) . '</option>
						<option value="2">' . esc_html__( 'ضعیف', 'stocksystem' ) . '</option>
						<option value="1">' . esc_html__( 'خیلی بد', 'stocksystem' ) . '</option>
					</select></div>';
				}

				$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'نظر شما', 'stocksystem' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="6" required placeholder="' . esc_attr__( 'وضعیت دستگاه، سرعت ارسال و تجربهٔ خرید خود را بنویسید…', 'stocksystem' ) . '"></textarea></p>';

				comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
				?>
			</div>
		</div>
	<?php else : ?>
		<p class="woocommerce-verification-required product-reviews__notice"><?php esc_html_e( 'فقط مشتریانی که این کالا را خریده‌اند و وارد حساب خود شده‌اند می‌توانند نظر بدهند.', 'stocksystem' ); ?></p>
	<?php endif; ?>
</div>
