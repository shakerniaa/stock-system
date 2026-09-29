<?php
/**
 * Review requests — the reason the shop has display-ready reviews but no
 * actual reviews: nothing ever asked for one.
 *
 * When an order is marked completed, a request is scheduled for N days
 * later (long enough that the customer has the device in hand and has
 * used it). The email lists what they bought, each with a direct link to
 * that product's review form.
 *
 * Also exposes real reviews as a source for the homepage testimonials
 * section, which until now could only show quotes typed by hand.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const STOCKSYSTEM_REVIEW_REQUEST_HOOK = 'stocksystem_send_review_request';
const STOCKSYSTEM_REVIEW_SENT_META    = '_ss_review_request_sent';

/** One review-request setting. */
function stocksystem_review_opt( $key, $fallback = '' ) {
	$cfg = stocksystem_opt( 'reviews' );

	return isset( $cfg[ $key ] ) && '' !== $cfg[ $key ] ? $cfg[ $key ] : $fallback;
}

/* -------------------------------------------------------------------------
 * Scheduling
 * ---------------------------------------------------------------------- */

/**
 * Schedules a request once an order completes. Uses a single scheduled
 * event rather than sending immediately: asking for a review of a laptop
 * the courier only just handed over gets a worse answer than asking once
 * the customer has actually used it.
 */
function stocksystem_schedule_review_request( $order_id ) {
	if ( ! stocksystem_review_opt( 'enabled' ) ) {
		return;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order || $order->get_meta( STOCKSYSTEM_REVIEW_SENT_META ) ) {
		return;
	}

	// Already queued (an order can be flipped to completed more than once).
	if ( wp_next_scheduled( STOCKSYSTEM_REVIEW_REQUEST_HOOK, array( $order_id ) ) ) {
		return;
	}

	$days = max( 0, (int) stocksystem_review_opt( 'delay_days', 7 ) );

	wp_schedule_single_event( time() + ( $days * DAY_IN_SECONDS ), STOCKSYSTEM_REVIEW_REQUEST_HOOK, array( $order_id ) );
}
add_action( 'woocommerce_order_status_completed', 'stocksystem_schedule_review_request' );

/** Products from an order that are still worth asking about. */
function stocksystem_review_request_products( WC_Order $order ) {
	$products = array();

	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();

		if ( ! $product ) {
			continue;
		}

		// A variation's reviews live on its parent.
		$id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

		if ( isset( $products[ $id ] ) ) {
			continue;
		}

		$parent = wc_get_product( $id );

		if ( ! $parent || 'publish' !== $parent->get_status() || ! $parent->get_reviews_allowed() ) {
			continue;
		}

		// Don't ask again for something this customer already reviewed.
		if ( stocksystem_customer_has_reviewed( $id, $order->get_billing_email() ) ) {
			continue;
		}

		$products[ $id ] = $parent;
	}

	return $products;
}

/** Whether this email address already left an approved review on a product. */
function stocksystem_customer_has_reviewed( $product_id, $email ) {
	if ( ! $email ) {
		return false;
	}

	$existing = get_comments(
		array(
			'post_id'      => $product_id,
			'author_email' => $email,
			'type'         => 'review',
			'count'        => true,
			'status'       => 'all',
		)
	);

	return $existing > 0;
}

/**
 * Sends the request. Kept separate from scheduling so the admin's
 * "send now" button can reuse it.
 *
 * @return true|WP_Error
 */
