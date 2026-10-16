/**
 * Archive filter sidebar: auto-submit on checkbox/radio change (the
 * "اعمال فیلتر" button is the no-JS fallback), and the mobile bottom-sheet
 * that the same sidebar renders inside below 1024px.
 * Source: 02 Category.dc.html, 10 Mobile Flows.dc.html, 13 Search
 * Results.dc.html §13-D.
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

	function initAutoSubmit() {
		var form = document.getElementById( 'archive-filters' );
		if ( ! form ) {
			return;
		}

		form.querySelectorAll( 'input[type="checkbox"], input[type="radio"]' ).forEach( function ( input ) {
			input.addEventListener( 'change', function () {
				form.requestSubmit ? form.requestSubmit() : form.submit();
			} );
		} );
	}

	function initMobileSheet() {
		var sheet = document.getElementById( 'archive-filters-sidebar' );
		var toggle = document.getElementById( 'mobile-filters-toggle' );
		var backdrop = document.getElementById( 'mobile-filters-backdrop' );
		var closeBtn = document.getElementById( 'mobile-filters-close' );
		if ( ! sheet || ! toggle || ! backdrop ) {
			return;
		}

		function open() {
			sheet.classList.add( 'is-open' );
			setHidden( backdrop, false );
			toggle.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
		}

		function close() {
			sheet.classList.remove( 'is-open' );
			setHidden( backdrop, true );
			toggle.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
		}

		toggle.addEventListener( 'click', open );
		backdrop.addEventListener( 'click', close );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', close );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && sheet.classList.contains( 'is-open' ) ) {
				close();
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initAutoSubmit();
		initMobileSheet();
	} );
} )();
