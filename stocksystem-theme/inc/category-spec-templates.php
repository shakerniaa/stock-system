<?php
/**
 * Category attribute templates — a fixed, ordered checklist of which
 * attributes belong to which product category, so every laptop (or
 * every monitor, every mini-PC, ...) ends up with the same set of
 * specs in the same order, instead of relying on the owner to
 * remember them one product at a time.
 *
 * Owner's request, verbatim: as the attribute catalog grows, it's easy
 * to forget one on a given product, or add them in a different order
 * each time. This gives every category its own canonical checklist,
 * plus a one-click "apply this category's template" action on the
 * product edit screen that adds any missing attribute in the right
 * position — filled-in values are never touched, and attributes not
 * in the template are never removed, only reordered to the end.
 *
 * Matched by KEYWORD inside the category name (same pattern as
 * stocksystem_category_icon() in inc/nav-data.php), not by slug or
 * term ID — WooCommerce's stored slugs for Persian category names are
 * percent-encoded and change if a category is ever renamed/recreated,
 * so matching the human-readable name is the only stable option here.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ordered list of (keyword, template title, attribute slugs). Checked
 * top to bottom against every category name assigned to the product;
 * the FIRST matching keyword wins (order here matters — a more
 * specific keyword like «آل‌این‌وان» is listed before the more general
 * «لپ‌تاپ» even though neither substring currently collides with the
 * other, to stay safe if category names are ever renamed).
 *
 * Slugs are the same ones from inc/attributes-bootstrap.php's catalog
 * (`pa_` + slug is the real taxonomy name), except the four attributes
 * that predate that catalog and keep their original slugs: `ram`,
 * `size`, `color`, `ذخیرهسازی`.
 */
function stocksystem_category_spec_templates() {
	return array(
		array(
			'keyword' => 'آل‌این‌وان',
			'title'   => __( 'آل‌این‌وان', 'stocksystem' ),
			'slugs'   => array( 'cpu', 'cpu-gen', 'ram', 'ram-type', 'ذخیرهسازی', 'gpu-type', 'size', 'resolution', 'panel-type', 'touchscreen', 'aio-connect', 'os', 'color' ),
		),
		array(
			'keyword' => 'لپ‌تاپ',
			'title'   => __( 'لپ‌تاپ', 'stocksystem' ),
			'slugs'   => array( 'cpu', 'cpu-gen', 'ram', 'ram-type', 'ذخیرهسازی', 'gpu-type', 'gpu', 'size', 'resolution', 'panel-type', 'weight', 'os', 'ports', 'color' ),
		),
		array(
			'keyword' => 'مانیتور',
			'title'   => __( 'مانیتور', 'stocksystem' ),
			'slugs'   => array( 'size', 'resolution', 'panel-type', 'refresh-rate', 'response-time', 'panel-curve', 'aspect-ratio', 'speaker', 'ports', 'color' ),
		),
		array(
			'keyword' => 'کیس',
			'title'   => __( 'کیس و مینی‌پی‌سی', 'stocksystem' ),
			'slugs'   => array( 'cpu', 'cpu-gen', 'ram', 'ram-type', 'ذخیرهسازی', 'gpu-type', 'gpu', 'form-factor', 'psu-watt', 'os', 'ports', 'color' ),
		),
		array(
			'keyword' => 'قطعات',
			'title'   => __( 'قطعات و ارتقا', 'stocksystem' ),
			'slugs'   => array( 'cpu-socket', 'chipset', 'ram-type', 'ram-freq', 'storage-if', 'storage-form', 'gpu', 'vram', 'gpu-interface', 'psu-watt', 'psu-cert', 'cooler-type' ),
		),
		array(
			'keyword' => 'لوازم',
			'title'   => __( 'لوازم جانبی', 'stocksystem' ),
			'slugs'   => array( 'connect-type', 'material', 'color', 'size' ),
		),
	);
}

/**
 * Templates matching this product's assigned categories, in the
 * catalog's own order, de-duplicated by keyword — a product filed
 * under two categories that both match (rare) contributes both
 * templates' slugs, first template's order preserved, second
 * template's new slugs appended.
 */
function stocksystem_match_category_templates( $product_id ) {
	$category_names = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );

	if ( is_wp_error( $category_names ) || empty( $category_names ) ) {
		return array();
	}

	$matched = array();

	foreach ( stocksystem_category_spec_templates() as $template ) {
		foreach ( $category_names as $category_name ) {
			if ( false !== mb_strpos( $category_name, $template['keyword'] ) ) {
				$matched[] = $template;
				break;
			}
		}
	}

	return $matched;
}

