<?php
/**
 * Supplier price quotes: a running history — not just the latest number —
 * of what each supplier has offered for each product, so "who is cheapest
 * right now" is a query instead of something the owner has to remember.
 * Lives in its own table (an append-only log across every supplier and
 * product over time isn't a fit for postmeta).
 *
 * Surfaced in two places, both reading the same helper: the product edit
 * screen ("تامین‌کنندگان" tab, next to the add-ons/test-report tabs) and
 * the order edit screen (per line item — the moment the owner actually
 * needs to decide who to buy from).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Table
 * ---------------------------------------------------------------------- */

function stocksystem_supplier_quotes_table() {
	global $wpdb;
	return $wpdb->prefix . 'stocksystem_supplier_quotes';
}

function stocksystem_create_supplier_quotes_table() {
	global $wpdb;

	$table_name      = stocksystem_supplier_quotes_table();
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE {$table_name} (
		id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
		product_id BIGINT UNSIGNED NOT NULL,
		supplier_id BIGINT UNSIGNED NOT NULL,
		price BIGINT UNSIGNED NOT NULL,
		source VARCHAR(20) NOT NULL DEFAULT 'manual',
		note TEXT NULL,
		created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
		quoted_at DATETIME NOT NULL,
		PRIMARY KEY  (id),
		KEY product_id (product_id),
		KEY supplier_id (supplier_id)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'stocksystem_create_supplier_quotes_table' );

/* -------------------------------------------------------------------------
 * Read / write
 * ---------------------------------------------------------------------- */

function stocksystem_record_supplier_quote( $product_id, $supplier_id, $price, $source = 'manual', $note = '' ) {
	global $wpdb;

	return $wpdb->insert(
		stocksystem_supplier_quotes_table(),
		array(
			'product_id'  => absint( $product_id ),
			'supplier_id' => absint( $supplier_id ),
			'price'       => absint( $price ),
			'source'      => sanitize_key( $source ),
			'note'        => sanitize_text_field( $note ),
			'created_by'  => get_current_user_id(),
			'quoted_at'   => current_time( 'mysql' ),
		),
		array( '%d', '%d', '%d', '%s', '%s', '%d', '%s' )
	);
}

/**
 * One row per supplier for this product — each supplier's most recent
 * quote — sorted cheapest first. This is the "who do I buy from right
 * now" answer.
 */
function stocksystem_get_latest_quote_per_supplier( $product_id ) {
	global $wpdb;
	$table = stocksystem_supplier_quotes_table();

	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT q.*
			FROM {$table} q
			INNER JOIN (
				SELECT supplier_id, MAX(quoted_at) AS max_quoted_at
				FROM {$table}
				WHERE product_id = %d
				GROUP BY supplier_id
			) latest ON latest.supplier_id = q.supplier_id AND latest.max_quoted_at = q.quoted_at
			WHERE q.product_id = %d
			ORDER BY q.price ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is our own constant name.
			$product_id,
			$product_id
		)
	);

	return $rows ? $rows : array();
}

/** Full price history for a product, newest first (all suppliers). */
function stocksystem_get_supplier_quotes_for_product( $product_id ) {
	global $wpdb;
	$table = stocksystem_supplier_quotes_table();

	$rows = $wpdb->get_results(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE product_id = %d ORDER BY quoted_at DESC", $product_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table is our own constant name.
	);

	return $rows ? $rows : array();
}

/* -------------------------------------------------------------------------
 * Product edit screen: "تامین‌کنندگان" tab
 * ---------------------------------------------------------------------- */

function stocksystem_supplier_quotes_product_tab( $tabs ) {
	$tabs['stocksystem_suppliers'] = array(
		'label'    => __( 'تامین‌کنندگان', 'stocksystem' ),
		'target'   => 'stocksystem_suppliers_data',
		'class'    => array( 'show_if_simple', 'show_if_variable' ),
		'priority' => 27,
	);
	return $tabs;
}
add_filter( 'woocommerce_product_data_tabs', 'stocksystem_supplier_quotes_product_tab' );

