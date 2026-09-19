<?php
/**
 * One switchable content view of the mega menu (brands, price ranges,
 * featured product) — see mega-menu.php.
 *
 * $args: key (string), brands (array of [name, url, count?]), all_url,
 * base_url (category/shop URL the price links are scoped to),
 * featured (WC_Product|null), hidden (bool).
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ranges   = stocksystem_price_ranges();
$featured = ! empty( $args['featured'] ) ? $args['featured'] : null;
?>
<div class="mega-menu__pane<?php echo $featured ? '' : ' mega-menu__pane--no-feature'; ?>" data-mega-pane="<?php echo esc_attr( $args['key'] ); ?>"<?php echo ! empty( $args['hidden'] ) ? ' hidden' : ''; ?>>
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

	<div class="mega-menu__col">
		<span class="mega-menu__col-title"><?php esc_html_e( 'بازهٔ قیمت', 'stocksystem' ); ?></span>
		<ul class="mega-menu__price-list">
			<?php foreach ( $ranges as $range ) : ?>
				<li>
					<a href="<?php echo esc_url( add_query_arg( array( 'min_price' => $range['min'], 'max_price' => $range['max'] ), $args['base_url'] ) ); ?>">
						<?php echo esc_html( $range['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<?php if ( $featured ) : ?>
		<a class="mega-menu__feature" href="<?php echo esc_url( $featured->get_permalink() ); ?>">
			<span class="mega-menu__feature-eyebrow"><?php esc_html_e( 'پیشنهاد هفته', 'stocksystem' ); ?></span>
			<span class="mega-menu__feature-title"><?php echo esc_html( $featured->get_name() ); ?></span>
			<span class="mega-menu__feature-price">
				<?php echo esc_html( stocksystem_format_number( $featured->get_price() ) ); ?> <?php esc_html_e( 'تومان', 'stocksystem' ); ?>
			</span>
		</a>
	<?php endif; ?>
</div>
