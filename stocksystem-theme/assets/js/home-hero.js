/**
 * Homepage hero behaviour: the 1a slider, the 1e multi-unit countdown,
 * and the 1d finder's answer highlighting.
 *
 * All three degrade cleanly: the slider's slides are all in the DOM and
 * the first is visible without JS, the countdown's units simply stay as
 * em dashes, and the finder's options are plain links that navigate on
 * their own.
 */
( function () {
	'use strict';

	var PERSIAN_DIGITS = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];

	function toPersianDigits( value ) {
		return String( value ).replace( /[0-9]/g, function ( d ) {
			return PERSIAN_DIGITS[ d ];
		} );
	}

	function pad( n ) {
		return n < 10 ? '0' + n : '' + n;
	}

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	}

	/* ---- 1a slider ---- */

	function initSlider( root ) {
		var track = root.querySelector( '[data-hero-track]' );
		var slides = Array.prototype.slice.call( root.querySelectorAll( '[data-hero-slide]' ) );
		var tabs = Array.prototype.slice.call( root.querySelectorAll( '[data-hero-tab]' ) );

		if ( ! track || slides.length < 2 ) {
			return;
		}

		var index = 0;
		var timer = null;
		var paused = false;
		// RTL: advancing means moving the track towards positive X.
		var isRtl = getComputedStyle( root ).direction === 'rtl';
		var autoplay = root.getAttribute( 'data-autoplay' ) === '1' && ! prefersReducedMotion();
		var interval = parseInt( root.getAttribute( 'data-interval' ), 10 ) || 6000;

		function show( next ) {
			index = ( next + slides.length ) % slides.length;

			track.style.transform = 'translateX(' + ( isRtl ? '' : '-' ) + index * 100 + '%)';

			slides.forEach( function ( slide, i ) {
				// Keep off-screen slides out of the accessibility tree and
				// out of the tab order, or a keyboard user tabs into links
				// they cannot see.
				if ( i === index ) {
					slide.removeAttribute( 'aria-hidden' );
				} else {
					slide.setAttribute( 'aria-hidden', 'true' );
				}
				slide.querySelectorAll( 'a' ).forEach( function ( link ) {
					if ( i === index ) {
						link.removeAttribute( 'tabindex' );
					} else {
						link.setAttribute( 'tabindex', '-1' );
					}
				} );
			} );

			tabs.forEach( function ( tab, i ) {
				tab.classList.toggle( 'is-active', i === index );
				tab.setAttribute( 'aria-selected', i === index ? 'true' : 'false' );
			} );
		}

		function stop() {
			if ( timer ) {
				clearInterval( timer );
				timer = null;
			}
		}

		function start() {
			stop();
			if ( ! autoplay ) {
				return;
			}
			timer = setInterval( function () {
				if ( ! paused && ! document.hidden ) {
					show( index + 1 );
				}
			}, interval );
		}

		root.addEventListener( 'mouseenter', function () { paused = true; } );
		root.addEventListener( 'mouseleave', function () { paused = false; } );
		root.addEventListener( 'focusin', function () { paused = true; } );
		root.addEventListener( 'focusout', function () { paused = false; } );

		var prev = root.querySelector( '[data-hero-prev]' );
		var next = root.querySelector( '[data-hero-next]' );
		if ( prev ) {
			prev.addEventListener( 'click', function () { show( index - 1 ); start(); } );
		}
		if ( next ) {
			next.addEventListener( 'click', function () { show( index + 1 ); start(); } );
		}

		tabs.forEach( function ( tab ) {
			tab.addEventListener( 'click', function () {
				show( parseInt( tab.getAttribute( 'data-hero-tab' ), 10 ) || 0 );
				start();
			} );
		} );

		// Touch swipe. Only treated as a swipe when the gesture is clearly
		// horizontal, so a vertical page scroll that starts on the hero
		// isn't hijacked.
		var startX = 0;
		var startY = 0;
		root.addEventListener( 'touchstart', function ( e ) {
			startX = e.touches[ 0 ].clientX;
			startY = e.touches[ 0 ].clientY;
			paused = true;
		}, { passive: true } );
		root.addEventListener( 'touchend', function ( e ) {
			var dx = e.changedTouches[ 0 ].clientX - startX;
			var dy = e.changedTouches[ 0 ].clientY - startY;
			paused = false;
			if ( Math.abs( dx ) > 45 && Math.abs( dx ) > Math.abs( dy ) ) {
				show( index + ( ( dx < 0 ) === isRtl ? -1 : 1 ) );
				start();
			}
		}, { passive: true } );

		show( 0 );
		start();
	}

	/* ---- 1e countdown, split into hour/minute/second boxes ---- */

	function initCountdown( root ) {
		var target = parseInt( root.getAttribute( 'data-hero-countdown' ), 10 ) * 1000;
		var units = {
			hours: root.querySelector( '[data-countdown-unit="hours"]' ),
			minutes: root.querySelector( '[data-countdown-unit="minutes"]' ),
			seconds: root.querySelector( '[data-countdown-unit="seconds"]' )
		};

		function render() {
			var remaining = target - Date.now();

			if ( remaining <= 0 ) {
				Object.keys( units ).forEach( function ( key ) {
					if ( units[ key ] ) {
						units[ key ].textContent = toPersianDigits( '00' );
					}
				} );
				root.classList.add( 'is-over' );
				return false;
			}

			var total = Math.floor( remaining / 1000 );
			// Anything past a day is folded into the hours box rather than
			// wrapping silently — a 3-day sale must not read as "۱۲ ساعت".
			var values = {
				hours: Math.floor( total / 3600 ),
				minutes: Math.floor( total / 60 ) % 60,
				seconds: total % 60
			};

			Object.keys( units ).forEach( function ( key ) {
				if ( units[ key ] ) {
					units[ key ].textContent = toPersianDigits( pad( values[ key ] ) );
				}
			} );

			return true;
		}

		if ( render() ) {
			var timer = setInterval( function () {
				if ( ! render() ) {
					clearInterval( timer );
				}
			}, 1000 );
		}
	}

	/* ---- 1d finder ---- */

	function initFinder( root ) {
		var go = root.querySelector( '[data-hero-finder-go]' );

		if ( ! go ) {
			return;
		}

		root.querySelectorAll( '[data-hero-answer]' ).forEach( function ( option ) {
			option.addEventListener( 'click', function ( e ) {
				// Intercept only to record the choice; the button below is
				// what actually navigates, so the click shouldn't follow
				// this option's own href.
				e.preventDefault();

				var group = option.getAttribute( 'data-hero-answer' );
				root.querySelectorAll( '[data-hero-answer="' + group + '"]' ).forEach( function ( sibling ) {
					sibling.classList.toggle( 'is-selected', sibling === option );
				} );

				// Budget is the narrower filter of the two, so it wins when
				// both have been answered.
				var budget = root.querySelector( '[data-hero-answer="budget"].is-selected' );
				var use = root.querySelector( '[data-hero-answer="use"].is-selected' );
				var chosen = budget || use;

				if ( chosen ) {
					go.setAttribute( 'href', chosen.getAttribute( 'href' ) );
				}
			} );
		} );

		// Reflect the pre-selected defaults in the button on first paint.
		var initial = root.querySelector( '[data-hero-answer="budget"].is-selected' ) || root.querySelector( '[data-hero-answer="use"].is-selected' );
		if ( initial ) {
			go.setAttribute( 'href', initial.getAttribute( 'href' ) );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '[data-hero-slider]' ).forEach( initSlider );
		document.querySelectorAll( '[data-hero-countdown]' ).forEach( initCountdown );
		document.querySelectorAll( '[data-hero-finder]' ).forEach( initFinder );
	} );
}() );
