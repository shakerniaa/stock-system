<?php
/**
 * AI extraction: turns one supplier price-list file (image or CSV) into
 * an array of structured product rows. One HTTP call per file — a single
 * flyer image can hold dozens of products across several sections (see
 * dev-tools/sample-supplier-lists/) — not one call per row.
 *
 * The provider sits behind one function boundary
 * (stocksystem_ai_extract_rows_from_attachment): swapping providers later
 * means rewriting stocksystem_ai_call_anthropic(), not the import feature
 * built on top of it in inc/import-batches.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * Settings: «استوک سیستم ← هوش مصنوعی»
 * ---------------------------------------------------------------------- */

function stocksystem_ai_settings() {
	$defaults = array(
		'provider' => 'anthropic',
		'api_key'  => '',
		'model'    => 'claude-sonnet-5',
	);

	return wp_parse_args( get_option( 'stocksystem_ai_settings', array() ), $defaults );
}

function stocksystem_ai_add_settings_page() {
	add_submenu_page(
		'stocksystem',
		__( 'هوش مصنوعی', 'stocksystem' ),
		__( 'هوش مصنوعی', 'stocksystem' ),
		'manage_options',
		'stocksystem-ai',
		'stocksystem_ai_settings_page'
	);
}
add_action( 'admin_menu', 'stocksystem_ai_add_settings_page' );

