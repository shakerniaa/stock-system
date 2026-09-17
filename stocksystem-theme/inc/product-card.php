<?php
/**
 * Product card: state detection and badge logic shared by every archive,
 * the mega menu tile, mini-cart, and related-products blocks.
 * Source: 15 Product States.dc.html §15-B (14 card states), §15-C (edge
 * case buy boxes).
 *
 * Decision #1 correction: the design fills "افزودن به سبد" / "خرید" /
 * "همین حالا بخرید" with orange (#F58220) and white text — DECISIONS-v1.1.md
 * #1 overrules this: primary purchase actions are teal with white text,
 * always; orange never carries white text. Applied throughout this file
 * and product-card.css.
 * Decision #9 correction: "in stock" badge is green (--c-status-success),
 * not the teal the design uses — teal is reserved for brand/CTA only.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Low-stock threshold for a product — WooCommerce 6.2+ per-product/global
 * setting when available, otherwise the option we seed to 2 on activation.
 * README: "'Low stock' is not a separate WooCommerce state — it is the
 * low-stock threshold."
 */
function stocksystem_low_stock_threshold( $product ) {
	if ( function_exists( 'wc_get_low_stock_amount' ) ) {
		$amount = wc_get_low_stock_amount( $product );
		if ( $amount ) {
			return (int) $amount;
		}
	}

	return (int) get_option( 'woocommerce_notify_low_stock_amount', 2 );
}

/**
 * Up to two pill badges for a card, keyed off which buy-box variant is
 * showing — the design shows badges only on the plain-stock and variable
 * (non-range) cards; color/size/model/price-range cards carry none.
 */
function stocksystem_product_badges( WC_Product $product, $variant ) {
	switch ( $variant ) {
		case 'simple':
		case 'out-of-stock':
			return stocksystem_simple_product_badges( $product );

		case 'quote':
			return array(
				array(
					'label' => __( 'سفارش سازمانی', 'stocksystem' ),
					'class' => 'badge--neutral',
				),
			);

		case 'variable':
			if ( $product instanceof WC_Product_Variable && ! stocksystem_variable_has_price_range( $product ) ) {
				return array(
					array(
						'label' => sprintf(
							/* translators: %s: number of variations, Persian digits */
							__( '%s پیکربندی', 'stocksystem' ),
							stocksystem_to_persian_digits( count( $product->get_children() ) )
						),
						'class' => 'badge--config',
					),
				);
			}
			return array();

		default:
			return array();
	}
}

/**
 * Stock-status + discount + marketing-label badges, right-to-left
 * priority, capped at 2 (README "14 product states" rule). Used for the
 * plain-stock and out-of-stock buy-box variants only.
 */
function stocksystem_simple_product_badges( WC_Product $product ) {
	$badges = array();

	if ( ! $product->is_in_stock() ) {
		$badges[] = array(
			'label' => __( 'ناموجود', 'stocksystem' ),
			'class' => 'badge--error',
		);
	} elseif ( $product->managing_stock() && null !== $product->get_stock_quantity()
		&& $product->get_stock_quantity() <= stocksystem_low_stock_threshold( $product ) ) {
		$badges[] = array(
			'label' => sprintf(
				/* translators: %s: remaining stock count, Persian digits */
				__( 'تنها %s عدد', 'stocksystem' ),
				stocksystem_to_persian_digits( $product->get_stock_quantity() )
			),
			'class' => 'badge--warning',
		);
	} else {
		$badges[] = array(
			'label' => __( 'موجود', 'stocksystem' ),
			'class' => 'badge--success',
		);
	}

	if ( count( $badges ) < 2 && $product->is_on_sale() ) {
		if ( has_term( 'clearance', 'product_tag', $product->get_id() ) ) {
			$badges[] = array(
				'label' => __( 'حراج پایان فصل', 'stocksystem' ),
				'class' => 'badge--clearance',
			);
		} else {
			$percent = stocksystem_product_discount_percent( $product );
			if ( $percent > 0 ) {
				$badges[] = array(
					'label' => sprintf(
						/* translators: %s: discount percentage, Persian digits */
						__( '٪%s−', 'stocksystem' ),
						stocksystem_to_persian_digits( $percent )
					),
					'class' => 'badge--discount',
				);
			}
		}
	}

	if ( count( $badges ) < 2 ) {
		if ( has_term( 'featured', 'product_tag', $product->get_id() ) ) {
			$badges[] = array(
				'label' => __( 'ویژه', 'stocksystem' ),
				'class' => 'badge--featured',
				'icon'  => 'star',
			);
		} elseif ( has_term( 'new-arrival', 'product_tag', $product->get_id() ) ) {
			$badges[] = array(
				'label' => __( 'تازه رسید', 'stocksystem' ),
				'class' => 'badge--new',
			);
		} elseif ( has_term( 'free-shipping', 'product_tag', $product->get_id() ) ) {
			$badges[] = array(
				'label' => __( 'ارسال رایگان', 'stocksystem' ),
				'class' => 'badge--shipping',
			);
		}
	}

	return array_slice( $badges, 0, 2 );
}

