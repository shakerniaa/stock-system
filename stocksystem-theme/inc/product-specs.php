<?php
/**
 * Grouped product specifications — replaces WooCommerce's flat
 * "Additional information" attribute table with panels grouped by
 * subject (پردازنده، حافظه و ذخیره‌سازی، نمایشگر، ...), matching the
 * house "framed panel with hairline grid" idiom already used for the
 * device test-report (inc/product-test-report.php), plus a compact
 * "key specs at a glance" grid for the buy box.
 *
 * Owner's request, verbatim: a competitor site groups CPU/RAM/display
 * info into separate sections instead of one flat list, and shows the
 * 4-6 most important specs near the top of the page (buy box), not
 * only inside a tab further down. Both are implemented here, in the
 * theme's own visual language (hairline grid + teal rule, not a copy
 * of the reference site's layout).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Which display group each attribute slug belongs to, and in what
 * order within that group. `pa_` + slug is the taxonomy name; the
 * legacy `ذخیره‌سازی` and `size`/`color`/`ram` attributes (created
 * before this catalog existed) are included by their real slugs.
 *
 * A slug not listed here still displays — it falls into "مشخصات دیگر"
 * at the end, so adding a brand-new attribute in
 * inc/attributes-bootstrap.php never makes it silently disappear from
 * the product page; it's just ungrouped until this map is updated.
 */
function stocksystem_spec_group_map() {
	return array(
		'cpu'           => array( 'group' => 'cpu', 'order' => 10 ),
		'cpu-gen'       => array( 'group' => 'cpu', 'order' => 20 ),
		'cpu-cores'     => array( 'group' => 'cpu', 'order' => 30 ),
		'cpu-socket'    => array( 'group' => 'cpu', 'order' => 40 ),
		'chipset'       => array( 'group' => 'cpu', 'order' => 50 ),
		'mobo-form'     => array( 'group' => 'cpu', 'order' => 60 ),

		'ram'           => array( 'group' => 'memory', 'order' => 10 ),
		'ram-type'      => array( 'group' => 'memory', 'order' => 20 ),
		'ram-upgrade'   => array( 'group' => 'memory', 'order' => 30 ),
		'ram-slots'     => array( 'group' => 'memory', 'order' => 40 ),
		'ram-freq'      => array( 'group' => 'memory', 'order' => 50 ),
		'ram-cl'        => array( 'group' => 'memory', 'order' => 60 ),
		'ram-form'      => array( 'group' => 'memory', 'order' => 70 ),
		'ذخیرهسازی'     => array( 'group' => 'memory', 'order' => 80 ),
		'storage-type'  => array( 'group' => 'memory', 'order' => 90 ),
		'storage-if'    => array( 'group' => 'memory', 'order' => 100 ),
		'storage-form'  => array( 'group' => 'memory', 'order' => 110 ),

		'gpu'           => array( 'group' => 'gpu', 'order' => 10 ),
		'gpu-type'      => array( 'group' => 'gpu', 'order' => 20 ),
		'vram'          => array( 'group' => 'gpu', 'order' => 30 ),
		'gpu-interface' => array( 'group' => 'gpu', 'order' => 40 ),
		'gpu-power'     => array( 'group' => 'gpu', 'order' => 50 ),
		'gpu-slot'      => array( 'group' => 'gpu', 'order' => 60 ),

		'size'          => array( 'group' => 'display', 'order' => 10 ),
		'resolution'    => array( 'group' => 'display', 'order' => 20 ),
		'panel-type'    => array( 'group' => 'display', 'order' => 30 ),
		'panel-curve'   => array( 'group' => 'display', 'order' => 40 ),
		'refresh-rate'  => array( 'group' => 'display', 'order' => 50 ),
		'response-time' => array( 'group' => 'display', 'order' => 60 ),
		'aspect-ratio'  => array( 'group' => 'display', 'order' => 70 ),
		'touchscreen'   => array( 'group' => 'display', 'order' => 80 ),
		'stand'         => array( 'group' => 'display', 'order' => 90 ),
		'speaker'       => array( 'group' => 'display', 'order' => 100 ),
		'aio-connect'   => array( 'group' => 'display', 'order' => 110 ),

		'weight'        => array( 'group' => 'body', 'order' => 10 ),
		'body-material' => array( 'group' => 'body', 'order' => 20 ),
		'case-material' => array( 'group' => 'body', 'order' => 30 ),
		'form-factor'   => array( 'group' => 'body', 'order' => 40 ),
		'keyboard'      => array( 'group' => 'body', 'order' => 50 ),
		'fingerprint'   => array( 'group' => 'body', 'order' => 60 ),
		'device-type'   => array( 'group' => 'body', 'order' => 70 ),
		'rgb'           => array( 'group' => 'body', 'order' => 80 ),
		'color'         => array( 'group' => 'body', 'order' => 90 ),

		'ports'         => array( 'group' => 'connectivity', 'order' => 10 ),
		'wireless'      => array( 'group' => 'connectivity', 'order' => 20 ),
		'webcam'        => array( 'group' => 'connectivity', 'order' => 30 ),
		'os'            => array( 'group' => 'connectivity', 'order' => 40 ),

		'psu-watt'      => array( 'group' => 'power', 'order' => 10 ),
		'psu-cert'      => array( 'group' => 'power', 'order' => 20 ),
		'psu-modular'   => array( 'group' => 'power', 'order' => 30 ),
		'cooler-type'   => array( 'group' => 'power', 'order' => 40 ),

		'connect-type'  => array( 'group' => 'other', 'order' => 10 ),
		'material'      => array( 'group' => 'other', 'order' => 20 ),
		'switch-type'   => array( 'group' => 'other', 'order' => 30 ),
		'dpi'           => array( 'group' => 'other', 'order' => 40 ),
	);
}

