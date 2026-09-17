/**
 * Wishlist heart toggle (template-parts/product/wishlist-toggle.php).
 * Delegated so it works for cards rendered after page load too.
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.wishlist-toggle' );
		if ( ! button || ! window.stocksystemAjax ) {
			return;
		}

		event.preventDefault();

		var body = new URLSearchParams();
		body.set( 'action', 'stocksystem_toggle_wishlist' );
		body.set( 'nonce', button.getAttribute( 'data-nonce' ) );
		body.set( 'product_id', button.getAttribute( 'data-product-id' ) );

		fetch( window.stocksystemAjax.url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} )
			.then( function ( response ) {
				return response.json();
			} )
			.then( function ( json ) {
				if ( json.data && json.data.require_login ) {
					window.location.href = window.stocksystemAjax.accountUrl;
					return;
				}

				if ( json.success ) {
					button.classList.toggle( 'is-active', json.data.in_wishlist );
					button.setAttribute( 'aria-pressed', json.data.in_wishlist ? 'true' : 'false' );
					var svg = button.querySelector( 'svg' );
					if ( svg ) {
						svg.setAttribute( 'fill', json.data.in_wishlist ? 'currentColor' : 'none' );
					}
				}
			} );
	} );
} )();
