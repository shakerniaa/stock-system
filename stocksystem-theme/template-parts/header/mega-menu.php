<?php
/**
 * Mega menu panel — 4 columns: categories, brands, price range, featured
 * tile. Opens on hover/focus of #mega-menu-toggle with a 200ms close
 * delay (assets/js/navigation.js). Source: 11 Desktop States.dc.html §01.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = stocksystem_nav_categories();
$brands     = stocksystem_nav_brands();
$ranges     = stocksystem_price_ranges();
$featured   = stocksystem_nav_featured_product();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
?>
<div id="mega-menu" class="mega-menu<?php echo $featured ? '' : ' mega-menu--no-feature'; ?>" role="menu" hidden>
	<div class="container mega-menu__inner">
		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php esc_html_e( 'دسته‌ها', 'stocksystem' ); ?></span>
			<ul>
				<?php foreach ( $categories as $i => $category ) : ?>
					<li>
						<a href="<?php echo esc_url( $category->url ); ?>"<?php echo 0 === $i ? ' class="is-current"' : ''; ?>>
							<?php echo esc_html( $category->name ); ?>
							<span class="mega-menu__count"><?php echo esc_html( stocksystem_format_number( $category->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php esc_html_e( 'برند', 'stocksystem' ); ?></span>
			<ul class="mega-menu__brand-grid">
				<?php foreach ( $brands as $brand ) : ?>
					<li><a class="ltr" href="<?php echo esc_url( $brand->url ); ?>"><?php echo esc_html( $brand->name ); ?></a></li>
				<?php endforeach; ?>
				<li><a class="mega-menu__brand-all" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'همه', 'stocksystem' ); ?></a></li>
			</ul>
		</div>

		<div class="mega-menu__col">
			<span class="mega-menu__col-title"><?php esc_html_e( 'بازهٔ قیمت', 'stocksystem' ); ?></span>
			<ul class="mega-menu__price-list">
				<?php foreach ( $ranges as $range ) : ?>
					<li>
						<a href="<?php echo esc_url( add_query_arg( array( 'min_price' => $range['min'], 'max_price' => $range['max'] ), $shop_url ) ); ?>">
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
</div>