function stocksystem_spec_group_titles() {
	return array(
		'cpu'          => __( 'پردازنده و مادربرد', 'stocksystem' ),
		'memory'       => __( 'حافظه و ذخیره‌سازی', 'stocksystem' ),
		'gpu'          => __( 'کارت گرافیک', 'stocksystem' ),
		'display'      => __( 'نمایشگر', 'stocksystem' ),
		'body'         => __( 'بدنه و طراحی', 'stocksystem' ),
		'connectivity' => __( 'اتصالات و سیستم‌عامل', 'stocksystem' ),
		'power'        => __( 'تغذیه و خنک‌کننده', 'stocksystem' ),
		'other'        => __( 'سایر مشخصات', 'stocksystem' ),
	);
}

/**
 * The slugs eligible for the "key specs at a glance" grid, in priority
 * order — the first ones found on the product win, capped at 6 by the
 * caller. Deliberately short list of the specs a buyer scans first;
 * everything else is still available below in the grouped panels.
 */
function stocksystem_key_spec_priority() {
	return array( 'cpu', 'ram', 'ذخیرهسازی', 'gpu', 'size', 'resolution', 'weight', 'os' );
}

/**
 * Returns this product's attribute values as slug => plain-text value,
 * for both taxonomy attributes (pa_*, including the legacy pa_ram /
 * pa_size / pa_color / pa_ذخیره‌سازی) and custom (non-taxonomy) ones.
 * Values are already the visible, human label — safe to esc_html when
 * printed, not yet-escaped HTML.
 */
function stocksystem_get_product_attribute_values( $product ) {
	$values = array();

	if ( ! $product instanceof WC_Product ) {
		return $values;
	}

	$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );

	foreach ( $attributes as $attribute ) {
		if ( $attribute->is_taxonomy() ) {
			$slug  = preg_replace( '/^pa_/', '', $attribute->get_name() );
			$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
			if ( empty( $terms ) ) {
				continue;
			}
			$values[ $slug ] = implode( '، ', $terms );
		} else {
			$slug    = sanitize_title( $attribute->get_name() );
			$options = $attribute->get_options();
			if ( empty( $options ) ) {
				continue;
			}
			$values[ $slug ] = implode( '، ', $options );
		}
	}

	if ( $product->has_weight() ) {
		$values['weight'] = wc_format_weight( $product->get_weight() );
	}

	return $values;
}