/**
 * Bestseller corner ribbon — independent of the badge cap above.
 * State 12 "labelled" in the design.
 */
function stocksystem_product_ribbon( WC_Product $product ) {
	if ( has_term( 'bestseller', 'product_tag', $product->get_id() ) ) {
		return __( 'پرفروش‌ترین', 'stocksystem' );
	}

	return '';
}

function stocksystem_product_discount_percent( WC_Product $product ) {
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();

	if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
		return 0;
	}

	return (int) round( ( $regular - $sale ) / $regular * 100 );
}

/**
 * Which buy-box partial (template-parts/product/buy-box-{variant}.php)
 * renders for this product. See inc/product-card.php file header for the
 * precedence rules.
 */
function stocksystem_product_card_variant( WC_Product $product ) {
	if ( '' === $product->get_price() ) {
		return 'quote';
	}

	if ( 'grouped' === $product->get_type() ) {
		return 'model';
	}

	if ( 'variable' === $product->get_type() ) {
		$variation_attribute = stocksystem_variable_product_axis( $product );

		if ( 'color' === $variation_attribute ) {
			return 'color';
		}

		if ( 'size' === $variation_attribute ) {
			return 'size';
		}

		return 'variable';
	}

	if ( ! $product->is_in_stock() ) {
		return 'out-of-stock';
	}

	return 'simple';
}

/**
 * Classifies a variable product's primary variation attribute as
 * 'color', 'size', or 'other' — determines whether the card shows color
 * swatches, size chips, or generic config chips / a price range.
 */
function stocksystem_variable_product_axis( WC_Product_Variable $product ) {
	foreach ( $product->get_variation_attributes() as $taxonomy => $values ) {
		$slug = str_replace( 'attribute_', '', wc_sanitize_taxonomy_name( $taxonomy ) );

		if ( false !== strpos( $slug, 'color' ) || false !== strpos( $slug, 'colour' ) || false !== strpos( $slug, 'رنگ' ) ) {
			return 'color';
		}

		if ( false !== strpos( $slug, 'size' ) || false !== strpos( $slug, 'سایز' ) ) {
			return 'size';
		}
	}

	return 'other';
}

/**
 * Condition/grading note under the title (02 Category.dc.html — "بدنه در
 * حد نو" / "بدنه سالم" / "خط جزئی روی بدنه" with a colored dot).
 * README: "Product grading (A+/A/B/C) — product attribute or taxonomy;
 * shown on cards, product page, and the printed test sheet." Reads a
 * `product_grading` taxonomy term with optional `condition_note` /
 * `dot_color` term meta so the exact wording stays editable rather than
 * hardcoded here — the grading page (07) is the source of truth for
 * default copy once it's built.
 */
function stocksystem_product_grading_note( WC_Product $product ) {
	if ( ! taxonomy_exists( 'product_grading' ) ) {
		return null;
	}

	$terms = wc_get_product_terms( $product->get_id(), 'product_grading', array( 'fields' => 'all' ) );

	if ( empty( $terms ) ) {
		return null;
	}

	$term  = $terms[0];
	$note  = get_term_meta( $term->term_id, 'condition_note', true );
	$color = get_term_meta( $term->term_id, 'dot_color', true );

	return array(
		'label' => $note ? $note : $term->name,
		'color' => $color ? $color : 'var(--c-status-success)',
	);
}

/**
 * Whether a variable product's variations span a meaningful price range
 * (state 10 "price range") vs. sharing effectively one price (state 6
 * "variable — N configurations").
 */
function stocksystem_variable_has_price_range( WC_Product_Variable $product ) {
	$prices = $product->get_variation_prices( true );

	if ( empty( $prices['price'] ) ) {
		return false;
	}

	$min = (float) min( $prices['price'] );
	$max = (float) max( $prices['price'] );

	return $max > $min;
}
