<?php
/**
 * Shared blog post card — used by home.php's grid, archive.php's grid,
 * and the homepage teaser (template-parts/home/blog-teaser.php passes
 * its own $args wrapper, see that file). Source: 05 Blog.dc.html.
 *
 * $args:
 *   post (WP_Post|int, required)
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post = get_post( $args['post'] ?? null );

if ( ! $post ) {
	return;
}

$categories = get_the_category( $post );
$category   = ! empty( $categories ) ? $categories[0] : null;
?>
<a class="blog-card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
	<span class="blog-card__cover">
		<?php echo get_the_post_thumbnail( $post, 'medium_large' ); ?>
	</span>
	<span class="blog-card__meta">
		<?php if ( $category ) : ?>
			<span class="blog-card__category"><?php echo esc_html( $category->name ); ?></span> ·
		<?php endif; ?>
		<?php
		printf(
			/* translators: %s: estimated reading time in minutes, Persian digits */
			esc_html__( '%s دقیقه', 'stocksystem' ),
			esc_html( stocksystem_to_persian_digits( stocksystem_reading_time( $post ) ) )
		);
		?>
		· <?php echo esc_html( stocksystem_to_persian_digits( get_the_date( '', $post ) ) ); ?>
	</span>
	<span class="blog-card__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
	<span class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post ), 18 ) ); ?></span>
</a>
