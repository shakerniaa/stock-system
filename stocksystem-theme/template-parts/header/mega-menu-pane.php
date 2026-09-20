<?php
/**
 * One switchable content view of the mega menu — up to four columns (brands,
 * price ranges, an extra link column, the highlight tile), each of which can be
 * absent (null). Built by stocksystem_mega_pane_args(); see mega-menu.php.
 *
 * $args: key, brands|null (name, url, count?), prices|null (label, url),
 * extra|null (title, links[label,url]), feature|null (eyebrow, title, text,
 * url), all_url, hidden.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_brands = null !== $args['brands'];
$has_prices = null !== $args['prices'];
$extra      = ! empty( $args['extra'] ) ? $args['extra'] : null;
$feature    = ! empty( $args['feature'] ) ? $args['feature'] : null;

$columns = array();
if ( $has_brands ) {
	$columns[] = '1fr';
}
if ( $has_prices ) {
	$columns[] = '.9fr';
}
if ( $extra ) {
	$columns[] = '.9fr';
}
if ( $feature ) {
	$columns[] = '1.2fr';
}
if ( empty( $columns ) ) {
	$columns[] = '1fr';
}
?>
<div class="mega-menu__pane<?php echo $feature ? '' : ' mega-menu__pane--no-feature'; ?>" data-mega-pane="<?php echo esc_attr( $args['key'] ); ?>" style="grid-template-columns: <?php echo esc_attr( implode( ' ', $columns ) ); ?>"<?php echo ! empty( $args['hidden'] ) ? ' hidden' : ''; ?>>
	<?php if ( $has_brands ) : ?>
		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php esc_html_e( 'برند', 'stocksystem' ); ?></span>
			<ul class="mega-menu__brand-grid">
				<?php foreach ( $args['brands'] as $brand ) : ?>
					<li>
						<a class="ltr" href="<?php echo esc_url( $brand['url'] ); ?>">
							<?php echo esc_html( $brand['name'] ); ?>
							<?php if ( isset( $brand['count'] ) ) : ?>
								<span class="mega-menu__count"><?php echo esc_html( stocksystem_format_number( $brand['count'] ) ); ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li><a class="mega-menu__brand-all" href="<?php echo esc_url( $args['all_url'] ); ?>"><?php esc_html_e( 'همه', 'stocksystem' ); ?></a></li>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $has_prices ) : ?>
		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php esc_html_e( 'بازهٔ قیمت', 'stocksystem' ); ?></span>
			<ul class="mega-menu__price-list">
				<?php foreach ( $args['prices'] as $price ) : ?>
					<li><a href="<?php echo esc_url( $price['url'] ); ?>"><?php echo esc_html( $price['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $extra ) : ?>
		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php echo esc_html( $extra['title'] ); ?></span>
			<ul class="mega-menu__price-list">
				<?php foreach ( $extra['links'] as $link ) : ?>
					<li><a href="<?php echo esc_url( $link['url'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php endif; ?>

	<?php if ( $feature ) : ?>
		<a class="mega-menu__feature" href="<?php echo esc_url( $feature['url'] ); ?>">
			<span class="mega-menu__feature-eyebrow"><?php echo esc_html( $feature['eyebrow'] ); ?></span>
			<span class="mega-menu__feature-title"><?php echo esc_html( $feature['title'] ); ?></span>
			<?php if ( '' !== $feature['text'] ) : ?>
				<span class="mega-menu__feature-price"><?php echo esc_html( $feature['text'] ); ?></span>
			<?php endif; ?>
		</a>
	<?php endif; ?>
</div>
