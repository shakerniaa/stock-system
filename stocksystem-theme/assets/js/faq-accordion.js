/**
 * Single-open FAQ accordion. Answers are always in the DOM (server
 * rendered, for SEO/FAQPage schema) — this only toggles the visual
 * collapse. Source: 14 Support Pages.dc.html §14-B behavior spec.
 */
( function () {
	'use strict';

	function closeItem( button, answer ) {
		button.setAttribute( 'aria-expanded', 'false' );
		answer.style.maxHeight = '';
		answer.classList.remove( 'is-open' );
	}

	function openItem( button, answer ) {
		button.setAttribute( 'aria-expanded', 'true' );
		answer.classList.add( 'is-open' );
		answer.style.maxHeight = answer.scrollHeight + 'px';
	}

	function initAccordion( accordion ) {
		var buttons = accordion.querySelectorAll( '.faq-accordion__question' );
		var first = buttons[ 0 ] && document.getElementById( buttons[ 0 ].getAttribute( 'aria-controls' ) );

		if ( accordion.hasAttribute( 'data-open-first' ) && first ) {
			openItem( buttons[ 0 ], first );
		}

		buttons.forEach( function ( button ) {
			var answer = document.getElementById( button.getAttribute( 'aria-controls' ) );
			if ( ! answer ) {
				return;
			}

			button.addEventListener( 'click', function () {
				var isOpen = 'true' === button.getAttribute( 'aria-expanded' );

				buttons.forEach( function ( otherButton ) {
					var otherAnswer = document.getElementById( otherButton.getAttribute( 'aria-controls' ) );
					if ( otherAnswer ) {
						closeItem( otherButton, otherAnswer );
					}
				} );

				if ( ! isOpen ) {
					openItem( button, answer );
				}
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.faq-accordion' ).forEach( initAccordion );
	} );
} )();
