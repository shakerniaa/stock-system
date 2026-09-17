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
 * Seeds the A / B / C grading terms with their term meta once, on theme
 * activation. `07 Stock Condition.dc.html` is now built (page-templates/
 * stock-condition.php) and is the source of truth per its own comment
 * above — copy below matches that file, not the shorter/older wording
 * from `13 Search Results.dc.html` this used to cite (that also had
 * grade C at red `#C62828`; 07 has it at orange `#F58220`, matching
 * here now).
 *
 * `condition_note` (short, product-card badge) stays separate from the
 * term's own `description` (long, grading-page paragraph — read by
 * stocksystem_grading_terms_for_display() below) so the product card
 * doesn't have to truncate a paragraph.
 */
function stocksystem_seed_grading_terms() {
	$grades = array(
		'A' => array(
			'title'       => __( 'در حد نو', 'stocksystem' ),
			'note'        => __( 'بدنه در حد نو', 'stocksystem' ),
			'description' => __( 'بدون خط و خش قابل مشاهده در فاصلهٔ ۳۰ سانتی‌متر. باتری بالای ۸۵٪. صفحه بدون پیکسل سوخته یا لکه.', 'stocksystem' ),
			'color'       => '#13A05C',
		),
		'B' => array(
			'title'       => __( 'کارکردهٔ سالم', 'stocksystem' ),
			'note'        => __( 'خط جزئی روی بدنه', 'stocksystem' ),
			'description' => __( 'خط‌های جزئی روی بدنه یا درب. باتری ۷۰ تا ۸۵٪. عملکرد کامل و بدون ایراد فنی.', 'stocksystem' ),
			'color'       => '#E0A302',
		),
		'C' => array(
			'title'       => __( 'اقتصادی', 'stocksystem' ),
			'note'        => __( 'فرسودگی قابل مشاهده، سالم از نظر فنی', 'stocksystem' ),
			'description' => __( 'آثار استفادهٔ واضح یا باتری زیر ۷۰٪. قیمت متناسب و برچسب صریح روی صفحهٔ محصول.', 'stocksystem' ),
			'color'       => '#F58220',
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
		update_term_meta( $term_id, 'grade_title', $data['title'] );
	}
}
add_action( 'after_switch_theme', 'stocksystem_seed_grading_terms' );

/**
 * A/B/C terms with everything the grading page's cards need, in display
 * order — falls back to nothing (page renders its intro/FAQ sections
 * only) if the taxonomy has no terms yet.
 */
function stocksystem_grading_terms_for_display() {
	if ( ! taxonomy_exists( 'product_grading' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_grading',
			'hide_empty' => false,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	// No taxonomy-product_grading.php archive template exists yet, so
	// these deliberately aren't links — just display cards, matching
	// the design (which doesn't treat them as filter links either).
	return array_map(
		function ( $term ) {
			return (object) array(
				'letter'  => $term->name,
				'title'   => get_term_meta( $term->term_id, 'grade_title', true ),
				'summary' => $term->description,
				'color'   => get_term_meta( $term->term_id, 'dot_color', true ) ?: '#0EBAAF',
			);
		},
		$terms
	);
}
