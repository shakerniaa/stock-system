<?php
/**
 * Edit pages for site-wide things: look & brand, announcement / cookie /
 * WhatsApp / pop-up, SEO & tracking, messages, and extra homepage sections.
 * Front-end output for all of it is in inc/site-extras.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_admin_pages_site() {
	$styles = array(
		'teal'   => __( 'فیروزه‌ای', 'stocksystem' ),
		'orange' => __( 'نارنجی', 'stocksystem' ),
		'dark'   => __( 'تیره', 'stocksystem' ),
		'red'    => __( 'قرمز (هشدار)', 'stocksystem' ),
	);

	return array(

		/* ---------------------------------------------------------------- */
		'look'     => array(
			'group'    => 'ظاهر و برند',
			'title'    => __( 'ظاهر و برند', 'stocksystem' ),
			'menu'     => __( 'رنگ‌ها و لوگو', 'stocksystem' ),
			'intro'    => __( 'لوگو، آیکون سایت، رنگ‌های اصلی و گردی گوشه‌ها. خالی گذاشتن هر رنگ یعنی رنگ پیش‌فرض برند.', 'stocksystem' ),
			'view'     => '/',
			'sections' => array(
				array(
					'title'  => __( 'لوگو و آیکون', 'stocksystem' ),
					'fields' => array(
						'logo'    => array( 'type' => 'image', 'label' => __( 'لوگو (برای زمینهٔ تیره، PNG شفاف)', 'stocksystem' ), 'help' => __( 'در هدر، منوی موبایل، فوتر و تسویه‌حساب. خالی = لوگوی پیش‌فرض.', 'stocksystem' ) ),
						'favicon' => array( 'type' => 'image', 'label' => __( 'آیکون تب مرورگر (فاوآیکن، مربع، حداقل ۹۶ پیکسل)', 'stocksystem' ) ),
					),
				),
				array(
					'title'  => __( 'رنگ‌ها', 'stocksystem' ),
					'fields' => array(
						'cta'       => array( 'type' => 'color', 'label' => __( 'دکمه‌های اصلی (افزودن به سبد، پرداخت)', 'stocksystem' ), 'default' => '#0EBAAF' ),
						'cta_hover' => array( 'type' => 'color', 'label' => __( 'دکمه‌های اصلی — حالت موس', 'stocksystem' ), 'default' => '#0A8F87' ),
						'brand'     => array( 'type' => 'color', 'label' => __( 'رنگ برند (آیکون‌ها، خط‌ها، لینک‌های روی تیره)', 'stocksystem' ), 'default' => '#0EBAAF' ),
						'accent'    => array( 'type' => 'color', 'label' => __( 'رنگ تأکید (شمارهٔ تلفن، نشان‌ها)', 'stocksystem' ), 'default' => '#F58220' ),
						'dark'      => array( 'type' => 'color', 'label' => __( 'زمینهٔ تیره (هیرو، فوتر، هدر)', 'stocksystem' ), 'default' => '#04211F' ),
						'dark_soft' => array( 'type' => 'color', 'label' => __( 'زمینهٔ تیرهٔ روشن‌تر (نوار منو، کارت‌های تیره)', 'stocksystem' ), 'default' => '#0B3A38' ),
					),
				),
				array(
					'title'  => __( 'شکل', 'stocksystem' ),
					'fields' => array(
						'radius' => array( 'type' => 'select', 'label' => __( 'گردی گوشه‌ها', 'stocksystem' ), 'default' => 'normal', 'options' => array( 'sharp' => __( 'کم (تیز)', 'stocksystem' ), 'normal' => __( 'متوسط (پیش‌فرض)', 'stocksystem' ), 'round' => __( 'زیاد (گرد)', 'stocksystem' ) ) ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'bars'     => array(
			'group'    => 'بنرها و پیام‌ها',
			'title'    => __( 'بنر اعلان، کوکی و واتساپ', 'stocksystem' ),
			'menu'     => __( 'بنر اعلان و واتساپ', 'stocksystem' ),
			'intro'    => __( 'نوار اعلان بالای سایت، پیام کوکی، دکمهٔ شناور واتساپ و پاپ‌آپ تبلیغاتی. هرکدام را جدا روشن یا خاموش کنید.', 'stocksystem' ),
			'view'     => '/',
			'sections' => array(
				array(
					'title'  => __( 'نوار اعلان بالای سایت', 'stocksystem' ),
					'help'   => __( 'برای کمپین‌ها و خبرهای کوتاه، مثلاً «حراج پاییزه تا جمعه». اگر زمان شروع/پایان بگذارید، خودش نمایش داده و برداشته می‌شود.', 'stocksystem' ),
					'fields' => array(
						'ann_on'    => array( 'type' => 'checkbox', 'label' => __( 'نوار اعلان روشن باشد', 'stocksystem' ), 'default' => 0 ),
						'ann_text'  => array( 'type' => 'text', 'label' => __( 'متن اعلان', 'stocksystem' ), 'wide' => true, 'allow_empty' => true, 'default' => '' ),
						'ann_link'  => array( 'type' => 'url', 'label' => __( 'لینک (اختیاری)', 'stocksystem' ), 'default' => '' ),
						'ann_label' => array( 'type' => 'text', 'label' => __( 'متن لینک', 'stocksystem' ), 'default' => __( 'مشاهده', 'stocksystem' ) ),
						'ann_style' => array( 'type' => 'select', 'label' => __( 'رنگ نوار', 'stocksystem' ), 'default' => 'teal', 'options' => $styles ),
						'ann_start' => array( 'type' => 'datetime', 'label' => __( 'شروع نمایش (اختیاری)', 'stocksystem' ) ),
						'ann_end'   => array( 'type' => 'datetime', 'label' => __( 'پایان نمایش (اختیاری)', 'stocksystem' ) ),
						'ann_close' => array( 'type' => 'checkbox', 'label' => __( 'بازدیدکننده بتواند آن را ببندد', 'stocksystem' ), 'default' => 1 ),
					),
				),
				array(
					'title'  => __( 'پیام کوکی و حریم خصوصی', 'stocksystem' ),
					'fields' => array(
						'cookie_on'    => array( 'type' => 'checkbox', 'label' => __( 'پیام کوکی نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'cookie_text'  => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'default' => __( 'این سایت برای بهتر شدن تجربهٔ شما از کوکی استفاده می‌کند. با ادامه، سیاست حریم خصوصی ما را می‌پذیرید.', 'stocksystem' ) ),
						'cookie_btn'   => array( 'type' => 'text', 'label' => __( 'متن دکمه', 'stocksystem' ), 'default' => __( 'متوجه شدم', 'stocksystem' ) ),
						'cookie_link'  => array( 'type' => 'url', 'label' => __( 'لینک سیاست حریم خصوصی', 'stocksystem' ), 'default' => stocksystem_page_url_by_template( 'page-templates/privacy.php', '/' ) ),
					),
				),
				array(
					'title'  => __( 'دکمهٔ شناور واتساپ', 'stocksystem' ),
					'fields' => array(
						'wa_on'    => array( 'type' => 'checkbox', 'label' => __( 'دکمهٔ واتساپ نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'wa_phone' => array( 'type' => 'text', 'label' => __( 'شمارهٔ واتساپ (مثل 09034535025)', 'stocksystem' ), 'default' => '', 'allow_empty' => true, 'help' => __( 'خالی = شمارهٔ تماس سایت.', 'stocksystem' ) ),
						'wa_msg'   => array( 'type' => 'text', 'label' => __( 'متن پیش‌فرض پیام', 'stocksystem' ), 'default' => __( 'سلام، دربارهٔ خرید لپ‌تاپ سوال دارم.', 'stocksystem' ), 'wide' => true ),
						'wa_label' => array( 'type' => 'text', 'label' => __( 'نوشتهٔ کنار دکمه', 'stocksystem' ), 'default' => __( 'گفتگو در واتساپ', 'stocksystem' ) ),
						'wa_side'  => array( 'type' => 'select', 'label' => __( 'کنار صفحه', 'stocksystem' ), 'default' => 'left', 'options' => array( 'left' => __( 'چپ', 'stocksystem' ), 'right' => __( 'راست', 'stocksystem' ) ) ),
					),
				),
				array(
					'title'  => __( 'پاپ‌آپ تبلیغاتی', 'stocksystem' ),
					'help'   => __( 'یک پنجرهٔ کوچک با تصویر و دکمه؛ بعد از تعداد ثانیهٔ مشخص باز می‌شود و برای بازدیدکننده‌ای که بسته، چند روز دوباره نمایش داده نمی‌شود.', 'stocksystem' ),
					'fields' => array(
						'pop_on'    => array( 'type' => 'checkbox', 'label' => __( 'پاپ‌آپ روشن باشد', 'stocksystem' ), 'default' => 0 ),
						'pop_home'  => array( 'type' => 'checkbox', 'label' => __( 'فقط در صفحهٔ اصلی', 'stocksystem' ), 'default' => 1 ),
						'pop_image' => array( 'type' => 'image', 'label' => __( 'تصویر (اختیاری)', 'stocksystem' ) ),
						'pop_title' => array( 'type' => 'text', 'label' => __( 'عنوان', 'stocksystem' ), 'default' => '', 'allow_empty' => true, 'wide' => true ),
						'pop_text'  => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'default' => '', 'allow_empty' => true ),
						'pop_btn'   => array( 'type' => 'text', 'label' => __( 'متن دکمه', 'stocksystem' ), 'default' => __( 'مشاهده', 'stocksystem' ) ),
						'pop_link'  => array( 'type' => 'url', 'label' => __( 'لینک دکمه', 'stocksystem' ), 'default' => '' ),
						'pop_delay' => array( 'type' => 'number', 'label' => __( 'بعد از چند ثانیه باز شود', 'stocksystem' ), 'default' => 6, 'min' => 0, 'max' => 120 ),
						'pop_days'  => array( 'type' => 'number', 'label' => __( 'بعد از بستن، چند روز دوباره نمایش داده نشود', 'stocksystem' ), 'default' => 3, 'min' => 0, 'max' => 90 ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'homex'    => array(
			'group'    => 'محتوای صفحه‌ها',
			'title'    => __( 'بخش‌های تازهٔ صفحهٔ اصلی', 'stocksystem' ),
			'menu'     => __( 'بخش‌های تازهٔ صفحهٔ اصلی', 'stocksystem' ),
			'intro'    => __( 'چهار بخش اختیاری که به صفحهٔ اصلی اضافه می‌شود: بنر تبلیغاتی، نظرات مشتریان، لوگوی برندها و عددها. جای هر بخش با «ترتیب» و در کنار بخش‌های قبلی مشخص می‌شود (عدد بزرگ‌تر = پایین‌تر). بخش‌های قبلی از «نمایش ← صفحهٔ اصلی» عوض می‌شوند.', 'stocksystem' ),
			'view'     => '/',
			'sections' => array(
				array(
					'title'  => __( 'بنر تبلیغاتی', 'stocksystem' ),
					'fields' => array(
						'banner_on'    => array( 'type' => 'checkbox', 'label' => __( 'نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'banner_order' => array( 'type' => 'number', 'label' => __( 'ترتیب', 'stocksystem' ), 'default' => 5, 'min' => 1, 'max' => 9 ),
						'banner_image' => array( 'type' => 'image', 'label' => __( 'تصویر بنر (اختیاری)', 'stocksystem' ) ),
						'banner_title' => array( 'type' => 'text', 'label' => __( 'تیتر', 'stocksystem' ), 'default' => '', 'allow_empty' => true, 'wide' => true ),
						'banner_text'  => array( 'type' => 'textarea', 'label' => __( 'متن', 'stocksystem' ), 'default' => '', 'allow_empty' => true ),
						'banner_btn'   => array( 'type' => 'text', 'label' => __( 'متن دکمه', 'stocksystem' ), 'default' => __( 'مشاهده', 'stocksystem' ) ),
						'banner_link'  => array( 'type' => 'url', 'label' => __( 'لینک دکمه', 'stocksystem' ), 'default' => '' ),
					),
				),
				array(
					'title'  => __( 'نظرات مشتریان', 'stocksystem' ),
					'fields' => array(
						'testi_on'    => array( 'type' => 'checkbox', 'label' => __( 'نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'testi_order' => array( 'type' => 'number', 'label' => __( 'ترتیب', 'stocksystem' ), 'default' => 6, 'min' => 1, 'max' => 9 ),
						'testi_title' => array( 'type' => 'text', 'label' => __( 'عنوان بخش', 'stocksystem' ), 'default' => __( 'مشتری‌ها چه می‌گویند', 'stocksystem' ) ),
						'testi_items' => array( 'type' => 'repeater', 'label' => __( 'نظرها', 'stocksystem' ), 'max' => 12, 'add_label' => __( '+ نظر تازه', 'stocksystem' ), 'fields' => array( 'name' => array( 'type' => 'text', 'label' => __( 'نام', 'stocksystem' ) ), 'role' => array( 'type' => 'text', 'label' => __( 'شهر یا عنوان (اختیاری)', 'stocksystem' ) ), 'text' => array( 'type' => 'textarea', 'label' => __( 'متن نظر', 'stocksystem' ), 'wide' => true ), 'stars' => array( 'type' => 'number', 'label' => __( 'ستاره (۱ تا ۵)', 'stocksystem' ), 'min' => 1, 'max' => 5, 'default' => 5 ) ), 'default' => array() ),
					),
				),
				array(
					'title'  => __( 'برندها (لوگو)', 'stocksystem' ),
					'fields' => array(
						'brands_on'    => array( 'type' => 'checkbox', 'label' => __( 'نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'brands_order' => array( 'type' => 'number', 'label' => __( 'ترتیب', 'stocksystem' ), 'default' => 7, 'min' => 1, 'max' => 9 ),
						'brands_title' => array( 'type' => 'text', 'label' => __( 'عنوان بخش', 'stocksystem' ), 'default' => __( 'برندهای معتبر', 'stocksystem' ) ),
						'brands_items' => array( 'type' => 'repeater', 'label' => __( 'برندها', 'stocksystem' ), 'max' => 16, 'add_label' => __( '+ برند تازه', 'stocksystem' ), 'fields' => array( 'name' => array( 'type' => 'text', 'label' => __( 'نام برند', 'stocksystem' ) ), 'logo' => array( 'type' => 'image', 'label' => __( 'لوگو (اختیاری؛ خالی = فقط نام)', 'stocksystem' ) ), 'url' => array( 'type' => 'url', 'label' => __( 'لینک', 'stocksystem' ) ) ), 'default' => array() ),
					),
				),
				array(
					'title'  => __( 'عددها', 'stocksystem' ),
					'fields' => array(
						'count_on'    => array( 'type' => 'checkbox', 'label' => __( 'نمایش داده شود', 'stocksystem' ), 'default' => 0 ),
						'count_order' => array( 'type' => 'number', 'label' => __( 'ترتیب', 'stocksystem' ), 'default' => 8, 'min' => 1, 'max' => 9 ),
						'count_items' => array( 'type' => 'repeater', 'label' => __( 'عددها', 'stocksystem' ), 'max' => 6, 'fields' => array( 'value' => array( 'type' => 'text', 'label' => __( 'عدد (مثل ۵۰۰+)', 'stocksystem' ) ), 'label' => array( 'type' => 'text', 'label' => __( 'شرح', 'stocksystem' ) ) ), 'default' => array() ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'seo'      => array(
			'group'    => 'سئو و آمار',
			'title'    => __( 'سئو و کدهای آمار', 'stocksystem' ),
			'menu'     => __( 'سئو و آمار', 'stocksystem' ),
			'intro'    => __( 'تنظیمات کلی برای گوگل و شبکه‌های اجتماعی. عنوان و توضیح هر صفحه، نوشته، محصول و دسته در همان صفحهٔ ویرایش‌اش (کادر «سئو») تنظیم می‌شود.', 'stocksystem' ),
			'sections' => array(
				array(
					'title'  => __( 'اشتراک‌گذاری (تلگرام، واتساپ، اینستاگرام …)', 'stocksystem' ),
					'fields' => array(
						'og_image' => array( 'type' => 'image', 'label' => __( 'تصویر پیش‌فرض اشتراک‌گذاری (۱۲۰۰×۶۳۰)', 'stocksystem' ), 'help' => __( 'وقتی لینک صفحه‌ای که تصویر مخصوص ندارد فرستاده می‌شود.', 'stocksystem' ) ),
						'home_desc' => array( 'type' => 'textarea', 'label' => __( 'توضیح متا برای صفحهٔ اصلی و صفحه‌های بدون توضیح', 'stocksystem' ), 'default' => __( 'فروش لپ‌تاپ و کامپیوتر استوک با تست کامل سخت‌افزاری، برگهٔ وضعیت دستگاه و گارانتی.', 'stocksystem' ) ),
					),
				),
				array(
					'title'  => __( 'تأیید مالکیت و آمار', 'stocksystem' ),
					'fields' => array(
						'google_verify' => array( 'type' => 'text', 'label' => __( 'کد تأیید گوگل سرچ‌کنسول (فقط مقدار content)', 'stocksystem' ), 'allow_empty' => true, 'default' => '', 'wide' => true ),
						'bing_verify'   => array( 'type' => 'text', 'label' => __( 'کد تأیید Bing', 'stocksystem' ), 'allow_empty' => true, 'default' => '' ),
						'ga4'           => array( 'type' => 'text', 'label' => __( 'شناسهٔ گوگل‌آنالیتیکس (مثل G-XXXXXXXXXX)', 'stocksystem' ), 'allow_empty' => true, 'default' => '' ),
						'gtm'           => array( 'type' => 'text', 'label' => __( 'شناسهٔ Google Tag Manager (مثل GTM-XXXXXXX)', 'stocksystem' ), 'allow_empty' => true, 'default' => '' ),
					),
				),
				array(
					'title'  => __( 'کد دلخواه', 'stocksystem' ),
					'help'   => __( 'برای پیکسل‌ها و ابزارهایی مثل متریکا/هات‌جار. کد را کامل (با تگ script) بچسبانید.', 'stocksystem' ),
					'fields' => array(
						'head_code'   => array( 'type' => 'code', 'label' => __( 'کد داخل <head>', 'stocksystem' ), 'default' => '' ),
						'footer_code' => array( 'type' => 'code', 'label' => __( 'کد پایین صفحه', 'stocksystem' ), 'default' => '' ),
					),
				),
			),
		),

		/* ---------------------------------------------------------------- */
		'messages' => array(
			'group'    => 'ایمیل و پیامک',
			'title'    => __( 'ایمیل و پیامک', 'stocksystem' ),
			'menu'     => __( 'ایمیل و پیامک', 'stocksystem' ),
			'intro'    => __( 'گیرندهٔ ایمیل درخواست‌ها و متن پیامک کد ورود. متن ایمیل‌های سفارش را از «ووکومرس ← تنظیمات ← ایمیل‌ها» عوض کنید.', 'stocksystem' ),
			'sections' => array(
				array(
					'title'  => __( 'ایمیل درخواست‌ها', 'stocksystem' ),
					'fields' => array(
						'notify_email' => array( 'type' => 'text', 'label' => __( 'ایمیلی که درخواست‌ها به آن می‌رسد', 'stocksystem' ), 'default' => '', 'allow_empty' => true, 'help' => __( 'درخواست تعمیر، اطلاع‌رسانی موجودی، استعلام قیمت سازمانی و شارژ کیف پول. خالی = ایمیل مدیر سایت.', 'stocksystem' ) ),
						'repair_subject' => array( 'type' => 'text', 'label' => __( 'عنوان ایمیل درخواست تعمیر', 'stocksystem' ), 'default' => __( 'درخواست تعمیر جدید', 'stocksystem' ), 'wide' => true ),
					),
				),
				array(
					'title'  => __( 'پیامک کد ورود', 'stocksystem' ),
					'help'   => __( 'در متن از {code} برای کد، {phone} برای شمارهٔ مشتری و {minutes} برای مدت اعتبار استفاده کنید. تا وقتی سرویس پیامک واقعی وصل نشده، این متن به ایمیل مدیر می‌رود.', 'stocksystem' ),
					'fields' => array(
						'otp_text' => array( 'type' => 'textarea', 'label' => __( 'متن پیامک', 'stocksystem' ), 'default' => __( 'استوک سیستم: کد ورود شما {code} است. تا {minutes} دقیقه معتبر است.', 'stocksystem' ) ),
					),
				),
			),
		),
	);
}