function stocksystem_ai_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['stocksystem_ai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stocksystem_ai_nonce'] ) ), 'stocksystem_ai_save' ) ) {
		update_option(
			'stocksystem_ai_settings',
			array(
				'provider' => sanitize_key( wp_unslash( $_POST['provider'] ?? 'anthropic' ) ),
				'api_key'  => sanitize_text_field( wp_unslash( $_POST['api_key'] ?? '' ) ),
				'model'    => sanitize_text_field( wp_unslash( $_POST['model'] ?? '' ) ),
			)
		);
		echo '<div class="notice notice-success"><p>' . esc_html__( 'ذخیره شد.', 'stocksystem' ) . '</p></div>';
	}

	$settings = stocksystem_ai_settings();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'تنظیمات هوش مصنوعی', 'stocksystem' ); ?></h1>
		<p><?php esc_html_e( 'برای استخراج خودکار کالا از لیست تامین‌کننده‌ها (استوک سیستم ← ایمپورت لیست) لازم است.', 'stocksystem' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'stocksystem_ai_save', 'stocksystem_ai_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="stocksystem_ai_provider"><?php esc_html_e( 'ارائه‌دهنده', 'stocksystem' ); ?></label></th>
					<td>
						<input type="text" id="stocksystem_ai_provider" name="provider" class="regular-text" value="<?php echo esc_attr( $settings['provider'] ); ?>">
						<p class="description"><?php esc_html_e( 'فعلاً فقط «anthropic» پیاده‌سازی شده است.', 'stocksystem' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="stocksystem_ai_key"><?php esc_html_e( 'کلید API', 'stocksystem' ); ?></label></th>
					<td><input type="password" id="stocksystem_ai_key" name="api_key" class="regular-text" value="<?php echo esc_attr( $settings['api_key'] ); ?>" autocomplete="off"></td>
				</tr>
				<tr>
					<th><label for="stocksystem_ai_model"><?php esc_html_e( 'مدل', 'stocksystem' ); ?></label></th>
					<td><input type="text" id="stocksystem_ai_model" name="model" class="regular-text" value="<?php echo esc_attr( $settings['model'] ); ?>"></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Extraction contract
 * ---------------------------------------------------------------------- */

function stocksystem_ai_extraction_prompt() {
	return <<<PROMPT
این تصویر یک لیست قیمت عمده‌فروشی از یک تامین‌کنندهٔ کالای دیجیتال (لپ‌تاپ، تبلت، دسکتاپ و مشابه) است.

تمام ردیف‌های محصول را استخراج کن — اطلاعات تماس، لوگوی برند فروشگاه (نه برند کالا)، و متن تبلیغاتی را نادیده بگیر. اگر برند کالا فقط با لوگو مشخص شده (نه متن)، از روی لوگو نام برند را تشخیص بده.

برای هر محصول دقیقاً این ساختار JSON را پر کن:
{"brand": string|null, "model": string, "cpu": string|null, "ram_gb": number|null, "storage_gb": number|null, "storage_type": "SSD"|"HDD"|null, "gpu": string|null, "screen_in": number|null, "screen_note": string|null, "adapter_included": true|false|null, "grade_reported": string|null, "category_guess": string|null, "price_raw": string, "price_toman": number|null, "price_confidence": "high"|"low", "price_note": string|null}

دربارهٔ price: تامین‌کننده‌ها گاهی قیمت را کوتاه‌نویسی می‌کنند (مثلاً «۳۴,۵۰۰» به‌جای «۳۴,۵۰۰,۰۰۰» تومان). اگر عدد نوشته‌شده برای نوع کالا غیرمعمول کوچک به نظر می‌رسد، بهترین حدس را با ضرب در ۱۰۰۰ در price_toman بگذار ولی price_confidence را "low" کن و دلیل را در price_note بنویس. اگر عدد به‌وضوح قیمت واقعی به تومان است، price_confidence را "high" بگذار و price_note را null کن.

فقط یک آرایهٔ JSON از این اشیاء برگردان. هیچ توضیح، مقدمه یا fence اضافه نکن.
PROMPT;
}

function stocksystem_ai_extract_rows_from_attachment( $attachment_id ) {
	$path = get_attached_file( $attachment_id );
	if ( ! $path || ! file_exists( $path ) ) {
		return new WP_Error( 'stocksystem_ai_missing_file', __( 'فایل پیدا نشد.', 'stocksystem' ) );
	}

	$ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

	if ( 'csv' === $ext ) {
		return stocksystem_parse_csv_rows( $path );
	}

	if ( wp_attachment_is_image( $attachment_id ) ) {
		return stocksystem_ai_extract_from_image( $attachment_id );
	}

	return new WP_Error(
		'stocksystem_ai_unsupported_type',
		__( 'این نوع فایل هنوز پشتیبانی نمی‌شود. لطفاً عکس (jpg/png) یا CSV آپلود کنید — اکسل را اول با «ذخیره به‌عنوان CSV» تبدیل کنید.', 'stocksystem' )
	);
}

function stocksystem_ai_extract_from_image( $attachment_id ) {
	$settings = stocksystem_ai_settings();
	if ( empty( $settings['api_key'] ) ) {
		return new WP_Error( 'stocksystem_ai_no_key', __( 'ابتدا کلید API را در «استوک سیستم ← هوش مصنوعی» وارد کنید.', 'stocksystem' ) );
	}

	$path = get_attached_file( $attachment_id );
	$mime = get_post_mime_type( $attachment_id );
	$data = file_get_contents( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- local uploaded file, not a remote URL.

	if ( false === $data ) {
		return new WP_Error( 'stocksystem_ai_read_failed', __( 'فایل خوانده نشد.', 'stocksystem' ) );
	}

	$response = stocksystem_ai_call_anthropic(
		$settings,
		array(
			array(
				'type'   => 'image',
				'source' => array(
					'type'       => 'base64',
					'media_type' => $mime,
					'data'       => base64_encode( $data ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- API payload encoding, not obfuscation.
				),
			),
			array(
				'type' => 'text',
				'text' => stocksystem_ai_extraction_prompt(),
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	return stocksystem_ai_parse_json_rows( $response );
}

function stocksystem_ai_call_anthropic( $settings, $content_blocks ) {
	$response = wp_remote_post(
		'https://api.anthropic.com/v1/messages',
		array(
			'timeout' => 90,
			'headers' => array(
				'x-api-key'         => $settings['api_key'],
				'anthropic-version' => '2023-06-01',
				'content-type'      => 'application/json',
			),
			'body'    => wp_json_encode(
				array(
					'model'      => $settings['model'],
					'max_tokens' => 4096,
					'messages'   => array(
						array(
							'role'    => 'user',
							'content' => $content_blocks,
						),
					),
				)
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( $code < 200 || $code >= 300 ) {
		$message = is_array( $body ) && isset( $body['error']['message'] ) ? $body['error']['message'] : wp_remote_retrieve_body( $response );
		return new WP_Error(
			'stocksystem_ai_http_error',
			sprintf(
				/* translators: 1: HTTP status code, 2: error message from the API */
				__( 'خطای API (%1$d): %2$s', 'stocksystem' ),
				$code,
				$message
			)
		);
	}

	$text = isset( $body['content'][0]['text'] ) ? $body['content'][0]['text'] : '';
	if ( '' === $text ) {
		return new WP_Error( 'stocksystem_ai_empty_response', __( 'پاسخی از هوش مصنوعی دریافت نشد.', 'stocksystem' ) );
	}

	return $text;
}

/**
 * Strips a ```json fence if the model added one anyway, then decodes.
 * Shared by the rows-array parser below (phase 2) and the single-object
 * content response in inc/ai-content.php (phase 3).
 */
function stocksystem_ai_decode_json_response( $text ) {
	$text = trim( $text );
	$text = preg_replace( '/^```(?:json)?\s*|\s*```$/', '', $text );

	$decoded = json_decode( $text, true );
	if ( ! is_array( $decoded ) ) {
		return new WP_Error( 'stocksystem_ai_bad_json', __( 'پاسخ هوش مصنوعی قابل‌خواندن نبود.', 'stocksystem' ) );
	}

	return $decoded;
}

function stocksystem_ai_parse_json_rows( $text ) {
	$decoded = stocksystem_ai_decode_json_response( $text );
	if ( is_wp_error( $decoded ) ) {
		return $decoded;
	}

	return array_map( 'stocksystem_normalize_extracted_row', $decoded );
}

/** Fills in any keys the model skipped, so every row has the full shape. */
function stocksystem_normalize_extracted_row( $row ) {
	$defaults = array(
		'brand'            => null,
		'model'            => '',
		'cpu'              => null,
		'ram_gb'           => null,
		'storage_gb'       => null,
		'storage_type'     => null,
		'gpu'              => null,
		'screen_in'        => null,
		'screen_note'      => null,
		'adapter_included' => null,
		'grade_reported'   => null,
		'category_guess'   => null,
		'price_raw'        => '',
		'price_toman'      => null,
		'price_confidence' => 'low',
		'price_note'       => null,
	);

	return array_merge( $defaults, is_array( $row ) ? $row : array() );
}

/* -------------------------------------------------------------------------
 * CSV path — no AI needed, deterministic parsing.
 * ---------------------------------------------------------------------- */

/**
 * Best-effort header aliases — no real supplier CSV has been seen yet
 * (all 3 samples so far are images, see فاز ۲ در zesty-toasting-treasure.md);
 * revisit the alias list once one shows up.
 */
function stocksystem_parse_csv_rows( $path ) {
	$handle = fopen( $path, 'r' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen -- local uploaded file.
	if ( ! $handle ) {
		return new WP_Error( 'stocksystem_csv_open_failed', __( 'فایل CSV باز نشد.', 'stocksystem' ) );
	}

	$header = fgetcsv( $handle, 0, ',', '"', '' ); // 5th arg required since PHP 8.4.
	if ( ! $header ) {
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return new WP_Error( 'stocksystem_csv_empty', __( 'فایل CSV خالی است.', 'stocksystem' ) );
	}

	$aliases = array(
		'brand'          => array( 'brand', 'برند' ),
		'model'          => array( 'model', 'مدل' ),
		'cpu'            => array( 'cpu', 'پردازنده' ),
		'ram_gb'         => array( 'ram', 'رم' ),
		'storage_gb'     => array( 'storage', 'hdd', 'ssd', 'حافظه' ),
		'category_guess' => array( 'category', 'دسته' ),
		'price_toman'    => array( 'price', 'قیمت' ),
	);

	$column_map = array();
	foreach ( $header as $index => $label ) {
		$label = strtolower( trim( (string) $label ) );
		foreach ( $aliases as $field => $names ) {
			if ( in_array( $label, $names, true ) ) {
				$column_map[ $field ] = $index;
			}
		}
	}

	$rows = array();
	while ( true ) {
		$line = fgetcsv( $handle, 0, ',', '"', '' );
		if ( false === $line ) {
			break;
		}

		$row = array();
		foreach ( $column_map as $field => $index ) {
			$row[ $field ] = isset( $line[ $index ] ) ? trim( $line[ $index ] ) : null;
		}

		if ( ! empty( $row['price_toman'] ) ) {
			$row['price_raw']        = $row['price_toman'];
			$row['price_toman']      = (float) preg_replace( '/[^\d.]/', '', $row['price_toman'] );
			$row['price_confidence'] = 'high';
		}

		$rows[] = stocksystem_normalize_extracted_row( $row );
	}
	fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

	return $rows;
}
