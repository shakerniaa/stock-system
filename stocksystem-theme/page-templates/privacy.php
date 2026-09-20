<?php
/**
 * Template Name: Privacy Policy
 * Source: 14 Support Pages.dc.html §14-D.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$privacy = stocksystem_opt( 'privacy' ); // «استوک سیستم ← حریم خصوصی».
?>

<div class="container page-crumb">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'خانه', 'stocksystem' ); ?></a>
	<span>/</span>
	<span><?php esc_html_e( 'حریم خصوصی', 'stocksystem' ); ?></span>
</div>

<header class="legal-page__header">
	<div class="container">
		<h1><?php echo esc_html( $privacy['title'] ); ?></h1>
		<p class="legal-page__meta"><?php printf( /* translators: %s: Jalali date */ esc_html__( 'آخرین بازنگری: %s', 'stocksystem' ), esc_html( stocksystem_jdate( 'j F Y', (int) get_post_modified_time( 'U', true ) ) ) ); ?></p>
	</div>
</header>

<section class="legal-page">
	<div class="container legal-page__single">
		<?php foreach ( $privacy['sections'] as $section ) : ?>
			<div class="legal-page__section">
				<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<?php echo wp_kses_post( wpautop( $section['body'] ) ); ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>

<?php get_footer(); ?>
