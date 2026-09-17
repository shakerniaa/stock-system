/**
 * Category chip filter for the general FAQ page (/faq/). Progressive
 * enhancement only — every answer is already server-rendered and
 * visible without JS, this just hides non-matching items when a chip
 * is clicked. Source: 14 Support Pages.dc.html §14-B.
 */
( function () {
	'use strict';

	function initFilter( wrap ) {
		var chips = wrap.querySelectorAll( '.faq-filter__chip' );
		var items = document.querySelectorAll( '[data-faq-category]' );

		chips.forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				chips.forEach( function ( other ) {
					other.classList.remove( 'is-current' );
				} );
				chip.classList.add( 'is-current' );

				var category = chip.getAttribute( 'data-category' );

				items.forEach( function ( item ) {
					var matches = ! category || item.getAttribute( 'data-faq-category' ) === category;
					item.hidden = ! matches;
				} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.querySelectorAll( '.faq-filter' ).forEach( initFilter );
	} );
} )();
