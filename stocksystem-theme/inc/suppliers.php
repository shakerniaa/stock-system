<?php
/**
 * Suppliers: each supplier is a lightweight, admin-only custom post type
 * carrying contact info and a tiered markup rule. The markup rule reuses
 * the exact "JSON in a textarea" pattern already used for product add-ons
 * (inc/product-addons.php's `_addons` field) instead of a plugin or a new
 * repeater UI. Purchase prices and margins are internal business data and
 * must never reach the public site.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Post type
 * ---------------------------------------------------------------------- */

function stocksystem_register_supplier_post_type() {
	register_post_type(
		'stocksystem_supplier',
		array(
			'label'           => __( 'تامین‌کنندگان', 'stocksystem' ),
			'labels'          => array(
				'name'          => __( 'تامین‌کنندگان', 'stocksystem' ),
				'singular_name' => __( 'تامین‌کننده', 'stocksystem' ),
				'add_new_item'  => __( 'افزودن تامین‌کننده', 'stocksystem' ),
				'edit_item'     => __( 'ویرایش تامین‌کننده', 'stocksystem' ),
				'search_items'  => __( 'جستجوی تامین‌کننده', 'stocksystem' ),
				'not_found'     => __( 'تامین‌کننده‌ای یافت نشد', 'stocksystem' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'stocksystem',
			'supports'        => array( 'title' ),
			'capability_type' => 'page',
			'map_meta_cap'    => true,
			// Purchase prices/margins are internal — only someone who can
			// already manage the whole "استوک سیستم" menu should ever see
			// this post type at all.
			//
			// Deliberately NOT overriding edit_post/read_post/delete_post
			// (the per-object META capabilities) here: WordPress registers
			// whatever string you give those into a GLOBAL reverse-lookup
			// table ($post_type_meta_caps in wp-includes/capabilities.php)
			// so that map_meta_cap() can route a custom meta-cap name back
			// to its real meaning. Setting them to 'manage_options' — a
			// real, extremely commonly-checked primitive capability —
			// registered 'manage_options' itself as an alias needing
			// translation, which broke current_user_can('manage_options')
			// SITE-WIDE (even WordPress's own Settings menu vanished) as
			// soon as a second post type here did the same thing. Left
			// unset, these default to capability_type's own safe,
			// already-globally-correct names (edit_page/read_page/
			// delete_page), which still resolve to the primitive
			// capabilities below via WordPress's normal (non-buggy) post
			// meta-cap logic. Confirmed via a real admin login after the
			// fix — see zesty-toasting-treasure.md.
			'capabilities'    => array(
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',
				'delete_posts'           => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'create_posts'           => 'manage_options',
			),
		)
	);
}
add_action( 'init', 'stocksystem_register_supplier_post_type' );

/* -------------------------------------------------------------------------
 * Meta boxes
 * ---------------------------------------------------------------------- */

function stocksystem_supplier_add_meta_boxes() {
	add_meta_box( 'stocksystem_supplier_contact', __( 'اطلاعات تماس', 'stocksystem' ), 'stocksystem_supplier_contact_box', 'stocksystem_supplier', 'normal', 'high' );
	add_meta_box( 'stocksystem_supplier_pricing', __( 'قانون سود', 'stocksystem' ), 'stocksystem_supplier_pricing_box', 'stocksystem_supplier', 'normal', 'default' );
	add_meta_box( 'stocksystem_supplier_csv_map', __( 'نگاشت ستون CSV (اختیاری)', 'stocksystem' ), 'stocksystem_supplier_csv_map_box', 'stocksystem_supplier', 'normal', 'low' );
}
add_action( 'add_meta_boxes', 'stocksystem_supplier_add_meta_boxes' );

function stocksystem_supplier_contact_box( $post ) {
	wp_nonce_field( 'stocksystem_supplier_save', 'stocksystem_supplier_nonce' );

	$phone = get_post_meta( $post->ID, '_phone', true );
	$notes = get_post_meta( $post->ID, '_notes', true );

	// A brand new supplier (meta never saved yet) defaults to active;
	// an explicit saved '0' stays unchecked.
	$active = metadata_exists( 'post', $post->ID, '_active' ) ? get_post_meta( $post->ID, '_active', true ) : '1';
	?>
	<p>
		<label for="stocksystem_supplier_phone"><strong><?php esc_html_e( 'تلفن', 'stocksystem' ); ?></strong></label><br>
		<input type="text" class="widefat" id="stocksystem_supplier_phone" name="stocksystem_supplier[phone]" value="<?php echo esc_attr( $phone ); ?>">
	</p>
	<p>
		<label for="stocksystem_supplier_notes"><strong><?php esc_html_e( 'یادداشت', 'stocksystem' ); ?></strong></label><br>
		<textarea class="widefat" rows="3" id="stocksystem_supplier_notes" name="stocksystem_supplier[notes]"><?php echo esc_textarea( $notes ); ?></textarea>
	</p>
	<p>
		<label><input type="checkbox" name="stocksystem_supplier[active]" value="1" <?php checked( $active, '1' ); ?>> <?php esc_html_e( 'فعال (در فهرست انتخاب تامین‌کننده نشان داده شود)', 'stocksystem' ); ?></label>
	</p>
	<?php
}

function stocksystem_supplier_pricing_box( $post ) {
	$value = get_post_meta( $post->ID, '_pricing_rules', true );
	?>
	<p style="color:#666">
		<?php esc_html_e( 'آرایهٔ JSON قوانین سود این تامین‌کننده. هر ردیف یک بازهٔ قیمتی (بر اساس قیمت اعلامی تامین‌کننده) با درصد یا مبلغ سود مربوط به آن است. category باید دقیقاً همان نامی باشد که در «محصولات ← دسته‌بندی‌ها» نوشته شده (مثلاً «لپ‌تاپ استوک») یا "any" — ردیف‌های دستهٔ خاص قبل از "any" بررسی می‌شوند. max خالی (null) یعنی بدون سقف.', 'stocksystem' ); ?>
	</p>
	<p class="form-field">
		<textarea
			name="stocksystem_supplier[pricing_rules]"
			rows="8"
			style="width:94%; margin:0 3%; font-family:monospace"
			placeholder='[{"category":"any","min":0,"max":null,"type":"percent","value":15}]'
		><?php echo esc_textarea( $value ); ?></textarea>
	</p>
	<?php
}

function stocksystem_supplier_csv_map_box( $post ) {
	$value = get_post_meta( $post->ID, '_csv_column_map', true );
	?>
	<p style="color:#666">
		<?php esc_html_e( 'فقط برای فایل‌های CSV این تامین‌کننده لازم است، و فقط اگر ستون‌های آن با نام‌های رایج (model، price، …) تشخیص داده نشوند. مقدار هر کلید باید دقیقاً همان متن هدر ستون در فایل CSV این تامین‌کننده باشد.', 'stocksystem' ); ?>
	</p>
	<p class="form-field">
		<textarea
			name="stocksystem_supplier[csv_column_map]"
			rows="4"
			style="width:94%; margin:0 3%; font-family:monospace"
			placeholder='{"model": "Item Name", "price": "Cost"}'
		><?php echo esc_textarea( $value ); ?></textarea>
	</p>
	<?php
}

function stocksystem_save_supplier_meta( $post_id ) {
	if ( ! isset( $_POST['stocksystem_supplier_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_supplier_nonce'] ) ), 'stocksystem_supplier_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'stocksystem_supplier' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$raw = isset( $_POST['stocksystem_supplier'] ) ? wp_unslash( $_POST['stocksystem_supplier'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	update_post_meta( $post_id, '_phone', sanitize_text_field( isset( $raw['phone'] ) ? $raw['phone'] : '' ) );
	update_post_meta( $post_id, '_notes', sanitize_textarea_field( isset( $raw['notes'] ) ? $raw['notes'] : '' ) );
	update_post_meta( $post_id, '_active', empty( $raw['active'] ) ? '0' : '1' );

	$decoded = isset( $raw['pricing_rules'] ) ? json_decode( $raw['pricing_rules'], true ) : null;
	update_post_meta( $post_id, '_pricing_rules', is_array( $decoded ) ? wp_json_encode( $decoded, JSON_UNESCAPED_UNICODE ) : '' );

	$csv_map = isset( $raw['csv_column_map'] ) ? json_decode( $raw['csv_column_map'], true ) : null;
	update_post_meta( $post_id, '_csv_column_map', is_array( $csv_map ) ? wp_json_encode( $csv_map, JSON_UNESCAPED_UNICODE ) : '' );
}
add_action( 'save_post', 'stocksystem_save_supplier_meta' );

/* -------------------------------------------------------------------------
 * Data helpers
 * ---------------------------------------------------------------------- */

/** Decoded CSV column-map override for a supplier: [ field => exact header text ], or []. */
function stocksystem_supplier_csv_column_map( $supplier_id ) {
	$raw = get_post_meta( $supplier_id, '_csv_column_map', true );
	if ( ! $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );

	return is_array( $decoded ) ? $decoded : array();
}

/** Decoded pricing rules for a supplier: [ [ category, min, max, type, value ], … ]. */
function stocksystem_supplier_pricing_rules( $supplier_id ) {
	$raw = get_post_meta( $supplier_id, '_pricing_rules', true );
	if ( ! $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );

	return is_array( $decoded ) ? $decoded : array();
}

/** Active suppliers as [ post_id => title ], for <select> dropdowns. */
function stocksystem_active_suppliers() {
	$posts = get_posts(
		array(
			'post_type'      => 'stocksystem_supplier',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_active',
					'value'   => '0',
					'compare' => '!=',
				),
			),
		)
	);

	return wp_list_pluck( $posts, 'post_title', 'ID' );
}

