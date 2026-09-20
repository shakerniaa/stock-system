/**
 * Global toast notifications — for real-time feedback on actions that
 * don't reload the page (wishlist toggle, etc). Actions with a full
 * page reload keep their own server-rendered inline notice instead
 * (repair-request, notify-me, wallet top-up) — a toast would just
 * duplicate feedback that's already on screen after reload.
 *
 * Usage: window.stocksystemToast.show('پیام', 'success' | 'error' | 'warning' | 'info',
 *        { label: 'بازگردانی', href: '/…' } | { label, onClick });
 * 11 Desktop States §03: dark surface, coloured leading edge, icon, optional
 * action on the far side, 4 s.
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

	var ICONS = {
		success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"></path></svg>',
		error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 7.5v5M12 16.2v.3"></path></svg>',
		info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h10"></path></svg>'
	};
	ICONS.warning = ICONS.error;

	function show( message, type, action ) {
		var root = getContainer();
		if ( ! root ) {
			return;
		}

		var toast = document.createElement( 'div' );
		toast.className = 'toast toast--' + ( type || 'success' );
		toast.setAttribute( 'role', 'error' === type ? 'alert' : 'status' );

		var kind = type || 'success';
		var icon = document.createElement( 'span' );
		icon.className = 'toast__icon';
		icon.innerHTML = ICONS[ kind ] || ICONS.info;

		var text = document.createElement( 'span' );
		text.className = 'toast__text';
		text.textContent = message;

		toast.appendChild( icon );
		toast.appendChild( text );

		if ( action && action.label ) {
			var link = document.createElement( action.href ? 'a' : 'button' );
			link.className = 'toast__action';
			link.textContent = action.label;
			if ( action.href ) {
				link.href = action.href;
			} else {
				link.type = 'button';
				link.addEventListener( 'click', function () {
					if ( action.onClick ) {
						action.onClick();
					}
					toast.remove();
				} );
			}
			toast.appendChild( link );
		}

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
