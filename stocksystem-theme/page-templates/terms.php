<?php
/**
 * Template Name: Terms & Conditions
 * Source: 14 Support Pages.dc.html §14-A.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$sections = stocksystem_terms_sections();
$terms    = stocksystem_opt( 'terms' ); // «استوک سیستم ← قوانین و مقررات».
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'قوانین و مقررات', 'stocksystem' ); ?></span>
</div>

<header class="legal-page__header">
	<div class="container">
		<h1><?php echo esc_html( $terms['title'] ); ?></h1>
		<p class="legal-page__meta"><?php printf( /* translators: %s: Jalali date */ esc_html__( 'آخرین بازنگری: %s', 'stocksystem' ), esc_html( stocksystem_jdate( 'j F Y', (int) get_post_modified_time( 'U', true ) ) ) ); ?></p>
	</div>
</header>

<section class="legal-page">
	<div class="container legal-page__grid">
		<aside class="legal-page__toc">
			<span class="legal-page__toc-title"><?php esc_html_e( 'در این صفحه', 'stocksystem' ); ?></span>
			<?php foreach ( $sections as $section ) : ?>
				<a href="#<?php echo esc_attr( $section['id'] ); ?>"><?php echo esc_html( $section['title'] ); ?></a>
			<?php endforeach; ?>
			<span class="legal-page__toc-contact">
				<span><?php esc_html_e( 'سوالی دارید؟', 'stocksystem' ); ?></span>
				<a class="btn btn--phone" href="tel:<?php echo esc_attr( stocksystem_business( 'phone' ) ); ?>"><?php esc_html_e( 'تماس با پشتیبانی', 'stocksystem' ); ?></a>
			</span>
		</aside>

		<article class="legal-page__content">
			<?php if ( '' !== $terms['intro'] ) : ?>
				<p class="legal-page__intro"><?php echo esc_html( $terms['intro'] ); ?></p>
			<?php endif; ?>

			<?php foreach ( $sections as $section ) : ?>
				<div class="legal-page__section" id="<?php echo esc_attr( $section['id'] ); ?>">
					<h2><?php echo esc_html( $section['title'] ); ?></h2>

					<?php echo wp_kses_post( wpautop( $section['body'] ) ); ?>
				</div>
			<?php endforeach; ?>
		</article>
	</div>
</section>

<?php get_footer(); ?>
