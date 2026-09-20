/**
 * Cart page: quantity changes update the cart on their own (debounced),
 * replacing the page frame with the server's fresh render — line prices,
 * summary and the mobile total bar (all inside the frame) come from the same response, so
 * nothing goes stale (WooCommerce's own cart AJAX only refreshes
 * `.cart_totals`, which this theme's summary card doesn't use).
 * Without JS the «به‌روزرسانی سبد» button still submits the form.
 */
( function () {
	'use strict';

	var timer = null;
	var busy = false;

	function frame() {
		return document.querySelector( '.checkout-page > .container' );
	}

	function update( form ) {
		var button = form.querySelector( '[name="update_cart"]' );
		var data = new FormData( form );

		if ( button ) {
			data.append( 'update_cart', button.value );
		}

		busy = true;
		form.closest( '.checkout-page' ).classList.add( 'is-updating' );

		fetch( form.action, { method: 'POST', body: data, credentials: 'same-origin' } )
			.then( function ( response ) {
				return response.text();
			} )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				var fresh = doc.querySelector( '.checkout-page > .container' );

				// Emptied the cart (or anything unexpected): show the real page.
				if ( ! fresh || ! fresh.querySelector( '.woocommerce-cart-form' ) ) {
					window.location.reload();
					return;
				}

				frame().innerHTML = fresh.innerHTML;

				if ( window.jQuery ) {
					window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
				}
			} )
			.catch( function () {
				window.location.reload();
			} )
			.then( function () {
				busy = false;
				var page = document.querySelector( '.checkout-page' );
				if ( page ) {
					page.classList.remove( 'is-updating' );
				}
			} );
	}

	document.addEventListener( 'change', function ( event ) {
		var input = event.target;
		if ( ! input.matches || ! input.matches( '.woocommerce-cart-form input.qty' ) ) {
			return;
		}

		var form = input.closest( 'form' );
		window.clearTimeout( timer );
		timer = window.setTimeout( function () {
			if ( ! busy ) {
				update( form );
			}
		}, 700 );
	} );

	document.documentElement.classList.add( 'cart-auto-update' );
} )();
