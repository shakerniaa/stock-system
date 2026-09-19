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

	/* ---- Mega menu ----
	   Opens ONLY from «همهٔ دسته‌ها» (hover, focus or click). Inside the
	   menu, hovering/focusing a category swaps in that category's brands,
	   price links and featured product (70ms intent delay so the panes
	   don't flicker while the pointer crosses the column). Leaving the nav
	   area, or moving onto the plain bar links, closes it after 200ms/80ms
	   and resets it to the global view. */
	function initMegaMenu() {
		var toggle = document.getElementById( 'mega-menu-toggle' );
		var menu = document.getElementById( 'mega-menu' );
		if ( ! toggle || ! menu ) {
			return;
		}

		var nav = toggle.closest( '.primary-nav' );
		var panes = menu.querySelectorAll( '[data-mega-pane]' );
		var categoryLinks = menu.querySelectorAll( '[data-mega-target]' );
		var barLinks = nav.querySelectorAll( '.primary-nav__links a' );
		var current = 'all';
		var closeTimer = null;
		var switchTimer = null;

		function paint() {
			panes.forEach( function ( pane ) {
				pane.hidden = pane.getAttribute( 'data-mega-pane' ) !== current;
			} );
			categoryLinks.forEach( function ( link ) {
				link.classList.toggle( 'is-active', link.getAttribute( 'data-mega-target' ) === current );
			} );
			toggle.classList.toggle( 'is-active', ! menu.hidden );
		}

		function cancelClose() {
			if ( closeTimer ) {
				window.clearTimeout( closeTimer );
				closeTimer = null;
			}
		}

		function open() {
			cancelClose();
			openPanel( menu, toggle );
			paint();
		}

		function close() {
			window.clearTimeout( switchTimer );
			closePanel( menu, toggle );
			current = 'all';
			paint();
		}

		function scheduleClose( delay ) {
			cancelClose();
			closeTimer = window.setTimeout( close, delay );
		}

		nav.addEventListener( 'mouseenter', cancelClose );
		nav.addEventListener( 'mouseleave', function () {
			scheduleClose( 200 );
		} );

		toggle.addEventListener( 'mouseenter', open );
		toggle.addEventListener( 'focus', open );
		toggle.addEventListener( 'click', function () {
			if ( menu.hidden ) {
				open();
			} else {
				close();
			}
		} );

		barLinks.forEach( function ( link ) {
			link.addEventListener( 'mouseenter', function () {
				scheduleClose( 80 );
			} );
			link.addEventListener( 'focus', function () {
				scheduleClose( 80 );
			} );
		} );

		categoryLinks.forEach( function ( link ) {
			var key = link.getAttribute( 'data-mega-target' );

			function show() {
				window.clearTimeout( switchTimer );
				switchTimer = window.setTimeout( function () {
					current = key;
					paint();
				}, 70 );
			}

			link.addEventListener( 'mouseenter', show );
			link.addEventListener( 'focus', show );
			link.addEventListener( 'mouseleave', function () {
				window.clearTimeout( switchTimer );
			} );
		} );

		nav.addEventListener( 'focusout', function ( event ) {
			if ( ! event.relatedTarget || ! nav.contains( event.relatedTarget ) ) {
				scheduleClose( 120 );
			}
		} );

		nav.addEventListener( 'keydown', function ( event ) {
			if ( 'Escape' === event.key && ! menu.hidden ) {
				close();
				toggle.focus();
			}
		} );

		paint();
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
