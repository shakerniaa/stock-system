<?php
/**
 * Two extra order statuses between "processing" and "completed" so the
 * 5-step order-tracking timeline (12 Checkout Flow.dc.html §STEP 04,
 * 16-D order tracking) has something real to show instead of guessing
 * from WooCommerce's default statuses alone. Admin moves an order
 * through these manually from the order edit screen, same as any other
 * status.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_register_order_statuses() {
	register_post_status(
		'wc-qc-packing',
		array(
			'label'                     => _x( 'تست فنی و بسته‌بندی', 'Order status', 'stocksystem' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'تست فنی و بسته‌بندی <span class="count">(%s)</span>', 'تست فنی و بسته‌بندی <span class="count">(%s)</span>', 'stocksystem' ),
		)
	);

	register_post_status(
		'wc-with-courier',
		array(
			'label'                     => _x( 'تحویل به پیک/پست', 'Order status', 'stocksystem' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: number of orders */
			'label_count'               => _n_noop( 'تحویل به پیک/پست <span class="count">(%s)</span>', 'تحویل به پیک/پست <span class="count">(%s)</span>', 'stocksystem' ),
		)
	);
}
add_action( 'init', 'stocksystem_register_order_statuses' );

function stocksystem_add_order_statuses( $order_statuses ) {
	$new_statuses = array();

	foreach ( $order_statuses as $key => $label ) {
		$new_statuses[ $key ] = $label;

		if ( 'wc-processing' === $key ) {
			$new_statuses['wc-qc-packing']   = _x( 'تست فنی و بسته‌بندی', 'Order status', 'stocksystem' );
			$new_statuses['wc-with-courier'] = _x( 'تحویل به پیک/پست', 'Order status', 'stocksystem' );
		}
	}

	return $new_statuses;
}
add_filter( 'wc_order_statuses', 'stocksystem_add_order_statuses' );

/**
 * "SS-48121" style order numbers (12 Checkout Flow.dc.html /
 * 16 Shop Pages.dc.html both show this prefix) — display only, the
 * real order ID underneath is unchanged so lookups stay simple.
 */
add_filter(
	'woocommerce_order_number',
	function ( $order_id, $order ) {
		return 'SS-' . $order->get_id();
	},
	10,
	2
);

/**
 * Timeline step data for template-parts/checkout/order-timeline.php.
 * Each step's `done` state is derived from the order's current status
 * and its status-change history (via order notes' timestamps), so the
 * timeline always reflects the order's real state rather than a guess.
 */
function stocksystem_order_timeline_steps( WC_Order $order ) {
	$status = $order->get_status();

	$status_order = array( 'pending', 'processing', 'qc-packing', 'with-courier', 'completed' );
	$current_index = array_search( $status, $status_order, true );
	if ( false === $current_index ) {
		// on-hold/cancelled/refunded/failed: treat as "order placed" only.
		$current_index = 0;
	}

	$steps = array(
		array( 'key' => 'pending', 'label' => __( 'ثبت سفارش', 'stocksystem' ), 'date' => $order->get_date_created() ),
		array( 'key' => 'processing', 'label' => __( 'تأیید پرداخت', 'stocksystem' ), 'date' => $order->get_date_paid() ),
		array( 'key' => 'qc-packing', 'label' => __( 'تست فنی و بسته‌بندی', 'stocksystem' ), 'date' => null ),
		array( 'key' => 'with-courier', 'label' => __( 'تحویل به پیک/پست', 'stocksystem' ), 'date' => null ),
		array( 'key' => 'completed', 'label' => __( 'تحویل شده', 'stocksystem' ), 'date' => $order->get_date_completed() ),
	);

	foreach ( $steps as $i => &$step ) {
		$step['done'] = $i <= $current_index;
	}
	unset( $step );

	return $steps;
}
