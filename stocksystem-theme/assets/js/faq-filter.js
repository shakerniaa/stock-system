/**
 * Category chips + text search for the general FAQ page (/faq/).
 * Progressive enhancement only — every answer is already server-rendered
 * and visible without JS; this just hides non-matching items. The two
 * filters combine (an item must match both). Source: 14 Support Pages.dc.html §14-B.
 */
( function () {
	'use strict';

	function normalise( text ) {
		// Arabic yeh/kaf -> Persian, so typing either form still matches.
		return ( text || '' )
			.replace( /\u064A/g, '\u06CC' )
			.replace( /\u0643/g, '\u06A9' )
			.toLowerCase();
	}

	function init() {
		var items = document.querySelectorAll( '[data-faq-category]' );
		var chips = document.querySelectorAll( '.faq-filter__chip' );
		var searchWrap = document.querySelector( '.faq-search' );
		var input = searchWrap && searchWrap.querySelector( '.faq-search__input' );
		var empty = document.querySelector( '.faq-empty' );
		var category = '';

		if ( ! items.length ) {
			return;
		}

		if ( searchWrap ) {
			searchWrap.hidden = false;
		}

		function apply() {
			var query = input ? normalise( input.value.trim() ) : '';
			var shown = 0;

			items.forEach( function ( item ) {
				var inCategory = ! category || item.getAttribute( 'data-faq-category' ) === category;
				var inSearch = ! query || normalise( item.textContent ).indexOf( query ) !== -1;
				item.hidden = ! ( inCategory && inSearch );
				if ( ! item.hidden ) {
					shown++;
				}
			} );

			if ( empty ) {
				empty.hidden = shown > 0;
			}
		}

		chips.forEach( function ( chip ) {
			chip.addEventListener( 'click', function () {
				chips.forEach( function ( other ) {
					other.classList.remove( 'is-current' );
				} );
				chip.classList.add( 'is-current' );
				category = chip.getAttribute( 'data-category' ) || '';
				apply();
			} );
		} );

		if ( input ) {
			input.addEventListener( 'input', apply );
		}
	}

	document.addEventListener( 'DOMContentLoaded', init );
} )();
