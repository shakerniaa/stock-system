<?php
/**
 * Hero 1d — "which laptop?" two-question picker.
 * Source: stocksystem-dev-kit/HomePage.dc.html §1d.
 *
 * Each answer is just a link to a filtered shop URL the owner sets in
 * the admin, so the whole thing works with JavaScript off: picking an
 * option and pressing the button navigates. The JS in
 * assets/js/home-hero.js only upgrades it — highlighting the chosen
 * chips and pointing the button at the narrower of the two answers.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$uses    = array();
$budgets = array();

foreach ( (array) stocksystem_hero( 'finder_uses' ) as $row ) {
	if ( '' !== trim( (string) $row['label'] ) ) {
		$uses[] = $row;
	}
}
foreach ( (array) stocksystem_hero( 'finder_budgets' ) as $row ) {
	if ( '' !== trim( (string) $row['label'] ) ) {
		$budgets[] = $row;
	}
}

// Nothing to ask: this hero would be an empty box.
if ( empty( $uses ) && empty( $budgets ) ) {
	get_template_part( 'template-parts/home/hero', 'classic' );
	return;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

// No budget rows set: fall back to the price ranges already configured
// for the mega menu, so this hero works out of the box. Those rows are
// arrays of label/min/max with no URL of their own — the link is built
// the same way template-parts/header/mega-menu.php builds it, so both
// land on identically filtered results.
if ( empty( $budgets ) && function_exists( 'stocksystem_price_ranges' ) ) {
	foreach ( stocksystem_price_ranges() as $range ) {
		$budgets[] = array(
			'label' => $range['label'],
			'url'   => add_query_arg(
				array( 'min_price' => $range['min'], 'max_price' => $range['max'] ),
				$shop_url
			),
		);
	}
}
$stats    = stocksystem_hero_stats();
?>
<section class="hero-finder">
	<div class="container hero-finder__grid">
		<div class="hero-finder__copy">
			<h1 class="hero-finder__title"><?php echo esc_html( stocksystem_hero( 'finder_title' ) ); ?></h1>
			<?php if ( '' !== trim( (string) stocksystem_hero( 'finder_body' ) ) ) : ?>
				<p class="hero-finder__body"><?php echo esc_html( stocksystem_hero( 'finder_body' ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $stats ) ) : ?>
				<div class="hero-finder__stats">
					<?php foreach ( $stats as $stat ) : ?>
						<span class="hero-finder__stat">
							<span class="hero-finder__stat-value"><?php echo esc_html( $stat['value'] ); ?></span>
							<span class="hero-finder__stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>

		<form class="hero-finder__card" data-hero-finder method="get" action="<?php echo esc_url( $shop_url ); ?>">
			<?php if ( ! empty( $uses ) ) : ?>
				<fieldset class="hero-finder__field">
					<legend class="hero-finder__legend"><?php echo esc_html( stocksystem_hero( 'finder_q1' ) ); ?></legend>
					<div class="hero-finder__options">
						<?php foreach ( $uses as $index => $use ) : ?>
							<a
								class="hero-finder__option<?php echo 0 === $index ? ' is-selected' : ''; ?>"
								href="<?php echo esc_url( stocksystem_home_link( $use['url'], $shop_url ) ); ?>"
								data-hero-answer="use"
							><?php echo esc_html( $use['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endif; ?>

			<?php if ( ! empty( $budgets ) ) : ?>
				<fieldset class="hero-finder__field">
					<legend class="hero-finder__legend"><?php echo esc_html( stocksystem_hero( 'finder_q2' ) ); ?></legend>
					<div class="hero-finder__options hero-finder__options--grid">
						<?php foreach ( $budgets as $index => $budget ) : ?>
							<a
								class="hero-finder__option<?php echo 0 === $index ? ' is-selected' : ''; ?>"
								href="<?php echo esc_url( stocksystem_home_link( $budget['url'], $shop_url ) ); ?>"
								data-hero-answer="budget"
							><?php echo esc_html( $budget['label'] ); ?></a>
						<?php endforeach; ?>
					</div>
				</fieldset>
			<?php endif; ?>

			<div class="hero-finder__result">
				<a class="btn btn--primary hero-finder__submit" data-hero-finder-go href="<?php echo esc_url( $shop_url ); ?>">
					<?php echo esc_html( stocksystem_hero( 'finder_cta' ) ); ?>
				</a>
			</div>
		</form>
	</div>
</section>
