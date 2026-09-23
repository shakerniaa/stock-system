<?php
/**
 * "خرید امروز" — a read-only page grouping every order that still needs
 * its items sourced, by which supplier is currently cheapest for each
 * one (phase 1's stocksystem_get_latest_quote_per_supplier()).
 *
 * Which order status counts as "still needs sourcing" is taken from the
 * site's own fulfillment pipeline (inc/order-statuses.php):
 * processing (تأیید پرداخت) → qc-packing (تست فنی و بسته‌بندی) → with-courier
 * → completed. "qc-packing" means the physical unit is already in hand —
 * so only "processing" orders belong here.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_add_purchase_list_page() {
	add_submenu_page(
		'stocksystem',
		__( 'خرید امروز', 'stocksystem' ),
		__( 'خرید امروز', 'stocksystem' ),
		'manage_options',
		'stocksystem-purchase-list',
		'stocksystem_purchase_list_page'
	);
}
add_action( 'admin_menu', 'stocksystem_add_purchase_list_page' );

/**
 * Returns [ $by_supplier, $order_urls ]:
 * - $by_supplier: [ supplier_id => [ 'title', 'phone', 'lines' => [ product_id => [ 'title', 'qty', 'unit_price', 'order_ids' ] ] ] ],
 *   plus a synthetic supplier_id 0 group for products with no recorded quote at all.
 * - $order_urls: [ order_id => edit URL ], since building it needs the
 *   $order object that's no longer around by the time the table renders.
 */
function stocksystem_build_purchase_list() {
	$orders = wc_get_orders(
		array(
			'status' => 'processing',
			'limit'  => -1,
		)
	);

	$by_supplier = array();
	$order_urls  = array();

	foreach ( $orders as $order ) {
		// $order->get_edit_order_url() — not get_edit_post_link() — since
		// HPOS orders (confirmed enabled on this site, فاز ۱) aren't
		// regular posts; get_edit_post_link() would silently give a wrong
		// or empty URL for them.
		$order_urls[ $order->get_id() ] = $order->get_edit_order_url();

		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( ! $product ) {
				continue;
			}

			$product_id = $product->get_id();
			$quotes     = stocksystem_get_latest_quote_per_supplier( $product_id );
			$cheapest   = $quotes ? $quotes[0] : null;
			$supplier_id = $cheapest ? (int) $cheapest->supplier_id : 0;

			if ( ! isset( $by_supplier[ $supplier_id ] ) ) {
				$by_supplier[ $supplier_id ] = array(
					'title' => $supplier_id ? get_the_title( $supplier_id ) : __( 'بدون تامین‌کنندهٔ ثبت‌شده', 'stocksystem' ),
					'phone' => $supplier_id ? get_post_meta( $supplier_id, '_phone', true ) : '',
					'lines' => array(),
				);
			}

			if ( ! isset( $by_supplier[ $supplier_id ]['lines'][ $product_id ] ) ) {
				$by_supplier[ $supplier_id ]['lines'][ $product_id ] = array(
					'title'      => $product->get_name(),
					'qty'        => 0,
					'unit_price' => $cheapest ? (float) $cheapest->price : null,
					'order_ids'  => array(),
				);
			}

			$by_supplier[ $supplier_id ]['lines'][ $product_id ]['qty'] += $item->get_quantity();
			$by_supplier[ $supplier_id ]['lines'][ $product_id ]['order_ids'][] = $order->get_id();
		}
	}

	// Suppliers with a real name sorted first (alphabetically), the
	// "no supplier recorded" bucket always last regardless of its title.
	uksort(
		$by_supplier,
		static function ( $a, $b ) use ( $by_supplier ) {
			if ( 0 === $a || 0 === $b ) {
				return 0 === $a ? 1 : -1;
			}
			return strnatcasecmp( $by_supplier[ $a ]['title'], $by_supplier[ $b ]['title'] );
		}
	);

	return array( $by_supplier, $order_urls );
}

function stocksystem_purchase_list_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	list( $by_supplier, $order_urls ) = stocksystem_build_purchase_list();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'خرید امروز', 'stocksystem' ); ?></h1>
		<p><?php esc_html_e( 'همهٔ سفارش‌های با وضعیت «تأیید پرداخت» که هنوز کالایشان تهیه نشده، بر اساس ارزان‌ترین تامین‌کنندهٔ ثبت‌شدهٔ هر کالا گروه‌بندی شده‌اند.', 'stocksystem' ); ?></p>

		<?php if ( ! $by_supplier ) : ?>
			<p><?php esc_html_e( 'در حال حاضر سفارشی در این وضعیت نیست.', 'stocksystem' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $by_supplier as $supplier_id => $group ) : ?>
			<h2>
				<?php echo esc_html( $group['title'] ); ?>
				<?php if ( $group['phone'] ) : ?>
					<small style="font-weight:normal">— <?php echo esc_html( $group['phone'] ); ?></small>
				<?php endif; ?>
			</h2>
			<table class="widefat striped" style="margin-bottom:24px">
				<thead>
					<tr>
						<th><?php esc_html_e( 'کالا', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'تعداد', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'قیمت واحد', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'جمع', 'stocksystem' ); ?></th>
						<th><?php esc_html_e( 'سفارش‌ها', 'stocksystem' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php $supplier_total = 0; ?>
					<?php foreach ( $group['lines'] as $product_id => $line ) : ?>
						<?php
						$line_total     = null !== $line['unit_price'] ? $line['unit_price'] * $line['qty'] : null;
						$supplier_total += (float) $line_total;
						?>
						<tr>
							<td><a href="<?php echo esc_url( (string) get_edit_post_link( $product_id ) ); ?>"><?php echo esc_html( $line['title'] ); ?></a></td>
							<td><?php echo esc_html( $line['qty'] ); ?></td>
							<td><?php echo null === $line['unit_price'] ? '—' : esc_html( number_format( $line['unit_price'] ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></td>
							<td><?php echo null === $line_total ? '—' : esc_html( number_format( $line_total ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></td>
							<td>
								<?php foreach ( array_unique( $line['order_ids'] ) as $order_id ) : ?>
									<a href="<?php echo esc_url( $order_urls[ $order_id ] ?? '' ); ?>">#<?php echo esc_html( $order_id ); ?></a>
								<?php endforeach; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<?php if ( $supplier_id ) : ?>
					<tfoot>
						<tr>
							<th colspan="3"><?php esc_html_e( 'جمع کل این تامین‌کننده', 'stocksystem' ); ?></th>
							<th colspan="2"><?php echo esc_html( number_format( $supplier_total ) . ' ' . __( 'تومان', 'stocksystem' ) ); ?></th>
						</tr>
					</tfoot>
				<?php endif; ?>
			</table>
		<?php endforeach; ?>
	</div>
	<?php
}
