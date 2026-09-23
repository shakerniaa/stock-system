<?php
/**
 * Import batches: one uploaded supplier price-list file (image or CSV) →
 * AI-extracted rows (inc/ai-extraction.php) → owner review/edit → confirmed
 * rows become draft WooCommerce products with their first supplier quote
 * recorded (phase 1's stocksystem_record_supplier_quote()).
 *
 * The CPT's own edit screen doubles as both the upload form (empty state)
 * and the review screen (needs_review state) — one meta box that renders
 * differently per _status, same "reuse core screens" approach as
 * inc/suppliers.php instead of a bespoke admin page.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Post type
 * ---------------------------------------------------------------------- */

function stocksystem_register_import_post_type() {
	register_post_type(
		'stocksystem_import',
		array(
			'label'           => __( 'ایمپورت لیست', 'stocksystem' ),
			'labels'          => array(
				'name'          => __( 'ایمپورت لیست', 'stocksystem' ),
				'singular_name' => __( 'ایمپورت', 'stocksystem' ),
				'add_new_item'  => __( 'ایمپورت لیست جدید', 'stocksystem' ),
				'edit_item'     => __( 'بررسی ایمپورت', 'stocksystem' ),
				'search_items'  => __( 'جستجوی ایمپورت', 'stocksystem' ),
				'not_found'     => __( 'ایمپورتی یافت نشد', 'stocksystem' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => 'stocksystem',
			'supports'        => array( 'title' ),
			'capability_type' => 'page',
			'map_meta_cap'    => true,
			// Same admin-only lockdown as inc/suppliers.php — extracted
			// rows include supplier purchase prices. Deliberately NOT
			// overriding edit_post/read_post/delete_post here — see the
			// long comment in inc/suppliers.php's capabilities array for
			// why that broke current_user_can('manage_options') site-wide
			// once two post types here both did it.
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
add_action( 'init', 'stocksystem_register_import_post_type' );

/* -------------------------------------------------------------------------
 * List table: a status column
 * ---------------------------------------------------------------------- */

function stocksystem_import_status_labels() {
	return array(
		'pending'      => __( 'در صف پردازش', 'stocksystem' ),
		'processing'   => __( 'در حال پردازش', 'stocksystem' ),
		'needs_review' => __( 'آمادهٔ بررسی', 'stocksystem' ),
		'done'         => __( 'انجام‌شده', 'stocksystem' ),
		'failed'       => __( 'خطا', 'stocksystem' ),
	);
}

function stocksystem_import_columns( $columns ) {
	$columns['stocksystem_status'] = __( 'وضعیت', 'stocksystem' );
	return $columns;
}
add_filter( 'manage_stocksystem_import_posts_columns', 'stocksystem_import_columns' );

function stocksystem_import_column_content( $column, $post_id ) {
	if ( 'stocksystem_status' !== $column ) {
		return;
	}

	$labels = stocksystem_import_status_labels();
	$status = get_post_meta( $post_id, '_status', true );
	echo esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : '—' );
}
add_action( 'manage_stocksystem_import_posts_custom_column', 'stocksystem_import_column_content', 10, 2 );

/* -------------------------------------------------------------------------
 * Meta box: upload / status / review / done / failed, per _status
 * ---------------------------------------------------------------------- */

function stocksystem_import_add_meta_box() {
	add_meta_box( 'stocksystem_import_box', __( 'ایمپورت', 'stocksystem' ), 'stocksystem_import_box', 'stocksystem_import', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'stocksystem_import_add_meta_box' );

function stocksystem_import_box( $post ) {
	wp_nonce_field( 'stocksystem_batch_save', 'stocksystem_batch_nonce' );

	$status = get_post_meta( $post->ID, '_status', true );

	if ( ! $status ) {
		stocksystem_import_render_upload_form();
		return;
	}

	if ( in_array( $status, array( 'pending', 'processing' ), true ) ) {
		echo '<p>' . esc_html__( 'در صف پردازش پس‌زمینه است — این صفحه را دوباره باز کنید (چند ثانیه تا چند دقیقه، بسته به هاست).', 'stocksystem' ) . '</p>';
		return;
	}

	if ( 'needs_review' === $status ) {
		stocksystem_import_render_review( $post );
		return;
	}

	if ( 'done' === $status ) {
		stocksystem_import_render_done( $post );
		return;
	}

	if ( 'failed' === $status ) {
		$error = get_post_meta( $post->ID, '_error', true );
		echo '<p style="color:#C8481A">' . esc_html( $error ? $error : __( 'خطای نامشخص.', 'stocksystem' ) ) . '</p>';
		echo '<p><label><input type="checkbox" name="stocksystem_retry_batch" value="1"> ' . esc_html__( 'تلاش مجدد پس از ذخیره', 'stocksystem' ) . '</label></p>';
	}
}

function stocksystem_import_render_upload_form() {
	$suppliers = stocksystem_active_suppliers();
	?>
	<p class="form-field">
		<label><?php esc_html_e( 'تامین‌کننده', 'stocksystem' ); ?></label><br>
		<select name="stocksystem_import_supplier" required>
			<option value=""><?php esc_html_e( '— انتخاب کنید —', 'stocksystem' ); ?></option>
			<?php foreach ( $suppliers as $id => $title ) : ?>
				<option value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $title ); ?></option>
			<?php endforeach; ?>
		</select>
	</p>
	<p class="form-field">
		<label><?php esc_html_e( 'فایل لیست (عکس/اسکرین‌شات یا CSV)', 'stocksystem' ); ?></label><br>
		<input type="file" name="stocksystem_import_file" accept="image/*,.csv" required>
	</p>
	<p class="description"><?php esc_html_e( 'با «انتشار»، فایل در پس‌زمینه پردازش می‌شود؛ نتیجه بعداً همین‌جا برای تأیید نشان داده می‌شود.', 'stocksystem' ); ?></p>
	<?php
}

function stocksystem_import_render_review( $post ) {
	$rows = json_decode( get_post_meta( $post->ID, '_extracted_rows', true ), true );
	if ( ! is_array( $rows ) ) {
		$rows = array();
	}
	?>
	<p><?php esc_html_e( 'ردیف‌های استخراج‌شده — قبل از تأیید ویرایش کنید. ردیف‌های با علامت هشدار قیمت پیش‌فرض غیرفعال‌اند.', 'stocksystem' ); ?></p>
	<table class="widefat striped">
		<thead>
			<tr>
				<th><?php esc_html_e( 'وارد کن', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'برند', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'مدل', 'stocksystem' ); ?></th>
				<th>CPU</th>
				<th><?php esc_html_e( 'رم (GB)', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'حافظه (GB)', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'دسته', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'قیمت (تومان)', 'stocksystem' ); ?></th>
				<th><?php esc_html_e( 'محصول مشابه', 'stocksystem' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $rows as $i => $row ) : ?>
				<?php $low_confidence = isset( $row['price_confidence'] ) && 'low' === $row['price_confidence']; ?>
				<tr>
					<td><input type="checkbox" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][include]" value="1" <?php checked( isset( $row['include'] ) ? ! empty( $row['include'] ) : ! $low_confidence ); ?>></td>
					<td><input type="text" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][brand]" value="<?php echo esc_attr( $row['brand'] ?? '' ); ?>" style="width:90px"></td>
					<td><input type="text" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][model]" value="<?php echo esc_attr( $row['model'] ?? '' ); ?>" style="width:160px"></td>
					<td><input type="text" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][cpu]" value="<?php echo esc_attr( $row['cpu'] ?? '' ); ?>" style="width:110px"></td>
					<td><input type="number" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][ram_gb]" value="<?php echo esc_attr( $row['ram_gb'] ?? '' ); ?>" style="width:60px"></td>
					<td><input type="number" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][storage_gb]" value="<?php echo esc_attr( $row['storage_gb'] ?? '' ); ?>" style="width:70px"></td>
					<td><input type="text" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][category_guess]" value="<?php echo esc_attr( $row['category_guess'] ?? '' ); ?>" style="width:90px"></td>
					<td>
						<input type="number" name="stocksystem_batch_rows[<?php echo (int) $i; ?>][price_toman]" value="<?php echo esc_attr( $row['price_toman'] ?? '' ); ?>" style="width:110px">
						<?php if ( ! empty( $row['price_raw'] ) ) : ?>
							<br><small><?php esc_html_e( 'خام:', 'stocksystem' ); ?> <?php echo esc_html( $row['price_raw'] ); ?></small>
						<?php endif; ?>
						<?php if ( $low_confidence ) : ?>
							<br><small style="color:#C8481A"><?php echo esc_html( $row['price_note'] ? $row['price_note'] : __( 'نامطمئن — بررسی کنید', 'stocksystem' ) ); ?></small>
						<?php endif; ?>
					</td>
					<td>
						<?php $matches = is_array( $row['matched_products'] ?? null ) ? $row['matched_products'] : array(); ?>
						<?php if ( $matches ) : ?>
							<select name="stocksystem_batch_rows[<?php echo (int) $i; ?>][target_product_id]" style="width:170px">
								<option value="0"><?php esc_html_e( 'محصول جدید بساز', 'stocksystem' ); ?></option>
								<?php foreach ( $matches as $match ) : ?>
									<option value="<?php echo esc_attr( $match['product_id'] ); ?>" <?php selected( (int) ( $row['target_product_id'] ?? 0 ), $match['product_id'] ); ?>>
										<?php echo esc_html( sprintf( '%s (%d%%)', $match['title'], round( $match['score'] * 100 ) ) ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<span>-</span>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p style="margin-top:12px">
		<button type="submit" name="stocksystem_confirm_batch" value="1" class="button button-primary"><?php esc_html_e( 'تأیید و ساخت پیش‌نویس‌ها', 'stocksystem' ); ?></button>
		<?php esc_html_e( '(ویرایش‌ها با این دکمه هم ذخیره می‌شوند)', 'stocksystem' ); ?>
	</p>
	<?php
}