function stocksystem_send_review_request( $order_id ) {
	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return new WP_Error( 'no_order', __( 'سفارش پیدا نشد.', 'stocksystem' ) );
	}

	$email = $order->get_billing_email();

	if ( ! is_email( $email ) ) {
		return new WP_Error( 'no_email', __( 'این سفارش ایمیل ندارد.', 'stocksystem' ) );
	}

	$products = stocksystem_review_request_products( $order );

	if ( empty( $products ) ) {
		return new WP_Error( 'nothing_to_ask', __( 'محصولی برای درخواست نظر باقی نمانده (یا قبلاً نظر داده شده).', 'stocksystem' ) );
	}

	$name = $order->get_billing_first_name() ? $order->get_billing_first_name() : __( 'مشتری عزیز', 'stocksystem' );

	$intro = stocksystem_review_opt(
		'body',
		__( 'از خرید شما ممنونیم. اگر چند دقیقه وقت دارید، تجربه‌تان از این دستگاه را بنویسید — نظر شما به خریدارهای بعدی کمک می‌کند تصمیم بهتری بگیرند.', 'stocksystem' )
	);

	$lines = array(
		sprintf( /* translators: %s: customer first name */ __( '%s سلام،', 'stocksystem' ), $name ),
		'',
		$intro,
		'',
	);

	foreach ( $products as $product ) {
		$lines[] = '• ' . $product->get_name();
		// #review_form lands on the form itself rather than the top of a
		// long product page.
		$lines[] = '  ' . $product->get_permalink() . '#review_form';
		$lines[] = '';
	}

	$lines[] = sprintf(
		/* translators: %s: shop name */
		__( 'با احترام، %s', 'stocksystem' ),
		get_bloginfo( 'name' )
	);

	$subject = stocksystem_review_opt(
		'subject',
		sprintf( /* translators: %s: shop name */ __( 'تجربه‌تان از خرید را با ما در میان بگذارید — %s', 'stocksystem' ), get_bloginfo( 'name' ) )
	);

	$sent = wp_mail( $email, $subject, implode( "\n", $lines ) );

	if ( ! $sent ) {
		return new WP_Error( 'mail_failed', __( 'ارسال ایمیل انجام نشد.', 'stocksystem' ) );
	}

	$order->update_meta_data( STOCKSYSTEM_REVIEW_SENT_META, current_time( 'mysql' ) );
	$order->save();

	$order->add_order_note( __( 'درخواست ثبت نظر برای مشتری ارسال شد.', 'stocksystem' ) );

	return true;
}
add_action( STOCKSYSTEM_REVIEW_REQUEST_HOOK, 'stocksystem_send_review_request' );

/* -------------------------------------------------------------------------
 * "Send now" from the order screen
 * ---------------------------------------------------------------------- */

function stocksystem_review_request_order_action( $actions ) {
	global $theorder;

	if ( $theorder && ! $theorder->get_meta( STOCKSYSTEM_REVIEW_SENT_META ) ) {
		$actions['stocksystem_review_request'] = __( 'ارسال درخواست ثبت نظر', 'stocksystem' );
	}

	return $actions;
}
add_filter( 'woocommerce_order_actions', 'stocksystem_review_request_order_action' );

function stocksystem_review_request_order_action_run( $order ) {
	$result = stocksystem_send_review_request( $order->get_id() );

	if ( is_wp_error( $result ) ) {
		$order->add_order_note(
			sprintf(
				/* translators: %s: error message */
				__( 'درخواست ثبت نظر ارسال نشد: %s', 'stocksystem' ),
				$result->get_error_message()
			)
		);
	}
}
add_action( 'woocommerce_order_action_stocksystem_review_request', 'stocksystem_review_request_order_action_run' );

/* -------------------------------------------------------------------------
 * Real reviews for the homepage testimonials section
 * ---------------------------------------------------------------------- */

/**
 * The best recent approved product reviews, shaped like the manual
 * testimonial rows so the template can render either source.
 *
 * Only 4-and-5-star reviews with enough text to be worth quoting: a bare
 * «خوب بود» on the homepage is worse than nothing.
 */
function stocksystem_recent_reviews( $limit = 6, $min_rating = 4, $min_chars = 40 ) {
	$comments = get_comments(
		array(
			'type'      => 'review',
			'status'    => 'approve',
			'post_type' => 'product',
			'number'    => (int) $limit * 4,
			'orderby'   => 'comment_date_gmt',
			'order'     => 'DESC',
		)
	);

	$items = array();

	foreach ( $comments as $comment ) {
		$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
		$text   = trim( (string) $comment->comment_content );

		if ( $rating < $min_rating || mb_strlen( $text ) < $min_chars ) {
			continue;
		}

		$items[] = array(
			'name'  => $comment->comment_author,
			'role'  => get_the_title( $comment->comment_post_ID ),
			'text'  => $text,
			'stars' => $rating,
			'url'   => get_permalink( $comment->comment_post_ID ),
		);

		if ( count( $items ) >= $limit ) {
			break;
		}
	}

	return $items;
}

