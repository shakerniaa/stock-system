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
	// WC_Product has no get_attribute_object() — look it up from the
	// product's own attributes array. Matched on get_name() rather than
	// on the array key: get_variation_attributes() keys $attribute_name
	// by the RAW attribute name ("pa_ذخیره‌سازی"), but the attributes
	// array is keyed by WooCommerce's stored key, which is
	// percent-encoded for a non-ASCII name ("pa_%d8%b0…"). Keying off
	// the array key therefore missed the storage axis entirely, leaving
	// $is_taxonomy false and rendering raw term slugs ("256gb-ssd") in
	// the tiles instead of labels ("256GB SSD"). RAM was unaffected only
	// because "pa_ram" is already ASCII.
	$attribute_object = null;
	foreach ( $product->get_attributes() as $candidate ) {
		if ( $candidate->get_name() === $attribute_name ) {
			$attribute_object = $candidate;
			break;
		}
	}
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

	// Options are discovered by walking the variations, so their order
	// is whatever order WooCommerce happened to return those in —
	// "۳۲GB، ۱۶GB، ۸GB" rather than ascending. Re-order them to the
	// attribute's own term order (which inc/attributes-bootstrap.php
	// pins via wc_set_term_order), so the tiles, the spec table and the
	// archive filters all present capacities in the same sequence on
	// every product.
	if ( $is_taxonomy ) {
		$ordered_slugs = wc_get_product_terms(
			$product->get_id(),
			$attribute_object->get_taxonomy(),
			array( 'fields' => 'slugs' )
		);

		if ( ! empty( $ordered_slugs ) ) {
			$sorted = array();
			foreach ( $ordered_slugs as $slug ) {
				if ( isset( $options[ $slug ] ) ) {
					$sorted[ $slug ] = $options[ $slug ];
					unset( $options[ $slug ] );
				}
			}
			// Anything the term list didn't cover keeps its old position
			// at the end rather than being dropped.
			$options = $sorted + $options;
		}
	}

	// Then by price, cheapest first ("قیمت پایه" leads, upgrades follow);
	// unavailable options go last. Equal prices keep the term order above
	// (the position index makes the sort stable on every PHP version).
	$options  = array_values( $options );
	$position = 0;
	foreach ( $options as &$option ) {
		$option['position'] = $position++;
	}
	unset( $option );

	usort(
		$options,
		function ( $a, $b ) {
			if ( $a['available'] !== $b['available'] ) {
				return $a['available'] ? -1 : 1;
			}
			if ( $a['available'] && (float) $a['min_price'] !== (float) $b['min_price'] ) {
				return (float) $a['min_price'] < (float) $b['min_price'] ? -1 : 1;
			}
			return $a['position'] - $b['position'];
		}
	);

	return $options;
}

/**
 * The option each attribute starts on, keyed by attribute name. Order of
 * preference: a value in the URL (?attribute_pa_ram=…, e.g. a shared
 * link), the product's own default form values (WooCommerce product edit
 * → Variations → "Default Form Values"), and — when the store set none —
 * the cheapest in-stock variation, so the page opens with a real price and
 * an active add-to-cart button instead of an empty configurator.
 *
 * Values are what the native <select>s hold (term slugs for global
 * attributes); '' means "nothing selected".
 */
function stocksystem_configurator_selected_values( WC_Product_Variable $product ) {
	$attributes = $product->get_variation_attributes();
	$selected   = array();
	$any_set    = false;

	foreach ( $attributes as $attribute_name => $values ) {
		$key = 'attribute_' . sanitize_title( $attribute_name );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only preselection, same as WooCommerce's own dropdown.
		$value = isset( $_REQUEST[ $key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $key ] ) ) : $product->get_variation_default_attribute( $attribute_name );

		$selected[ $attribute_name ] = (string) $value;
		if ( '' !== (string) $value ) {
			$any_set = true;
		}
	}

	if ( $any_set ) {
		return $selected;
	}

	$cheapest = null;
	foreach ( $product->get_available_variations() as $variation ) {
		if ( empty( $variation['is_in_stock'] ) || empty( $variation['is_purchasable'] ) ) {
			continue;
		}
		if ( null === $cheapest || $variation['display_price'] < $cheapest['display_price'] ) {
			$cheapest = $variation;
		}
	}

	if ( null === $cheapest ) {
		return $selected;
	}

	foreach ( $attributes as $attribute_name => $values ) {
		$key   = 'attribute_' . sanitize_title( $attribute_name );
		$value = isset( $cheapest['attributes'][ $key ] ) ? (string) $cheapest['attributes'][ $key ] : '';

		// A variation set to "any" for this attribute: start on its first value.
		if ( '' === $value && ! empty( $values ) ) {
			$value = (string) reset( $values );
		}

		$selected[ $attribute_name ] = $value;
	}

	return $selected;
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
