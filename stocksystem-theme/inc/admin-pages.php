<?php
/**
 * Edit pages for the content pages (declared for the engine in admin-kit.php).
 * Defaults are the copy the templates always had; the site-wide pages
 * (look, banners, SEO, messages) are in admin-pages-site.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Permalink of the page using a page template (slugs are Persian/auto-created, so never assume /privacy/). */
function stocksystem_page_url_by_template( $template, $fallback = '/' ) {
	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => $template, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	return $pages ? get_permalink( $pages[0] ) : home_url( $fallback );
}

/** Icons the repair-services list can use. */
function stocksystem_service_icons() {
	return array(
		'display'  => array( 'label' => 'نمایشگر', 'svg' => '<rect x="2.5" y="4" width="19" height="12.5" rx="2"></rect><path d="M8 20h8"></path>' ),
		'board'    => array( 'label' => 'مادربرد', 'svg' => '<rect x="4" y="4" width="16" height="16" rx="2"></rect><path d="M9 9h6v6H9z"></path>' ),
		'thermal'  => array( 'label' => 'سرویس حرارتی', 'svg' => '<path d="M12 3v6M12 15v6M5 12h14"></path><circle cx="12" cy="12" r="2.5"></circle>' ),
		'drive'    => array( 'label' => 'هارد / SSD', 'svg' => '<ellipse cx="12" cy="6" rx="7" ry="3"></ellipse><path d="M5 6v12c0 1.7 3.1 3 7 3s7-1.3 7-3V6"></path>' ),
		'battery'  => array( 'label' => 'باتری', 'svg' => '<rect x="3" y="8" width="16" height="9" rx="2"></rect><path d="M21 11v3"></path>' ),
		'keyboard' => array( 'label' => 'کیبورد', 'svg' => '<rect x="2.5" y="6" width="19" height="12" rx="2"></rect><path d="M6 10h.01M10 10h.01M14 10h.01M18 10h.01M7 14h10"></path>' ),
		'software' => array( 'label' => 'نرم‌افزار', 'svg' => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4M13.5 6l-3 12"></path>' ),
		'wrench'   => array( 'label' => 'آچار (عمومی)', 'svg' => '<path d="M14.5 6.5a4 4 0 0 0-5.3 5.3L3.5 17.5l3 3 5.7-5.7a4 4 0 0 0 5.3-5.3l-2.5 2.5-2.5-.5-.5-2.5z"></path>' ),
	);
}

function stocksystem_service_icon_options() {
	return wp_list_pluck( stocksystem_service_icons(), 'label' );
}

/** FAQ category select for a FAQ row — the categories as last saved. */
function stocksystem_faq_category_options() {
	$options = array();

	foreach ( stocksystem_opt( 'faq', 'categories' ) as $row ) {
		if ( '' !== $row['key'] && '' !== $row['label'] ) {
			$options[ $row['key'] ] = $row['label'];
		}
	}

	return $options ? $options : array( '' => '—' );
}

/** Rows of array( text => … ) from a plain list of strings. */
function stocksystem_kit_text_rows( $list ) {
	return array_map(
		function ( $text ) {
			return array( 'text' => $text );
		},
		$list
	);
}

/**
 * All edit pages. Called once, after WordPress (and translations) are ready.
 */
function stocksystem_admin_pages_schema() {
	$repair_defaults = array();
	foreach ( stocksystem_repair_services_default() as $service ) {
		$repair_defaults[] = array(
			'name'       => $service['name'],
			'icon'       => $service['icon_key'],
			'turnaround' => $service['turnaround'],
			'warranty'   => $service['warranty'],
			'price_from' => null === $service['price_from'] ? '' : $service['price_from'],
		);
	}

	$faq_categories = array();
	foreach ( stocksystem_faq_categories_default() as $key => $label ) {
		$faq_categories[] = array( 'key' => $key, 'label' => $label );
	}

	$privacy_defaults = array(
		array(
			'title' => __( 'چه داده‌ای جمع می‌کنیم', 'stocksystem' ),
			'body'  => '<p>' . __( 'برای ثبت سفارش فقط نام، شمارهٔ موبایل و نشانی تحویل لازم است. کد ملی تنها در صدور فاکتور رسمی درخواستی گرفته می‌شود.', 'stocksystem' ) . '</p><ul class="legal-page__list"><li>' . __( 'اطلاعات کارت بانکی هرگز روی سرور ما ذخیره نمی‌شود.', 'stocksystem' ) . '</li><li>' . __( 'سریال دستگاه برای اعتبارسنجی گارانتی نگهداری می‌شود.', 'stocksystem' ) . '</li><li>' . __( 'کد پیامکی ورود (OTP) فقط برای تأیید شمارهٔ موبایل استفاده و پس از استفاده باطل می‌شود.', 'stocksystem' ) . '</li></ul>',
		),
		array(
			'title' => __( 'حذف حساب و داده‌ها', 'stocksystem' ),
			'body'  => '<div class="legal-page__callout"><p>' . sprintf( /* translators: %s: support phone number */ __( 'برای درخواست حذف حساب و داده‌های خود، از طریق تلفن پشتیبانی (%s) یا ایمیل با ما تماس بگیرید. سوابق فاکتور به حکم قانون تا ۱۰ سال نگهداری می‌شود و مشمول این درخواست نیست.', 'stocksystem' ), stocksystem_business( 'phone' ) ) . '</p></div>',
		),
		array(
			'title' => __( 'اشتراک‌گذاری با اشخاص ثالث', 'stocksystem' ),
			'body'  => '<p>' . __( 'اطلاعات تحویل سفارش (نام، تلفن، نشانی) فقط با شرکت پستی/تیپاکس طرف قرارداد برای ارسال بسته به اشتراک گذاشته می‌شود؛ هیچ داده‌ای برای تبلیغات به شخص ثالث فروخته یا اجاره داده نمی‌شود.', 'stocksystem' ) . '</p>',
		),
	);

	$pages = array(

		/* ---------------------------------------------------------------- */
		'about'   => array(
			'title'    => __( 'درباره ما و تماس', 'stocksystem' ),
			'menu'     => __( 'درباره ما و تماس', 'stocksystem' ),
			'intro'    => __( 'بالای صفحه، آمار، اطلاعات تماس و نقشهٔ صفحهٔ «درباره ما».', 'stocksystem' ),
			'view'     => '/about/',
			'sections' => array(
				array(
					'title'  => __( 'بالای صفحه', 'stocksystem' ),
					'fields' => array(
						'eyebrow' => array( 'type' => 'text', 'label' => __( 'برچسب کوچک (مثلاً «از سال ۱۳۹۴»)', 'stocksystem' ), 'default' => __( 'از سال ۱۳۹۴', 'stocksystem' ), 'allow_empty' => true ),
						'title'   => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'یک کارگاه واقعی، نه فقط یک فروشگاه اینترنتی', 'stocksystem' ), 'wide' => true ),
						'desc'    => array( 'type' => 'textarea', 'label' => __( 'توضیح', 'stocksystem' ), 'default' => __( 'کار ما با تعمیر شروع شد. همان دانش فنی باعث شد بتوانیم کالای استوک را تست کنیم، درجه بدهیم و با گارانتی بفروشیم. اگر بخواهید، می‌توانید پیش از خرید بیایید و دستگاه را روشن کنید.', 'stocksystem' ) ),
						'image'   => array( 'type' => 'image', 'label' => __( 'عکس فروشگاه یا میز کار', 'stocksystem' ), 'help' => __( 'خالی = جای‌نما', 'stocksystem' ) ),
						'values'  => array( 'type' => 'repeater', 'label' => __( 'کلمه‌های ارزشی زیر توضیح', 'stocksystem' ), 'max' => 5, 'fields' => array( 'text' => array( 'type' => 'text', 'label' => __( 'کلمه', 'stocksystem' ) ) ), 'default' => stocksystem_kit_text_rows( array( __( 'اعتماد', 'stocksystem' ), __( 'کیفیت', 'stocksystem' ), __( 'تکنولوژی', 'stocksystem' ) ) ) ),
					),
				),
				array(
					'title'  => __( 'آمار', 'stocksystem' ),
					'help'   => __( 'تعداد کالاهای موجود و متن گارانتی خودکار محاسبه می‌شوند؛ بقیهٔ عددها را خودتان بنویسید.', 'stocksystem' ),
					'fields' => array(
						'stats'      => array( 'type' => 'repeater', 'label' => __( 'عددها', 'stocksystem' ), 'max' => 6, 'fields' => array( 'value' => array( 'type' => 'text', 'label' => __( 'عدد (مثل ۱۰ یا ۱۰۰٪)', 'stocksystem' ) ), 'label' => array( 'type' => 'text', 'label' => __( 'شرح', 'stocksystem' ) ) ), 'default' => array( array( 'value' => '۱۰', 'label' => __( 'سال سابقهٔ فنی', 'stocksystem' ) ), array( 'value' => '۱۰۰٪', 'label' => __( 'تست پیش از فروش', 'stocksystem' ) ) ) ),
						'show_stock' => array( 'type' => 'checkbox', 'label' => __( 'تعداد کالاهای موجود (خودکار) نمایش داده شود', 'stocksystem' ), 'default' => 1 ),
						'show_warr'  => array( 'type' => 'checkbox', 'label' => __( 'متن گارانتی نمایش داده شود', 'stocksystem' ), 'default' => 1 ),
					),
				),
				array(
					'title'  => __( 'تماس و نقشه', 'stocksystem' ),
					'help'   => __( 'نشانی، تلفن و ساعت کاری از «سفارشی‌سازی» می‌آید؛ اینجا موارد دیگر را اضافه کنید.', 'stocksystem' ),
					'fields' => array(
						'contact_title' => array( 'type' => 'text', 'label' => __( 'عنوان بخش', 'stocksystem' ), 'default' => __( 'فروشگاه و تماس', 'stocksystem' ) ),
						'cta_label'     => array( 'type' => 'text', 'label' => __( 'متن دکمهٔ تماس', 'stocksystem' ), 'default' => __( 'تماس فوری', 'stocksystem' ) ),
						'extra_rows'    => array( 'type' => 'repeater', 'label' => __( 'ردیف‌های اضافه (ایمیل، تلگرام، اینستاگرام …)', 'stocksystem' ), 'max' => 6, 'fields' => array( 'label' => array( 'type' => 'text', 'label' => __( 'عنوان', 'stocksystem' ) ), 'value' => array( 'type' => 'text', 'label' => __( 'مقدار', 'stocksystem' ) ), 'url' => array( 'type' => 'url', 'label' => __( 'لینک (اختیاری)', 'stocksystem' ) ) ), 'default' => array() ),
						'map'           => array( 'type' => 'code', 'label' => __( 'کد نقشه (iframe)', 'stocksystem' ), 'help' => __( 'از نقشهٔ گوگل/نشان/بلد گزینهٔ «اشتراک‌گذاری ← جاسازی» را بزنید و کد را اینجا بچسبانید. خالی = جای‌نما.', 'stocksystem' ), 'default' => '' ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'repair'  => array(
			'title'    => __( 'تعمیرات تخصصی', 'stocksystem' ),
			'menu'     => __( 'تعمیرات تخصصی', 'stocksystem' ),
			'intro'    => __( 'خدمات و قیمت‌ها، مراحل کار و فرم درخواست تعمیر.', 'stocksystem' ),
			'view'     => '/repair/',
			'sections' => array(
				array(
					'title'  => __( 'بالای صفحه', 'stocksystem' ),
					'fields' => array(
						'title'  => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'تعمیر در کارگاه، با تشخیص پیش از هزینه', 'stocksystem' ), 'wide' => true ),
						'desc'   => array( 'type' => 'textarea', 'label' => __( 'توضیح', 'stocksystem' ), 'default' => __( 'عیب‌یابی اولیه رایگان است. پس از تشخیص، هزینه و زمان دقیق اعلام می‌شود و کار فقط با تأیید شما آغاز می‌شود.', 'stocksystem' ) ),
						'badges' => array( 'type' => 'repeater', 'label' => __( 'نشان‌های زیر توضیح', 'stocksystem' ), 'max' => 6, 'fields' => array( 'text' => array( 'type' => 'text', 'label' => __( 'نوشته', 'stocksystem' ) ) ), 'default' => stocksystem_kit_text_rows( array( __( 'عیب‌یابی رایگان', 'stocksystem' ), __( 'گارانتی کتبی تعمیر', 'stocksystem' ), __( 'قطعات اصلی', 'stocksystem' ) ) ) ),
						'steps'  => array( 'type' => 'repeater', 'label' => __( 'مراحل کار', 'stocksystem' ), 'max' => 8, 'fields' => array( 'text' => array( 'type' => 'text', 'label' => __( 'مرحله', 'stocksystem' ) ) ), 'default' => stocksystem_kit_text_rows( stocksystem_repair_steps_default() ) ),
					),
				),
				array(
					'title'  => __( 'خدمات و هزینه', 'stocksystem' ),
					'help'   => __( 'هزینه را به تومان بنویسید؛ خالی = «پس از بررسی».', 'stocksystem' ),
					'fields' => array(
						'services' => array( 'type' => 'repeater', 'label' => __( 'فهرست خدمات', 'stocksystem' ), 'max' => 30, 'fields' => array(
							'name'       => array( 'type' => 'text', 'label' => __( 'نام خدمت', 'stocksystem' ) ),
							'icon'       => array( 'type' => 'select', 'label' => __( 'آیکون', 'stocksystem' ), 'options_cb' => 'stocksystem_service_icon_options', 'default' => 'wrench' ),
							'turnaround' => array( 'type' => 'text', 'label' => __( 'زمان انجام', 'stocksystem' ) ),
							'warranty'   => array( 'type' => 'text', 'label' => __( 'گارانتی (خالی = ندارد)', 'stocksystem' ) ),
							'price_from' => array( 'type' => 'number', 'label' => __( 'هزینه از (تومان)', 'stocksystem' ) ),
						), 'default' => $repair_defaults ),
					),
				),
				array(
					'title'  => __( 'فرم درخواست تعمیر', 'stocksystem' ),
					'fields' => array(
						'form_title'  => array( 'type' => 'text', 'label' => __( 'عنوان', 'stocksystem' ), 'default' => __( 'درخواست تعمیر ثبت کنید', 'stocksystem' ) ),
						'form_desc'   => array( 'type' => 'textarea', 'label' => __( 'توضیح', 'stocksystem' ), 'default' => __( 'ظرف چند ساعت کاری تماس می‌گیریم.', 'stocksystem' ) ),
						'form_values' => array( 'type' => 'repeater', 'label' => __( 'نکته‌های کنار فرم', 'stocksystem' ), 'max' => 5, 'fields' => array( 'text' => array( 'type' => 'text', 'label' => __( 'نوشته', 'stocksystem' ) ) ), 'default' => stocksystem_kit_text_rows( array( __( 'عیب‌یابی رایگان', 'stocksystem' ), __( 'هزینه پیش از کار', 'stocksystem' ), __( 'گارانتی کتبی', 'stocksystem' ) ) ) ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'grading' => array(
			'title'    => __( 'وضعیت کالای استوک (درجه‌بندی)', 'stocksystem' ),
			'menu'     => __( 'درجه‌بندی استوک', 'stocksystem' ),
			'intro'    => __( 'توضیح شاخص‌ها، پرسش‌ها و نمونهٔ برگهٔ تست. خود درجه‌ها (A، B، C) از «محصولات ← درجه‌بندی» ویرایش می‌شوند.', 'stocksystem' ),
			'view'     => '/stock-condition/',
			'sections' => array(
				array(
					'title'  => __( 'بالای صفحه', 'stocksystem' ),
					'fields' => array(
						'title' => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'استاندارد درجه‌بندی استوک سیستم', 'stocksystem' ), 'wide' => true ),
						'desc'  => array( 'type' => 'textarea', 'label' => __( 'توضیح', 'stocksystem' ), 'default' => __( '«استوک» کلمه‌ای کلی است و همین کلی‌بودن باعث بی‌اعتمادی می‌شود. ما هر دستگاه را با سه شاخص عددی و یک درجهٔ ظاهری می‌فروشیم. تعریف دقیق هر درجه اینجاست.', 'stocksystem' ) ),
					),
				),
				array(
					'title'  => __( 'شاخص‌ها', 'stocksystem' ),
					'fields' => array(
						'metrics_title' => array( 'type' => 'text', 'label' => __( 'عنوان بخش', 'stocksystem' ), 'default' => __( 'سه شاخصی که همیشه اعلام می‌کنیم', 'stocksystem' ) ),
						'metrics'       => array( 'type' => 'repeater', 'label' => __( 'شاخص‌ها', 'stocksystem' ), 'max' => 6, 'fields' => array( 'title' => array( 'type' => 'text', 'label' => __( 'عنوان', 'stocksystem' ) ), 'desc' => array( 'type' => 'textarea', 'label' => __( 'توضیح', 'stocksystem' ), 'wide' => true ) ), 'default' => array(
							array( 'title' => __( 'سلامت باتری', 'stocksystem' ), 'desc' => __( 'درصد ظرفیت باقی‌ماندهٔ باتری نسبت به روز اول. زیر ۸۰٪ با قیمت متفاوت و امکان تعویض با هزینهٔ اعلام‌شده.', 'stocksystem' ) ),
							array( 'title' => __( 'ساعت کارکرد', 'stocksystem' ), 'desc' => __( 'ساعت کارکرد واقعی دستگاه از شاخص SMART — عددی که قابل جعل نیست و سن واقعی دستگاه را نشان می‌دهد.', 'stocksystem' ) ),
							array( 'title' => __( 'وضعیت بدنه', 'stocksystem' ), 'desc' => __( 'درجهٔ ظاهری A تا C بر اساس معیار بالا، ثبت‌شده با عکس واقعی همان دستگاه، نه عکس کاتالوگ.', 'stocksystem' ) ),
						) ),
					),
				),
				array(
					'title'  => __( 'پرسش‌های همین صفحه', 'stocksystem' ),
					'fields' => array(
						'faq_title' => array( 'type' => 'text', 'label' => __( 'عنوان بخش', 'stocksystem' ), 'default' => __( 'پرسش‌های متداول', 'stocksystem' ) ),
						'faq'       => array( 'type' => 'repeater', 'label' => __( 'پرسش و پاسخ', 'stocksystem' ), 'max' => 12, 'fields' => array( 'question' => array( 'type' => 'text', 'label' => __( 'پرسش', 'stocksystem' ), 'wide' => true ), 'answer' => array( 'type' => 'textarea', 'label' => __( 'پاسخ', 'stocksystem' ), 'wide' => true ) ), 'default' => array(
							array( 'question' => __( 'تفاوت استوک و رفربیشد چیست؟', 'stocksystem' ), 'answer' => __( 'رفربیشد یعنی دستگاه پیش از فروش قطعات فرسوده‌اش (مثل باتری) تعویض شده. استوک بدون این تعویض و با سلامت واقعی‌اش اعلام و فروخته می‌شود — شفاف‌تر و معمولاً ارزان‌تر.', 'stocksystem' ) ),
							array( 'question' => __( 'گارانتی شامل چه چیزهایی است؟', 'stocksystem' ), 'answer' => stocksystem_business( 'warranty_text' ) . __( ' — ایرادات فنی که در برگهٔ تست ذکر نشده باشد. جزئیات کامل در صفحهٔ شرایط گارانتی و مرجوعی.', 'stocksystem' ) ),
							array( 'question' => __( 'اگر باتری زودتر خراب شود چه می‌شود؟', 'stocksystem' ), 'answer' => __( 'در بازهٔ گارانتی، افت غیرعادی باتری نسبت به عدد ثبت‌شده در برگهٔ تست مشمول گارانتی است.', 'stocksystem' ) ),
							array( 'question' => __( 'امکان تست حضوری پیش از خرید هست؟', 'stocksystem' ), 'answer' => __( 'بله، برای خرید حضوری در فروشگاه نیشابور می‌توانید پیش از خرید دستگاه را روشن و تست کنید.', 'stocksystem' ) ),
						) ),
					),
				),
				array(
					'title'  => __( 'نمونهٔ برگهٔ تست', 'stocksystem' ),
					'fields' => array(
						'sample_title'   => array( 'type' => 'text', 'label' => __( 'عنوان', 'stocksystem' ), 'default' => __( 'نمونهٔ برگهٔ تست', 'stocksystem' ) ),
						'sample_battery' => array( 'type' => 'number', 'label' => __( 'سلامت باتری (٪)', 'stocksystem' ), 'default' => 92, 'min' => 0, 'max' => 100 ),
						'sample_hours'   => array( 'type' => 'number', 'label' => __( 'ساعت کارکرد', 'stocksystem' ), 'default' => 1240 ),
						'sample_body'    => array( 'type' => 'text', 'label' => __( 'وضعیت بدنه', 'stocksystem' ), 'default' => 'A' ),
						'sample_pixels'  => array( 'type' => 'text', 'label' => __( 'پیکسل سوخته', 'stocksystem' ), 'default' => __( 'ندارد', 'stocksystem' ) ),
						'sample_note'    => array( 'type' => 'textarea', 'label' => __( 'یادداشت زیر برگه', 'stocksystem' ), 'default' => __( 'این برگه در صفحهٔ هر دستگاه، در سبد خرید و داخل بستهٔ ارسالی تکرار می‌شود.', 'stocksystem' ) ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'faq'     => array(
			'title'    => __( 'سوالات متداول', 'stocksystem' ),
			'menu'     => __( 'سوالات متداول', 'stocksystem' ),
			'intro'    => __( 'سوال و جواب‌های صفحهٔ «سوالات متداول»، با دسته‌بندی برای فیلتر بالای صفحه.', 'stocksystem' ),
			'view'     => '/faq/',
			'sections' => array(
				array(
					'title'  => __( 'بالای صفحه و ستون کنار', 'stocksystem' ),
					'fields' => array(
						'title'        => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => __( 'چه سوالی دارید؟', 'stocksystem' ) ),
						'desc'         => array( 'type' => 'text', 'label' => __( 'توضیح', 'stocksystem' ), 'default' => __( 'پرتکرارترین سوال‌های خریداران استوک سیستم.', 'stocksystem' ), 'wide' => true ),
						'side_title'   => array( 'type' => 'text', 'label' => __( 'عنوان ستون کنار', 'stocksystem' ), 'default' => __( 'جواب سوالتان را نگرفتید؟', 'stocksystem' ) ),
						'side_text'    => array( 'type' => 'textarea', 'label' => __( 'متن ستون کنار', 'stocksystem' ), 'default' => __( 'کارشناس فنی ما پاسخگوست. برای سوال فنی، مدل دستگاه را آماده داشته باشید.', 'stocksystem' ) ),
					),
				),
				array(
					'title'  => __( 'دسته‌ها', 'stocksystem' ),
					'help'   => __( 'کلید = یک کلمهٔ انگلیسی کوتاه (مثل quality). بعد از افزودن یا تغییر دسته، ذخیره کنید تا در فهرست سوال‌ها دیده شود.', 'stocksystem' ),
					'fields' => array(
						'categories' => array( 'type' => 'repeater', 'label' => __( 'دسته‌های فیلتر', 'stocksystem' ), 'max' => 12, 'fields' => array( 'key' => array( 'type' => 'text', 'label' => __( 'کلید (انگلیسی)', 'stocksystem' ) ), 'label' => array( 'type' => 'text', 'label' => __( 'نام دسته', 'stocksystem' ) ) ), 'default' => $faq_categories ),
					),
				),
				array(
					'title'  => __( 'سوال‌ها', 'stocksystem' ),
					'fields' => array(
						'items' => array( 'type' => 'repeater', 'label' => __( 'سوال و جواب', 'stocksystem' ), 'max' => 100, 'add_label' => __( '+ سوال تازه', 'stocksystem' ), 'fields' => array(
							'question' => array( 'type' => 'text', 'label' => __( 'سوال', 'stocksystem' ), 'wide' => true ),
							'category' => array( 'type' => 'select', 'label' => __( 'دسته', 'stocksystem' ), 'options_cb' => 'stocksystem_faq_category_options' ),
							'answer'   => array( 'type' => 'textarea', 'label' => __( 'پاسخ', 'stocksystem' ), 'wide' => true ),
						), 'default' => stocksystem_general_faq_items_default() ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'terms'   => array(
			'title'    => __( 'قوانین و مقررات', 'stocksystem' ),
			'menu'     => __( 'قوانین و مقررات', 'stocksystem' ),
			'intro'    => __( 'متن صفحهٔ «قوانین و مقررات فروش»؛ هر بند یک عنوان و متن دارد و فهرست کنار صفحه خودکار ساخته می‌شود.', 'stocksystem' ),
			'view'     => '/terms/',
			'sections' => array(
				array(
					'title'  => __( 'قوانین و مقررات فروش', 'stocksystem' ),
					'fields' => array(
						'title'    => array( 'type' => 'text', 'label' => __( 'تیتر صفحه', 'stocksystem' ), 'default' => __( 'قوانین و مقررات فروش', 'stocksystem' ) ),
						'intro'    => array( 'type' => 'textarea', 'label' => __( 'مقدمه', 'stocksystem' ), 'default' => __( 'ثبت سفارش در استوک سیستم به‌معنای پذیرش بندهای زیر است. این متن برای خریدار نوشته شده، نه برای واحد حقوقی؛ هر جا شرطی به سود ما و به زیان شماست، صریح گفته‌ایم.', 'stocksystem' ) ),
						'sections' => array( 'type' => 'repeater', 'label' => __( 'بندها', 'stocksystem' ), 'max' => 30, 'add_label' => __( '+ بند تازه', 'stocksystem' ), 'fields' => array( 'title' => array( 'type' => 'text', 'label' => __( 'عنوان بند', 'stocksystem' ), 'wide' => true ), 'body' => array( 'type' => 'html', 'label' => __( 'متن (می‌توانید <p>، <ul>، <strong> بگذارید)', 'stocksystem' ), 'wide' => true ) ), 'default' => stocksystem_terms_rows_default() ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'privacy' => array(
			'title'    => __( 'حریم خصوصی', 'stocksystem' ),
			'menu'     => __( 'حریم خصوصی', 'stocksystem' ),
			'intro'    => __( 'متن صفحهٔ «سیاست حفظ حریم خصوصی».', 'stocksystem' ),
			'view'     => stocksystem_page_url_by_template( 'page-templates/privacy.php', '/' ),
			'sections' => array(
				array(
					'title'  => __( 'سیاست حفظ حریم خصوصی', 'stocksystem' ),
					'fields' => array(
						'title'    => array( 'type' => 'text', 'label' => __( 'تیتر صفحه', 'stocksystem' ), 'default' => __( 'سیاست حفظ حریم خصوصی', 'stocksystem' ) ),
						'sections' => array( 'type' => 'repeater', 'label' => __( 'بندها', 'stocksystem' ), 'max' => 30, 'add_label' => __( '+ بند تازه', 'stocksystem' ), 'fields' => array( 'title' => array( 'type' => 'text', 'label' => __( 'عنوان بند', 'stocksystem' ), 'wide' => true ), 'body' => array( 'type' => 'html', 'label' => __( 'متن (می‌توانید <p>، <ul>، <strong> بگذارید)', 'stocksystem' ), 'wide' => true ) ), 'default' => $privacy_defaults ),
					),
				),
			),
		),
	);

	return apply_filters( 'stocksystem_admin_pages', array_merge( $pages, function_exists( 'stocksystem_admin_pages_site' ) ? stocksystem_admin_pages_site() : array() ) );
}
