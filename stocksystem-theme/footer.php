<?php
/**
 * Site footer wrapper.
 *
 * @package StockSystem
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_template_part( 'template-parts/footer/site-footer' );
?>

<div id="toast-container" aria-live="polite"></div>

<?php wp_footer(); ?>
</body>
</html>
