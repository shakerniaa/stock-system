<?php
/**
 * Device test report — battery health %, runtime hours, body condition,
 * dead pixels, test date. README: "Device test report — repeatable meta
 * group rendered on the product page and downloadable from the order
 * detail." Implemented as plain product meta fields (not a true
 * repeater — no such plugin decision made yet); battery_health and
 * runtime_hours reuse the exact meta keys inc/archive-filters.php
 * already queries as search facets.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_test_report_product_data_tab( $tabs ) {
	$tabs['stocksystem_test_report'] = array(
		'label'    => __( 'برگهٔ تست دستگاه', 'stocksystem' ),
		'target'   => 'stocksystem_test_report_data',
		'class'    => array( 'show_if_simple', 'show_if_variable' ),
		'priority' => 26,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'stocksystem_test_report_product_data_tab' );

function stocksystem_test_report_product_data_panel() {
	global $post;
	?>
	<div id="stocksystem_test_report_data" class="panel woocommerce_options_panel">
		<div class="options_group">
			<?php
			woocommerce_wp_text_input(
				array(
					'id'          => '_battery_health',
					'label'       => __( 'سلامت باتری (٪)', 'stocksystem' ),
					'type'        => 'number',
					'custom_attributes' => array( 'min' => '0', 'max' => '100' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'    => '_runtime_hours',
					'label' => __( 'ساعت کارکرد', 'stocksystem' ),
					'type'  => 'number',
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'    => '_body_condition',
					'label' => __( 'وضعیت بدنه', 'stocksystem' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'          => '_dead_pixels',
					'label'       => __( 'پیکسل سوخته', 'stocksystem' ),
					'placeholder' => __( 'ندارد', 'stocksystem' ),
				)
			);
			woocommerce_wp_text_input(
				array(
					'id'                => '_test_date',
					'label'             => __( 'تاریخ تست', 'stocksystem' ),
					'type'              => 'date',
					'custom_attributes' => array(),
				)
			);
			?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'stocksystem_test_report_product_data_panel' );

function stocksystem_save_test_report_meta( $post_id ) {
	$fields = array( '_battery_health', '_runtime_hours', '_body_condition', '_dead_pixels', '_test_date' );

	foreach ( $fields as $field ) {
		if ( isset( $_POST[ $field ] ) ) {
			update_post_meta( $post_id, $field, sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) );
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'stocksystem_save_test_report_meta' );

/**
 * The 4 test-report stats for display, or null if none have been filled
 * in yet (the panel is skipped rather than shown empty).
 */
function stocksystem_get_test_report( $product_id ) {
	$battery = get_post_meta( $product_id, '_battery_health', true );
	$runtime = get_post_meta( $product_id, '_runtime_hours', true );
	$body    = get_post_meta( $product_id, '_body_condition', true );
	$pixels  = get_post_meta( $product_id, '_dead_pixels', true );
	$date    = get_post_meta( $product_id, '_test_date', true );

	if ( '' === $battery && '' === $runtime && '' === $body && '' === $pixels ) {
		return null;
	}

	return array(
		'battery' => $battery,
		'runtime' => $runtime,
		'body'    => $body,
		'pixels'  => $pixels,
		'date'    => $date,
	);
}
