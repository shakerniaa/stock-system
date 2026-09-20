/**
 * Appearance → «منو و فوتر»: repeatable rows (add / remove / move) and the
 * automatic ↔ manual switches of each mega-menu column.
 */
( function () {
	'use strict';

	// ---- Repeaters ----
	document.querySelectorAll( '.ss-rep' ).forEach( function ( rep ) {
		var rows = rep.querySelector( '.ss-rep__rows' );
		var tpl = rep.querySelector( '.ss-rep__tpl' );
		var counter = Date.now();

		rep.querySelector( '.ss-rep__add' ).addEventListener( 'click', function () {
			var html = tpl.innerHTML.replace( /__i__/g, String( counter++ ) );
			var holder = document.createElement( 'div' );
			holder.innerHTML = html.trim();
			var row = holder.firstElementChild;
			rows.appendChild( row );
			var first = row.querySelector( 'input' );
			if ( first ) {
				first.focus();
			}
		} );

		rep.addEventListener( 'click', function ( event ) {
			var row = event.target.closest( '.ss-rep__row' );
			if ( ! row ) {
				return;
			}

			if ( event.target.closest( '.ss-rep__remove' ) ) {
				row.remove();
			} else if ( event.target.closest( '.ss-rep__up' ) && row.previousElementSibling ) {
				rows.insertBefore( row, row.previousElementSibling );
			} else if ( event.target.closest( '.ss-rep__down' ) && row.nextElementSibling ) {
				rows.insertBefore( row.nextElementSibling, row );
			}
		} );
	} );

	// ---- Mode switches: show only the panel that matches the selected mode ----
	function syncScope( scope ) {
		var select = scope.querySelector( '.ss-mode' );
		if ( ! select ) {
			return;
		}
		scope.querySelectorAll( '.ss-mode-panel' ).forEach( function ( panel ) {
			panel.hidden = panel.getAttribute( 'data-mode' ) !== select.value;
		} );
	}

	document.querySelectorAll( '[data-mode-scope]' ).forEach( function ( scope ) {
		var select = scope.querySelector( '.ss-mode' );
		if ( select ) {
			select.addEventListener( 'change', function () {
				syncScope( scope );
			} );
		}
		syncScope( scope );
	} );
} )();
