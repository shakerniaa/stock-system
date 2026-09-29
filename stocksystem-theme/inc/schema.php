<?php
/**
 * JSON-LD structured data.
 *
 * The theme already prints title/description/OpenGraph (inc/seo-meta.php)
 * but emitted no product schema at all, so Google had no machine-readable
 * price, availability or rating to build a rich result from — the single
 * biggest technical gap for a shop whose traffic comes from model-name
 * searches.
 *
 * Emits:
 *   - Organization + WebSite on the front page
 *   - Product (with Offer, AggregateRating, and the device's test-report
 *     figures as additionalProperty) on single products
 *   - BreadcrumbList wherever WooCommerce draws a breadcrumb
 *   - Article on single blog posts
 *
 * Everything is derived from data already on the product, so there is
 * nothing extra for the owner to fill in. Google penalises structured
 * data that disagrees with the visible page, so each block is built from
 * the same source the template renders.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Prints one JSON-LD block. Uses the same escaping rules WordPress core
 * uses for its own JSON-LD: unescaped slashes and unicode so Persian
 * stays readable in view-source, and no HTML entity mangling.
 */
function stocksystem_print_jsonld( array $data ) {
	if ( empty( $data ) ) {
		return;
	}

	echo '<script type="application/ld+json">'
		. wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		. '</script>' . "\n";
}

/** The shop itself, referenced by @id from the other blocks. */
function stocksystem_schema_organization() {
	$logo = stocksystem_logo_url();

	$org = array(
		'@type'  => 'Store',
		'@id'    => home_url( '/#organization' ),
		'name'   => get_bloginfo( 'name' ),
		'url'    => home_url( '/' ),
		'image'  => $logo,
		'logo'   => $logo,
	);

	$phone = stocksystem_business( 'phone' );
	if ( $phone ) {
		$org['telephone'] = $phone;
	}

	$address = stocksystem_business( 'address' );
	if ( $address ) {
		$org['address'] = array(
			'@type'           => 'PostalAddress',
			'streetAddress'   => $address,
			'addressCountry'  => 'IR',
		);
	}

	return $org;
}

/**
 * WooCommerce stock status → the schema.org URL Google expects.
 */
function stocksystem_schema_availability( WC_Product $product ) {
	if ( ! $product->is_in_stock() ) {
		return 'https://schema.org/OutOfStock';
	}

	if ( $product->is_on_backorder( 1 ) ) {
		return 'https://schema.org/BackOrder';
	}

	return 'https://schema.org/InStock';
}

/**
 * The device's own test figures, as additionalProperty. This is the
 * shop's actual differentiator over marketplaces — battery health,
 * powered-on hours and body grade measured per unit — so it belongs in
 * the machine-readable description, not just the visible panel.
 */
function stocksystem_schema_test_report_properties( $product_id ) {
	if ( ! function_exists( 'stocksystem_get_test_report' ) ) {
		return array();
	}

	$report = stocksystem_get_test_report( $product_id );

	if ( ! $report ) {
		return array();
	}

	$map = array(
		'battery' => __( 'سلامت باتری', 'stocksystem' ),
		'runtime' => __( 'ساعت کارکرد', 'stocksystem' ),
		'body'    => __( 'وضعیت بدنه', 'stocksystem' ),
		'pixels'  => __( 'پیکسل سوخته', 'stocksystem' ),
	);

	$props = array();

	foreach ( $map as $key => $label ) {
		if ( empty( $report[ $key ] ) && '0' !== (string) $report[ $key ] ) {
			continue;
		}
		$value = $report[ $key ];
		if ( 'battery' === $key ) {
			$value .= '%';
		}
		$props[] = array(
			'@type' => 'PropertyValue',
			'name'  => $label,
			'value' => (string) $value,
		);
	}

	return $props;
}

/** The product's visible attributes as additionalProperty entries. */
function stocksystem_schema_spec_properties( WC_Product $product ) {
	if ( ! function_exists( 'stocksystem_get_product_attribute_values' ) ) {
		return array();
	}

	$props = array();

	foreach ( stocksystem_get_product_attribute_values( $product ) as $slug => $value ) {
		if ( '' === trim( (string) $value ) ) {
			continue;
		}
		$taxonomy = 'pa_' . $slug;
		$props[]  = array(
			'@type' => 'PropertyValue',
			'name'  => wc_attribute_label( taxonomy_exists( $taxonomy ) ? $taxonomy : $slug ),
			'value' => (string) $value,
		);
	}

	return $props;
}