function stocksystem_import_render_done( $post ) {
	$created = get_post_meta( $post->ID, '_created_product_ids', true );
	$created = is_array( $created ) ? $created : array();
	$updated = get_post_meta( $post->ID, '_updated_product_ids', true );
	$updated = is_array( $updated ) ? $updated : array();

	printf(
		/* translators: %d: number of drafts created */
		'<p>' . esc_html__( '%d پیش‌نویس ساخته شد:', 'stocksystem' ) . '</p>',
		count( $created )
	);
	echo '<ul>';
	foreach ( $created as $product_id ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( (string) get_edit_post_link( $product_id ) ), esc_html( get_the_title( $product_id ) ) );
	}
	echo '</ul>';

	if ( $updated ) {
		printf(
			/* translators: %d: number of existing products a quote was added to */
			'<p>' . esc_html__( '%d محصول موجود قیمت جدید گرفت (به‌جای ساخت محصول تکراری):', 'stocksystem' ) . '</p>',
			count( $updated )
		);
		echo '<ul>';
		foreach ( $updated as $product_id ) {
			printf( '<li><a href="%s">%s</a></li>', esc_url( (string) get_edit_post_link( $product_id ) ), esc_html( get_the_title( $product_id ) ) );
		}
		echo '</ul>';
	}
}

