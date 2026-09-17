<?php
/**
 * get_search_form() template — used wherever a widget/block calls it
 * directly rather than the header's own search markup.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$unique_id = wp_unique_id( 'search-form-' );
?>
<form role="search" method="get" class="site-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label for="<?php echo esc_attr( $unique_id ); ?>" class="screen-reader-text"><?php esc_html_e( 'جست‌وجوی مدل، برند یا مشخصات', 'stocksystem' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $unique_id ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جست‌وجوی مدل، برند یا مشخصات…', 'stocksystem' ); ?>">
	<input type="hidden" name="post_type" value="product">
	<button type="submit"><?php esc_html_e( 'جست‌وجو', 'stocksystem' ); ?></button>
</form>
