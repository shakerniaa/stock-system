<?php
/**
 * Mega menu panel — a category column plus a content area (brands, price
 * range, featured tile) that switches per category: hovering or focusing a
 * category, in the menu or in the nav bar, swaps in that category's own
 * brands / price links / featured product (assets/js/navigation.js). The
 * default view («همه») is the global lists. Source: 11 Desktop States.dc.html §01.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$categories = stocksystem_nav_categories();
$shop_url   = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
$mega_data  = stocksystem_mega_menu_data();

$global_brands = array_map(
	function ( $brand ) {
		return array(
			'name' => $brand->name,
			'url'  => $brand->url,
		);
	},
	stocksystem_nav_brands()
);
?>
<div id="mega-menu" class="mega-menu" role="menu" hidden>
	<div class="container mega-menu__inner">
		<div class="mega-menu__col mega-menu__col--categories">
			<span class="mega-menu__col-title"><?php esc_html_e( 'دسته‌ها', 'stocksystem' ); ?></span>
			<ul>
				<?php foreach ( $categories as $i => $category ) : ?>
					<li>
						<a href="<?php echo esc_url( $category->url ); ?>" data-mega-target="<?php echo esc_attr( stocksystem_mega_key( $category, $i ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
							<span class="mega-menu__count"><?php echo esc_html( stocksystem_format_number( $category->count ) ); ?></span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

		<div class="mega-menu__panes">
			<?php
			get_template_part(
				'template-parts/header/mega-menu-pane',
				null,
				array(
					'key'      => 'all',
					'brands'   => $global_brands,
					'all_url'  => $shop_url,
					'base_url' => $shop_url,
					'featured' => stocksystem_nav_featured_product(),
					'hidden'   => false,
				)
			);

			foreach ( $categories as $i => $category ) {
				$cat_data = ! empty( $category->id ) && isset( $mega_data[ $category->id ] ) ? $mega_data[ $category->id ] : array();
				$brands   = array();

				foreach ( isset( $cat_data['brands'] ) ? $cat_data['brands'] : array() as $brand ) {
					$brands[] = array(
						'name'  => $brand['name'],
						'url'   => add_query_arg( 'brand', array( $brand['slug'] ), $category->url ),
						'count' => $brand['count'],
					);
				}

				get_template_part(
					'template-parts/header/mega-menu-pane',
					null,
					array(
						'key'      => stocksystem_mega_key( $category, $i ),
						'brands'   => $brands,
						'all_url'  => $category->url,
						'base_url' => $category->url,
						'featured' => ! empty( $cat_data['featured'] ) ? wc_get_product( $cat_data['featured'] ) : null,
						'hidden'   => true,
					)
				);
			}
			?>
		</div>
	</div>
</div>
