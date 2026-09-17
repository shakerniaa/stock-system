<?php
/**
 * Search-suggestions dropdown shell. Activates from the 3rd character
 * typed into its paired input (assets/js/navigation.js, matched via
 * aria-controls), separating matched categories from matched products.
 * Source: 13 Search Results §13-A.
 *
 * Rendered twice — once for the desktop masthead search, once for the
 * mobile header search — each with its own id, since they can't share
 * one absolutely-positioned panel.
 *
 * TODO: wire assets/js/navigation.js fetch() to a REST route that queries
 * products/categories server-side — this only renders the empty shell.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id = ! empty( $args['id'] ) ? $args['id'] : 'search-suggestions';
?>
<div id="<?php echo esc_attr( $id ); ?>" class="search-suggestions" role="listbox" hidden></div>
