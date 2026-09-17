<?php
/**
 * Data helpers for the RAM/storage configurator (15-A). The actual
 * price/stock/SKU numbers always come from WooCommerce's own variation
 * data — these functions only pre-compute per-option availability and
 * price deltas server-side so the tile UI can render its states without
 * any client-side pricing logic.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * For one variation attribute (e.g. "attribute_pa_ram"), returns each
 * possible option with: label, slug, whether ANY variation offering it
 * is in stock, and its price delta versus the product's cheapest
 * in-stock variation overall. README's configurator rule ("out-of-stock
 * options render struck-through … not clickable") is per-option, not
 * per full attribute combination, so "available" here means "at least
 * one in-stock variation uses this value" — matching the flat RAM/DISK
 * availability model in 15-A's own behavior spec.
 */
function stocksystem_configurator_attribute_options( WC_Product_Variable $product, $attribute_name ) {
	$variations = $product->get_available_variations();
	if ( empty( $variations ) ) {
		return array();
	}

	$key = 'attribute_' . sanitize_title( $attribute_name );

	$cheapest_in_stock = null;
	foreach ( $variations as $variation ) {
		if ( $variation['is_in_stock'] && ( null === $cheapest_in_stock || $variation['display_price'] < $cheapest_in_stock ) ) {
			$cheapest_in_stock = $variation['display_price'];
		}
	}
	if ( null === $cheapest_in_stock ) {
		$cheapest_in_stock = min( wp_list_pluck( $variations, 'display_price' ) );
	}

	$options = array();
	$attribute_object = $product->get_attribute_object( $attribute_name );
	$is_taxonomy = $attribute_object && $attribute_object->is_taxonomy();

	foreach ( $variations as $variation ) {
		$value = isset( $variation['attributes'][ $key ] ) ? $variation['attributes'][ $key ] : '';

		if ( ! isset( $options[ $value ] ) ) {
			$label = $value;
			if ( $is_taxonomy ) {
				$term = get_term_by( 'slug', $value, $attribute_object->get_taxonomy() );
				$label = $term ? $term->name : $value;
			}
			$options[ $value ] = array(
				'value'     => $value,
				'label'     => $label,
				'available' => false,
				'min_price' => null,
			);
		}

		if ( $variation['is_in_stock'] ) {
			$options[ $value ]['available'] = true;
			if ( null === $options[ $value ]['min_price'] || $variation['display_price'] < $options[ $value ]['min_price'] ) {
				$options[ $value ]['min_price'] = $variation['display_price'];
			}
		}
	}

	foreach ( $options as &$option ) {
		$option['delta'] = null !== $option['min_price'] ? $option['min_price'] - $cheapest_in_stock : 0;
	}
	unset( $option );

	return array_values( $options );
}

/**
 * Whether this variable product's axes are specifically RAM + storage —
 * the only case where the "workshop upgraded" stock/lead-time copy
 * (README's configurator behavior spec) makes sense. A color- or size-
 * variant product (15-B states 7/8) still gets the tile UI for a better
 * picker than bare <select>s, just without this store-specific copy.
 */
function stocksystem_is_hardware_configurator_product( WC_Product_Variable $product ) {
	$has_ram     = false;
	$has_storage = false;

	foreach ( array_keys( $product->get_variation_attributes() ) as $attribute_name ) {
		$slug = sanitize_title( $attribute_name );
		if ( false !== strpos( $slug, 'ram' ) ) {
			$has_ram = true;
		}
		if ( false !== strpos( $slug, 'storage' ) || false !== strpos( $slug, 'disk' ) || false !== strpos( $slug, 'rom' ) || false !== strpos( $slug, 'ssd' ) || false !== strpos( $slug, 'nvme' ) ) {
			$has_storage = true;
		}
	}

	return $has_ram && $has_storage;
}

/**
 * Whether the currently-selected combination counts as "workshop
 * upgraded" — README: "if either axis is above its base value, the item
 * is treated as workshop-upgraded." A variation is upgraded when its
 * price is above the product's cheapest in-stock variation.
 */
function stocksystem_configurator_cheapest_price( WC_Product_Variable $product ) {
	$variations = $product->get_available_variations();
	$in_stock_prices = wp_list_pluck( array_filter( $variations, fn( $v ) => $v['is_in_stock'] ), 'display_price' );

	return ! empty( $in_stock_prices ) ? min( $in_stock_prices ) : ( ! empty( $variations ) ? min( wp_list_pluck( $variations, 'display_price' ) ) : 0 );
}
