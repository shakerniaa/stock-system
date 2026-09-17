<?php
/**
 * Single blog post. Source: 05 Blog.dc.html "نمای صفحهٔ مقاله".
 *
 * The sticky table-of-contents and the [stocksystem_product_promo]
 * inline product callout are real (inc/blog.php); the design's
 * structured FAQ block is not — that needs per-post repeatable fields
 * (an ACF-style meta box) that isn't in scope yet, so authors write
 * Q&A as plain content for now instead of getting a fake accordion
 * that isn't actually wired to anything.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$toc_items = stocksystem_post_toc_items( get_post() );
	$related   = get_posts(
		array(
			'numberposts'    => 2,
			'post_status'    => 'publish',
			'post__not_in'   => array( get_the_ID() ),
			'category__in'   => wp_list_pluck( get_the_category(), 'term_id' ),
			'no_found_rows'  => true,
		)
	);
	?>

	<div class="container page-crumb">
		<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'بلاگ', 'stocksystem' ); ?></a>
		<span>/</span>
		<?php $category = get_the_category(); ?>
		<?php if ( ! empty( $category ) ) : ?>
			<a href="<?php echo esc_url( get_category_link( $category[0] ) ); ?>"><?php echo esc_html( $category[0]->name ); ?></a>
		<?php endif; ?>
	</div>

	<article class="container blog-single">
		<div class="blog-single__main">
			<h1 class="blog-single__title"><?php the_title(); ?></h1>

			<div class="blog-single__meta">
				<span><?php echo esc_html( stocksystem_to_persian_digits( get_the_date() ) ); ?></span>
				<span>
					<?php
					printf(
						/* translators: %s: estimated reading time in minutes, Persian digits */
						esc_html__( '%s دقیقه مطالعه', 'stocksystem' ),
						esc_html( stocksystem_to_persian_digits( stocksystem_reading_time( get_post() ) ) )
					);
					?>
				</span>
				<span><?php esc_html_e( 'نویسنده:', 'stocksystem' ); ?> <?php the_author(); ?></span>
			</div>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="blog-single__cover"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>

			<div class="blog-single__content">
				<?php the_content(); ?>
			</div>
		</div>

		<aside class="blog-single__sidebar">
			<?php if ( ! empty( $toc_items ) ) : ?>
				<div class="blog-toc">
					<span class="blog-toc__title"><?php esc_html_e( 'فهرست مطالب', 'stocksystem' ); ?></span>
					<?php foreach ( $toc_items as $item ) : ?>
						<a class="blog-toc__link" href="#<?php echo esc_attr( $item['id'] ); ?>"><?php echo esc_html( $item['text'] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $related ) ) : ?>
				<div class="blog-related">
					<span class="blog-related__title"><?php esc_html_e( 'مقالات مرتبط', 'stocksystem' ); ?></span>
					<?php foreach ( $related as $related_post ) : ?>
						<a class="blog-related__link" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>"><?php echo esc_html( get_the_title( $related_post ) ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</aside>
	</article>

	<?php
endwhile;

get_footer();
