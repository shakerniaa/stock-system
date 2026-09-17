<?php
/**
 * Term-meta admin fields for content that has nowhere else to live yet:
 * per-category FAQ (02 Category.dc.html "SEO text" block) and per-brand
 * intro stats (16-B). No repeater-field plugin has been chosen, so this
 * is a plain textarea/JSON field rather than a real admin UI — good
 * enough for the client to fill in once, worth replacing with ACF (or
 * similar) if the content team needs something friendlier later.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ---- product_cat: FAQ (JSON array of {question, answer}) ---- */

function stocksystem_product_cat_faq_field( $term ) {
	$value = is_object( $term ) ? get_term_meta( $term->term_id, 'faq_json', true ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label for="faq_json"><?php esc_html_e( 'پرسش‌های متداول (FAQ)', 'stocksystem' ); ?></label></th>
		<td>
			<textarea name="faq_json" id="faq_json" rows="6" style="width:100%; font-family:monospace" placeholder='[{"question":"...","answer":"..."}]'><?php echo esc_textarea( $value ); ?></textarea>
			<p class="description"><?php esc_html_e( 'آرایهٔ JSON: [{"question":"...","answer":"..."}]. اگر خالی بماند، بخش پرسش‌های متداول نمایش داده نمی‌شود.', 'stocksystem' ); ?></p>
		</td>
	</tr>
	<?php
}
add_action( 'product_cat_edit_form_fields', 'stocksystem_product_cat_faq_field' );

function stocksystem_product_cat_faq_field_add() {
	?>
	<div class="form-field">
		<label for="faq_json"><?php esc_html_e( 'پرسش‌های متداول (FAQ)', 'stocksystem' ); ?></label>
		<textarea name="faq_json" id="faq_json" rows="6" placeholder='[{"question":"...","answer":"..."}]'></textarea>
	</div>
	<?php
}
add_action( 'product_cat_add_form_fields', 'stocksystem_product_cat_faq_field_add' );

function stocksystem_save_product_cat_faq_field( $term_id ) {
	if ( ! isset( $_POST['faq_json'] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST['faq_json'] );

	// Validate as JSON before storing so a malformed edit can't wreck the
	// front-end render — store empty rather than garbage.
	$decoded = json_decode( $raw, true );
	update_term_meta( $term_id, 'faq_json', is_array( $decoded ) ? wp_json_encode( $decoded ) : '' );
}
add_action( 'edited_product_cat', 'stocksystem_save_product_cat_faq_field' );
add_action( 'created_product_cat', 'stocksystem_save_product_cat_faq_field' );

/**
 * Decoded FAQ items for a product_cat term, ready for
 * template-parts/global/faq-accordion.php's `items` arg.
 */
function stocksystem_product_cat_faq_items( $term_id ) {
	$raw = get_term_meta( $term_id, 'faq_json', true );
	if ( ! $raw ) {
		return array();
	}

	$decoded = json_decode( $raw, true );
	return is_array( $decoded ) ? $decoded : array();
}

/* ---- product_brand: intro stats ---- */

function stocksystem_product_brand_fields( $term ) {
	$battery = is_object( $term ) ? get_term_meta( $term->term_id, 'avg_battery_health', true ) : '';
	$return  = is_object( $term ) ? get_term_meta( $term->term_id, 'return_rate_percent', true ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label for="avg_battery_health"><?php esc_html_e( 'میانگین سلامت باتری (٪)', 'stocksystem' ); ?></label></th>
		<td><input type="number" name="avg_battery_health" id="avg_battery_health" value="<?php echo esc_attr( $battery ); ?>" min="0" max="100"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="return_rate_percent"><?php esc_html_e( 'نرخ مرجوعی (٪)', 'stocksystem' ); ?></label></th>
		<td><input type="number" name="return_rate_percent" id="return_rate_percent" value="<?php echo esc_attr( $return ); ?>" min="0" max="100" step="0.1"></td>
	</tr>
	<?php
}
add_action( 'product_brand_edit_form_fields', 'stocksystem_product_brand_fields' );

function stocksystem_product_brand_fields_add() {
	?>
	<div class="form-field">
		<label for="avg_battery_health"><?php esc_html_e( 'میانگین سلامت باتری (٪)', 'stocksystem' ); ?></label>
		<input type="number" name="avg_battery_health" id="avg_battery_health" min="0" max="100">
	</div>
	<div class="form-field">
		<label for="return_rate_percent"><?php esc_html_e( 'نرخ مرجوعی (٪)', 'stocksystem' ); ?></label>
		<input type="number" name="return_rate_percent" id="return_rate_percent" min="0" max="100" step="0.1">
	</div>
	<?php
}
add_action( 'product_brand_add_form_fields', 'stocksystem_product_brand_fields_add' );

function stocksystem_save_product_brand_fields( $term_id ) {
	if ( isset( $_POST['avg_battery_health'] ) && '' !== $_POST['avg_battery_health'] ) {
		update_term_meta( $term_id, 'avg_battery_health', (int) $_POST['avg_battery_health'] );
	}
	if ( isset( $_POST['return_rate_percent'] ) && '' !== $_POST['return_rate_percent'] ) {
		update_term_meta( $term_id, 'return_rate_percent', (float) $_POST['return_rate_percent'] );
	}
}
add_action( 'edited_product_brand', 'stocksystem_save_product_brand_fields' );
add_action( 'created_product_brand', 'stocksystem_save_product_brand_fields' );

/* ---- product_grading: short label shown on the grading page's cards
   (07 Stock Condition.dc.html) alongside the term's own name (A/B/C)
   and description (the long paragraph, already editable via the
   taxonomy's built-in description field). ---- */

function stocksystem_product_grading_fields( $term ) {
	$title = is_object( $term ) ? get_term_meta( $term->term_id, 'grade_title', true ) : '';
	?>
	<tr class="form-field">
		<th scope="row"><label for="grade_title"><?php esc_html_e( 'عنوان کوتاه (مثلاً «در حد نو»)', 'stocksystem' ); ?></label></th>
		<td><input type="text" name="grade_title" id="grade_title" value="<?php echo esc_attr( $title ); ?>"></td>
	</tr>
	<?php
}
add_action( 'product_grading_edit_form_fields', 'stocksystem_product_grading_fields' );

function stocksystem_product_grading_fields_add() {
	?>
	<div class="form-field">
		<label for="grade_title"><?php esc_html_e( 'عنوان کوتاه (مثلاً «در حد نو»)', 'stocksystem' ); ?></label>
		<input type="text" name="grade_title" id="grade_title">
	</div>
	<?php
}
add_action( 'product_grading_add_form_fields', 'stocksystem_product_grading_fields_add' );

function stocksystem_save_product_grading_fields( $term_id ) {
	if ( isset( $_POST['grade_title'] ) ) {
		update_term_meta( $term_id, 'grade_title', sanitize_text_field( wp_unslash( $_POST['grade_title'] ) ) );
	}
}
add_action( 'edited_product_grading', 'stocksystem_save_product_grading_fields' );
add_action( 'created_product_grading', 'stocksystem_save_product_grading_fields' );
