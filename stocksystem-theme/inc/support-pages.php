<?php
/**
 * Terms/privacy/FAQ content. Source: 14 Support Pages.dc.html (14-A
 * terms template, 14-B FAQ, 14-D privacy). 14-C's installment guide +
 * live calculator is **not built** — installments are out of scope for
 * v1 per decision #6, and a calculator with no real product/gateway
 * behind it would just be UI theater.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Terms & conditions body, as [id, title, html] sections — same shape
 * as stocksystem_repair_steps() rather than real post_content, so the
 * page can build its own sticky-TOC sidebar without re-parsing HTML.
 * Warranty length comes from stocksystem_business() so it never drifts
 * from the 1-month figure again (decision #5 — the design's own 18/6-
 * month table is exactly the kind of drift that correction guards
 * against, so it's replaced with one paragraph instead of carried
 * over). Sections 5–7 (shipping, disputes) aren't written out in the
 * source file at all — it stops after section 4 — so this fills them
 * in from decisions #4/#7 rather than leaving a numbered gap. An
 * installments section is deliberately not numbered in here per
 * decision #6 — nothing to explain about a feature that doesn't exist.
 */
function stocksystem_terms_sections_default() {
	$sections = array(
		array(
			'id'    => 'definition',
			'title' => __( '۱. تعریف کالای استوک', 'stocksystem' ),
			'body'  => __( 'کالای استوک، دستگاه کارکردهٔ وارداتی است که در کارگاه ما تست سخت‌افزاری کامل شده و بر اساس جدول درجه‌بندی (A تا C) قیمت‌گذاری می‌شود. خش، رنگ‌پریدگی بدنه و کاهش ظرفیت باتری در محدودهٔ اعلام‌شدهٔ هر درجه، ایراد محسوب نمی‌شود. درجهٔ هر دستگاه و درصد سلامت باتری، هم در صفحهٔ محصول و هم روی برگهٔ تست داخل جعبه درج می‌شود.', 'stocksystem' ),
		),
		array(
			'id'    => 'order-price',
			'title' => __( '۲. ثبت سفارش و قیمت', 'stocksystem' ),
			'body'  => __( 'قیمت‌ها تا لحظهٔ پرداخت معتبرند. اگر پس از ثبت سفارش، کالا موجود نباشد یا قیمت به‌دلیل خطای سیستمی اشتباه درج شده باشد، سفارش لغو و مبلغ حداکثر ظرف ۷۲ ساعت کاری بازگردانده می‌شود. سفارش با پرداخت موفق قطعی می‌شود، نه با افزودن به سبد. فاکتور رسمی با احتساب مالیات، در صورت درخواست هنگام ثبت سفارش صادر می‌شود.', 'stocksystem' ),
		),
		array(
			'id'    => 'warranty',
			'title' => __( '۳. گارانتی و پشتیبانی', 'stocksystem' ),
			'body'  => null, // Rendered specially in the template — pulls stocksystem_business('warranty_text').
		),
		array(
			'id'    => 'returns',
			'title' => __( '۴. مرجوعی و انصراف', 'stocksystem' ),
			'body'  => __( 'تا ۷ روز پس از تحویل، بدون نیاز به دلیل می‌توانید کالا را مرجوع کنید؛ به شرط آنکه جعبه، برگهٔ تست و متعلقات کامل باشد و دستگاه آسیب فیزیکی تازه نداشته باشد. هزینهٔ ارسال برگشت در صورت ایراد فنی با ماست.', 'stocksystem' ),
		),
		array(
			'id'    => 'shipping',
			'title' => __( '۵. ارسال و تحویل', 'stocksystem' ),
			'body'  => null, // Rendered specially — pulls stocksystem_business('address').
		),
		array(
			'id'    => 'disputes',
			'title' => __( '۶. حل اختلاف', 'stocksystem' ),
			'body'  => __( 'هر اختلاف ابتدا از طریق پشتیبانی حل می‌شود. در صورت عدم توافق، مرجع رسیدگی مراجع قانونی ذی‌صلاح در جمهوری اسلامی ایران است.', 'stocksystem' ),
		),
	);

	return $sections;
}

/** Default terms sections as editable rows (title + body text) — warranty/shipping bodies filled in from the store settings. */
function stocksystem_terms_rows_default() {
	$rows = array();

	foreach ( stocksystem_terms_sections_default() as $section ) {
		if ( 'warranty' === $section['id'] ) {
			$body = sprintf( /* translators: %s: warranty text */ __( 'تمام دستگاه‌ها مشمول %s هستند. ضربه، نفوذ مایعات و باز شدن دستگاه توسط فرد غیرمجاز، گارانتی را باطل می‌کند.', 'stocksystem' ), stocksystem_business( 'warranty_text' ) );
		} elseif ( 'shipping' === $section['id'] ) {
			$body = sprintf( /* translators: %s: store address */ __( 'تحویل حضوری در فروشگاه استوک سیستم رایگان است (%s). ارسال به سراسر کشور با پست یا تیپاکس انجام می‌شود؛ هزینه و بازهٔ ارسال بر اساس مقصد، هنگام تسویه‌حساب محاسبه و نمایش داده می‌شود.', 'stocksystem' ), stocksystem_business( 'address' ) );
		} else {
			$body = $section['body'];
		}
		$rows[] = array( 'title' => $section['title'], 'body' => $body );
	}

	return $rows;
}

