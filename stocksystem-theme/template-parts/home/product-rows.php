<?php
/**
 * Extra homepage product rows (استوک سیستم ← «ردیف‌های محصول»).
 * Each row reuses the standard product card and the same header markup
 * as «پیشنهاد این هفته», so a page with six rows still looks like one
 * design rather than six.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$rows = stocksystem_home_rows();

if ( empty( $rows ) ) {
	return;
}

foreach ( $rows as $row ) :
	$products = stocksystem_home_row_products( $row );

	// A row whose source is empty is skipped entirely — no empty grid
	// under a heading.
	if ( empty( $products ) ) {
		continue;
	}
	?>
	<section class="home-featured">
		<div class="container">
			<div class="home-featured__header">
				<div class="home-featured__heading">
					<h2><?php echo esc_html( $row['heading'] ); ?></h2>
					<span class="home-featured__rule" aria-hidden="true"></span>
				</div>
				<?php if ( '' !== trim( (string) $row['link_label'] ) ) : ?>
					<a href="<?php echo esc_url( stocksystem_home_row_link( $row ) ); ?>"><?php echo esc_html( $row['link_label'] ); ?></a>
				<?php endif; ?>
			</div>

			<div class="product-grid">
				<?php
				foreach ( $products as $row_product ) :
					global $product;
					$product = $row_product;
					get_template_part( 'template-parts/product/card' );
				endforeach;
				?>
			</div>
		</div>
	</section>
	<?php
endforeach;
