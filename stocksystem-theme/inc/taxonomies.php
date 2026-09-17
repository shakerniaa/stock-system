<?php
/**
 * Custom product taxonomies. WooCommerce ships product_cat and
 * product_tag but not brand or grading — README: "Product brand — a real
 * taxonomy (product_brand)" and "Product grading (A+/A/B/C) — product
 * attribute or taxonomy". Registered here rather than left to a plugin
 * decision, since inc/nav-data.php, inc/product-card.php, and the
 * upcoming archive templates all assume they exist.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function stocksystem_register_taxonomies() {
	register_taxonomy(
		'product_brand',
		'product',
		array(
			'label'             => __( 'برند', 'stocksystem' ),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'brand', 'with_front' => false ),
		)
	);

	register_taxonomy(
		'product_grading',
		'product',
		array(
			'label'             => __( 'گرید ظاهری', 'stocksystem' ),
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'grading', 'with_front' => false ),
		)
	);
}
add_action( 'init', 'stocksystem_register_taxonomies' );

/**
 * Seeds the A / B / C grading terms with their condition note + dot color
 * term meta (read by stocksystem_product_grading_note()) once, on theme
 * activation. Copy sourced from 13 Search Results.dc.html §13-B, the most
 * detailed grading description in the design set — 07 Stock Condition is
 * the eventual source of truth once that page is built.
 */
function stocksystem_seed_grading_terms() {
	$grades = array(
		'A' => array(
			'description' => __( 'بدون خط‌وخش قابل مشاهده', 'stocksystem' ),
			'note'        => __( 'بدنه در حد نو', 'stocksystem' ),
			'color'       => '#13A05C',
		),
		'B' => array(
			'description' => __( 'خط‌وخش جزئی روی بدنه', 'stocksystem' ),
			'note'        => __( 'خط جزئی روی بدنه', 'stocksystem' ),
			'color'       => '#E0A302',
		),
		'C' => array(
			'description' => __( 'فرسودگی مشخص، سالم از نظر فنی', 'stocksystem' ),
			'note'        => __( 'فرسودگی قابل مشاهده، سالم از نظر فنی', 'stocksystem' ),
			'color'       => '#C62828',
		),
	);

	foreach ( $grades as $slug => $data ) {
		$term = get_term_by( 'slug', strtolower( $slug ), 'product_grading' );

		if ( ! $term ) {
			$inserted = wp_insert_term( $slug, 'product_grading', array( 'slug' => strtolower( $slug ) ) );
			if ( is_wp_error( $inserted ) ) {
				continue;
			}
			$term_id = $inserted['term_id'];
		} else {
			$term_id = $term->term_id;
		}

		wp_update_term( $term_id, 'product_grading', array( 'description' => $data['description'] ) );
		update_term_meta( $term_id, 'condition_note', $data['note'] );
		update_term_meta( $term_id, 'dot_color', $data['color'] );
	}
}
add_action( 'after_switch_theme', 'stocksystem_seed_grading_terms' );