/** Terms sections for the page: id, title, body (text/HTML). Edited in «استوک سیستم ← قوانین». */
function stocksystem_terms_sections() {
	$sections = array();

	foreach ( stocksystem_opt( 'terms', 'sections' ) as $i => $row ) {
		$sections[] = array( 'id' => 'section-' . ( $i + 1 ), 'title' => $row['title'], 'body' => $row['body'] );
	}

	return apply_filters( 'stocksystem_terms_sections', $sections );
}

/**
 * General FAQ, [category, question, answer], for /faq/. Category keys
 * feed the horizontal filter chips; 'quality'/'warranty'/'shipping'/
 * 'repair' only — no 'payment installments' category, matching the
 * terms page's silence on installments.
 */
function stocksystem_general_faq_items_default() {
	$items = array(
		array(
			'category' => 'quality',
			'question' => __( 'استوک با کارکرده چه فرقی دارد؟', 'stocksystem' ),
			'answer'   => __( 'کالای استوک وارداتی است و معمولاً از شرکت‌های اروپایی با چرخهٔ تعویض چندساله می‌آید؛ کارکردهٔ داخلی سابقهٔ نامعلومی دارد. ما هر دستگاه استوک را تست و درجه‌بندی می‌کنیم و برگهٔ تست می‌دهیم.', 'stocksystem' ),
		),
		array(
			'category' => 'quality',
			'question' => __( 'درجه‌بندی A تا C یعنی چه؟', 'stocksystem' ),
			'answer'   => __( 'درجه فقط به وضعیت ظاهری بدنه و قاب مربوط است، نه سلامت فنی — سلامت فنی همهٔ درجه‌ها یکسان تست می‌شود. جزئیات کامل در صفحهٔ «وضعیت کالای استوک».', 'stocksystem' ),
		),
		array(
			'category' => 'warranty',
			'question' => __( 'اگر باتری زود خالی شد چه؟', 'stocksystem' ),
			'answer'   => __( 'درصد سلامت باتری هر دستگاه پیش از فروش اعلام می‌شود. افت غیرعادی باتری نسبت به عدد اعلام‌شده، در بازهٔ گارانتی مشمول تعویض است.', 'stocksystem' ),
		),
		array(
			'category' => 'quality',
			'question' => __( 'امکان تست حضوری پیش از خرید هست؟', 'stocksystem' ),
			'answer'   => __( 'بله، برای خرید حضوری در فروشگاه نیشابور می‌توانید پیش از خرید دستگاه را روشن و تست کنید.', 'stocksystem' ),
		),
		array(
			'category' => 'quality',
			'question' => __( 'ویندوز نصب است؟', 'stocksystem' ),
			'answer'   => __( 'ویندوز با لایسنس دیجیتال دستگاه نصب و فعال تحویل داده می‌شود. برای جزئیات مجوزهای اضافه (آفیس و مانند آن)، حین خرید با ما هماهنگ کنید.', 'stocksystem' ),
		),
		array(
			'category' => 'returns',
			'question' => __( 'مرجوعی چطور کار می‌کند؟', 'stocksystem' ),
			'answer'   => __( 'تا ۷ روز پس از تحویل بدون نیاز به دلیل. جعبه، برگهٔ تست و متعلقات باید کامل باشد. اگر دلیل مرجوعی ایراد فنی باشد، هزینهٔ ارسال برگشت با ماست؛ در غیر این صورت با خریدار.', 'stocksystem' ),
		),
		array(
			'category' => 'shipping',
			'question' => __( 'ارسال به شهرستان چند روز طول می‌کشد؟', 'stocksystem' ),
			'answer'   => __( 'بسته به مقصد و روش ارسال (پست یا تیپاکس) معمولاً چند روز کاری. زمان دقیق هنگام تسویه‌حساب، بر اساس کد پستی شما نمایش داده می‌شود.', 'stocksystem' ),
		),
		array(
			'category' => 'repair',
			'question' => __( 'تعمیر دستگاهی که از جای دیگر خریده‌ام را هم انجام می‌دهید؟', 'stocksystem' ),
			'answer'   => __( 'بله، تعمیرات تخصصی ما محدود به دستگاه‌های فروخته‌شده در استوک سیستم نیست. جزئیات در صفحهٔ «تعمیرات تخصصی».', 'stocksystem' ),
		),
	);

	return $items;
}

/** FAQ items: the rows saved in «استوک سیستم ← سوالات متداول», else the defaults. */
function stocksystem_general_faq_items() {
	return apply_filters( 'stocksystem_general_faq_items', stocksystem_opt( 'faq', 'items' ) );
}

/**
 * Category chip labels, in the order the filter row shows them.
 */
function stocksystem_faq_categories_default() {
	return array(
		'quality'  => __( 'کیفیت و درجه‌بندی', 'stocksystem' ),
		'warranty' => __( 'گارانتی', 'stocksystem' ),
		'shipping' => __( 'ارسال', 'stocksystem' ),
		'returns'  => __( 'مرجوعی', 'stocksystem' ),
		'repair'   => __( 'تعمیرات', 'stocksystem' ),
	);
}

/** Category filter chips: key => label, from «استوک سیستم ← سوالات متداول». */
function stocksystem_faq_categories() {
	$out = array();

	foreach ( stocksystem_opt( 'faq', 'categories' ) as $row ) {
		if ( '' !== $row['key'] && '' !== $row['label'] ) {
			$out[ $row['key'] ] = $row['label'];
		}
	}

	return $out;
}
