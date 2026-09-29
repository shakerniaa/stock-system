/**
 * Product comparison: selection in localStorage, a floating bar while
 * products are selected, and the diff filter on the compare page.
 *
 * The selection deliberately lives client-side — comparing is a
 * throwaway act, and requiring a login for it would kill the feature.
 * The compare PAGE itself is server-rendered from ?ids=, so this script
 * only keeps state in sync; with JS off, the toggles simply don't
 * appear as interactive and nothing else breaks.
 */
( function () {
	'use strict';

	var KEY = 'ss_compare';

	// Read lazily, never captured at parse time: compare.js ships inside
	// the global bundle, and WordPress prints that bundle BEFORE the
	// wp_localize_script inline block it depends on. Capturing here left
	// every label English and the compare link pointing at '#'.
	function conf() {
		return window.stocksystemCompare || {};
	}

	function maxItems() {
		return parseInt( conf().max, 10 ) || 4;
	}

	function read() {
		try {
			var raw = window.localStorage.getItem( KEY );
			var ids = raw ? JSON.parse( raw ) : [];
			return Array.isArray( ids ) ? ids.filter( function ( n ) {
				return typeof n === 'number' && n > 0;
			} ).slice( 0, maxItems() ) : [];
		} catch ( e ) {
			// Private mode, blocked storage, or corrupt JSON: behave as empty
			// rather than throwing and taking the rest of the page with it.
			return [];
		}
	}

	function write( ids ) {
		try {
			window.localStorage.setItem( KEY, JSON.stringify( ids ) );
		} catch ( e ) {}
	}

	function compareUrl( ids ) {
		var cfg = conf();
		if ( ! cfg.url ) {
			return '#';
		}
		return cfg.url + ( cfg.url.indexOf( '?' ) === -1 ? '?' : '&' ) + 'ids=' + ids.join( ',' );
	}

	/* ---- Toggles ---- */

	function syncToggles( ids ) {
		document.querySelectorAll( '[data-compare-toggle]' ).forEach( function ( btn ) {
			var id = parseInt( btn.getAttribute( 'data-compare-toggle' ), 10 );
			var on = ids.indexOf( id ) !== -1;
			var label = btn.querySelector( '.compare-toggle__label' );
			var cfg = conf();

			btn.classList.toggle( 'is-active', on );
			btn.setAttribute( 'aria-pressed', on ? 'true' : 'false' );
			if ( label && cfg.labelAdd ) {
				label.textContent = on ? cfg.labelAdded : cfg.labelAdd;
			}
		} );
	}

	/* ---- Floating bar ---- */

	var bar = null;

	function buildBar() {
		bar = document.createElement( 'div' );
		bar.className = 'compare-bar';
		bar.setAttribute( 'role', 'region' );
		bar.setAttribute( 'aria-label', conf().compare || 'compare' );
		bar.innerHTML =
			'<div class="container compare-bar__inner">' +
				'<div class="compare-bar__items" data-compare-items></div>' +
				'<div class="compare-bar__actions">' +
					'<button type="button" class="compare-bar__clear" data-compare-clear></button>' +
					'<a class="btn btn--primary compare-bar__go" data-compare-go></a>' +
				'</div>' +
			'</div>';
		document.body.appendChild( bar );

		bar.querySelector( '[data-compare-clear]' ).textContent = conf().clear || '×';
		bar.querySelector( '[data-compare-clear]' ).addEventListener( 'click', function () {
			write( [] );
			render();
		} );

		bar.addEventListener( 'click', function ( e ) {
			var chip = e.target.closest( '[data-compare-drop]' );
			if ( ! chip ) {
				return;
			}
			remove( parseInt( chip.getAttribute( 'data-compare-drop' ), 10 ) );
		} );
	}

	function renderBar( ids ) {
		// The compare page has its own remove controls; a floating bar on
		// top of it would just cover the table.
		if ( document.querySelector( '[data-compare-table]' ) || document.querySelector( '.compare-page' ) ) {
			return;
		}

		if ( ! ids.length ) {
			if ( bar ) {
				bar.classList.remove( 'is-visible' );
			}
			document.body.classList.remove( 'has-compare-bar' );
			return;
		}

		if ( ! bar ) {
			buildBar();
		}

		var items = bar.querySelector( '[data-compare-items]' );
		items.innerHTML = '';

		ids.forEach( function ( id ) {
			// Reuse the thumbnail already on the page when there is one, so
			// the bar needs no extra request.
			var card = document.querySelector( '[data-compare-toggle="' + id + '"]' );
			var img = card ? ( card.closest( '.product-card, .buy-box, .single-product-layout' ) || document ).querySelector( 'img' ) : null;

			var chip = document.createElement( 'span' );
			chip.className = 'compare-bar__chip';
			chip.setAttribute( 'data-compare-drop', id );
			chip.setAttribute( 'role', 'button' );
			chip.setAttribute( 'tabindex', '0' );
			chip.innerHTML = ( img ? '<img src="' + img.getAttribute( 'src' ) + '" alt="">' : '' ) +
				'<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>';
			items.appendChild( chip );
		} );

		var go = bar.querySelector( '[data-compare-go]' );
		go.textContent = ( conf().compare || 'compare' ) + ' (' + toFa( ids.length ) + ')';
		go.setAttribute( 'href', compareUrl( ids ) );

		bar.classList.add( 'is-visible' );
		document.body.classList.add( 'has-compare-bar' );
	}

	var FA = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];
	function toFa( n ) {
		return String( n ).replace( /[0-9]/g, function ( d ) {
			return FA[ d ];
		} );
	}

	function render() {
		var ids = read();
		syncToggles( ids );
		renderBar( ids );
	}

	function remove( id ) {
		write( read().filter( function ( n ) {
			return n !== id;
		} ) );
		render();
	}

	function toggle( id, btn ) {
		var ids = read();
		var at = ids.indexOf( id );

		if ( at !== -1 ) {
			ids.splice( at, 1 );
		} else {
			if ( ids.length >= maxItems() ) {
				if ( window.stocksystemToast ) {
					window.stocksystemToast( conf().full, 'error' );
				}
				return;
			}
			ids.push( id );
		}

		write( ids );
		render();

		if ( at === -1 && btn ) {
			btn.classList.add( 'just-added' );
			setTimeout( function () {
				btn.classList.remove( 'just-added' );
			}, 400 );
		}
	}

	/* ---- Compare page: remove column + differences-only ---- */

	/** The ids this page is actually showing, read from its own URL. */
	function shownIds() {
		var q = new URLSearchParams( window.location.search ).get( 'ids' );

		return q ? q.split( ',' ).map( function ( n ) {
			return parseInt( n, 10 );
		} ).filter( Boolean ) : [];
	}

	function initComparePage() {
		var columns = document.querySelectorAll( '[data-compare-remove]' );

		if ( ! columns.length ) {
			return;
		}

		// A compare URL is shareable, so the page may be showing a list that
		// has nothing to do with what this browser had stored. Adopt the
		// URL's list, otherwise the toggles and the bar would disagree with
		// the table in front of the visitor.
		var current = shownIds();
		if ( current.length ) {
			write( current );
		}

		columns.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var id = parseInt( btn.getAttribute( 'data-compare-remove' ), 10 );
				// Filter the ids ON SCREEN, not whatever is in storage: those
				// are the columns the visitor is actually looking at.
				var ids = shownIds().filter( function ( n ) {
					return n !== id;
				} );
				write( ids );
				// Re-render server-side: removing a column changes which rows
				// still differ, which only the server can recompute.
				window.location.href = ids.length ? compareUrl( ids ) : conf().url;
			} );
		} );

		var diff = document.querySelector( '[data-compare-diff]' );
		if ( ! diff ) {
			return;
		}

		diff.addEventListener( 'change', function () {
			var on = diff.checked;
			var shown = 0;

			document.querySelectorAll( '.compare-table__row' ).forEach( function ( row ) {
				var same = row.hasAttribute( 'data-compare-same' );
				row.hidden = on && same;
				if ( ! row.hidden ) {
					shown++;
				}
			} );

			// A group whose rows are all hidden shouldn't keep its heading.
			document.querySelectorAll( '.compare-table__group' ).forEach( function ( group ) {
				var visible = group.querySelectorAll( '.compare-table__row:not([hidden])' ).length;
				group.hidden = on && visible === 0;
			} );

			var note = document.querySelector( '[data-compare-all-same]' );
			if ( note ) {
				note.hidden = ! ( on && shown === 0 );
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		document.addEventListener( 'click', function ( e ) {
			var btn = e.target.closest( '[data-compare-toggle]' );
			if ( ! btn ) {
				return;
			}
			e.preventDefault();
			toggle( parseInt( btn.getAttribute( 'data-compare-toggle' ), 10 ), btn );
		} );

		initComparePage();
		render();
	} );

	// Keep tabs in sync when the list changes in another one.
	window.addEventListener( 'storage', function ( e ) {
		if ( e.key === KEY ) {
			render();
		}
	} );
}() );