/**
 * Price as an amount in the currency code the schema declares.
 *
 * This store follows the usual Iranian WooCommerce convention (set up in
 * inc/woocommerce.php): the currency code is IRR because there is no ISO
 * 4217 code for Toman, but prices are ENTERED in Toman and the symbol is
 * overridden to «تومان». WooCommerce never converts — it just prints the
 * symbol you configured.
 *
 * Structured data has no such freedom: it states a number against an ISO
 * code, so publishing the Toman figure under IRR would advertise every
 * product at a tenth of its real price. When that convention is detected
 * (IRR + a Toman symbol) the amount is converted to actual Rial, which is
 * the same amount of money the page shows, expressed in the unit the
 * schema claims.
 */
function stocksystem_schema_price( $amount ) {
	if ( '' === $amount || null === $amount ) {
		return '';
	}

	$amount = (float) $amount;

	if ( 'IRR' === get_woocommerce_currency() && false !== mb_strpos( (string) get_woocommerce_currency_symbol(), 'تومان' ) ) {
		$amount *= 10;
	}

	return wc_format_decimal( $amount, 0 );
}

/**
 * Product schema for the single-product page.
 *
 * A variable product publishes an AggregateOffer with its real low/high
 * price rather than a single made-up number; a quote-only product
 * publishes no offer at all, because advertising a price Google can't
 * verify on the page is exactly what triggers a manual action.
 */
function stocksystem_schema_product() {
	// NOT the $product global: this runs on wp_head, before the loop has
	// started, so that global is still null there. Resolve from the query
	// instead.
	$product = wc_get_product( get_queried_object_id() );

	if ( ! $product instanceof WC_Product ) {
		return array();
	}

	$product_id = $product->get_id();

	$data = array(
		'@type'       => 'Product',
		'@id'         => get_permalink( $product_id ) . '#product',
		'name'        => $product->get_name(),
		'url'         => get_permalink( $product_id ),
		'description' => wp_strip_all_tags( $product->get_short_description() ? $product->get_short_description() : $product->get_description() ),
	);

	$data['description'] = wp_trim_words( $data['description'], 50, '…' );

	$image_id = $product->get_image_id();
	if ( $image_id ) {
		$data['image'] = wp_get_attachment_image_url( $image_id, 'large' );
	}

	if ( $product->get_sku() ) {
		$data['sku'] = $product->get_sku();
	}

	// Brand, from the product_brand taxonomy the theme already uses.
	$brands = wp_get_post_terms( $product_id, 'product_brand', array( 'fields' => 'names' ) );
	if ( ! is_wp_error( $brands ) && ! empty( $brands ) ) {
		$data['brand'] = array( '@type' => 'Brand', 'name' => $brands[0] );
	}

	// Refurbished is a first-class condition in schema.org, and saying so
	// is an advantage here rather than something to hide.
	$data['itemCondition'] = 'https://schema.org/RefurbishedCondition';

	$properties = array_merge(
		stocksystem_schema_test_report_properties( $product_id ),
		stocksystem_schema_spec_properties( $product )
	);
	if ( ! empty( $properties ) ) {
		$data['additionalProperty'] = $properties;
	}

	// Reviews.
	if ( $product->get_review_count() > 0 && $product->get_average_rating() > 0 ) {
		$data['aggregateRating'] = array(
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $product->get_average_rating(),
			'reviewCount' => (int) $product->get_review_count(),
		);
	}

	// Offers — skipped entirely for quote-only products.
	$is_quote = function_exists( 'stocksystem_product_card_variant' ) && 'quote' === stocksystem_product_card_variant( $product );

	if ( ! $is_quote ) {
		$currency = get_woocommerce_currency();
		$base     = array(
			'availability'  => stocksystem_schema_availability( $product ),
			'priceCurrency' => $currency,
			'url'           => get_permalink( $product_id ),
			'seller'        => array( '@id' => home_url( '/#organization' ) ),
		);

		if ( $product->is_type( 'variable' ) ) {
			$prices = $product->get_variation_prices( true );
			if ( ! empty( $prices['price'] ) ) {
				$data['offers'] = array_merge(
					$base,
					array(
						'@type'      => 'AggregateOffer',
						'lowPrice'   => stocksystem_schema_price( min( $prices['price'] ) ),
						'highPrice'  => stocksystem_schema_price( max( $prices['price'] ) ),
						'offerCount' => count( $prices['price'] ),
					)
				);
			}
		} elseif ( '' !== $product->get_price() ) {
			$data['offers'] = array_merge( $base, array( '@type' => 'Offer', 'price' => stocksystem_schema_price( wc_get_price_to_display( $product ) ) ) );

			$sale_end = $product->get_date_on_sale_to();
			if ( $sale_end ) {
				$data['offers']['priceValidUntil'] = $sale_end->date( 'Y-m-d' );
			}
		}
	}

	return $data;
}

