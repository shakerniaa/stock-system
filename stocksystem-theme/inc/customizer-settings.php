<?php
/**
 * Theme Customizer — business numbers the client edits without touching
 * code. Defaults below match DECISIONS-v1.1.md, NOT the placeholder copy
 * baked into the .dc.html design files (those say 18/6/3-month warranty
 * and a Tehran address — both wrong, see decisions #5 and #7).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single source of truth for each setting's key/default/label — used to
 * both register the Customizer control and, critically, as the $default
 * argument to get_theme_mod() in stocksystem_business() below. Passing
 * that default is not optional: a Customizer 'default' only applies
 * inside the Customizer preview itself, never on the real front end.
 * (Missing that argument here previously meant every one of these
 * defaults silently rendered as an empty string on the live site.)
 */
function stocksystem_business_settings() {
	return array(
		'warranty_text'           => array(
			'mod'     => 'stocksystem_warranty_text',
			// Decision #5: 1-month hardware warranty (design files say 18/6/3 — wrong).
			'default' => 'گارانتی ۱ ماهه سخت‌افزار',
			'label'   => __( 'متن گارانتی (فوتر، صفحه محصول، قوانین)', 'stocksystem' ),
		),
		'phone'                   => array(
			'mod'     => 'stocksystem_phone',
			// Decision #7: real phone/address, replacing Tehran placeholder copy.
			'default' => '09034535025',
			'label'   => __( 'شماره تماس', 'stocksystem' ),
		),
		'address'                 => array(
			'mod'     => 'stocksystem_address',
			'default' => 'نیشابور، بین بعثت ۳۰ و ۳۲',
			'label'   => __( 'آدرس فروشگاه', 'stocksystem' ),
		),
		'free_shipping_threshold' => array(
			'mod'     => 'stocksystem_free_shipping_threshold',
			// Open per DECISIONS-v1.1.md — client confirms in content phase.
			'default' => '',
			'label'   => __( 'آستانه ارسال رایگان (تومان — کارفرما بعداً مشخص می‌کند)', 'stocksystem' ),
		),
		'store_hours'             => array(
			'mod'     => 'stocksystem_store_hours',
			'default' => '',
			'label'   => __( 'ساعات کاری فروشگاه', 'stocksystem' ),
		),
	);
}

function stocksystem_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'stocksystem_business',
		array(
			'title'    => __( 'اطلاعات کسب‌وکار', 'stocksystem' ),
			'priority' => 30,
		)
	);

	foreach ( stocksystem_business_settings() as $setting ) {
		$wp_customize->add_setting(
			$setting['mod'],
			array(
				'default'           => $setting['default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$setting['mod'],
			array(
				'label'   => $setting['label'],
				'section' => 'stocksystem_business',
				'type'    => 'text',
			)
		);
	}
}
add_action( 'customize_register', 'stocksystem_customize_register' );

/**
 * Convenience getter — templates call stocksystem_business( 'phone' )
 * instead of get_theme_mod() so the option-name/default pairing lives in
 * one place.
 */
function stocksystem_business( $key ) {
	$settings = stocksystem_business_settings();

	if ( ! isset( $settings[ $key ] ) ) {
		return '';
	}

	return get_theme_mod( $settings[ $key ]['mod'], $settings[ $key ]['default'] );
}
