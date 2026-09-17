/**
 * Global toast notifications — for real-time feedback on actions that
 * don't reload the page (wishlist toggle, etc). Actions with a full
 * page reload keep their own server-rendered inline notice instead
 * (repair-request, notify-me, wallet top-up) — a toast would just
 * duplicate feedback that's already on screen after reload.
 *
 * Usage: window.stocksystemToast.show('پیام', 'success' | 'error').
 */
( function () {
	'use strict';

	var container;

	function getContainer() {
		if ( ! container ) {
			container = document.getElementById( 'toast-container' );
		}
		return container;
	}

	function show( message, type ) {
		var root = getContainer();
		if ( ! root ) {
			return;
		}

		var toast = document.createElement( 'div' );
		toast.className = 'toast toast--' + ( type || 'success' );
		toast.setAttribute( 'role', 'status' );
		toast.setAttribute( 'aria-live', 'polite' );
		toast.textContent = message;

		root.appendChild( toast );

		requestAnimationFrame( function () {
			toast.classList.add( 'is-visible' );
		} );

		window.setTimeout( function () {
			toast.classList.remove( 'is-visible' );
			toast.addEventListener( 'transitionend', function () {
				toast.remove();
			}, { once: true } );
		}, 4000 );
	}

	window.stocksystemToast = { show: show };
} )();
