<?php
/**
 * AI-generated SEO content for a product: title/description for the
 * existing «سئو» box (_ss_seo_title/_ss_seo_desc in inc/seo-meta.php), a
 * full description (post_content), and a brand-taxonomy guess. Reuses the
 * same provider call as the import extraction (stocksystem_ai_call_anthropic()
 * in inc/ai-extraction.php) with a plain text prompt instead of an image —
 * one provider adapter, two use cases.
 *
 * Runs automatically after phase 2 confirms an import batch (one
 * Action Scheduler job per product — see the stocksystem_product_imported
 * hook in inc/import-batches.php), and is also available as a manual
 * "بازتولید" checkbox on any product, imported or not.
 *
 * Never touches grade, the device test report, or photos — those describe
 * the physical unit in hand, which no text prompt can know (فاز ۱ boundary).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Generation
 * ---------------------------------------------------------------------- */

function stocksystem_ai_content_prompt( $product_id ) {
	$title      = get_the_title( $product_id );
	$categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'names' ) );
	$categories = is_wp_error( $categories ) ? array() : $categories;

	// Same label-based lookup as phase 2 — this site's own storage
	// attribute is pa_ذخیره‌سازی, not pa_storage.
	$ram_taxonomy     = stocksystem_find_attribute_taxonomy( array( 'ram', 'رم' ) );
	$storage_taxonomy = stocksystem_find_attribute_taxonomy( array( 'storage', 'ذخیره', 'حافظه' ) );
	$ram              = $ram_taxonomy ? wp_get_post_terms( $product_id, $ram_taxonomy, array( 'fields' => 'names' ) ) : array();
	$storage          = $storage_taxonomy ? wp_get_post_terms( $product_id, $storage_taxonomy, array( 'fields' => 'names' ) ) : array();

	// Reading the product's OWN current content (rather than reaching back
	// into the import batch) makes this work equally for imported products
	// (whose content is phase 2's bare spec line) and for any other
	// product the owner runs this on by hand.
	$existing_content = wp_strip_all_tags( (string) get_post_field( 'post_content', $product_id ) );
	$grade_hint       = get_post_meta( $product_id, '_grade_reported_by_supplier', true );

	$lines   = array();
	$lines[] = sprintf( 'عنوان: %s', $title );
	if ( $categories ) {
		$lines[] = sprintf( 'دسته: %s', implode( '، ', $categories ) );
	}
	if ( ! is_wp_error( $ram ) && $ram ) {
		$lines[] = sprintf( 'رم: %s', implode( '، ', $ram ) );
	}
	if ( ! is_wp_error( $storage ) && $storage ) {
		$lines[] = sprintf( 'حافظه: %s', implode( '، ', $storage ) );
	}
	if ( $existing_content ) {
		$lines[] = sprintf( 'مشخصات خام موجود: %s', $existing_content );
	}
	if ( $grade_hint ) {
		$lines[] = sprintf( 'گرید اعلامی تامین‌کننده (هنوز تأییدنشده توسط فروشگاه): %s', $grade_hint );
	}

	$facts = implode( "\n", $lines );

	return <<<PROMPT
این یک محصول در فروشگاه لوازم دیجیتال استوک (دست دوم، تست‌شده و گارانتی‌دار) است. اطلاعات محصول:

{$facts}

بر اساس این اطلاعات، دقیقاً این ساختار JSON را بساز:
{"seo_title": string, "seo_description": string, "long_description_html": string, "brand_guess": string|null}

seo_title: حداکثر ۶۰ نویسه، برای عنوان نتیجهٔ گوگل.
seo_description: حداکثر ۱۶۰ نویسه، برای توضیح نتیجهٔ گوگل.
long_description_html: توضیح کامل محصول به فارسی، با HTML ساده (چند پاراگراف p یا یک لیست ul/li)، شامل مشخصات فنی موجود و نکات فروش واقعی — بدون ادعای اغراق‌آمیز یا ساختن مشخصاتی که داده نشده.
brand_guess: نام برند به شکل استاندارد انگلیسی (مثل HP، Apple، Dell، Lenovo) یا null اگر از روی اطلاعات بالا مشخص نیست.

فقط یک شیء JSON خروجی بده، بدون توضیح یا fence اضافه.
PROMPT;
}

