<?php
/**
 * Real vs. legal (company) invoice toggle — 12 Checkout Flow.dc.html
 * §STEP 01 "اطلاعات فاکتور". billing_company already exists in core;
 * this adds the economic code field for legal invoices and a radio to
 * switch between the two (JS toggles field visibility, see
 * assets/js/checkout.js).
 *
 * TODO: "گیرندهٔ سفارش شخص دیگری است" (recipient is someone else) from
 * the same design panel isn't built yet — out of scope for this pass.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_add_checkout_fields( $fields ) {
	$fields['billing']['billing_invoice_type'] = array(
		'type'     => 'radio',
		'label'    => __( 'نوع فاکتور', 'stocksystem' ),
		'options'  => array(
			'individual' => __( 'فاکتور حقیقی', 'stocksystem' ),
			'company'    => __( 'فاکتور حقوقی (شرکتی)', 'stocksystem' ),
		),
		'default'  => 'individual',
		// Always has a default value, so "required" is realistic here —
		// also avoids WooCommerce appending "(اختیاری)" to every option
		// label, which it does for any non-required field.
		'required' => true,
		'class'    => array( 'form-row-wide', 'invoice-type-field' ),
		'priority' => 5,
	);

	if ( isset( $fields['billing']['billing_company'] ) ) {
		$fields['billing']['billing_company']['label']    = __( 'نام شرکت', 'stocksystem' );
		$fields['billing']['billing_company']['class'][]  = 'invoice-type-company-only';
		$fields['billing']['billing_company']['priority'] = 25;
	}

	$fields['billing']['billing_economic_code'] = array(
		'type'     => 'text',
		'label'    => __( 'کد اقتصادی', 'stocksystem' ),
		'required' => false,
		'class'    => array( 'form-row-wide', 'invoice-type-company-only' ),
		'priority' => 26,
	);

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'stocksystem_add_checkout_fields' );

function stocksystem_validate_checkout_fields( $data, $errors ) {
	if ( 'company' === $data['billing_invoice_type'] && empty( $data['billing_company'] ) ) {
		$errors->add( 'validation', __( 'برای فاکتور حقوقی، نام شرکت الزامی است.', 'stocksystem' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'stocksystem_validate_checkout_fields', 10, 2 );

function stocksystem_save_economic_code_meta( $order, $data ) {
	if ( ! empty( $data['billing_economic_code'] ) ) {
		$order->update_meta_data( '_billing_economic_code', sanitize_text_field( $data['billing_economic_code'] ) );
	}
}
add_action( 'woocommerce_checkout_create_order', 'stocksystem_save_economic_code_meta', 10, 2 );