/* -------------------------------------------------------------------------
 * Save: upload, or row edits + optional confirm, or retry
 * ---------------------------------------------------------------------- */

function stocksystem_save_import_batch( $post_id ) {
	if ( ! isset( $_POST['stocksystem_batch_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_batch_nonce'] ) ), 'stocksystem_batch_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'stocksystem_import' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$status = get_post_meta( $post_id, '_status', true );

	if ( ! $status && ! empty( $_FILES['stocksystem_import_file']['name'] ) && ! empty( $_POST['stocksystem_import_supplier'] ) ) {
		stocksystem_handle_batch_upload( $post_id );
		return;
	}

	if ( 'needs_review' === $status && ! empty( $_POST['stocksystem_batch_rows'] ) && is_array( $_POST['stocksystem_batch_rows'] ) ) {
		stocksystem_save_batch_row_edits( $post_id, wp_unslash( $_POST['stocksystem_batch_rows'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

		if ( ! empty( $_POST['stocksystem_confirm_batch'] ) ) {
			stocksystem_confirm_import_batch( $post_id );
		}
		return;
	}

	if ( 'failed' === $status && ! empty( $_POST['stocksystem_retry_batch'] ) ) {
		update_post_meta( $post_id, '_status', 'pending' );
		delete_post_meta( $post_id, '_error' );
		as_enqueue_async_action( 'stocksystem_process_import_batch', array( 'batch_id' => $post_id ) );
	}
}
add_action( 'save_post', 'stocksystem_save_import_batch' );

function stocksystem_handle_batch_upload( $post_id ) {
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attachment_id = media_handle_upload( 'stocksystem_import_file', $post_id );
	if ( is_wp_error( $attachment_id ) ) {
		update_post_meta( $post_id, '_status', 'failed' );
		update_post_meta( $post_id, '_error', $attachment_id->get_error_message() );
		return;
	}

	$supplier_id = absint( $_POST['stocksystem_import_supplier'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification.Missing -- nonce already verified in stocksystem_save_import_batch().

	update_post_meta( $post_id, '_supplier_id', $supplier_id );
	update_post_meta( $post_id, '_attachment_id', $attachment_id );
	update_post_meta( $post_id, '_status', 'pending' );

	// Auto-name the batch instead of asking the owner to type a title —
	// remove/re-add our own save_post hook to avoid infinite recursion.
	remove_action( 'save_post', 'stocksystem_save_import_batch' );
	wp_update_post(
		array(
			'ID'         => $post_id,
			'post_title' => sprintf( '%s — %s', get_the_title( $supplier_id ), current_time( 'Y/m/d H:i' ) ),
		)
	);
	add_action( 'save_post', 'stocksystem_save_import_batch' );

	as_enqueue_async_action( 'stocksystem_process_import_batch', array( 'batch_id' => $post_id ) );
}

function stocksystem_save_batch_row_edits( $post_id, $posted_rows ) {
	$existing = json_decode( get_post_meta( $post_id, '_extracted_rows', true ), true );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}

	foreach ( $posted_rows as $i => $edited ) {
		if ( ! isset( $existing[ $i ] ) ) {
			continue;
		}

		foreach ( array( 'brand', 'model', 'cpu', 'category_guess' ) as $field ) {
			if ( isset( $edited[ $field ] ) ) {
				$existing[ $i ][ $field ] = sanitize_text_field( $edited[ $field ] );
			}
		}
		foreach ( array( 'ram_gb', 'storage_gb', 'price_toman' ) as $field ) {
			if ( isset( $edited[ $field ] ) && '' !== $edited[ $field ] ) {
				$existing[ $i ][ $field ] = (float) $edited[ $field ];
			}
		}
		$existing[ $i ]['include']           = ! empty( $edited['include'] );
		$existing[ $i ]['target_product_id'] = isset( $edited['target_product_id'] ) ? absint( $edited['target_product_id'] ) : 0;
	}

	update_post_meta( $post_id, '_extracted_rows', wp_json_encode( $existing, JSON_UNESCAPED_UNICODE ) );
}

/* -------------------------------------------------------------------------
 * Background processing (Action Scheduler — bundled with WooCommerce)
 * ---------------------------------------------------------------------- */

function stocksystem_run_import_batch( $batch_id ) {
	update_post_meta( $batch_id, '_status', 'processing' );

	$attachment_id = (int) get_post_meta( $batch_id, '_attachment_id', true );
	$supplier_id   = (int) get_post_meta( $batch_id, '_supplier_id', true );
	$rows          = stocksystem_ai_extract_rows_from_attachment( $attachment_id, $supplier_id );

	if ( is_wp_error( $rows ) ) {
		update_post_meta( $batch_id, '_status', 'failed' );
		update_post_meta( $batch_id, '_error', $rows->get_error_message() );
		return;
	}

	foreach ( $rows as &$row ) {
		// Low-confidence prices (the scale-ambiguity case) start unchecked
		// in the review table — an explicit opt-in, not a silent guess.
		$row['include']          = 'low' !== $row['price_confidence'];
		$row['matched_products'] = stocksystem_find_similar_products( trim( ( $row['brand'] ?? '' ) . ' ' . ( $row['model'] ?? '' ) ) );
		$row['target_product_id'] = 0;
	}
	unset( $row );

	update_post_meta( $batch_id, '_extracted_rows', wp_json_encode( $rows, JSON_UNESCAPED_UNICODE ) );
	update_post_meta( $batch_id, '_status', 'needs_review' );
}
add_action( 'stocksystem_process_import_batch', 'stocksystem_run_import_batch' );

/* -------------------------------------------------------------------------
 * Confirm: create draft products from the checked rows
 * ---------------------------------------------------------------------- */

function stocksystem_confirm_import_batch( $post_id ) {
	$rows        = json_decode( get_post_meta( $post_id, '_extracted_rows', true ), true );
	$supplier_id = (int) get_post_meta( $post_id, '_supplier_id', true );
	$created     = array();
	$updated     = array();

	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			if ( empty( $row['include'] ) ) {
				continue;
			}

			$target_product_id = (int) ( $row['target_product_id'] ?? 0 );
			if ( $target_product_id > 0 ) {
				if ( stocksystem_add_quote_to_existing_product( $row, $supplier_id, $target_product_id ) ) {
					$updated[] = $target_product_id;
				}
				continue;
			}

			$product_id = stocksystem_create_product_from_extracted_row( $row, $supplier_id );
			if ( $product_id ) {
				$created[] = $product_id;
			}
		}
	}

	update_post_meta( $post_id, '_created_product_ids', $created );
	update_post_meta( $post_id, '_updated_product_ids', $updated );
	update_post_meta( $post_id, '_status', 'done' );
}

/**
 * "This row is actually a supplier already listed" path — logs the quote
 * (phase 1) on the existing product instead of creating a duplicate.
 * Deliberately does NOT fire stocksystem_product_imported: an existing
 * product likely already has real content, and auto-regenerating its SEO
 * text without being asked would silently overwrite the owner's own edits.
 */
function stocksystem_add_quote_to_existing_product( $row, $supplier_id, $product_id ) {
	if ( 'product' !== get_post_type( $product_id ) || empty( $row['price_toman'] ) ) {
		return false;
	}

	stocksystem_record_supplier_quote( $product_id, $supplier_id, $row['price_toman'], 'import', $row['price_note'] ?? '' );

	return true;
}

/* -------------------------------------------------------------------------
 * Similar-product matching — prevents the same model being imported twice
 * as two separate products just because two suppliers both offer it.
 * ---------------------------------------------------------------------- */

/**
 * Existing products whose title overlaps enough with $text to be worth
 * flagging as "maybe this is the same item" — never auto-merges, only
 * suggests, in the review table. No exact-match requirement: catches
 * near-duplicates (a supplier writing "840" where the catalog has "845")
 * too, which is exactly the case the owner should eyeball, not skip.
 */
function stocksystem_find_similar_products( $text, $limit = 3 ) {
	$tokens = stocksystem_tokenize_for_matching( $text );
	if ( empty( $tokens ) ) {
		return array();
	}

	$candidates = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 50,
			// Seed the search with the most distinctive words rather than
			// the whole string — WordPress's 's' does a broad LIKE match,
			// so a shorter, more specific seed finds better candidates.
			's'              => implode( ' ', array_slice( $tokens, 0, 3 ) ),
			'fields'         => 'ids',
		)
	);

	$scored = array();
	foreach ( $candidates as $candidate_id ) {
		$overlap = stocksystem_token_overlap( $tokens, stocksystem_tokenize_for_matching( get_the_title( $candidate_id ) ) );
		if ( $overlap >= 0.5 ) {
			$scored[] = array(
				'product_id' => $candidate_id,
				'title'      => get_the_title( $candidate_id ),
				'score'      => $overlap,
			);
		}
	}

	usort( $scored, static fn( $a, $b ) => $b['score'] <=> $a['score'] );

	return array_slice( $scored, 0, $limit );
}

