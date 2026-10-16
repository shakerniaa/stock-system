/**
 * Announcement bar (dismissible, remembered per message), cookie notice and
 * promo pop-up (shown after a delay, then quiet for N days). State lives in
 * localStorage; everything works without it, just without remembering.
 */
( function () {
	'use strict';

	// Close with motion.js's exit animation when it is loaded (plain .hidden otherwise).
	function setHidden( el, hidden ) {
		if ( window.stocksystemMotion ) {
			window.stocksystemMotion.setHidden( el, hidden );
		} else {
			el.hidden = hidden;
		}
	}

	function get( key ) {
		try {
			return window.localStorage.getItem( key );
		} catch ( e ) {
			return null;
		}
	}

	function set( key, value ) {
		try {
			window.localStorage.setItem( key, value );
		} catch ( e ) {
			/* private mode: nothing to remember */
		}
	}

	// Announcement bar.
	var bar = document.querySelector( '[data-announce]' );
	if ( bar ) {
		var barKey = 'ss_announce_' + bar.getAttribute( 'data-announce' );
		if ( get( barKey ) ) {
			bar.hidden = true;
		}
		var barClose = bar.querySelector( '[data-announce-close]' );
		if ( barClose ) {
			barClose.addEventListener( 'click', function () {
				setHidden( bar, true );
				set( barKey, '1' );
			} );
		}
	}

	// Cookie notice.
	var cookie = document.querySelector( '[data-cookie-notice]' );
	if ( cookie && ! get( 'ss_cookie_ok' ) ) {
		cookie.hidden = false;
		cookie.querySelector( '[data-cookie-accept]' ).addEventListener( 'click', function () {
			setHidden( cookie, true );
			set( 'ss_cookie_ok', '1' );
		} );
	}

	// Promo pop-up.
	var promo = document.querySelector( '[data-promo]' );
	if ( promo ) {
		var promoKey = 'ss_promo_' + promo.getAttribute( 'data-promo' );
		var until = Number( get( promoKey ) || 0 );
		var days = Number( promo.getAttribute( 'data-days' ) ) || 0;
		var opener = null;

		var closePromo = function () {
			setHidden( promo, true );
			set( promoKey, String( Date.now() + days * 86400000 ) );
			document.removeEventListener( 'keydown', onKey );
			if ( opener && opener.focus ) {
				opener.focus();
			}
		};

		var onKey = function ( event ) {
			if ( 'Escape' === event.key ) {
				closePromo();
			}
		};

		if ( Date.now() > until ) {
			window.setTimeout( function () {
				opener = document.activeElement;
				promo.hidden = false;
				var focusTarget = promo.querySelector( '.promo-pop__cta, .promo-pop__close' );
				if ( focusTarget ) {
					focusTarget.focus();
				}
				document.addEventListener( 'keydown', onKey );
			}, ( Number( promo.getAttribute( 'data-delay' ) ) || 0 ) * 1000 );
		}

		promo.querySelectorAll( '[data-promo-close]' ).forEach( function ( el ) {
			el.addEventListener( 'click', closePromo );
		} );
	}
} )();
