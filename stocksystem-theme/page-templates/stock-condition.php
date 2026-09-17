<?php
/**
 * Template Name: Stock Condition Grading
 * Source: 07 Stock Condition.dc.html — "مهم‌ترین صفحهٔ اعتماد و سئو".
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$grades = stocksystem_grading_terms_for_display();

$faq_items = array(
	array(
		'question' => __( 'تفاوت استوک و رفربیشد چیست؟', 'stocksystem' ),
		'answer'   => '<p>' . esc_html__( 'رفربیشد یعنی دستگاه پیش از فروش قطعات فرسوده‌اش (مثل باتری) تعویض شده. استوک بدون این تعویض و با سلامت واقعی‌اش اعلام و فروخته می‌شود — شفاف‌تر و معمولاً ارزان‌تر.', 'stocksystem' ) . '</p>',
	),
	array(
		'question' => __( 'گارانتی شامل چه چیزهایی است؟', 'stocksystem' ),
		'answer'   => '<p>' . esc_html( stocksystem_business( 'warranty_text' ) ) . esc_html__( ' — ایرادات فنی که در برگهٔ تست ذکر نشده باشد. جزئیات کامل در صفحهٔ شرایط گارانتی و مرجوعی.', 'stocksystem' ) . '</p>',
	),
	array(
		'question' => __( 'اگر باتری زودتر خراب شود چه می‌شود؟', 'stocksystem' ),
		'answer'   => '<p>' . esc_html__( 'در بازهٔ گارانتی، افت غیرعادی باتری نسبت به عدد ثبت‌شده در برگهٔ تست مشمول گارانتی است.', 'stocksystem' ) . '</p>',
	),
	array(
		'question' => __( 'امکان تست حضوری پیش از خرید هست؟', 'stocksystem' ),
		'answer'   => '<p>' . esc_html__( 'بله، برای خرید حضوری در فروشگاه نیشابور می‌توانید پیش از خرید دستگاه را روشن و تست کنید.', 'stocksystem' ) . '</p>',
	),
);
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'وضعیت کالای استوک', 'stocksystem' ); ?></span>
</div>

<section class="grading-hero">
	<div class="container">
		<h1><?php esc_html_e( 'استاندارد درجه‌بندی استوک سیستم', 'stocksystem' ); ?></h1>
		<p><?php esc_html_e( '«استوک» کلمه‌ای کلی است و همین کلی‌بودن باعث بی‌اعتمادی می‌شود. ما هر دستگاه را با سه شاخص عددی و یک درجهٔ ظاهری می‌فروشیم. تعریف دقیق هر درجه اینجاست.', 'stocksystem' ); ?></p>
	</div>
</section>

<?php if ( ! empty( $grades ) ) : ?>
	<section class="grading-cards">
		<div class="container grading-cards__grid">
			<?php foreach ( $grades as $grade ) : ?>
				<div class="grading-card">
					<span class="grading-card__bar" style="background:<?php echo esc_attr( $grade->color ); ?>"></span>
					<span class="grading-card__body">
						<span class="grading-card__letter"><?php echo esc_html( $grade->letter ); ?></span>
						<?php if ( $grade->title ) : ?><span class="grading-card__title"><?php echo esc_html( $grade->title ); ?></span><?php endif; ?>
						<?php if ( $grade->summary ) : ?><span class="grading-card__summary"><?php echo esc_html( $grade->summary ); ?></span><?php endif; ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<section class="grading-metrics">
	<div class="container">
		<h2><?php esc_html_e( 'سه شاخصی که همیشه اعلام می‌کنیم', 'stocksystem' ); ?></h2>
		<div class="grading-metrics__grid">
			<span class="grading-metric">
				<span class="grading-metric__title"><?php esc_html_e( 'سلامت باتری', 'stocksystem' ); ?></span>
				<span class="grading-metric__desc"><?php esc_html_e( 'درصد ظرفیت باقی‌ماندهٔ باتری نسبت به روز اول. زیر ۸۰٪ با قیمت متفاوت و امکان تعویض با هزینهٔ اعلام‌شده.', 'stocksystem' ); ?></span>
			</span>
			<span class="grading-metric">
				<span class="grading-metric__title"><?php esc_html_e( 'ساعت کارکرد', 'stocksystem' ); ?></span>
				<span class="grading-metric__desc"><?php esc_html_e( 'ساعت کارکرد واقعی دستگاه از شاخص SMART — عددی که قابل جعل نیست و سن واقعی دستگاه را نشان می‌دهد.', 'stocksystem' ); ?></span>
			</span>
			<span class="grading-metric">
				<span class="grading-metric__title"><?php esc_html_e( 'وضعیت بدنه', 'stocksystem' ); ?></span>
				<span class="grading-metric__desc"><?php esc_html_e( 'درجهٔ ظاهری A تا C بر اساس معیار بالا، ثبت‌شده با عکس واقعی همان دستگاه، نه عکس کاتالوگ.', 'stocksystem' ); ?></span>
			</span>
		</div>
	</div>
</section>

<section class="grading-faq-sample">
	<div class="container grading-faq-sample__grid">
		<div>
			<?php
			get_template_part(
				'template-parts/global/faq-accordion',
				null,
				array(
					'title'     => __( 'پرسش‌های متداول', 'stocksystem' ),
					'items'     => $faq_items,
					'id_prefix' => 'grading-faq',
				)
			);
			?>
		</div>

		<div class="test-report test-report--sample">
			<div class="test-report__header">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 9h6M7 13h8"></path></svg>
				<span class="test-report__title"><?php esc_html_e( 'نمونهٔ برگهٔ تست', 'stocksystem' ); ?></span>
			</div>
			<div class="test-report__grid">
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'سلامت باتری', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value test-report__stat-value--good"><?php echo esc_html( stocksystem_to_persian_digits( 92 ) ); ?>٪</span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'ساعت کارکرد', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value"><?php echo esc_html( stocksystem_format_number( 1240 ) ); ?></span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'وضعیت بدنه', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value">A</span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'پیکسل سوخته', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value test-report__stat-value--good"><?php esc_html_e( 'ندارد', 'stocksystem' ); ?></span>
				</div>
			</div>
			<p class="grading-faq-sample__note"><?php esc_html_e( 'این برگه در صفحهٔ هر دستگاه، در سبد خرید و داخل بستهٔ ارسالی تکرار می‌شود.', 'stocksystem' ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'مشاهدهٔ موجودی', 'stocksystem' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
