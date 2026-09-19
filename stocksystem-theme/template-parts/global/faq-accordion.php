<?php
/**
 * Reusable single-open FAQ accordion with FAQPage JSON-LD schema.
 * README: "Answers must be server-rendered (present in the HTML even
 * when collapsed) and marked up with FAQPage schema — collapsing is
 * CSS/JS only." Reused by category/brand/search SEO blocks and, later,
 * 14 Support Pages.
 *
 * $args:
 *   title (string, optional)
 *   items (array) [ [ 'question' => '', 'answer' => '<p>…</p>' ], … ]
 *   id_prefix (string, optional) — keeps element ids unique when this
 *   part is rendered more than once on a page
 *   open_first (bool, optional) — expand the first question on load
 *   (14-B shows the first answer open)
 *   items[]['category'] (string, optional) — adds data-faq-category so
 *   a page can filter items client-side (14-B's category chips); items
 *   without it just never get hidden.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = ! empty( $args['items'] ) ? $args['items'] : array();

if ( empty( $items ) ) {
	return;
}

$id_prefix   = ! empty( $args['id_prefix'] ) ? sanitize_html_class( $args['id_prefix'] ) : 'faq';
$schema_data = array(
	'@context'   => 'https://schema.org',
	'@type'      => 'FAQPage',
	'mainEntity' => array(),
);

foreach ( $items as $item ) {
	$schema_data['mainEntity'][] = array(
		'@type'          => 'Question',
		'name'           => wp_strip_all_tags( $item['question'] ),
		'acceptedAnswer' => array(
			'@type' => 'Answer',
			'text'  => wp_strip_all_tags( $item['answer'] ),
		),
	);
}
?>
<section class="faq-accordion"<?php echo ! empty( $args['open_first'] ) ? ' data-open-first' : ''; ?>>
	<?php if ( ! empty( $args['title'] ) ) : ?>
		<h2 class="faq-accordion__title"><?php echo esc_html( $args['title'] ); ?></h2>
	<?php endif; ?>

	<div class="faq-accordion__list">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php
			$question_id = $id_prefix . '-question-' . $i;
			$answer_id   = $id_prefix . '-answer-' . $i;
			?>
			<div class="faq-accordion__item"<?php echo ! empty( $item['category'] ) ? ' data-faq-category="' . esc_attr( $item['category'] ) . '"' : ''; ?>>
				<h3 class="faq-accordion__heading">
					<button
						type="button"
						class="faq-accordion__question"
						id="<?php echo esc_attr( $question_id ); ?>"
						aria-expanded="false"
						aria-controls="<?php echo esc_attr( $answer_id ); ?>"
					>
						<?php echo esc_html( $item['question'] ); ?>
						<span class="faq-accordion__icon" aria-hidden="true">+</span>
					</button>
				</h3>
				<div
					class="faq-accordion__answer"
					id="<?php echo esc_attr( $answer_id ); ?>"
					role="region"
					aria-labelledby="<?php echo esc_attr( $question_id ); ?>"
				>
					<div class="faq-accordion__answer-inner">
						<?php echo wp_kses_post( wpautop( $item['answer'] ) ); ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

	<script type="application/ld+json"><?php echo wp_json_encode( $schema_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?></script>
</section>