/**
 * The combined, de-duplicated, ordered slug checklist for this
 * product's matched templates.
 */
function stocksystem_get_product_template_slugs( $product_id ) {
	$slugs = array();

	foreach ( stocksystem_match_category_templates( $product_id ) as $template ) {
		foreach ( $template['slugs'] as $slug ) {
			if ( ! in_array( $slug, $slugs, true ) ) {
				$slugs[] = $slug;
			}
		}
	}

	return $slugs;
}

/**
 * Adds every missing attribute from this product's category template
 * (empty — ready for the owner to pick values), reorders ALL of the
 * product's attributes to match the template's order, and leaves
 * already-filled attributes and any attribute outside the template
 * untouched aside from position (template attributes first in
 * template order, everything else after, in its previous relative
 * order). Returns a report array; never deletes an attribute.
 */
function stocksystem_apply_category_template( $product_id ) {
	$product = wc_get_product( $product_id );

	if ( ! $product instanceof WC_Product ) {
		return array( 'error' => __( 'محصول پیدا نشد.', 'stocksystem' ) );
	}

	$slugs = stocksystem_get_product_template_slugs( $product_id );

	if ( empty( $slugs ) ) {
		return array( 'error' => __( 'این محصول با هیچ‌کدام از دسته‌های دارای قالب مطابقت ندارد.', 'stocksystem' ) );
	}

	// Keyed by the attribute's REAL taxonomy name (WC_Product_Attribute::
	// get_name(), always the plain "pa_slug" string) rather than by the
	// product's own array key — WooCommerce sometimes stores that key
	// percent-encoded for a non-ASCII attribute name (the site's legacy
	// pa_ذخیره‌سازی does this; see inc/import-batches.php's
	// stocksystem_find_attribute_taxonomy() for the same landmine).
	// Matching on the array key here would silently "lose" that
	// attribute on every re-apply — recreated empty and pushed to the
	// end instead of recognized in place.
	$existing = array();
	foreach ( $product->get_attributes() as $attribute ) {
		$existing[ $attribute->get_name() ] = $attribute;
	}

	$ordered  = array();
	$added    = array();
	$position = 0;

	foreach ( $slugs as $slug ) {
		$taxonomy = 'pa_' . $slug;

		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue; // Attribute doesn't exist yet (bootstrap not run) — skip rather than fatal.
		}

		if ( isset( $existing[ $taxonomy ] ) ) {
			$attribute = $existing[ $taxonomy ];
			unset( $existing[ $taxonomy ] );
		} else {
			$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
			$attribute    = new WC_Product_Attribute();
			$attribute->set_id( $attribute_id );
			$attribute->set_name( $taxonomy );
			$attribute->set_options( array() );
			$attribute->set_visible( true );
			$attribute->set_variation( false );
			$added[] = wc_attribute_label( $taxonomy );
		}

		$attribute->set_position( $position );
		$ordered[ $taxonomy ] = $attribute;
		++$position;
	}

	// Anything the product already had that isn't part of this
	// template (custom attributes, or a taxonomy attribute this
	// category's template doesn't mention) is kept, appended after,
	// in its previous relative order — nothing is ever dropped.
	foreach ( $existing as $taxonomy => $attribute ) {
		$attribute->set_position( $position );
		$ordered[ $taxonomy ] = $attribute;
		++$position;
	}

	$product->set_attributes( $ordered );
	$product->save();

	return array(
		'added' => $added,
		'total' => count( $slugs ),
	);
}

/**
 * Product-data tab — sits right before "ویژگی‌ها" (Attributes) so the
 * natural flow is: open this tab, apply the template, then switch to
 * Attributes to fill in values.
 */
