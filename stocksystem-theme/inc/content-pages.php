<?php
/**
 * Content pages auto-created on theme activation, same pattern as
 * stocksystem_create_order_tracking_page() in inc/woocommerce.php —
 * a fresh site gets working nav links (/about/, /contact/, /repair/,
 * /stock-condition/) without a manual wp-admin step.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * slug => [ title, page template ].
 */
function stocksystem_content_pages() {
	// Nav (header/footer) already links to /terms/ too — add it here
	// once its page template exists (PROGRESS.md tracks this as still
	// open).
	return array(
		'about'           => array( __( 'دربارهٔ ما', 'stocksystem' ), 'page-templates/about-contact.php' ),
		'contact'         => array( __( 'تماس با ما', 'stocksystem' ), 'page-templates/about-contact.php' ),
		'repair'          => array( __( 'تعمیرات تخصصی', 'stocksystem' ), 'page-templates/repair.php' ),
		'stock-condition' => array( __( 'وضعیت کالای استوک', 'stocksystem' ), 'page-templates/stock-condition.php' ),
	);
}

function stocksystem_create_content_pages() {
	foreach ( stocksystem_content_pages() as $slug => $page ) {
		list( $title, $template ) = $page;

		$existing = get_page_by_path( $slug );
		if ( $existing ) {
			continue;
		}

		wp_insert_post(
			array(
				'post_title'     => $title,
				'post_name'      => $slug,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'page_template'  => $template,
				'comment_status' => 'closed',
			)
		);
	}
}
add_action( 'after_switch_theme', 'stocksystem_create_content_pages' );