/**
 * Suggested retail price for one supplier quote on one product, from that
 * supplier's tiered markup rules — category-specific tiers are checked
 * before the "any" fallback. Returns null when nothing matches (never
 * guesses a margin).
 *
 * @param int   $supplier_id    Supplier post ID.
 * @param float $supplier_price The price that supplier quoted.
 * @param int   $product_id     Product this quote is for (its categories decide which tier applies).
 */
function stocksystem_suggested_price( $supplier_id, $supplier_price, $product_id ) {
	$rules = stocksystem_supplier_pricing_rules( $supplier_id );
	if ( empty( $rules ) ) {
		return null;
	}

	// Matched by category NAME, not slug: Persian category slugs are
	// percent-encoded in the database (e.g. "%d9%84%d9%be...") — nobody
	// could type that by hand into the pricing-rule JSON below, but the
	// name is exactly what the owner already sees under Products ← Categories.
	$categories   = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
	$categories   = is_wp_error( $categories ) ? array() : $categories;
	$categories[] = 'any';

	foreach ( $categories as $category ) {
		foreach ( $rules as $rule ) {
			if ( ! isset( $rule['category'], $rule['min'], $rule['type'], $rule['value'] ) || $rule['category'] !== $category ) {
				continue;
			}

			$max = isset( $rule['max'] ) ? $rule['max'] : null;
			if ( $supplier_price < (float) $rule['min'] || ( null !== $max && $supplier_price > (float) $max ) ) {
				continue;
			}

			return 'percent' === $rule['type']
				? $supplier_price * ( 1 + (float) $rule['value'] / 100 )
				: $supplier_price + (float) $rule['value'];
		}
	}

	return null;
}