add_filter(
	'woocommerce_product_data_tabs',
	function ( $tabs ) {
		$tabs['stocksystem_category_template'] = array(
			'label'    => __( 'قالب ویژگی‌ها', 'stocksystem' ),
			'target'   => 'stocksystem_category_template_data',
			'class'    => array( 'show_if_simple', 'show_if_variable' ),
			'priority' => 39,
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
		$templates  = stocksystem_match_category_templates( $product_id );
		$slugs      = stocksystem_get_product_template_slugs( $product_id );
		$values     = function_exists( 'stocksystem_get_product_attribute_values' ) ? stocksystem_get_product_attribute_values( wc_get_product( $product_id ) ) : array();
		?>
		<div id="stocksystem_category_template_data" class="panel woocommerce_options_panel">
			<div class="options_group" style="padding: 12px 20px;">
				<?php if ( empty( $templates ) ) : ?>
					<p><?php esc_html_e( 'این محصول هنوز در یکی از دسته‌های دارای قالب ثابت (لپ‌تاپ، آل‌این‌وان، مانیتور، کیس و مینی‌پی‌سی، قطعات و ارتقا، لوازم جانبی) قرار نگرفته — اول دسته‌بندی را انتخاب و محصول را «به‌روزرسانی» کن، بعد به این تب برگرد.', 'stocksystem' ); ?></p>
				<?php else : ?>
					<p>
						<strong><?php esc_html_e( 'قالب تشخیص‌داده‌شده:', 'stocksystem' ); ?></strong>
						<?php echo esc_html( implode( '، ', wp_list_pluck( $templates, 'title' ) ) ); ?>
					</p>
					<table class="widefat striped" style="max-width: 480px; margin: 10px 0 16px;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'ویژگی', 'stocksystem' ); ?></th>
								<th><?php esc_html_e( 'وضعیت', 'stocksystem' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $slugs as $slug ) : ?>
								<?php $label = wc_attribute_label( 'pa_' . $slug ); ?>
								<tr>
									<td><?php echo esc_html( $label ); ?></td>
									<td>
										<?php if ( ! taxonomy_exists( 'pa_' . $slug ) ) : ?>
											<span style="color:#a94442;"><?php esc_html_e( 'ویژگی هنوز ساخته نشده', 'stocksystem' ); ?></span>
										<?php elseif ( ! empty( $values[ $slug ] ) ) : ?>
											<span style="color:#0f7a44;">✓ <?php echo esc_html( $values[ $slug ] ); ?></span>
										<?php else : ?>
											<span style="color:#8a8a8a;"><?php esc_html_e( 'خالی', 'stocksystem' ); ?></span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>

					<?php
					$apply_url = wp_nonce_url(
						add_query_arg(
							array(
								'action'     => 'stocksystem_apply_category_template',
								'product_id' => $product_id,
							),
							admin_url( 'admin-post.php' )
						),
						'stocksystem_apply_category_template_' . $product_id
					);
					?>
					<p>
						<a href="<?php echo esc_url( $apply_url ); ?>" class="button button-primary">
							<?php esc_html_e( 'افزودن و مرتب‌سازی ویژگی‌های این قالب', 'stocksystem' ); ?>
						</a>
					</p>
					<p class="description">
						<?php esc_html_e( 'ویژگی‌های خالی بالا به تب «ویژگی‌ها» اضافه می‌شوند (بدون مقدار — خودت مقدارشان را انتخاب می‌کنی)، و ترتیب همهٔ ویژگی‌ها طبق همین جدول تنظیم می‌شود. مقادیری که از قبل پر کرده‌ای دست‌نخورده می‌مانند.', 'stocksystem' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
);

add_action(
	'admin_post_stocksystem_apply_category_template',
	function () {
		$product_id = isset( $_GET['product_id'] ) ? absint( $_GET['product_id'] ) : 0;

		if ( ! $product_id || ! current_user_can( 'edit_product', $product_id ) ) {
			wp_die( esc_html__( 'اجازهٔ دسترسی وجود ندارد.', 'stocksystem' ) );
		}

		check_admin_referer( 'stocksystem_apply_category_template_' . $product_id );

		$result = stocksystem_apply_category_template( $product_id );

		$redirect = add_query_arg(
			array(
				'post'                        => $product_id,
				'action'                      => 'edit',
				'stocksystem_template_applied' => isset( $result['error'] ) ? '0' : '1',
				'stocksystem_template_added'   => isset( $result['added'] ) ? count( $result['added'] ) : 0,
			),
			admin_url( 'post.php' )
		);

		wp_safe_redirect( $redirect );
		exit;
	}
);

add_action(
	'admin_notices',
	function () {
		if ( ! isset( $_GET['stocksystem_template_applied'] ) ) {
			return;
		}

		if ( '1' === $_GET['stocksystem_template_applied'] ) {
			$added = isset( $_GET['stocksystem_template_added'] ) ? absint( $_GET['stocksystem_template_added'] ) : 0;
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				esc_html(
					sprintf(
						/* translators: %d: number of newly added attributes */
						_n( 'قالب اعمال شد — %d ویژگی جدید اضافه شد (خالی، آماده برای پر کردن مقدار).', 'قالب اعمال شد — %d ویژگی جدید اضافه شد (خالی، آماده برای پر کردن مقدار).', $added, 'stocksystem' ),
						$added
					)
				)
			);
		} else {
			printf( '<div class="notice notice-error is-dismissible"><p>%s</p></div>', esc_html__( 'اعمال قالب ناموفق بود — این محصول با هیچ دستهٔ دارای قالب مطابقت نداشت.', 'stocksystem' ) );
		}
	}
);
