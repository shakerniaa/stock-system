<?php
/**
 * Tiered variation pricing — "base config price + per-upgrade
 * surcharge", generated into native WooCommerce variations.
 *
 * Owner's request, verbatim: register a machine's price at its BASE
 * configuration (e.g. 8GB RAM + 256GB storage), then define what each
 * upgrade costs (512GB → +X تومان); a customer picking a higher config
 * pays base + the difference. Updating a price later should mean
 * editing one or two numbers, not re-typing a price into every
 * variation row.
 *
 * WHY THIS GENERATES NATIVE VARIATIONS instead of computing prices at
 * runtime: the cart, checkout, order line items, stock, reports, SEO
 * structured data, AND this theme's own configurator
 * (inc/product-configurator.php, which derives the "+X تومان" chips on
 * each tile from variation prices) all read WooCommerce's native
 * variation price. Anything that priced on the fly would have to be
 * patched into every one of those. So the owner edits a simple
 * base + surcharge model, and one button materialises it into the
 * variation rows WooCommerce already knows how to price correctly.
 *
 * Three layers, most specific wins:
 *   1. per-product override  — product meta `_ss_price_deltas`
 *   2. global default        — term meta `_ss_price_delta` on the
 *                              attribute term itself (set once in
 *                              Products → Attributes → Configure terms)
 *   3. zero                  — the term is part of the base config
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const STOCKSYSTEM_BASE_PRICE_META   = '_ss_base_price';
const STOCKSYSTEM_PRICE_DELTAS_META = '_ss_price_deltas';
const STOCKSYSTEM_TERM_DELTA_META   = '_ss_price_delta';

/* -------------------------------------------------------------------------
 * Delta resolution
 * ---------------------------------------------------------------------- */

/**
 * Per-product delta overrides, as taxonomy => term_slug => amount.
 */
