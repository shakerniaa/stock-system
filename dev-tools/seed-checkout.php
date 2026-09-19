<?php
/**
 * Dev checkout seed: one Iran shipping zone (in-store pickup, post/Tipax
 * flat rate, free shipping over a threshold), "pay in store" for pickup
 * orders, and a MOCK online gateway so the whole cart -> checkout -> thank
 * you flow can be exercised locally. NEVER ship the mock gateway: the real
 * store needs the client's Iranian gateway plugin (Zarinpal etc.).
 *
 * Amounts are in Toman (the store currency is IRR displayed as «تومان»).
 * The free-shipping threshold and the post price are placeholders — the
 * client hasn't decided them yet (DECISIONS-v1.1.md, "still open").
 *
 * Usage (from the WordPress site root):
 *   wp eval-file /path/to/repo/dev-tools/seed-checkout.php
 *
 * Safe to re-run. Gotcha: under `wp eval-file`, top-level variables are NOT
 * visible to functions via `global`, so everything is passed as parameters.
 */

function ss_seed_shipping_zone() {
	$existing = WC_Data_Store::load( 'shipping-zone' )->get_zones();
	foreach ( $existing as $row ) {
		if ( 'ایران' === $row->zone_name ) {
			return 'shipping zone already exists';
		}
	}

	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'ایران' );
	$zone->set_zone_order( 0 );
	$zone->add_location( 'IR', 'country' );
	$zone->save();

	$methods = array(
		'local_pickup'  => array(
			'title'      => 'تحویل حضوری در فروشگاه نیشابور',
			'tax_status' => 'none',
			'cost'       => '0',
		),
		'flat_rate'     => array(
			'title'      => 'ارسال با پست / تیپاکس',
			'tax_status' => 'none',
			'cost'       => '95000',
		),
		'free_shipping' => array(
			'title'    => 'ارسال رایگان',
			'requires' => 'min_amount',
			// Placeholder threshold, to be set by the client.
			'min_amount' => '50000000',
		),
	);

	foreach ( $methods as $method_id => $settings ) {
		$instance_id = $zone->add_shipping_method( $method_id );
		update_option( 'woocommerce_' . $method_id . '_' . $instance_id . '_settings', $settings );
	}

	return 'created the Iran shipping zone (pickup, post/Tipax, free over threshold)';
}

function ss_seed_gateways( $mu_source ) {
	update_option(
		'woocommerce_cod_settings',
		array(
			'enabled'            => 'yes',
			'title'              => 'پرداخت در فروشگاه',
			'description'        => 'مبلغ سفارش را هنگام تحویل حضوری در فروشگاه نیشابور پرداخت می‌کنید.',
			'instructions'       => 'سفارش شما آماده است؛ برای تحویل و پرداخت به فروشگاه نیشابور، بین بعثت ۳۰ و ۳۲ مراجعه کنید.',
			'enable_for_methods' => array( 'local_pickup' ),
			'enable_for_virtual' => 'yes',
		)
	);

	update_option(
		'woocommerce_stocksystem_dev_settings',
		array(
			'enabled' => 'yes',
		)
	);

	$target = WPMU_PLUGIN_DIR . '/stocksystem-dev-gateway.php';
	wp_mkdir_p( WPMU_PLUGIN_DIR );
	copy( $mu_source, $target );

	return 'enabled "pay in store" (pickup only) and installed the mock online gateway mu-plugin';
}

function ss_seed_store_defaults() {
	// LOCAL SQLITE ONLY: WooCommerce reserves stock with MySQL-specific SQL
	// (INSERT ... SELECT ... FOR UPDATE / ON DUPLICATE KEY) that the SQLite
	// integration can't run, so every checkout fails with "not enough
	// stock". Disabling the hold sidesteps it; leave the MySQL default (60)
	// on the real site.
	update_option( 'woocommerce_hold_stock_minutes', '0' );

	update_option( 'woocommerce_default_country', 'IR' );
	update_option( 'woocommerce_ship_to_countries', 'specific' );
	update_option( 'woocommerce_specific_ship_to_countries', array( 'IR' ) );
	update_option( 'woocommerce_specific_allowed_countries', array( 'IR' ) );
	update_option( 'woocommerce_allowed_countries', 'specific' );

	return 'store sells to / ships to Iran only; stock hold disabled (SQLite)';
}

$repo_root = dirname( __FILE__ );

echo ss_seed_shipping_zone() . "\n";
echo ss_seed_gateways( $repo_root . '/mu-plugins/stocksystem-dev-gateway.php' ) . "\n";
echo ss_seed_store_defaults() . "\n";
