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
				<?php get_template_part( 'template-parts/blog/card', null, array( 'post' => $post, 'compact' => true ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