/**
 * Groups this product's attribute values per stocksystem_spec_group_map(),
 * sorted by each group's own order, dropping any group with no values.
 * Returns array of ['key','title','rows' => [['label','value'], ...]].
 */
function stocksystem_get_grouped_product_specs( $product ) {
	$values = stocksystem_get_product_attribute_values( $product );

	if ( empty( $values ) ) {
		return array();
	}

	$map    = stocksystem_spec_group_map();
	$titles = stocksystem_spec_group_titles();
	$groups = array();

	foreach ( $values as $slug => $value ) {
		$meta       = isset( $map[ $slug ] ) ? $map[ $slug ] : array( 'group' => 'other', 'order' => 999 );
		$group_key  = $meta['group'];
		$label      = wc_attribute_label( taxonomy_exists( 'pa_' . $slug ) ? 'pa_' . $slug : $slug );

		if ( ! isset( $groups[ $group_key ] ) ) {
			$groups[ $group_key ] = array(
				'key'   => $group_key,
				'title' => isset( $titles[ $group_key ] ) ? $titles[ $group_key ] : $titles['other'],
				'rows'  => array(),
			);
		}

		$groups[ $group_key ]['rows'][] = array(
			'order' => $meta['order'],
			'label' => $label,
			'value' => $value,
		);
	}

	// Stable group order follows stocksystem_spec_group_titles(); rows
	// within a group sort by their declared priority.
	$ordered_keys = array_keys( $titles );
	uksort(
		$groups,
		function ( $a, $b ) use ( $ordered_keys ) {
			return array_search( $a, $ordered_keys, true ) <=> array_search( $b, $ordered_keys, true );
		}
	);

	foreach ( $groups as &$group ) {
		usort(
			$group['rows'],
			function ( $a, $b ) {
				return $a['order'] <=> $b['order'];
			}
		);
	}
	unset( $group );

	return array_values( $groups );
}

/**
 * Up to $limit key specs for the buy-box glance grid: the first
 * available slugs from stocksystem_key_spec_priority(), skipping any
 * this product hasn't filled in.
 */
function stocksystem_get_key_specs( $product, $limit = 6 ) {
	$values = stocksystem_get_product_attribute_values( $product );

	if ( empty( $values ) ) {
		return array();
	}

	$specs = array();

	foreach ( stocksystem_key_spec_priority() as $slug ) {
		if ( ! isset( $values[ $slug ] ) ) {
			continue;
		}
		$label   = wc_attribute_label( taxonomy_exists( 'pa_' . $slug ) ? 'pa_' . $slug : $slug );
		$specs[] = array(
			'label' => $label,
			'value' => $values[ $slug ],
		);
		if ( count( $specs ) >= $limit ) {
			break;
		}
	}

	return $specs;
}

/**
 * Swaps WooCommerce's flat attribute table for our grouped-panel
 * template. wc_display_product_attributes() is the sole default
 * callback on this hook (wc-template-hooks.php); removing just that
 * one function leaves the "مشخصات فنی" tab's heading/visibility logic
 * (inc/woocommerce.php, additional-information.php) completely intact
 * — only what renders inside the tab changes.
 */
remove_action( 'woocommerce_product_additional_information', 'wc_display_product_attributes', 10 );
add_action( 'woocommerce_product_additional_information', 'stocksystem_render_grouped_specs', 10 );

function stocksystem_render_grouped_specs( $product ) {
	get_template_part( 'template-parts/product/specs', null, array( 'product' => $product ) );
}
