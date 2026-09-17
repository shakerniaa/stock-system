<?php
/**
 * "راهنمای خرید و نگهداری" — latest 3 blog posts. Source: 01 Home.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$posts = get_posts(
	array(
		'numberposts' => 3,
		'post_status' => 'publish',
	)
);

if ( empty( $posts ) ) {
	return;
}
?>
<section class="home-blog">
	<div class="container">
		<div class="home-blog__header">
			<h2><?php esc_html_e( 'راهنمای خرید و نگهداری', 'stocksystem' ); ?></h2>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'همهٔ مقالات ←', 'stocksystem' ); ?></a>
		</div>

		<div class="home-blog__grid">
			<?php foreach ( $posts as $post ) : ?>
				<?php
				$categories   = get_the_category( $post->ID );
				$category     = ! empty( $categories ) ? $categories[0]->name : '';
				$word_count   = str_word_count( wp_strip_all_tags( $post->post_content ) );
				$reading_mins = max( 1, (int) round( $word_count / 200 ) );
				?>
				<a class="home-blog__card" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
					<span class="home-blog__cover">
						<?php echo get_the_post_thumbnail( $post, 'medium' ); ?>
					</span>
					<span class="home-blog__meta">
						<?php if ( $category ) : ?><span class="home-blog__category"><?php echo esc_html( $category ); ?></span> · <?php endif; ?>
						<?php
						printf(
							/* translators: %s: estimated reading time in minutes, Persian digits */
							esc_html__( '%s دقیقه', 'stocksystem' ),
							esc_html( stocksystem_to_persian_digits( $reading_mins ) )
						);
						?>
					</span>
					<span class="home-blog__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
