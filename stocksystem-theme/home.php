<?php
/**
 * Blog index (Reading > "Posts page", auto-set to /blog/ by
 * stocksystem_create_blog_page() in inc/blog.php). Separate from
 * front-page.php, which is the real homepage. Source: 05 Blog.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$categories = get_categories( array( 'hide_empty' => true ) );
?>

<header class="archive-header">
	<div class="container">
		<h1 class="archive-header__title"><?php esc_html_e( 'راهنمای خرید و نگهداری', 'stocksystem' ); ?></h1>
		<p class="blog-index__desc"><?php esc_html_e( 'آنچه در کارگاه می‌بینیم، نوشته می‌شود: عیب‌های تکرارشونده، مقایسهٔ واقعی مدل‌ها و نکاتی که هزینهٔ تعمیر را کم می‌کند.', 'stocksystem' ); ?></p>

		<?php if ( ! empty( $categories ) ) : ?>
			<div class="archive-header__chips">
				<a class="archive-header__chip<?php echo ! is_category() ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_permalink() ); ?>"><?php esc_html_e( 'همه', 'stocksystem' ); ?></a>
				<?php foreach ( $categories as $category ) : ?>
					<a class="archive-header__chip" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</header>

<section class="blog-index">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="blog-index__grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/blog/card', null, array( 'post' => get_post() ) );
				endwhile;
				?>
			</div>

			<?php the_posts_pagination(); ?>
		<?php else : ?>
			<p class="blog-index__empty"><?php esc_html_e( 'هنوز مقاله‌ای منتشر نشده است.', 'stocksystem' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
