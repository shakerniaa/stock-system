<?php
/**
 * Blog: posts-page provisioning + shared helpers.
 * Source: 05 Blog.dc.html.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates a "blog" page and sets it as the Reading > "Posts page" so
 * /blog/ (hardcoded throughout the header/footer nav) resolves to the
 * post index. Same auto-provision pattern as
 * stocksystem_create_order_tracking_page() / stocksystem_create_content_pages().
 * home.php is what actually renders it.
 *
 * A real trap this hit: `page_for_posts` alone does nothing — WordPress
 * only treats it as the posts index when `show_on_front` is `'page'`.
 * Left at the default `'posts'`, front-page.php still covers "/" (its
 * own hierarchy rule fires regardless of show_on_front, which is why
 * the homepage worked before this), but /blog/ — now a completely
 * ordinary static page — falls through to page.php/index.php, blank,
 * instead of ever reaching home.php. So this also sets `show_on_front`
 * to `'page'` and provisions a `page_on_front`, even though
 * front-page.php ignores that page's actual post content and always
 * renders the hardcoded homepage template parts.
 */
function stocksystem_create_blog_page() {
	if ( ! get_option( 'page_on_front' ) ) {
		$front = get_page_by_path( 'home' );
		$front_id = $front ? $front->ID : null;

		if ( ! $front_id ) {
			$front_id = wp_insert_post(
				array(
					'post_title'     => __( 'صفحهٔ اصلی', 'stocksystem' ),
					'post_name'      => 'home',
					'post_status'    => 'publish',
					'post_type'      => 'page',
					'comment_status' => 'closed',
				)
			);
		}

		if ( $front_id && ! is_wp_error( $front_id ) ) {
			update_option( 'page_on_front', $front_id );
			update_option( 'show_on_front', 'page' );
		}
	}

	if ( get_option( 'page_for_posts' ) ) {
		return;
	}

	$existing = get_page_by_path( 'blog' );
	$page_id  = $existing ? $existing->ID : null;

	if ( ! $page_id ) {
		$page_id = wp_insert_post(
			array(
				'post_title'     => __( 'بلاگ', 'stocksystem' ),
				'post_name'      => 'blog',
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'comment_status' => 'closed',
			)
		);
	}

	if ( $page_id && ! is_wp_error( $page_id ) ) {
		update_option( 'page_for_posts', $page_id );
	}
}
add_action( 'after_switch_theme', 'stocksystem_create_blog_page' );

/**
 * Estimated reading time in minutes, Persian-Indic digits. Shared by the
 * homepage teaser, the archive/category grid, and the single-post meta
 * row so the ~200wpm estimate only lives in one place.
 */
function stocksystem_reading_time( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 1;
	}

	$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );

	return max( 1, (int) round( $word_count / 200 ) );
}

/**
 * [stocksystem_product_promo id="15"] — the "مقاله میان‌متنی" inline
 * product callout (05 Blog.dc.html), reusing the same buy-box markup
 * pattern as the product card rather than a bespoke block/plugin. Authors
 * drop this shortcode into a post's content where they want it.
 */
function stocksystem_product_promo_shortcode( $atts ) {
	$atts = shortcode_atts( array( 'id' => 0 ), $atts, 'stocksystem_product_promo' );
	$id   = absint( $atts['id'] );

	if ( ! $id || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $id );

	if ( ! $product || 'publish' !== $product->get_status() ) {
		return '';
	}

	ob_start();
	?>
	<a class="blog-promo" href="<?php echo esc_url( $product->get_permalink() ); ?>">
		<span class="blog-promo__image"><?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?></span>
		<span class="blog-promo__body">
			<span class="blog-promo__eyebrow"><?php esc_html_e( 'محصول مرتبط با این مقاله', 'stocksystem' ); ?></span>
			<span class="blog-promo__title"><?php echo esc_html( $product->get_name() ); ?></span>
		</span>
		<span class="blog-promo__cta">
			<span class="blog-promo__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<span class="btn btn--primary"><?php esc_html_e( 'مشاهدهٔ دستگاه', 'stocksystem' ); ?></span>
		</span>
	</a>
	<?php
	return ob_get_clean();
}
add_shortcode( 'stocksystem_product_promo', 'stocksystem_product_promo_shortcode' );

/**
 * Sticky table-of-contents sidebar (single.php): walks the post's H2s,
 * gives each a slug id (via the 'wp_unique_post_slug'-style sanitizer)
 * and returns [ [ 'id' => '', 'text' => '' ], … ]. The same ids are
 * injected into $content by stocksystem_add_toc_ids_to_content() below,
 * hooked on 'the_content' so both stay in sync off one source of truth.
 */
function stocksystem_post_toc_items( $post = null ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	preg_match_all( '/<h2[^>]*>(.*?)<\/h2>/i', $post->post_content, $matches );

	$items = array();
	foreach ( $matches[1] as $heading ) {
		$text = wp_strip_all_tags( $heading );
		$items[] = array(
			'id'   => sanitize_title( $text ),
			'text' => $text,
		);
	}

	return $items;
}

function stocksystem_add_toc_ids_to_content( $content ) {
	if ( ! is_single() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	return preg_replace_callback(
		'/<h2([^>]*)>(.*?)<\/h2>/i',
		function ( $match ) {
			$id = sanitize_title( wp_strip_all_tags( $match[2] ) );
			return '<h2' . $match[1] . ' id="' . esc_attr( $id ) . '">' . $match[2] . '</h2>';
		},
		$content
	);
}
add_filter( 'the_content', 'stocksystem_add_toc_ids_to_content', 5 );
