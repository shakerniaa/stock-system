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

		// Remove a line without leaving the page: WooCommerce's remove_from_cart
		// endpoint drops the item, then the cart-fragments refresh re-renders
		// #mini-cart-content and the header badges.
		panel.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( '.mini-cart-panel__remove' );
			if ( ! button || button.disabled ) {
				return;
			}
			event.preventDefault();

			var row = button.closest( '.mini-cart-panel__row' );
			var endpoint = ( window.wc_add_to_cart_params && window.wc_add_to_cart_params.wc_ajax_url ) || '/?wc-ajax=%%endpoint%%';
			var name = button.getAttribute( 'data-product-name' );

			button.disabled = true;
			if ( row ) {
				row.classList.add( 'is-removing' );
			}

			fetch( endpoint.replace( '%%endpoint%%', 'remove_from_cart' ), {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: 'cart_item_key=' + encodeURIComponent( button.getAttribute( 'data-cart-item-key' ) )
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( data ) {
					if ( ! data || ! data.fragments ) {
						throw new Error( 'remove failed' );
					}
					if ( window.jQuery ) {
						window.jQuery( document.body ).trigger( 'wc_fragment_refresh' );
					}
					if ( window.stocksystemToast ) {
						window.stocksystemToast.show( name ? window.stocksystemT( 'cart_item_removed', '«%s» از سبد حذف شد' ).replace( '%s', name ) : window.stocksystemT( 'cart_removed', 'از سبد حذف شد' ), 'info' );
					}
					// Fragments swap in via cart-fragments; keep the drawer open and give it focus back.
					if ( autoCloseTimer ) {
						window.clearTimeout( autoCloseTimer );
					}
				} )
				.catch( function () {
					button.disabled = false;
					if ( row ) {
						row.classList.remove( 'is-removing' );
					}
					if ( window.stocksystemToast ) {
						window.stocksystemToast.show( window.stocksystemT( 'cart_remove_failed', 'حذف انجام نشد؛ دوباره تلاش کنید.' ), 'error' );
					}
				} );
		} );

		// WooCommerce fires this jQuery event on every successful add-to-cart.
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart', function ( event, fragments, hash, $button ) {
				// The card button confirms briefly ("✓ به سبد اضافه شد"), then goes back to its label.
				var btn = $button && $button[ 0 ];
				if ( btn && ! btn.hasAttribute( 'data-label' ) ) {
					btn.setAttribute( 'data-label', btn.textContent.trim() );
					btn.textContent = window.stocksystemT( 'added_to_cart', 'به سبد اضافه شد' );
					window.setTimeout( function () {
						btn.textContent = btn.getAttribute( 'data-label' );
						btn.removeAttribute( 'data-label' );
						btn.classList.remove( 'added' );
					}, 2500 );
				}

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
