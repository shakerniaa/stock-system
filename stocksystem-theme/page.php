<?php
/**
 * Generic page template. Until this existed every plain page fell
 * through to index.php, which printed the page title as an <h1> above the
 * content — so WooCommerce's cart/checkout/account pages (whose own
 * templates already have a heading) showed the title twice.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$is_cart_or_checkout = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );
	$is_account          = function_exists( 'is_account_page' ) && is_account_page();

	if ( $is_cart_or_checkout ) :
		// Full-bleed: the cart/checkout templates draw their own stepper
		// band, headings, and containers.
		?>
		<main id="primary" class="site-main">
			<?php the_content(); ?>
		</main>
	<?php elseif ( $is_account ) : ?>
		<main id="primary" class="site-main container">
			<?php the_content(); ?>
		</main>
	<?php else : ?>
		<header class="legal-page__header">
			<div class="container">
				<h1><?php the_title(); ?></h1>
			</div>
		</header>
		<main id="primary" class="site-main legal-page">
			<div class="container legal-page__single legal-page__prose">
				<?php the_content(); ?>
			</div>
		</main>
		<?php
	endif;
endwhile;

get_footer();
