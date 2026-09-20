<?php
/**
 * Site header. Source: 01 Home.dc.html, 11 Desktop States.dc.html,
 * 10 Mobile Flows.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary"><?php esc_html_e( 'رفتن به محتوای اصلی', 'stocksystem' ); ?></a>

<?php do_action( 'stocksystem_before_header' ); // Announcement bar (استوک سیستم ← بنر اعلان). ?>
<header id="masthead" class="site-header">
	<?php if ( function_exists( 'is_checkout' ) && is_checkout() ) : ?>
		<?php get_template_part( 'template-parts/header/checkout-header' ); ?>
	<?php else : ?>
		<?php get_template_part( 'template-parts/header/topbar' ); ?>
		<?php get_template_part( 'template-parts/header/masthead' ); ?>
		<?php get_template_part( 'template-parts/header/primary-nav' ); ?>
		<?php get_template_part( 'template-parts/header/mobile-header' ); ?>
	<?php endif; ?>
</header>
<main id="primary" tabindex="-1">
