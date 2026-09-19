<?php
/**
 * Product add-ons: a flat-surcharge extra-fields layer, deliberately NOT
 * WooCommerce variations. README: "Add-ons are a separate add-on/extra-
 * fields layer, not variations." No "Product Add-Ons"-type plugin has
 * been chosen yet, so this is a minimal from-scratch implementation:
 * a JSON meta field (same admin-editable-without-code pattern as the
 * category FAQ / brand stats in inc/term-meta-fields.php) plus the cart
 * hooks that actually apply the surcharge.
 *
 * Configurator UI: template-parts/product/configurator.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---- Admin: product data tab ---- */

function stocksystem_addons_product_data_tab( $tabs ) {
	$tabs['stocksystem_addons'] = array(
		'label'    => __( 'افزودنی‌ها', 'stocksystem' ),
		'target'   => 'stocksystem_addons_data',
		'class'    => array( 'show_if_simple', 'show_if_variable' ),
		'priority' => 25,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'stocksystem_addons_product_data_tab' );

function stocksystem_addons_product_data_panel() {
	global $post;
	$value = get_post_meta( $post->ID, '_addons', true );
	?>
	<div id="stocksystem_addons_data" class="panel woocommerce_options_panel">
		<div class="options_group">
			<p style="padding:0 12px; color:#666;">
				<?php esc_html_e( 'آرایهٔ JSON افزودنی‌های اختیاری این محصول (مثل لایسنس ویندوز، کیف و ماوس، تمدید گارانتی). هر ورودی: id، label، price.', 'stocksystem' ); ?>
			</p>
			<p class="form-field">
				<textarea
					name="_addons"
					rows="6"
					style="width:94%; margin:0 3%; font-family:monospace"
					placeholder='[{"id":"win","label":"لایسنس دائم ویندوز ۱۱ پرو","price":1200000}]'
				><?php echo esc_textarea( $value ); ?></textarea>
			</p>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'stocksystem_addons_product_data_panel' );

function stocksystem_save_addons_meta( $post_id ) {
	if ( ! isset( $_POST['_addons'] ) ) {
		return;
	}

	$raw     = wp_unslash( $_POST['_addons'] );
	$decoded = json_decode( $raw, true );

	// JSON_UNESCAPED_UNICODE so Persian labels store as real UTF-8
	// text, not \uXXXX escapes — keeps raw postmeta human-readable and
	// avoids relying on every consumer decoding those escapes back
	// correctly (an admin's Persian addon label going in with default
	// json_encode escaping is exactly the kind of thing worth not
	// having to debug from a hex dump later).
	update_post_meta( $post_id, '_addons', is_array( $decoded ) ? wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE ) : '' );
}
add_action( 'woocommerce_process_product_meta', 'stocksystem_save_addons_meta' );

/**
 * Decoded add-ons for a product: [ [ 'id', 'label', 'price' ], … ].
 */
function stocksystem_get_product_addons( $product_id ) {
	$raw = get_post_meta( $product_id, '_addons', true );
	if ( ! $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return array();
	}

	return array_filter(
		$decoded,
		function ( $addon ) {
			return ! empty( $addon['id'] ) && isset( $addon['price'] );
		}
	);
}

/* ---- Cart: apply the surcharge ---- */

function stocksystem_add_addons_to_cart_item_data( $cart_item_data, $product_id ) {
	if ( empty( $_POST['stocksystem_addons'] ) || ! is_array( $_POST['stocksystem_addons'] ) ) {
		return $cart_item_data;
	}

	// array_column, not wp_list_pluck: wp_list_pluck() can't take a null
	// $field to mean "the whole item" — it plucks $item[''] instead,
	// logs "Undefined array key" and returns nulls, so no chosen add-on
	// ever matched and the surcharge silently never applied.
	$available = array_column( stocksystem_get_product_addons( $product_id ), null, 'id' );
	$chosen    = array();

	foreach ( wp_unslash( $_POST['stocksystem_addons'] ) as $addon_id ) {
		$addon_id = sanitize_key( $addon_id );
		if ( isset( $available[ $addon_id ] ) ) {
			$chosen[] = $available[ $addon_id ];
		}
	}

	if ( ! empty( $chosen ) ) {
		$cart_item_data['stocksystem_addons'] = $chosen;
	}

	return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'stocksystem_add_addons_to_cart_item_data', 10, 2 );

function stocksystem_addons_surcharge( array $addons ) {
	return array_sum( wp_list_pluck( $addons, 'price' ) );
}

function stocksystem_apply_addon_surcharge( $cart ) {
	static $processed = array();

	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		if ( empty( $cart_item['stocksystem_addons'] ) || isset( $processed[ $cart_item_key ] ) ) {
			continue;
		}

		$surcharge = stocksystem_addons_surcharge( $cart_item['stocksystem_addons'] );
		$cart_item['data']->set_price( $cart_item['data']->get_price() + $surcharge );
		$processed[ $cart_item_key ] = true;
	}
}
add_action( 'woocommerce_before_calculate_totals', 'stocksystem_apply_addon_surcharge' );

/* ---- Display: cart, checkout, order ---- */

function stocksystem_addons_cart_item_data( $item_data, $cart_item ) {
	if ( empty( $cart_item['stocksystem_addons'] ) ) {
		return $item_data;
	}

	foreach ( $cart_item['stocksystem_addons'] as $addon ) {
		$item_data[] = array(
			'name'  => $addon['label'],
			'value' => stocksystem_format_number( $addon['price'] ) . ' ' . __( 'تومان', 'stocksystem' ),
		);
	}

	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'stocksystem_addons_cart_item_data', 10, 2 );

function stocksystem_addons_order_line_item( $item, $cart_item_key, $values ) {
	if ( empty( $values['stocksystem_addons'] ) ) {
		return;
	}

	foreach ( $values['stocksystem_addons'] as $addon ) {
		$item->add_meta_data( $addon['label'], stocksystem_format_number( $addon['price'] ) . ' ' . __( 'تومان', 'stocksystem' ) );
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'stocksystem_addons_order_line_item', 10, 3 );
