<?php
/**
 * Homepage hero — routes to whichever variant is selected in
 * استوک سیستم ← «هیروی صفحهٔ اصلی» (inc/hero-settings.php).
 *
 * Each variant is a self-contained template part; switching in the
 * admin swaps the whole hero without touching front-page.php.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$stocksystem_hero_variant = stocksystem_hero_variant();

// get_template_part() falls back to {$slug}.php — i.e. THIS file — when
// {$slug}-{$name}.php is missing, which would recurse forever. Resolve
// the variant file first and fall back to the classic hero explicitly.
if ( ! locate_template( 'template-parts/home/hero-' . $stocksystem_hero_variant . '.php' ) ) {
	$stocksystem_hero_variant = 'classic';
}

get_template_part( 'template-parts/home/hero', $stocksystem_hero_variant );
