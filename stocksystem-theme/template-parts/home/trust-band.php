<?php
/**
 * Trust band — up to 4 reassurance points, editable in Appearance →
 * «صفحهٔ اصلی» (icon, title, description each). Source: 01 Home.dc.html.
 *
 * Decision fixes: an empty first title falls back to the Customizer warranty
 * text (not the design's hardcoded "۱۸ ماه گارانتی") and an empty last
 * description to the store address; default shipping copy is Neyshabur pickup
 * + nationwide post (decision #4/#7).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cfg   = stocksystem_home( 'trust' );
$icons = stocksystem_home_icons();
$items = array();

foreach ( $cfg['items'] as $index => $item ) {
	if ( empty( $item['enabled'] ) ) {
		continue;
	}

	$title = $item['title'];
	$desc  = $item['desc'];

	if ( '' === $title && 0 === $index ) {
		$title = stocksystem_business( 'warranty_text' );
	}
	if ( '' === $desc && 3 === $index ) {
		$desc = stocksystem_business( 'address' );
	}
	if ( '' === $title && '' === $desc ) {
		continue;
	}

	$items[] = array(
		'svg'   => isset( $icons[ $item['icon'] ] ) ? $icons[ $item['icon'] ]['svg'] : $icons['shield']['svg'],
		'title' => $title,
		'desc'  => $desc,
	);
}

if ( empty( $items ) ) {
	return;
}
?>
<section class="home-trust">
	<div class="container home-trust__grid">
		<?php foreach ( $items as $item ) : ?>
			<span class="home-trust__item">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?php echo $item['svg']; // phpcs:ignore WordPress.Security.EscapeOutput -- static SVG paths from stocksystem_home_icons() ?></svg>
				<span class="home-trust__text">
					<?php if ( '' !== $item['title'] ) : ?>
						<span class="home-trust__title"><?php echo esc_html( $item['title'] ); ?></span>
					<?php endif; ?>
					<?php if ( '' !== $item['desc'] ) : ?>
						<span class="home-trust__desc"><?php echo esc_html( $item['desc'] ); ?></span>
					<?php endif; ?>
				</span>
			</span>
		<?php endforeach; ?>
	</div>
</section>