/** BreadcrumbList built from WooCommerce's own breadcrumb trail. */
function stocksystem_schema_breadcrumbs() {
	if ( ! function_exists( 'wc_get_breadcrumb' ) ) {
		return array();
	}

	$crumbs = wc_get_breadcrumb();

	if ( empty( $crumbs ) || count( $crumbs ) < 2 ) {
		return array();
	}

	$items = array();

	foreach ( $crumbs as $i => $crumb ) {
		$item = array(
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb[0],
		);
		if ( ! empty( $crumb[1] ) ) {
			$item['item'] = $crumb[1];
		}
		$items[] = $item;
	}

	return array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
}

/** Article schema for a blog post. */
function stocksystem_schema_article() {
	$id = get_queried_object_id();

	$data = array(
		'@type'         => 'Article',
		'headline'      => wp_trim_words( get_the_title( $id ), 20, '' ),
		'datePublished' => get_the_date( 'c', $id ),
		'dateModified'  => get_the_modified_date( 'c', $id ),
		'author'        => array( '@type' => 'Person', 'name' => get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $id ) ) ),
		'publisher'     => array( '@id' => home_url( '/#organization' ) ),
		'mainEntityOfPage' => get_permalink( $id ),
	);

	$image = get_the_post_thumbnail_url( $id, 'large' );
	if ( $image ) {
		$data['image'] = $image;
	}

	return $data;
}

/**
 * Assembles the page's graph and prints it as a single block, which is
 * what Google prefers over several disconnected scripts.
 */
function stocksystem_print_schema() {
	if ( is_admin() || is_feed() || is_404() ) {
		return;
	}

	// Don't advertise pages that are deliberately kept out of the index.
	$ctx = function_exists( 'stocksystem_seo_context' ) ? stocksystem_seo_context() : array( 'noindex' => false );
	if ( ! empty( $ctx['noindex'] ) ) {
		return;
	}

	$graph = array();

	if ( is_front_page() ) {
		$graph[] = stocksystem_schema_organization();
		$graph[] = array(
			'@type'           => 'WebSite',
			'@id'             => home_url( '/#website' ),
			'url'             => home_url( '/' ),
			'name'            => get_bloginfo( 'name' ),
			'inLanguage'      => 'fa-IR',
			'publisher'       => array( '@id' => home_url( '/#organization' ) ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => home_url( '/?s={search_term_string}&post_type=product' ),
				),
				'query-input' => 'required name=search_term_string',
			),
		);
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$graph[] = stocksystem_schema_organization();
		$product_schema = stocksystem_schema_product();
		if ( ! empty( $product_schema ) ) {
			$graph[] = $product_schema;
		}
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = stocksystem_schema_organization();
		$graph[] = stocksystem_schema_article();
	}

	// Breadcrumbs are deliberately NOT emitted here on WooCommerce pages:
	// WC_Structured_Data already prints a complete BreadcrumbList of its
	// own (verified in the rendered page), and two of them is a duplicate
	// Google will flag. Blog posts have no such block, so they get ours.
	if ( is_singular( 'post' ) ) {
		$crumbs = stocksystem_schema_breadcrumbs();
		if ( ! empty( $crumbs ) ) {
			$graph[] = $crumbs;
		}
	}

	if ( empty( $graph ) ) {
		return;
	}

	stocksystem_print_jsonld( array( '@context' => 'https://schema.org', '@graph' => $graph ) );
}
add_action( 'wp_head', 'stocksystem_print_schema', 5 );
