/**
 * Quantity stepper −/+ buttons (woocommerce/global/quantity-input.php).
 * Dispatches a native 'change' event so WooCommerce's own cart/AJAX
 * scripts react exactly as if the number input were edited directly.
 */
( function () {
	'use strict';

	function step( input, direction ) {
		var step = parseFloat( input.step ) || 1;
		var min = '' !== input.min ? parseFloat( input.min ) : 0;
		var max = '' !== input.max ? parseFloat( input.max ) : Infinity;
		var value = parseFloat( input.value ) || 0;
		var next = value + ( direction * step );

		next = Math.max( min, Math.min( max, next ) );
		input.value = next;
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.quantity-stepper__btn' );
		if ( ! button ) {
			return;
		}

		var input = button.parentElement.querySelector( 'input.qty' );
		if ( ! input ) {
			return;
		}

		step( input, button.classList.contains( 'quantity-stepper__btn--plus' ) ? 1 : -1 );
	} );
} )();
