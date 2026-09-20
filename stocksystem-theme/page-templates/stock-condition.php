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

$grading = stocksystem_opt( 'grading' ); // «استوک سیستم ← درجه‌بندی استوک».

$faq_items = array();
foreach ( $grading['faq'] as $faq_row ) {
	$faq_items[] = array(
		'question' => $faq_row['question'],
		'answer'   => '<p>' . esc_html( $faq_row['answer'] ) . '</p>',
	);
}
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'وضعیت کالای استوک', 'stocksystem' ); ?></span>
</div>

<section class="grading-hero">
	<div class="container">
		<h1><?php echo esc_html( $grading['title'] ); ?></h1>
		<p><?php echo esc_html( $grading['desc'] ); ?></p>
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
		<h2><?php echo esc_html( $grading['metrics_title'] ); ?></h2>
		<div class="grading-metrics__grid">
			<?php foreach ( $grading['metrics'] as $metric ) : ?>
				<span class="grading-metric">
					<span class="grading-metric__title"><?php echo esc_html( $metric['title'] ); ?></span>
					<span class="grading-metric__desc"><?php echo esc_html( $metric['desc'] ); ?></span>
				</span>
			<?php endforeach; ?>
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
					'title'     => $grading['faq_title'],
					'items'     => $faq_items,
					'id_prefix' => 'grading-faq',
				)
			);
			?>
		</div>

		<div class="test-report test-report--sample">
			<div class="test-report__header">
				<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"></rect><path d="M7 9h6M7 13h8"></path></svg>
				<span class="test-report__title"><?php echo esc_html( $grading['sample_title'] ); ?></span>
			</div>
			<div class="test-report__grid">
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'سلامت باتری', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value test-report__stat-value--good"><?php echo esc_html( stocksystem_to_persian_digits( $grading['sample_battery'] ) ); ?>٪</span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'ساعت کارکرد', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value"><?php echo esc_html( stocksystem_format_number( $grading['sample_hours'] ) ); ?></span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'وضعیت بدنه', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value"><?php echo esc_html( $grading['sample_body'] ); ?></span>
				</div>
				<div class="test-report__stat">
					<span class="test-report__stat-label"><?php esc_html_e( 'پیکسل سوخته', 'stocksystem' ); ?></span>
					<span class="test-report__stat-value test-report__stat-value--good"><?php echo esc_html( $grading['sample_pixels'] ); ?></span>
				</div>
			</div>
			<p class="grading-faq-sample__note"><?php echo esc_html( $grading['sample_note'] ); ?></p>
			<a class="btn btn--primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ) ); ?>"><?php esc_html_e( 'مشاهدهٔ موجودی', 'stocksystem' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