/** Lowercases, strips punctuation (Persian/Latin-aware), splits into words ≥2 chars. */
function stocksystem_tokenize_for_matching( $text ) {
	$text  = mb_strtolower( (string) $text );
	$text  = preg_replace( '/[^\p{L}\p{N}\s]/u', ' ', $text );
	$words = preg_split( '/\s+/u', trim( $text ) );

	return array_values( array_filter( $words, static fn( $w ) => mb_strlen( $w ) > 1 ) );
}

/** Jaccard similarity (intersection / union) between two token lists. */
function stocksystem_token_overlap( $a, $b ) {
	if ( empty( $a ) || empty( $b ) ) {
		return 0;
	}

	$union = array_unique( array_merge( $a, $b ) );

	return count( array_intersect( $a, $b ) ) / count( $union );
}

function stocksystem_create_product_from_extracted_row( $row, $supplier_id ) {
	$title = trim( ( $row['brand'] ?? '' ) . ' ' . ( $row['model'] ?? '' ) );
	if ( '' === $title ) {
		return 0;
	}

	$product_id = wp_insert_post(
		array(
			'post_type'    => 'product',
			'post_title'   => $title,
			'post_status'  => 'draft',
			'post_content' => stocksystem_build_row_description( $row ),
		)
	);
	if ( is_wp_error( $product_id ) || ! $product_id ) {
		return 0;
	}

	wp_set_object_terms( $product_id, 'simple', 'product_type' );

	if ( ! empty( $row['category_guess'] ) ) {
		// Matched by name against existing categories only — never
		// auto-creates a new one; a curated taxonomy stays curated.
		$term = get_term_by( 'name', $row['category_guess'], 'product_cat' );
		if ( $term ) {
			wp_set_object_terms( $product_id, array( $term->term_id ), 'product_cat' );
		}
	}

	$ram_taxonomy     = stocksystem_find_attribute_taxonomy( array( 'ram', 'رم' ) );
	$storage_taxonomy = stocksystem_find_attribute_taxonomy( array( 'storage', 'ذخیره', 'حافظه' ) );

	if ( $ram_taxonomy ) {
		stocksystem_set_extracted_attribute( $product_id, $ram_taxonomy, $row['ram_gb'] ?? null, 'GB' );
	}
	if ( $storage_taxonomy ) {
		stocksystem_set_extracted_attribute( $product_id, $storage_taxonomy, $row['storage_gb'] ?? null, 'GB' );
	}

	if ( ! empty( $row['grade_reported'] ) ) {
		// Private note only — never written to the public product_grading
		// taxonomy. A supplier's own grade claim isn't the owner's
		// physical inspection (فاز ۱/۳ boundary).
		update_post_meta( $product_id, '_grade_reported_by_supplier', sanitize_text_field( $row['grade_reported'] ) );
	}

	if ( ! empty( $row['price_toman'] ) ) {
		stocksystem_record_supplier_quote( $product_id, $supplier_id, $row['price_toman'], 'import', $row['price_note'] ?? '' );

		$suggested = stocksystem_suggested_price( $supplier_id, $row['price_toman'], $product_id );
		if ( null !== $suggested ) {
			$suggested = (string) round( $suggested );
			update_post_meta( $product_id, '_regular_price', $suggested );
			update_post_meta( $product_id, '_price', $suggested );
		}
	}

	/**
	 * Generic "a product was imported" event — phase 2 doesn't need to
	 * know phase 3 (inc/ai-content.php) exists; it just fires this, and
	 * whatever's hooked to it (SEO content generation, today) runs.
	 */
	do_action( 'stocksystem_product_imported', $product_id );

	return $product_id;
}