function stocksystem_supplier_quotes_product_panel() {
	global $post;
	$quotes    = stocksystem_get_latest_quote_per_supplier( $post->ID );
	$suppliers = stocksystem_active_suppliers();
	?>
	<div id="stocksystem_suppliers_data" class="panel woocommerce_options_panel">
		<div class="options_group" style="padding:12px">
			<?php if ( $quotes ) : ?>
				<table class="widefat striped" style="margin-bottom:12px">
					<thead>
						<tr>
							<th><?php esc_html_e( 'تامین‌کننده', 'stocksystem' ); ?></th>
							<th><?php esc_html_e( 'آخرین قیمت', 'stocksystem' ); ?></th>
							<th><?php esc_html_e( 'قیمت پیشنهادی فروش', 'stocksystem' ); ?></th>
							<th><?php esc_html_e( 'تاریخ', 'stocksystem' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $quotes as $quote ) : ?>
							<?php $suggested = stocksystem_suggested_price( $quote->supplier_id, $quote->price, $post->ID ); ?>
							<tr>
								<td><?php echo esc_html( get_the_title( $quote->supplier_id ) ); ?></td>
								<td><?php echo esc_html( number_format( $quote->price ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></td>
								<td><?php echo null === $suggested ? '—' : esc_html( number_format( $suggested ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></td>
								<td><?php echo esc_html( mysql2date( 'Y/m/d', $quote->quoted_at ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'هنوز قیمتی از هیچ تامین‌کننده‌ای برای این کالا ثبت نشده.', 'stocksystem' ); ?></p>
			<?php endif; ?>

			<?php if ( $suppliers ) : ?>
				<p style="font-weight:600"><?php esc_html_e( 'ثبت قیمت جدید', 'stocksystem' ); ?></p>
				<p class="form-field">
					<label><?php esc_html_e( 'تامین‌کننده', 'stocksystem' ); ?></label>
					<select name="stocksystem_new_quote[supplier_id]">
						<option value=""><?php esc_html_e( '— انتخاب کنید —', 'stocksystem' ); ?></option>
						<?php foreach ( $suppliers as $id => $title ) : ?>
							<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></option>
						<?php endforeach; ?>
					</select>
				</p>
				<p class="form-field">
					<label><?php esc_html_e( 'قیمت (تومان)', 'stocksystem' ); ?></label>
					<input type="number" name="stocksystem_new_quote[price]" min="0" step="1000">
				</p>
				<p class="form-field">
					<label><?php esc_html_e( 'یادداشت', 'stocksystem' ); ?></label>
					<input type="text" class="widefat" name="stocksystem_new_quote[note]">
				</p>
				<p class="description"><?php esc_html_e( 'با ذخیرهٔ محصول، این قیمت هم ثبت می‌شود.', 'stocksystem' ); ?></p>
			<?php else : ?>
				<p><?php esc_html_e( 'اول از «استوک سیستم ← تامین‌کنندگان» یک تامین‌کننده بسازید.', 'stocksystem' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}
add_action( 'woocommerce_product_data_panels', 'stocksystem_supplier_quotes_product_panel' );

function stocksystem_save_new_supplier_quote( $post_id ) {
	if ( empty( $_POST['stocksystem_new_quote'] ) || ! is_array( $_POST['stocksystem_new_quote'] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST['stocksystem_new_quote'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

	if ( empty( $raw['supplier_id'] ) || ! isset( $raw['price'] ) || '' === $raw['price'] ) {
		return;
	}

	stocksystem_record_supplier_quote(
		$post_id,
		absint( $raw['supplier_id'] ),
		absint( $raw['price'] ),
		'manual',
		isset( $raw['note'] ) ? sanitize_text_field( $raw['note'] ) : ''
	);
}
add_action( 'woocommerce_process_product_meta', 'stocksystem_save_new_supplier_quote' );

/* -------------------------------------------------------------------------
 * Order edit screen: cheapest supplier per line item
 * ---------------------------------------------------------------------- */

function stocksystem_supplier_quotes_add_order_meta_box() {
	// No wc_get_page_screen_id() in this WC version (verified against the
	// local install, WC 11.1.0) — Automattic\WooCommerce\Utilities\OrderUtil
	// is the real, documented HPOS-aware helper for this.
	add_meta_box(
		'stocksystem_order_suppliers',
		__( 'تامین‌کننده برای خرید', 'stocksystem' ),
		'stocksystem_supplier_quotes_order_box',
		\Automattic\WooCommerce\Utilities\OrderUtil::get_order_admin_screen(),
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'stocksystem_supplier_quotes_add_order_meta_box' );

/**
 * WordPress passes the $post object on the legacy post-based orders
 * screen, but WooCommerce passes the $order object directly on the HPOS
 * ("High-Performance Order Storage") screen — handle both.
 */
function stocksystem_supplier_quotes_order_box( $post_or_order ) {
	$order = ( $post_or_order instanceof WP_Post ) ? wc_get_order( $post_or_order->ID ) : $post_or_order;
	if ( ! $order ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( ! $product ) {
			continue;
		}

		$quotes = stocksystem_get_latest_quote_per_supplier( $product->get_id() );
		?>
		<p style="font-weight:600; margin-bottom:4px"><?php echo esc_html( $product->get_name() ); ?></p>
		<?php if ( $quotes ) : ?>
			<table class="widefat striped" style="margin-bottom:16px">
				<thead>
					<tr>
						<th><?php esc_html_e( 'تامین‌کننده', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'آخرین قیمت', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'تاریخ', 'stocksystem' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $quotes as $i => $quote ) : ?>
						<tr<?php echo 0 === $i ? ' style="font-weight:600"' : ''; ?>>
							<td><?php echo esc_html( get_the_title( $quote->supplier_id ) . ( 0 === $i ? ' — ' . __( 'ارزان‌ترین', 'stocksystem' ) : '' ) ); ?></td>
							<td><?php echo esc_html( number_format( $quote->price ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></td>
							<td><?php echo esc_html( mysql2date( 'Y/m/d', $quote->quoted_at ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p style="margin-bottom:16px; color:#666"><?php esc_html_e( 'قیمتی از هیچ تامین‌کننده‌ای ثبت نشده.', 'stocksystem' ); ?></p>
		<?php endif; ?>
		<?php
	}
}
