/**
 * Adds `is-typing` to <html> while a text field has focus, so fixed bottom
 * bars (checkout/cart total bar, product buy bar) can step aside: with the
 * on-screen keyboard open they would eat a third of the visible form.
 */
( function () {
	'use strict';

	var root = document.documentElement;

	function isTextField( el ) {
		if ( ! el || ! el.matches ) {
			return false;
		}
		if ( el.matches( 'textarea, select' ) ) {
			return true;
		}
		return el.matches( 'input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="submit"]):not([type="button"]):not(.qty)' );
	}

	document.addEventListener( 'focusin', function ( event ) {
		root.classList.toggle( 'is-typing', isTextField( event.target ) );
	} );

	document.addEventListener( 'focusout', function () {
		// Focus moving between two fields fires focusout → focusin; the next
		// focusin re-adds the class before paint.
		window.setTimeout( function () {
			if ( ! isTextField( document.activeElement ) ) {
				root.classList.remove( 'is-typing' );
			}
		}, 0 );
	} );
} )();
