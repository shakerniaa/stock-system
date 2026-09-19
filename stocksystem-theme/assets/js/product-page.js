/**
 * Quantity stepper −/+ buttons (woocommerce/global/quantity-input.php).
 * Dispatches a native 'change' event so WooCommerce's own cart/AJAX
 * scripts react exactly as if the number input were edited directly.
 * Also drives the phone-only fixed add-to-cart bar on the product page.
 */
( function () {
	'use strict';

	function step( input, direction ) {
		var step = parseFloat( input.step ) || 1;
		var min = '' !== input.min ? parseFloat( input.min ) : 0;
		var max = '' !== input.max ? parseFloat( input.max ) : Infinity;
		var value = parseFloat( input.value ) || 0;
		var next = value + ( direction * step );

		next = Math.max( min, Math.min( max, next ) );
		input.value = next;
		input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	document.addEventListener( 'click', function ( event ) {
		var button = event.target.closest( '.quantity-stepper__btn' );
		if ( ! button ) {
			return;
		}

		var input = button.parentElement.querySelector( 'input.qty' );
		if ( ! input ) {
			return;
		}

		step( input, button.classList.contains( 'quantity-stepper__btn--plus' ) ? 1 : -1 );
	} );

	// Fixed add-to-cart bar: appears once the real button leaves the viewport
	// and forwards its click to that button, so validation, AJAX and the
	// configurator all keep working through the one real form.
	var bar = document.querySelector( '[data-mobile-buy-bar]' );
	var form = document.querySelector( 'form.cart' );
	var real = form && form.querySelector( '.single_add_to_cart_button' );

	if ( bar && real ) {
		bar.querySelector( '.mobile-buy-bar__cta' ).addEventListener( 'click', function () {
			if ( real.disabled || real.classList.contains( 'disabled' ) ) {
				form.scrollIntoView( { behavior: 'smooth', block: 'center' } );
				return;
			}
			real.click();
		} );

		if ( 'IntersectionObserver' in window ) {
			new IntersectionObserver( function ( entries ) {
				bar.classList.toggle( 'is-visible', ! entries[ 0 ].isIntersecting );
			} ).observe( real );
		} else {
			bar.classList.add( 'is-visible' );
		}
	}
} )();