function stocksystem_build_row_description( $row ) {
	$map = array(
		'cpu'       => __( 'پردازنده', 'stocksystem' ),
		'ram_gb'    => __( 'رم', 'stocksystem' ),
		'storage_gb' => __( 'حافظه', 'stocksystem' ),
		'gpu'       => __( 'گرافیک', 'stocksystem' ),
		'screen_in' => __( 'صفحه‌نمایش', 'stocksystem' ),
	);

	$parts = array();
	foreach ( $map as $field => $label ) {
		if ( ! empty( $row[ $field ] ) ) {
			$parts[] = sprintf( '%s: %s', $label, $row[ $field ] );
		}
	}

	return $parts ? '<p>' . esc_html( implode( ' | ', $parts ) ) . '</p>' : '';
}

/**
 * Resolves the real attribute taxonomy for "RAM" or "storage" by matching
 * the attribute's LABEL, not a hardcoded slug — verified against the local
 * site that its own storage attribute is labelled «ذخیره‌سازی» (Persian),
 * so a hardcoded `pa_storage` silently matches nothing there even though
 * `pa_ram` happens to exist. Returns null if the store hasn't defined a
 * matching attribute yet (never invents one — see stocksystem_set_extracted_attribute).
 */
function stocksystem_find_attribute_taxonomy( array $keywords ) {
	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$label = mb_strtolower( $attribute->attribute_label );
		foreach ( $keywords as $keyword ) {
			if ( false !== mb_strpos( $label, mb_strtolower( $keyword ) ) ) {
				return wc_attribute_taxonomy_name( $attribute->attribute_name );
			}
		}
	}
	return null;
}

