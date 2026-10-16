/**
 * Motion layer (styles: assets/css/components/motion.css).
 *
 *   • Staggered fade-up: content that starts below the fold fades up as it
 *     scrolls in (above-the-fold content is never hidden, so first paint and
 *     LCP are untouched).
 *   • Fly-to-cart: a ghost of the product image flies to the header cart,
 *     then the cart badge pops. AJAX adds (product cards) animate on
 *     WooCommerce's 'added_to_cart'; the product page form posts and reloads,
 *     so its badge pop plays on the next page when the count went up.
 *   • Heart pop when a product is added to the wishlist.
 *   • Price/stock line eases in after each variation change.
 *   • Product card images shimmer until they have loaded.
 *   • Shared element: the clicked card's image becomes the product page's
 *     gallery image (View Transitions, where the browser supports them).
 *
 * Web Animations API + IntersectionObserver only — no library. Nothing runs
 * when the visitor prefers reduced motion, and nothing here changes what a
 * click does: every handler only adds visuals on top.
 */
( function () {
	'use strict';

	var reduceQuery = window.matchMedia ? window.matchMedia( '(prefers-reduced-motion: reduce)' ) : null;

	function reduced() {
		return ! reduceQuery || reduceQuery.matches;
	}

	var SPRING = 'cubic-bezier(.2, .8, .2, 1)';
	var BUMP_KEY = 'stocksystem:cart-bump';

	function canAnimate( el ) {
		return el && 'function' === typeof el.animate && ! reduced();
	}

	/* ---- Staggered fade-up ---- */
	var REVEAL_SELECTORS = [
		'.product-grid > *',
		'.home-featured__header',
		'.home-categories__header',
		'.home-categories__grid > *',
		'.home-blog__header',
		'.home-blog__grid > *',
		'.home-x__head',
		'.home-testi__grid > *',
		'.home-counters__row > *',
		'.home-brands__row > *',
		'.home-banner__box',
		'.home-trust',
		'.blog-index article',
		'.test-report',
		'.product-tabs'
	].join( ',' );

	function initReveal() {
		if ( reduced() || ! ( 'IntersectionObserver' in window ) ) {
			return;
		}

		var fold = window.innerHeight;
		var matches = Array.prototype.slice.call( document.querySelectorAll( REVEAL_SELECTORS ) );
		var targets = matches.filter( function ( el ) {
			// Below the fold only, and never nested in another target (it would animate twice).
			return el.getBoundingClientRect().top > fold && ! matches.some( function ( other ) {
				return other !== el && other.contains( el );
			} );
		} );

		if ( ! targets.length ) {
			return;
		}

		document.documentElement.classList.add( 'motion-ready' );

		// The huge top margin counts anything ABOVE the viewport as seen too,
		// so content scrolled straight past (jump link, fast fling) never
		// stays hidden.
		var observer = new IntersectionObserver( function ( entries ) {
			var batch = entries.filter( function ( entry ) {
				return entry.isIntersecting;
			} );

			batch.forEach( function ( entry, index ) {
				var el = entry.target;
				el.style.setProperty( '--reveal-delay', Math.min( index, 6 ) * 55 + 'ms' );
				el.classList.add( 'is-revealed' );
				observer.unobserve( el );
				el.addEventListener( 'animationend', function done( event ) {
					if ( event.target !== el ) {
						return;
					}
					el.removeEventListener( 'animationend', done );
					el.classList.remove( 'reveal', 'is-revealed' );
					el.style.removeProperty( '--reveal-delay' );
				} );
			} );
		}, { rootMargin: '100000px 0px -8% 0px', threshold: 0 } );

		targets.forEach( function ( el ) {
			el.classList.add( 'reveal' );
			observer.observe( el );
		} );
	}

	/* ---- Cart: fly-to-cart + badge pop ---- */
	function visible( el ) {
		return el && el.getClientRects().length > 0 && ! el.closest( '[hidden]' );
	}

	function cartTarget() {
		var candidates = [ document.getElementById( 'mini-cart-toggle-mobile' ), document.getElementById( 'mini-cart-toggle' ) ];
		for ( var i = 0; i < candidates.length; i++ ) {
			if ( visible( candidates[ i ] ) ) {
				return candidates[ i ];
			}
		}
		return null;
	}

	function cartBadges() {
		return Array.prototype.filter.call( document.querySelectorAll( '#mini-cart-count, .mobile-header__cart-count' ), visible );
	}

	function cartCount() {
		var badge = document.getElementById( 'mini-cart-count' ) || document.querySelector( '.mobile-header__cart-count' );
		if ( ! badge || badge.hidden ) {
			return 0;
		}
		var digits = badge.textContent.replace( /[۰-۹]/g, function ( d ) {
			return String( d.charCodeAt( 0 ) - 1776 );
		} ).replace( /[^0-9]/g, '' );
		return parseInt( digits, 10 ) || 0;
	}

	function bumpBadge() {
		var target = cartTarget();
		cartBadges().forEach( function ( badge ) {
			if ( canAnimate( badge ) ) {
				badge.animate(
					[ { transform: 'scale(1)' }, { transform: 'scale(1.45)', offset: 0.35 }, { transform: 'scale(.9)', offset: 0.7 }, { transform: 'scale(1)' } ],
					{ duration: 460, easing: 'ease-out' }
				);
			}
		} );
		if ( canAnimate( target ) ) {
			target.animate(
				[ { transform: 'rotate(0)' }, { transform: 'rotate(-10deg)', offset: 0.3 }, { transform: 'rotate(7deg)', offset: 0.6 }, { transform: 'rotate(0)' } ],
				{ duration: 420, easing: 'ease-out' }
			);
		}
	}

	function flyToCart( sourceImg, onDone ) {
		var target = cartTarget();
		if ( ! canAnimate( sourceImg ) || ! target || ! visible( sourceImg ) ) {
			onDone();
			return;
		}

		var from = sourceImg.getBoundingClientRect();
		var to = target.getBoundingClientRect();
		if ( ! from.width || ! from.height ) {
			onDone();
			return;
		}

		var size = Math.min( from.width, from.height, 160 );
		var ghost = document.createElement( 'img' );
		ghost.className = 'motion-fly';
		ghost.alt = '';
		ghost.setAttribute( 'aria-hidden', 'true' );
		ghost.src = sourceImg.currentSrc || sourceImg.src;
		ghost.style.width = size + 'px';
		ghost.style.height = size + 'px';
		ghost.style.left = ( from.left + ( from.width - size ) / 2 ) + 'px';
		ghost.style.top = ( from.top + ( from.height - size ) / 2 ) + 'px';
		document.body.appendChild( ghost );

		var dx = ( to.left + to.width / 2 ) - ( from.left + from.width / 2 );
		var dy = ( to.top + to.height / 2 ) - ( from.top + from.height / 2 );
		var end = 22 / size;

		var animation = ghost.animate(
			[
				{ transform: 'translate3d(0, 0, 0) scale(1)', opacity: 1 },
				{ transform: 'translate3d(' + dx * 0.45 + 'px, ' + ( dy * 0.45 - 60 ) + 'px, 0) scale(.6)', opacity: 0.95, offset: 0.45 },
				{ transform: 'translate3d(' + dx + 'px, ' + dy + 'px, 0) scale(' + end + ')', opacity: 0.3 }
			],
			{ duration: 620, easing: 'cubic-bezier(.45, 0, .25, 1)', fill: 'forwards' }
		);

		var finished = false;
		function finish() {
			if ( finished ) {
				return;
			}
			finished = true;
			ghost.remove();
			onDone();
		}
		animation.onfinish = finish;
		animation.oncancel = finish;
		window.setTimeout( finish, 900 );
	}

	function cardImage( button ) {
		var card = button && button.closest( '.product-card, .compare-table, li.product' );
		return card ? card.querySelector( '.product-card__image, img' ) : null;
	}

	function galleryImage() {
		return document.querySelector( '.woocommerce-product-gallery__image img:not(.zoomImg)' );
	}

	function initCart() {
		// AJAX add (cards): fly, then pop. Fragments are already swapped in by
		// the time the ghost lands, so the badge animated is the new one.
		if ( window.jQuery ) {
			window.jQuery( document.body ).on( 'added_to_cart', function ( event, fragments, hash, $button ) {
				var button = $button && $button[ 0 ];
				if ( canAnimate( button ) ) {
					button.animate(
						[ { transform: 'scale(1)' }, { transform: 'scale(1.05)', offset: 0.4 }, { transform: 'scale(1)' } ],
						{ duration: 360, easing: SPRING }
					);
				}
				flyToCart( cardImage( button ), bumpBadge );
			} );
		}

		// Product page form: it posts and reloads. Fly now; pop on the next page.
		document.addEventListener( 'submit', function ( event ) {
			var form = event.target;
			if ( ! form.matches || ! form.matches( 'form.cart' ) || event.defaultPrevented ) {
				return;
			}
			try {
				window.sessionStorage.setItem( BUMP_KEY, String( cartCount() ) );
			} catch ( e ) {}
			flyToCart( galleryImage(), function () {} );
		} );

		var before = null;
		try {
			before = window.sessionStorage.getItem( BUMP_KEY );
			window.sessionStorage.removeItem( BUMP_KEY );
		} catch ( e ) {}

		if ( null !== before && cartCount() > ( parseInt( before, 10 ) || 0 ) ) {
			window.setTimeout( bumpBadge, 250 );
		}
	}

	/* ---- Wishlist heart pop ---- */
	function initWishlist() {
		if ( ! ( 'MutationObserver' in window ) ) {
			return;
		}

		document.addEventListener( 'click', function ( event ) {
			var button = event.target.closest( '.wishlist-toggle' );
			if ( ! button || reduced() ) {
				return;
			}
			var wasActive = button.classList.contains( 'is-active' );
			var observer = new MutationObserver( function () {
				var isActive = button.classList.contains( 'is-active' );
				if ( isActive === wasActive ) {
					return;
				}
				observer.disconnect();
				var icon = button.querySelector( 'svg' ) || button;
				if ( isActive && canAnimate( icon ) ) {
					icon.animate(
						[ { transform: 'scale(1)' }, { transform: 'scale(1.35)', offset: 0.35 }, { transform: 'scale(.92)', offset: 0.7 }, { transform: 'scale(1)' } ],
						{ duration: 480, easing: 'ease-out' }
					);
				}
			} );
			observer.observe( button, { attributes: true, attributeFilter: [ 'class' ] } );
			window.setTimeout( function () {
				observer.disconnect();
			}, 8000 );
		} );
	}

	/* ---- Variation price swap ---- */
	function initVariationSwap() {
		if ( ! window.jQuery ) {
			return;
		}
		window.jQuery( document.body ).on( 'found_variation', 'form.variations_form', function () {
			if ( reduced() ) {
				return;
			}
			var form = this;
			// After WooCommerce has written the new price into the DOM.
			window.requestAnimationFrame( function () {
				var line = form.querySelector( '.woocommerce-variation-price' );
				if ( ! line || ! line.textContent.trim() ) {
					return;
				}
				line.classList.remove( 'motion-swap' );
				void line.offsetWidth; // restart the animation
				line.classList.add( 'motion-swap' );
			} );
		} );
	}

	/* ---- Product card image skeleton ---- */
	function initImageSkeletons() {
		if ( reduced() ) {
			return;
		}
		document.querySelectorAll( '.product-card__media' ).forEach( function ( media ) {
			var img = media.querySelector( 'img' );
			if ( ! img || ( img.complete && img.naturalWidth > 0 ) ) {
				return;
			}
			media.classList.add( 'is-img-loading' );
			var done = function () {
				media.classList.remove( 'is-img-loading' );
			};
			img.addEventListener( 'load', done, { once: true } );
			img.addEventListener( 'error', done, { once: true } );
			// Never leave a blank card if an event is missed.
			window.setTimeout( done, 10000 );
		} );
	}

	/* ---- Shared element: card image → product page ---- */
	function initSharedElement() {
		// Cross-document view transitions only; elsewhere this is a no-op.
		if ( ! ( 'CSSViewTransitionRule' in window ) ) {
			return;
		}

		function clearNames() {
			document.querySelectorAll( '[data-vt-named]' ).forEach( function ( el ) {
				el.style.viewTransitionName = '';
				el.removeAttribute( 'data-vt-named' );
			} );
		}

		document.addEventListener( 'click', function ( event ) {
			if ( reduced() || event.defaultPrevented || event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}
			var link = event.target.closest( '.product-card a[href]' );
			if ( ! link || link.target === '_blank' ) {
				return;
			}
			var img = link.closest( '.product-card' ).querySelector( '.product-card__image' );
			if ( ! img ) {
				return;
			}
			clearNames();
			// Only one element may carry the name: drop the gallery's own while leaving.
			var gallery = galleryImage();
			if ( gallery ) {
				gallery.style.viewTransitionName = 'none';
				gallery.setAttribute( 'data-vt-named', '' );
			}
			img.style.viewTransitionName = 'product-media';
			img.setAttribute( 'data-vt-named', '' );
		} );

		// Back/forward cache restores the page as it was left.
		window.addEventListener( 'pageshow', clearNames );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initReveal();
		initCart();
		initWishlist();
		initVariationSwap();
		initImageSkeletons();
		initSharedElement();
	} );
} )();
