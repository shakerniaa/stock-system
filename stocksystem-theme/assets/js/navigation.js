/**
 * Header interactions: mega menu (hover + 200ms close delay), mini-cart
 * drawer (opens on toggle, auto-closes 4s after an add-to-cart), search
 * suggestions (activates from the 3rd character), mobile drawer.
 * Source: 11 Desktop States.dc.html, 10 Mobile Flows.dc.html.
 */
( function () {
	'use strict';

	function openPanel( panel, trigger ) {
		panel.hidden = false;
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'true' );
		}
	}

	function closePanel( panel, trigger ) {
		panel.hidden = true;
		if ( trigger ) {
			trigger.setAttribute( 'aria-expanded', 'false' );
		}
	}

	/* ---- Mega menu: hover open, 200ms close delay ---- */
	function initMegaMenu() {
		var toggle = document.getElementById( 'mega-menu-toggle' );
		var menu = document.getElementById( 'mega-menu' );
		if ( ! toggle || ! menu ) {
			return;
		}

		var closeTimer = null;
		var nav = toggle.closest( '.primary-nav' );

		function scheduleClose() {
			closeTimer = window.setTimeout( function () {
				closePanel( menu, toggle );
			}, 200 );
		}

		function cancelClose() {
			if ( closeTimer ) {
				window.clearTimeout( closeTimer );
				closeTimer = null;
			}
		}

		nav.addEventListener( 'mouseenter', function () {
			cancelClose();
			openPanel( menu, toggle );
		} );
		nav.addEventListener( 'mouseleave', scheduleClose );

		toggle.addEventListener( 'click', function () {
			if ( 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
				closePanel( menu, toggle );
			} else {
				openPanel( menu, toggle );
			}
		} );

		toggle.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key ) {
				closePanel( menu, toggle );
				toggle.focus();
			}
		} );
	}

	/* ---- Mini-cart: toggle open/close, auto-close 4s after cart update ---- */
	function initMiniCart() {
		var panel = document.getElementById( 'mini-cart' );
		if ( ! panel ) {
			return;
		}

		var toggles = document.querySelectorAll( '#mini-cart-toggle, #mini-cart-toggle-mobile' );
		var autoCloseTimer = null;

		function toggleFor( trigger ) {
			return function ( event ) {
				event.preventDefault();
				if ( panel.hidden ) {
					openPanel( panel, trigger );
				} else {
					closePanel( panel, trigger );
				}
			};
		}

		toggles.forEach( function ( trigger ) {
			trigger.addEventListener( 'click', toggleFor( trigger ) );
		} );

		document.addEventListener( 'click', function ( event ) {
			if ( panel.hidden ) {
				return;
			}
			var clickedInsidePanel = panel.contains( event.target );
			var clickedTrigger = event.target.closest( '#mini-cart-toggle, #mini-cart-toggle-mobile' );
			if ( ! clickedInsidePanel && ! clickedTrigger ) {
				closePanel( panel, document.getElementById( 'mini-cart-toggle' ) );
			}
		} );

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! panel.hidden ) {
				closePanel( panel, document.getElementById( 'mini-cart-toggle' ) );
			}
		} );

		// WooCommerce fires this jQuery event on every successful add-to-cart.
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart', function () {
				openPanel( panel, document.getElementById( 'mini-cart-toggle' ) );
				if ( autoCloseTimer ) {
					window.clearTimeout( autoCloseTimer );
				}
				autoCloseTimer = window.setTimeout( function () {
					closePanel( panel, document.getElementById( 'mini-cart-toggle' ) );
				}, 4000 );
			} );
		}
	}

	/* ---- Search suggestions: activate from the 3rd character ---- */
	function initSearchSuggestions() {
		var inputs = document.querySelectorAll( '#site-search-input, #site-search-input-mobile' );

		inputs.forEach( function ( input ) {
			var panel = document.getElementById( input.getAttribute( 'aria-controls' ) );
			if ( ! panel ) {
				return;
			}

			var debounceTimer = null;

			input.addEventListener( 'input', function () {
				window.clearTimeout( debounceTimer );
				var query = input.value.trim();

				if ( query.length < 3 ) {
					panel.hidden = true;
					input.setAttribute( 'aria-expanded', 'false' );
					return;
				}

				debounceTimer = window.setTimeout( function () {
					// TODO: fetch(`/wp-json/stocksystem/v1/search-suggest?q=${query}`)
					// and render results into `panel` once that REST route exists.
					panel.hidden = false;
					input.setAttribute( 'aria-expanded', 'true' );
				}, 200 );
			} );

			input.addEventListener( 'blur', function () {
				window.setTimeout( function () {
					panel.hidden = true;
					input.setAttribute( 'aria-expanded', 'false' );
				}, 150 );
			} );
		} );
	}

	/* ---- Mobile drawer ---- */
	function initMobileDrawer() {
		var drawer = document.getElementById( 'mobile-drawer' );
		var backdrop = document.getElementById( 'mobile-drawer-backdrop' );
		var openBtn = document.getElementById( 'mobile-drawer-toggle' );
		var closeBtn = document.getElementById( 'mobile-drawer-close' );
		if ( ! drawer || ! backdrop || ! openBtn ) {
			return;
		}

		function open() {
			drawer.hidden = false;
			backdrop.hidden = false;
			openBtn.setAttribute( 'aria-expanded', 'true' );
			document.body.style.overflow = 'hidden';
			if ( closeBtn ) {
				closeBtn.focus();
			}
		}

		function close() {
			drawer.hidden = true;
			backdrop.hidden = true;
			openBtn.setAttribute( 'aria-expanded', 'false' );
			document.body.style.overflow = '';
			openBtn.focus();
		}

		openBtn.addEventListener( 'click', open );
		backdrop.addEventListener( 'click', close );
		if ( closeBtn ) {
			closeBtn.addEventListener( 'click', close );
		}

		document.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! drawer.hidden ) {
				close();
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initMegaMenu();
		initMiniCart();
		initSearchSuggestions();
		initMobileDrawer();
	} );
} )();