function stocksystem_get_product_price_deltas( $product_id ) {
	$raw = get_post_meta( $product_id, STOCKSYSTEM_PRICE_DELTAS_META, true );

	if ( ! $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * The surcharge that applies to one attribute value on one product,
 * resolving the three layers above. Returns an int (Toman); may be
 * negative, which is legitimate — a *smaller* base config than the one
 * the price was registered at (e.g. base registered at 8GB but a 4GB
 * build exists) should subtract.
 */
function stocksystem_get_effective_delta( $product_id, $taxonomy, $term_slug ) {
	$overrides = stocksystem_get_product_price_deltas( $product_id );

	if ( isset( $overrides[ $taxonomy ][ $term_slug ] ) && '' !== $overrides[ $taxonomy ][ $term_slug ] ) {
		return (int) $overrides[ $taxonomy ][ $term_slug ];
	}

	$term = get_term_by( 'slug', $term_slug, $taxonomy );

	if ( $term && ! is_wp_error( $term ) ) {
		$global = get_term_meta( $term->term_id, STOCKSYSTEM_TERM_DELTA_META, true );
		if ( '' !== $global && null !== $global ) {
			return (int) $global;
		}
	}

	return 0;
}

/* -------------------------------------------------------------------------
 * Variation generation
 * ---------------------------------------------------------------------- */

/**
 * The product's variation axes, keyed the way WooCommerce's own STORAGE
 * layer keys them, with each axis' real taxonomy carried alongside.
 *
 * This distinction is load-bearing, and getting it wrong silently
 * corrupts every generated variation. wc_get_product_variation_attributes()
 * (wc-product-functions.php) reads a variation by walking the PARENT's
 * `_product_attributes` ARRAY KEYS and looking for post meta named
 * `attribute_` . sanitize_title( $array_key ). For a non-ASCII attribute
 * name that array key is percent-encoded (`pa_%d8%b0…` for
 * `pa_ذخیره‌سازی`) while get_variation_attributes() hands back the RAW
 * name as its key. Writing with the raw key stores
 * `attribute_pa_ذخیره‌سازی`, which the read path then doesn't recognise
 * and discards — the variation silently degrades to "Any storage", and
 * because its signature no longer matches, a re-run creates a duplicate
 * instead of updating. (Caught exactly that way in testing: 9 correct
 * prices, all with an empty storage axis, then 9 duplicates on re-run.)
 *
 * So: keys come from the attribute's own array key (what WooCommerce
 * reads), values from get_slugs() (term slugs, what a variation stores).
 *
 * @return array storage_key => array{ taxonomy: string, values: string[] }
 */
function stocksystem_variation_axes( WC_Product_Variable $product ) {
	$axes = array();

	foreach ( $product->get_attributes() as $array_key => $attribute ) {
		if ( ! $attribute->get_variation() ) {
			continue;
		}

		$values = $attribute->get_slugs();

		if ( empty( $values ) ) {
			continue; // An axis with no values selected can't contribute.
		}

		$axes[ sanitize_title( $array_key ) ] = array(
			'taxonomy' => $attribute->get_name(),
			'values'   => $values,
		);
	}

	return $axes;
}

/**
 * Cartesian product of the axes above, as an array of
 * [ storage_key => term_slug ] maps. With the usual 2 axes
 * (RAM × storage) this stays small, but it is capped so a
 * mis-configured product (5 axes × 6 values = 7776) can't try to create
 * thousands of posts in one request.
 */
function stocksystem_variation_combinations( WC_Product_Variable $product, $cap = 100 ) {
	$axes = stocksystem_variation_axes( $product );

	if ( empty( $axes ) ) {
		return array();
	}

	$combinations = array( array() );

	foreach ( $axes as $storage_key => $axis ) {
		$next = array();
		foreach ( $combinations as $combination ) {
			foreach ( $axis['values'] as $value ) {
				$next[] = $combination + array( $storage_key => $value );
				if ( count( $next ) > $cap ) {
					return array(); // Signals "too many" to the caller.
				}
			}
		}
		$combinations = $next;
	}

	return $combinations;
}

/**
 * Creates any missing variation and (re)prices every one of them from
 * base + Σ deltas. Existing variations keep their stock, SKU and
 * image — only the price is rewritten, because the price is exactly
 * what this model owns. Variations whose combination is no longer
 * possible (an attribute value was unchecked) are left alone rather
 * than deleted; WooCommerce already hides those from the front end,
 * and deleting a customer-facing SKU silently is not this button's
 * job.
 */
function stocksystem_generate_priced_variations( $product_id ) {
	$product = wc_get_product( $product_id );

	if ( ! $product instanceof WC_Product_Variable ) {
		return array( 'error' => __( 'این محصول از نوع «محصول متغیر» نیست.', 'stocksystem' ) );
	}

	$base = (int) get_post_meta( $product_id, STOCKSYSTEM_BASE_PRICE_META, true );

	if ( $base <= 0 ) {
		return array( 'error' => __( 'اول «قیمت پایه» را وارد و محصول را به‌روزرسانی کن.', 'stocksystem' ) );
	}

	$axes         = stocksystem_variation_axes( $product );
	$combinations = stocksystem_variation_combinations( $product );

	if ( empty( $combinations ) ) {
		return array( 'error' => __( 'هیچ ترکیبی برای ساخت پیدا نشد — مطمئن شو حداقل یک ویژگی با تیک «Used for variations» و چند مقدار انتخاب‌شده داری (و تعداد ترکیب‌ها از ۱۰۰ بیشتر نباشد).', 'stocksystem' ) );
	}

	// Index the existing variations by their attribute signature so a
	// re-run updates in place instead of duplicating.
	$existing = array();
	foreach ( $product->get_children() as $child_id ) {
		$variation = wc_get_product( $child_id );
		if ( ! $variation instanceof WC_Product_Variation ) {
			continue;
		}
		$existing[ stocksystem_variation_signature( $variation->get_attributes() ) ] = $variation;
	}

	$created = 0;
	$updated = 0;

	foreach ( $combinations as $combination ) {
		$price     = $base;
		$signature = stocksystem_variation_signature( $combination );

		foreach ( $combination as $storage_key => $term_slug ) {
			// The combination is keyed by storage key; deltas are looked
			// up against the real taxonomy the term actually lives in.
			$taxonomy = $axes[ $storage_key ]['taxonomy'];
			$price   += stocksystem_get_effective_delta( $product_id, $taxonomy, $term_slug );
		}

		$price = max( 0, $price );

		if ( isset( $existing[ $signature ] ) ) {
			$variation = $existing[ $signature ];
			++$updated;
		} else {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product_id );
			$variation->set_attributes( $combination );
			$variation->set_status( 'publish' );
			$variation->set_stock_status( 'instock' );
			++$created;
		}

		$variation->set_regular_price( (string) $price );

		// A variation left on sale from a previous manual edit would
		// silently override the generated price, so the sale price is
		// cleared unless it is still below the new regular price.
		$sale = $variation->get_sale_price( 'edit' );
		if ( '' !== $sale && (float) $sale >= (float) $price ) {
			$variation->set_sale_price( '' );
		}

		$variation->save();
	}

	// Re-sync the parent's cached price range / variation lookup table.
	WC_Product_Variable::sync( $product_id );
	wc_delete_product_transients( $product_id );

	return array(
		'created' => $created,
		'updated' => $updated,
		'base'    => $base,
	);
}

/**
 * Stable key for one attribute combination, independent of array
 * order — WC_Product_Variation::get_attributes() and
 * get_variation_attributes() don't guarantee the same ordering.
 */
function stocksystem_variation_signature( array $attributes ) {
	$normalised = array();

	foreach ( $attributes as $taxonomy => $value ) {
		$normalised[ sanitize_title( $taxonomy ) ] = (string) $value;
	}

	ksort( $normalised );

	return wp_json_encode( $normalised );
}

/* -------------------------------------------------------------------------
 * Term-level default surcharge (Products → Attributes → Configure terms)
 * ---------------------------------------------------------------------- */

/**
 * Attaches the "default surcharge" field to every variation-capable
 * attribute taxonomy, so the owner sets "16GB costs +2,500,000" once
 * and every product inherits it.
 */
function stocksystem_price_delta_term_taxonomies() {
	if ( ! function_exists( 'stocksystem_variation_capable_slugs' ) ) {
		return array();
	}

	return array_map(
		function ( $slug ) {
			return 'pa_' . $slug;
		},
		stocksystem_variation_capable_slugs()
	);
}

function stocksystem_register_term_delta_fields() {
	foreach ( stocksystem_price_delta_term_taxonomies() as $taxonomy ) {
		add_action( $taxonomy . '_add_form_fields', 'stocksystem_term_delta_field_add' );
		add_action( $taxonomy . '_edit_form_fields', 'stocksystem_term_delta_field_edit' );
		add_action( 'created_' . $taxonomy, 'stocksystem_save_term_delta_field' );
		add_action( 'edited_' . $taxonomy, 'stocksystem_save_term_delta_field' );
	}
}
add_action( 'admin_init', 'stocksystem_register_term_delta_fields' );

function stocksystem_term_delta_field_add() {
	?>
	<div class="form-field">
		<label for="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>"><?php esc_html_e( 'اختلاف قیمت پیش‌فرض (تومان)', 'stocksystem' ); ?></label>
		<input type="number" step="1000" name="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>" id="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>" value="">
		<p class="description"><?php esc_html_e( 'مبلغی که بابت این گزینه به قیمت پایهٔ محصول اضافه می‌شود. برای گزینه‌های پایه (مثلاً ۸ گیگ رم) صفر یا خالی بگذار.', 'stocksystem' ); ?></p>
	</div>
	<?php
}

function stocksystem_term_delta_field_edit( $term ) {
	$value = get_term_meta( $term->term_id, STOCKSYSTEM_TERM_DELTA_META, true );
	?>
	<tr class="form-field">
		<th scope="row"><label for="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>"><?php esc_html_e( 'اختلاف قیمت پیش‌فرض (تومان)', 'stocksystem' ); ?></label></th>
		<td>
			<input type="number" step="1000" name="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>" id="<?php echo esc_attr( STOCKSYSTEM_TERM_DELTA_META ); ?>" value="<?php echo esc_attr( $value ); ?>">
			<p class="description"><?php esc_html_e( 'مبلغی که بابت این گزینه به قیمت پایهٔ محصول اضافه می‌شود. در هر محصول می‌توانی این عدد را بازنویسی کنی.', 'stocksystem' ); ?></p>
		</td>
	</tr>
	<?php
}

function stocksystem_save_term_delta_field( $term_id ) {
	if ( ! isset( $_POST[ STOCKSYSTEM_TERM_DELTA_META ] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST[ STOCKSYSTEM_TERM_DELTA_META ] );

	if ( '' === trim( $raw ) ) {
		delete_term_meta( $term_id, STOCKSYSTEM_TERM_DELTA_META );
		return;
	}

	update_term_meta( $term_id, STOCKSYSTEM_TERM_DELTA_META, (int) $raw );
}

/* -------------------------------------------------------------------------
 * Product-data tab
 * ---------------------------------------------------------------------- */

add_filter(
	'woocommerce_product_data_tabs',
	function ( $tabs ) {
		$tabs['stocksystem_tiered_pricing'] = array(
			'label'    => __( 'قیمت‌گذاری پلکانی', 'stocksystem' ),
			'target'   => 'stocksystem_tiered_pricing_data',
			'class'    => array( 'show_if_variable' ),
			'priority' => 41,
		);
		return $tabs;
	}
);

add_action(
	'woocommerce_product_data_panels',
	function () {
		global $post;

		if ( ! $post ) {
			return;
		}

		$product_id = $post->ID;
		$product    = wc_get_product( $product_id );
		$base       = get_post_meta( $product_id, STOCKSYSTEM_BASE_PRICE_META, true );
		$overrides  = stocksystem_get_product_price_deltas( $product_id );
		// Same resolver the generator uses, so this preview can never
		// drift from what the button actually produces.
		$axes = $product instanceof WC_Product_Variable ? stocksystem_variation_axes( $product ) : array();
		?>
		<div id="stocksystem_tiered_pricing_data" class="panel woocommerce_options_panel">
			<div class="options_group" style="padding: 12px 20px;">
				<p>
					<?php esc_html_e( 'قیمت دستگاه را با پایین‌ترین کانفیگ (مثلاً ۸ گیگ رم و ۲۵۶ گیگ هارد) وارد کن، و برای هر ارتقا فقط مبلغ اضافه را بنویس. با زدن دکمهٔ پایین، همهٔ ترکیب‌ها ساخته و قیمت‌گذاری می‌شوند.', 'stocksystem' ); ?>
				</p>

				<p>
					<label for="<?php echo esc_attr( STOCKSYSTEM_BASE_PRICE_META ); ?>" style="display:block;font-weight:600;margin-bottom:4px;">
						<?php esc_html_e( 'قیمت پایه (تومان)', 'stocksystem' ); ?>
					</label>
					<input
						type="number"
						step="1000"
						style="width:240px;"
						name="<?php echo esc_attr( STOCKSYSTEM_BASE_PRICE_META ); ?>"
						id="<?php echo esc_attr( STOCKSYSTEM_BASE_PRICE_META ); ?>"
						value="<?php echo esc_attr( $base ); ?>"
					>
				</p>

				<?php if ( empty( $axes ) ) : ?>
					<p style="color:#a94442;">
						<?php esc_html_e( 'هنوز هیچ ویژگی‌ای با تیک «Used for variations» روی این محصول نیست. اول از تب «قالب ویژگی‌ها» یا «ویژگی‌ها» رم/ذخیره‌سازی را اضافه کن، چند مقدار انتخاب کن و محصول را به‌روزرسانی کن.', 'stocksystem' ); ?>
					</p>
				<?php else : ?>
					<?php foreach ( $axes as $axis ) : ?>
						<?php $taxonomy = $axis['taxonomy']; $values = $axis['values']; ?>
						<h4 style="margin:18px 0 6px;"><?php echo esc_html( wc_attribute_label( $taxonomy ) ); ?></h4>
						<table class="widefat striped" style="max-width:560px;">
							<thead>
								<tr>
									<th><?php esc_html_e( 'گزینه', 'stocksystem' ); ?></th>
									<th style="width:180px;"><?php esc_html_e( 'اختلاف قیمت (تومان)', 'stocksystem' ); ?></th>
									<th style="width:130px;"><?php esc_html_e( 'قیمت نهایی', 'stocksystem' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $values as $value ) : ?>
									<?php
									$term       = get_term_by( 'slug', $value, $taxonomy );
									$label      = $term ? $term->name : $value;
									$global     = $term ? get_term_meta( $term->term_id, STOCKSYSTEM_TERM_DELTA_META, true ) : '';
									$override   = isset( $overrides[ $taxonomy ][ $value ] ) ? $overrides[ $taxonomy ][ $value ] : '';
									$effective  = stocksystem_get_effective_delta( $product_id, $taxonomy, $value );
									$field_name = sprintf( 'ss_delta[%s][%s]', $taxonomy, $value );
									?>
									<tr>
										<td><?php echo esc_html( $label ); ?></td>
										<td>
											<input
												type="number"
												step="1000"
												style="width:150px;"
												name="<?php echo esc_attr( $field_name ); ?>"
												value="<?php echo esc_attr( $override ); ?>"
												placeholder="<?php echo esc_attr( '' !== $global ? $global : '0' ); ?>"
											>
										</td>
										<td>
											<?php
											if ( $base ) {
												echo esc_html( stocksystem_format_number( (int) $base + $effective ) );
											} else {
												echo '—';
											}
											?>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
						<p class="description" style="margin-top:4px;">
							<?php esc_html_e( 'خالی گذاشتن = استفاده از مقدار پیش‌فرض همان گزینه (که در محصولات ← ویژگی‌ها ← پیکربندی مشخصه تعریف می‌شود). عدد وارد‌شده اینجا فقط روی همین محصول اثر دارد.', 'stocksystem' ); ?>
						</p>
					<?php endforeach; ?>

					<?php
					$generate_url = wp_nonce_url(
						add_query_arg(
							array(
								'action'     => 'stocksystem_generate_variations',
								'product_id' => $product_id,
							),
							admin_url( 'admin-post.php' )
						),
						'stocksystem_generate_variations_' . $product_id
					);
					?>
					<p style="margin-top:18px;">
						<a href="<?php echo esc_url( $generate_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'ساخت و قیمت‌گذاری همهٔ ترکیب‌ها', 'stocksystem' ); ?>
						</a>
					</p>
					<p class="description">
						<?php esc_html_e( 'قبل از زدن این دکمه، حتماً یک‌بار «به‌روزرسانی» محصول را بزن تا قیمت پایه و اختلاف‌ها ذخیره شوند. ترکیب‌های موجود فقط قیمتشان به‌روز می‌شود — موجودی، SKU و عکسشان دست‌نخورده می‌ماند.', 'stocksystem' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
);

/**
 * Saves the base price and the per-product delta overrides alongside
 * the rest of the product's meta.
 */
function stocksystem_save_tiered_pricing_meta( $post_id ) {
	if ( isset( $_POST[ STOCKSYSTEM_BASE_PRICE_META ] ) ) {
		$base = trim( wp_unslash( $_POST[ STOCKSYSTEM_BASE_PRICE_META ] ) );
		if ( '' === $base ) {
			delete_post_meta( $post_id, STOCKSYSTEM_BASE_PRICE_META );
		} else {
			update_post_meta( $post_id, STOCKSYSTEM_BASE_PRICE_META, (int) $base );
		}
	}

	if ( ! isset( $_POST['ss_delta'] ) || ! is_array( $_POST['ss_delta'] ) ) {
		return;
	}

	$clean = array();

	foreach ( wp_unslash( $_POST['ss_delta'] ) as $taxonomy => $values ) {
		if ( ! is_array( $values ) ) {
			continue;
		}
		foreach ( $values as $slug => $amount ) {
			if ( '' === trim( (string) $amount ) ) {
				continue; // Empty means "inherit the term's default".
			}
			$clean[ sanitize_text_field( $taxonomy ) ][ sanitize_title( $slug ) ] = (int) $amount;
		}
	}

	if ( empty( $clean ) ) {
		delete_post_meta( $post_id, STOCKSYSTEM_PRICE_DELTAS_META );
		return;
	}

	update_post_meta( $post_id, STOCKSYSTEM_PRICE_DELTAS_META, wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) );
}
add_action( 'woocommerce_process_product_meta', 'stocksystem_save_tiered_pricing_meta' );

/* -------------------------------------------------------------------------
 * Generate action
 * ---------------------------------------------------------------------- */

add_action(
	'admin_post_stocksystem_generate_variations',
	function () {
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

		if ( ! $product_id || ! current_user_can( 'edit_product', $product_id ) ) {
			wp_die( esc_html__( 'اجازهٔ دسترسی وجود ندارد.', 'stocksystem' ) );
		}

		check_admin_referer( 'stocksystem_generate_variations_' . $product_id );

		$result = stocksystem_generate_priced_variations( $product_id );

		$args = array(
			'post'   => $product_id,
			'action' => 'edit',
		);

		if ( isset( $result['error'] ) ) {
			$args['ss_pricing_error'] = rawurlencode( $result['error'] );
		} else {
			$args['ss_pricing_created'] = $result['created'];
			$args['ss_pricing_updated'] = $result['updated'];
		}

		wp_safe_redirect( add_query_arg( $args, admin_url( 'post.php' ) ) );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( isset( $_GET['ss_pricing_error'] ) ) {
			printf(
				'<div class="notice notice-error is-dismissible"><p>%s</p></div>',
				esc_html( rawurldecode( wp_unslash( $_GET['ss_pricing_error'] ) ) )
			);
			return;
		}

		if ( ! isset( $_GET['ss_pricing_created'] ) ) {
			return;
		}

		printf(
			'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: created variation count, 2: updated variation count */
					__( 'قیمت‌گذاری انجام شد — %1$d ترکیب جدید ساخته شد و قیمت %2$d ترکیب موجود به‌روز شد.', 'stocksystem' ),
					absint( $_GET['ss_pricing_created'] ),
					absint( isset( $_GET['ss_pricing_updated'] ) ? $_GET['ss_pricing_updated'] : 0 )
				)
			)
		);
	}
);
