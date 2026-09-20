<?php
/**
 * "راهنمای خرید و نگهداری" — latest 3 blog posts. Source: 01 Home.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg  = stocksystem_home( 'blog' ); // Appearance → «صفحهٔ اصلی».
$args = array(
	'numberposts' => (int) $cfg['count'],
	'post_status' => 'publish',
);
if ( ! empty( $cfg['category_id'] ) ) {
	$args['category'] = (int) $cfg['category_id'];
}
$posts = get_posts( $args );

if ( empty( $posts ) ) {
	return;
}
?>
<section class="home-blog">
	<div class="container">
		<div class="home-blog__header">
			<h2><?php echo esc_html( $cfg['heading'] ); ?></h2>
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ?: home_url( '/blog/' ) ); ?>"><?php echo esc_html( $cfg['link_label'] ); ?></a>
		</div>

		<div class="home-blog__grid">
			<?php foreach ( $posts as $post ) : ?>
				<?php get_template_part( 'template-parts/blog/card', null, array( 'post' => $post, 'compact' => true ) ); ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
