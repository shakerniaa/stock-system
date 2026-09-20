/**
 * Wishlist heart toggle (template-parts/product/wishlist-toggle.php).
 * Delegated so it works for cards rendered after page load too.
 */
( function () {
	'use strict';

	function toast( message, type ) {
		if ( window.stocksystemToast ) {
			window.stocksystemToast.show( message, type );
		}
	}

	// Wishlist page: the × removes the row (same endpoint as the heart toggle).
	document.addEventListener( 'click', function ( event ) {
		var remove = event.target.closest( '.wishlist-row__remove' );
		if ( ! remove || ! window.stocksystemAjax || remove.disabled ) {
			return;
		}

		var row = remove.closest( '.wishlist-row' );
		var body = new URLSearchParams();
		body.set( 'action', 'stocksystem_toggle_wishlist' );
		body.set( 'nonce', remove.getAttribute( 'data-nonce' ) );
		body.set( 'product_id', remove.getAttribute( 'data-product-id' ) );
		remove.disabled = true;
		row.classList.add( 'is-removing' );

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
				if ( ! json.success || json.data.in_wishlist ) {
					throw new Error( 'not-removed' );
				}

				var table = row.closest( '.wishlist-table' );
				row.remove();
				var left = table.querySelectorAll( '.wishlist-row' ).length;
				var count = document.querySelector( '[data-wishlist-count]' );
				if ( count ) {
					count.textContent = String( left ).replace( /[0-9]/g, function ( d ) {
						return '۰۱۲۳۴۵۶۷۸۹'.charAt( Number( d ) );
					} );
					count.textContent = window.stocksystemT( 'wishlist_count', '%s کالا ذخیره شده' ).replace( '%s', count.textContent );
				}
				if ( ! left ) {
					table.hidden = true;
					var empty = document.querySelector( '[data-wishlist-empty]' );
					if ( empty ) {
						empty.hidden = false;
					}
				}
				toast( window.stocksystemT( 'wishlist_removed', 'از علاقه‌مندی‌ها حذف شد' ), 'info' );
			} )
			.catch( function () {
				remove.disabled = false;
				row.classList.remove( 'is-removing' );
				toast( window.stocksystemT( 'wishlist_remove_failed', 'حذف انجام نشد، دوباره تلاش کنید.' ), 'error' );
			} );
	} );

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.wishlist-toggle' );
		if ( ! button || ! window.stocksystemAjax ) {
			return;
		}

		event.preventDefault();

		if ( button.classList.contains( 'is-loading' ) ) {
			return;
		}

		var body = new URLSearchParams();
		body.set( 'action', 'stocksystem_toggle_wishlist' );
		body.set( 'nonce', button.getAttribute( 'data-nonce' ) );
		body.set( 'product_id', button.getAttribute( 'data-product-id' ) );

		button.classList.add( 'is-loading' );

		fetch( window.stocksystemAjax.url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} )
			.then( function ( response ) {
				if ( ! response.ok ) {
					throw new Error( 'http-error' );
				}
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
					toast(
						json.data.in_wishlist ? window.stocksystemT( 'wishlist_added', 'به علاقه‌مندی‌ها اضافه شد' ) : window.stocksystemT( 'wishlist_removed', 'از علاقه‌مندی‌ها حذف شد' ),
						'success'
					);
				} else {
					toast( window.stocksystemT( 'generic_error', 'مشکلی پیش آمد، دوباره تلاش کنید' ), 'error' );
				}
			} )
			.catch( function () {
				toast( window.stocksystemT( 'offline_error', 'اتصال برقرار نشد. اتصال اینترنت را بررسی کنید.' ), 'error' );
			} )
			.finally( function () {
				button.classList.remove( 'is-loading' );
			} );
	} );
} )();
