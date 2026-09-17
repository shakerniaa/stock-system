<?php
/**
 * Content pages auto-created on theme activation, same pattern as
 * stocksystem_create_order_tracking_page() in inc/woocommerce.php —
 * a fresh site gets working nav links (/about/, /contact/, /repair/,
 * /stock-condition/, /terms/, /faq/) without a manual wp-admin step.
 * Also publishes and templates WordPress's own auto-created privacy
 * policy page (see stocksystem_setup_privacy_page() below) rather than
 * creating a second one.
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
	return array(
		'about'           => array( __( 'دربارهٔ ما', 'stocksystem' ), 'page-templates/about-contact.php' ),
		'contact'         => array( __( 'تماس با ما', 'stocksystem' ), 'page-templates/about-contact.php' ),
		'repair'          => array( __( 'تعمیرات تخصصی', 'stocksystem' ), 'page-templates/repair.php' ),
		'stock-condition' => array( __( 'وضعیت کالای استوک', 'stocksystem' ), 'page-templates/stock-condition.php' ),
		'terms'           => array( __( 'قوانین و مقررات', 'stocksystem' ), 'page-templates/terms.php' ),
		'faq'             => array( __( 'سوالات متداول', 'stocksystem' ), 'page-templates/faq.php' ),
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

	stocksystem_setup_privacy_page();
}
add_action( 'after_switch_theme', 'stocksystem_create_content_pages' );

/**
 * WordPress auto-creates a "Reading > Privacy Policy" page on install
 * (`wp_page_for_privacy_policy` option) and leaves it as a draft with
 * placeholder boilerplate — reuse that page rather than creating a
 * second, competing privacy page at a different slug: assign our
 * template and publish it.
 */
function stocksystem_setup_privacy_page() {
	$page_id = (int) get_option( 'wp_page_for_privacy_policy' );

	if ( ! $page_id || ! get_post( $page_id ) ) {
		return;
	}

	wp_update_post(
		array(
			'ID'            => $page_id,
			'post_status'   => 'publish',
			'page_template' => 'page-templates/privacy.php',
		)
	);
}
