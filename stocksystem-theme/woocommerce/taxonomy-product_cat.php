<?php
/**
 * Category archive — also serves subcategories (same template, WordPress
 * taxonomy hierarchy). Source: 02 Category.dc.html, 16 Shop Pages.dc.html
 * §16-B.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header( 'shop' );

$term = get_queried_object();

// Sibling chip row (16-B): this term's own children if it has any,
// otherwise its siblings (its parent's other children).
$chip_terms = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'parent'     => $term->term_id,
		'hide_empty' => true,
	)
);

if ( is_wp_error( $chip_terms ) || empty( $chip_terms ) ) {
	$chip_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term->parent,
			'hide_empty' => true,
		)
	);
}
?>

<div class="archive-header">
	<div class="container">
		<?php woocommerce_breadcrumb(); ?>
		<h1 class="archive-header__title"><?php echo esc_html( $term->name ); ?></h1>
		<p class="archive-header__count"><?php echo esc_html( stocksystem_to_persian_digits( $term->count ) ); ?> <?php esc_html_e( 'کالا — همه با گارانتی و برگهٔ تست', 'stocksystem' ); ?></p>

		<?php if ( ! empty( $chip_terms ) && ! is_wp_error( $chip_terms ) ) : ?>
			<div class="archive-header__chips">
				<?php foreach ( $chip_terms as $chip ) : ?>
					<a
						href="<?php echo esc_url( get_term_link( $chip ) ); ?>"
						class="archive-header__chip<?php echo $chip->term_id === $term->term_id ? ' is-current' : ''; ?>"
					>
						<?php echo esc_html( $chip->name ); ?>
						<span><?php echo esc_html( stocksystem_to_persian_digits( $chip->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="container">
	<?php get_template_part( 'template-parts/archive/results', null, array( 'show_stock_facets' => false ) ); ?>

	<?php if ( $term->description || stocksystem_product_cat_faq_items( $term->term_id ) ) : ?>
		<div class="archive-seo">
			<?php if ( $term->description ) : ?>
				<div class="archive-seo__text">
					<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
				</div>
			<?php endif; ?>

			<?php
			$faq_items = stocksystem_product_cat_faq_items( $term->term_id );
			if ( ! empty( $faq_items ) ) {
				get_template_part(
					'template-parts/global/faq-accordion',
					null,
					array(
						'title'     => __( 'پرسش‌های متداول', 'stocksystem' ),
						'items'     => $faq_items,
						'id_prefix' => 'cat-faq-' . $term->term_id,
					)
				);
			}
			?>
		</div>
	<?php endif; ?>
</div>

<?php get_footer( 'shop' ); ?>
