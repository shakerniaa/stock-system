/**
 * Footer link columns collapse into accordions on phones, where the
 * four stacked columns made the footer several screens tall.
 *
 * The markup ships as <details open>, so with JS off — or on desktop —
 * everything stays expanded. This only ever closes them, and only
 * below the mobile breakpoint.
 */
( function () {
	'use strict';

	var MOBILE = '(max-width: 767px)';

	function sync( query, columns ) {
		columns.forEach( function ( column, index ) {
			if ( query.matches ) {
				// Leave the first column open so the footer never reads as
				// a wall of closed rows with nothing visible.
				column.open = index === 0;
			} else {
				column.open = true;
			}
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var columns = Array.prototype.slice.call(
			document.querySelectorAll( '.site-footer details.site-footer__nav-col' )
		);

		if ( ! columns.length || ! window.matchMedia ) {
			return;
		}

		var query = window.matchMedia( MOBILE );
		sync( query, columns );

		if ( query.addEventListener ) {
			query.addEventListener( 'change', function () { sync( query, columns ); } );
		} else if ( query.addListener ) {
			query.addListener( function () { sync( query, columns ); } );
		}
	} );
}() );
