<?php
/**
 * Blog category/tag archive. WooCommerce's own product taxonomies each
 * have their own template (taxonomy-product_cat.php etc.) so this only
 * ever serves post categories/tags. Source: 05 Blog.dc.html.
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
		<div class="blog-index__crumb">
			<a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'بلاگ', 'stocksystem' ); ?></a>
			<span>/</span>
			<span><?php the_archive_title(); ?></span>
		</div>
		<h1 class="archive-header__title"><?php the_archive_title(); ?></h1>

		<?php if ( ! empty( $categories ) ) : ?>
			<div class="archive-header__chips">
				<a class="archive-header__chip" href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>"><?php esc_html_e( 'همه', 'stocksystem' ); ?></a>
				<?php foreach ( $categories as $category ) : ?>
					<a class="archive-header__chip<?php echo is_category( $category ) ? ' is-current' : ''; ?>" href="<?php echo esc_url( get_category_link( $category ) ); ?>"><?php echo esc_html( $category->name ); ?></a>
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
			<p class="blog-index__empty"><?php esc_html_e( 'مقاله‌ای در این دسته یافت نشد.', 'stocksystem' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