/**
 * Finds an existing pa_* term by numeric value (e.g. 8 -> the term "8GB"),
 * or creates one — attribute values like RAM/storage are objective and
 * enumerable, unlike product categories, so auto-creating a genuinely new
 * one here is reasonable.
 */
function stocksystem_set_extracted_attribute( $product_id, $taxonomy, $value, $unit ) {
	if ( empty( $value ) || ! taxonomy_exists( $taxonomy ) ) {
		return;
	}

	$term = null;
	foreach ( get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) ) as $existing ) {
		if ( (int) preg_replace( '/\D/', '', $existing->name ) === (int) $value ) {
			$term = $existing;
			break;
		}
	}

	if ( ! $term ) {
		$inserted = wp_insert_term( $value . $unit, $taxonomy );
		if ( is_wp_error( $inserted ) ) {
			return;
		}
		$term = get_term( $inserted['term_id'], $taxonomy );
	}

	wp_set_object_terms( $product_id, array( $term->term_id ), $taxonomy );

	$attributes              = array_filter( (array) get_post_meta( $product_id, '_product_attributes', true ) );
	$attributes[ $taxonomy ] = array(
		'name'         => $taxonomy,
		'value'        => '',
		'is_visible'   => 1,
		'is_variation' => 0,
		'is_taxonomy'  => 1,
	);
	update_post_meta( $product_id, '_product_attributes', $attributes );
}