/**
 * What the testimonials section should render: real reviews when that
 * source is selected and any qualify, otherwise the hand-written rows.
 * Falling back matters — a shop with no reviews yet would otherwise show
 * an empty section the day it switches the setting on.
 */
function stocksystem_testimonial_items() {
	$cfg = stocksystem_opt( 'homex' );

	if ( 'reviews' === ( isset( $cfg['testi_source'] ) ? $cfg['testi_source'] : 'manual' ) ) {
		$real = stocksystem_recent_reviews();

		if ( ! empty( $real ) ) {
			return $real;
		}
	}

	return ! empty( $cfg['testi_items'] ) ? $cfg['testi_items'] : array();
}

/* -------------------------------------------------------------------------
 * Admin page
 * ---------------------------------------------------------------------- */

function stocksystem_reviews_admin_page( $pages ) {
	$pages['reviews'] = array(
		'group'    => 'محتوای صفحه‌ها',
		'title'    => __( 'درخواست نظر از مشتری', 'stocksystem' ),
		'menu'     => __( 'درخواست نظر', 'stocksystem' ),
		'intro'    => __( 'وقتی سفارشی «تکمیل‌شده» می‌شود، بعد از چند روز یک ایمیل برای مشتری می‌رود و از او می‌خواهد نظرش را دربارهٔ دستگاهی که خریده بنویسد. برای هر سفارش فقط یک‌بار ارسال می‌شود و محصولاتی که مشتری قبلاً نظر داده حذف می‌شوند. از صفحهٔ هر سفارش هم می‌توانی دستی «ارسال درخواست ثبت نظر» را بزنی.', 'stocksystem' ),
		'view'     => '/',
		'sections' => array(
			array(
				'title'  => __( 'ایمیل درخواست نظر', 'stocksystem' ),
				'fields' => array(
					'enabled'    => array( 'type' => 'checkbox', 'label' => __( 'ارسال خودکار فعال باشد', 'stocksystem' ), 'default' => 1 ),
					'delay_days' => array( 'type' => 'number', 'label' => __( 'چند روز بعد از تکمیل سفارش؟', 'stocksystem' ), 'default' => 7, 'min' => 0, 'max' => 60 ),
					'subject'    => array( 'type' => 'text', 'label' => __( 'موضوع ایمیل', 'stocksystem' ), 'default' => '', 'allow_empty' => true, 'wide' => true, 'help' => __( 'خالی = متن پیش‌فرض', 'stocksystem' ) ),
					'body'       => array( 'type' => 'textarea', 'label' => __( 'متن ایمیل (قبل از فهرست محصولات)', 'stocksystem' ), 'default' => '', 'allow_empty' => true ),
				),
			),
		),
	);

	return $pages;
}
add_filter( 'stocksystem_admin_pages', 'stocksystem_reviews_admin_page' );

/** Adds the "real reviews" source option to the testimonials section. */
function stocksystem_reviews_testimonial_source( $pages ) {
	if ( ! isset( $pages['homex']['sections'] ) ) {
		return $pages;
	}

	foreach ( $pages['homex']['sections'] as $i => $section ) {
		if ( ! isset( $section['fields']['testi_items'] ) ) {
			continue;
		}

		$fields = array();

		// Placed before the manual rows so the choice reads first.
		foreach ( $section['fields'] as $key => $field ) {
			if ( 'testi_items' === $key ) {
				$fields['testi_source'] = array(
					'type'    => 'select',
					'label'   => __( 'منبع نظرها', 'stocksystem' ),
					'default' => 'manual',
					'options' => array(
						'manual'  => __( 'نظرهایی که خودم اینجا می‌نویسم', 'stocksystem' ),
						'reviews' => __( 'نظرهای واقعی ثبت‌شده روی محصولات (۴ و ۵ ستاره)', 'stocksystem' ),
					),
					'wide'    => true,
				);
			}
			$fields[ $key ] = $field;
		}

		$pages['homex']['sections'][ $i ]['fields'] = $fields;
		break;
	}

	return $pages;
}
// Priority 20: must run after the homex page itself is registered.
add_filter( 'stocksystem_admin_pages', 'stocksystem_reviews_testimonial_source', 20 );