function stocksystem_generate_seo_content( $product_id ) {
	$settings = stocksystem_ai_settings();
	if ( empty( $settings['api_key'] ) ) {
		update_post_meta( $product_id, '_seo_ai_status', 'failed' );
		update_post_meta( $product_id, '_seo_ai_error', __( 'ابتدا کلید API را در «استوک سیستم ← هوش مصنوعی» وارد کنید.', 'stocksystem' ) );
		return;
	}

	$response = stocksystem_ai_call_anthropic(
		$settings,
		array(
			array(
				'type' => 'text',
				'text' => stocksystem_ai_content_prompt( $product_id ),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		update_post_meta( $product_id, '_seo_ai_status', 'failed' );
		update_post_meta( $product_id, '_seo_ai_error', $response->get_error_message() );
		return;
	}

	$content = stocksystem_ai_decode_json_response( $response );
	if ( is_wp_error( $content ) ) {
		update_post_meta( $product_id, '_seo_ai_status', 'failed' );
		update_post_meta( $product_id, '_seo_ai_error', $content->get_error_message() );
		return;
	}

	stocksystem_apply_seo_content( $product_id, $content );

	update_post_meta( $product_id, '_seo_ai_status', 'done' );
	delete_post_meta( $product_id, '_seo_ai_error' );
}
add_action( 'stocksystem_generate_seo_content', 'stocksystem_generate_seo_content' );

/**
 * Writes one decoded AI content response onto a product. Split out from
 * stocksystem_generate_seo_content() so the writing logic is independently
 * testable with a stubbed array — the same shape as phase 2's
 * stocksystem_create_product_from_extracted_row(), for the same reason
 * (no way to fake a real API response from outside).
 */
function stocksystem_apply_seo_content( $product_id, $content ) {
	if ( ! empty( $content['seo_title'] ) ) {
		update_post_meta( $product_id, '_ss_seo_title', sanitize_text_field( $content['seo_title'] ) );
	}
	if ( ! empty( $content['seo_description'] ) ) {
		update_post_meta( $product_id, '_ss_seo_desc', sanitize_textarea_field( $content['seo_description'] ) );
	}
	if ( ! empty( $content['long_description_html'] ) ) {
		wp_update_post(
			array(
				'ID'           => $product_id,
				'post_content' => wp_kses_post( $content['long_description_html'] ),
			)
		);
	}
	if ( ! empty( $content['brand_guess'] ) ) {
		stocksystem_set_product_brand( $product_id, sanitize_text_field( $content['brand_guess'] ) );
	}
}

/**
 * Sets product_brand only if the product doesn't already have one — never
 * overrides an existing (possibly manually-chosen) brand. Auto-creates a
 * genuinely new brand term: brand names are an objective, enumerable set
 * (there really is a finite list of laptop brands), unlike product
 * categories, which phase 2 deliberately never auto-creates.
 */
function stocksystem_set_product_brand( $product_id, $brand_name ) {
	if ( ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	$existing = wp_get_post_terms( $product_id, 'product_brand' );
	if ( ! is_wp_error( $existing ) && ! empty( $existing ) ) {
		return;
	}

	$term = get_term_by( 'name', $brand_name, 'product_brand' );
	if ( ! $term ) {
		$inserted = wp_insert_term( $brand_name, 'product_brand' );
		if ( is_wp_error( $inserted ) ) {
			return;
		}
		$term = get_term( $inserted['term_id'], 'product_brand' );
	}

	wp_set_object_terms( $product_id, array( $term->term_id ), 'product_brand' );
}

/* -------------------------------------------------------------------------
 * Product edit screen: status + a manual (re)generate checkbox
 * ---------------------------------------------------------------------- */

function stocksystem_ai_content_add_meta_box() {
	add_meta_box( 'stocksystem_ai_content', __( 'محتوای سئو با هوش مصنوعی', 'stocksystem' ), 'stocksystem_ai_content_box', 'product', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'stocksystem_ai_content_add_meta_box' );

function stocksystem_ai_content_box( $post ) {
	$status = get_post_meta( $post->ID, '_seo_ai_status', true );
	$error  = get_post_meta( $post->ID, '_seo_ai_error', true );

	$labels = array(
		'processing' => __( 'در صف پردازش — چند ثانیه دیگر این صفحه را دوباره باز کنید.', 'stocksystem' ),
		'done'       => __( 'آخرین‌بار با موفقیت تولید شد.', 'stocksystem' ),
		'failed'     => __( 'تولید محتوا با خطا مواجه شد.', 'stocksystem' ),
	);

	if ( isset( $labels[ $status ] ) ) {
		echo '<p>' . esc_html( $labels[ $status ] ) . '</p>';
	}
	if ( 'failed' === $status && $error ) {
		echo '<p style="color:#C8481A">' . esc_html( $error ) . '</p>';
	}
	?>
	<p>
		<label>
			<input type="checkbox" name="stocksystem_generate_seo" value="1">
			<?php esc_html_e( 'با ذخیرهٔ این محصول، عنوان/توضیح سئو و توضیحات محصول با هوش مصنوعی (باز)تولید شود', 'stocksystem' ); ?>
		</label>
	</p>
	<p class="description"><?php esc_html_e( 'عنوان/توضیح سئوی فعلی و متن توضیحات محصول جایگزین می‌شوند. گرید ظاهری، برگهٔ تست دستگاه و عکس‌ها دست‌نخورده می‌مانند.', 'stocksystem' ); ?></p>
	<?php
}

function stocksystem_maybe_queue_seo_generation( $post_id ) {
	if ( empty( $_POST['stocksystem_generate_seo'] ) ) {
		return;
	}
	if ( 'product' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_seo_ai_status', 'processing' );
	as_enqueue_async_action( 'stocksystem_generate_seo_content', array( 'product_id' => $post_id ) );
}
add_action( 'woocommerce_process_product_meta', 'stocksystem_maybe_queue_seo_generation' );

/* -------------------------------------------------------------------------
 * Auto-queue right after phase 2 creates a draft from an import batch
 * ---------------------------------------------------------------------- */

function stocksystem_queue_seo_for_imported_product( $product_id ) {
	update_post_meta( $product_id, '_seo_ai_status', 'processing' );
	as_enqueue_async_action( 'stocksystem_generate_seo_content', array( 'product_id' => $product_id ) );
}
add_action( 'stocksystem_product_imported', 'stocksystem_queue_seo_for_imported_product' );
