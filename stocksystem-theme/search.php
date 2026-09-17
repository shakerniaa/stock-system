<?php
/**
 * Search results — product search (13 Search Results.dc.html) is the
 * primary path since the header search form always sends post_type=
 * product; a plain-post fallback covers any other search entry point.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$is_product_search = isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'];
?>

<div class="archive-header">
	<div class="container">
		<?php woocommerce_breadcrumb(); ?>
		<h1 class="archive-header__title">
			<?php
			printf(
				/* translators: %s: search query */
				esc_html__( 'نتایج جستجو برای «%s»', 'stocksystem' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
		<p class="archive-header__count">
			<?php
			global $wp_query;
			printf(
				/* translators: %s: result count, Persian digits */
				esc_html__( '%s نتیجه', 'stocksystem' ),
				esc_html( stocksystem_to_persian_digits( $wp_query->found_posts ) )
			);
			?>
		</p>
	</div>
</div>

<div class="container">
	<?php if ( $is_product_search ) : ?>
		<?php get_template_part( 'template-parts/archive/results', null, array( 'show_stock_facets' => true ) ); ?>
	<?php elseif ( have_posts() ) : ?>
		<div class="search-results-list">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'search-results-list__item' ); ?>>
					<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/archive/empty-results' ); ?>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
