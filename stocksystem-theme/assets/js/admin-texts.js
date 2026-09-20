/**
 * Appearance → «متن‌های سایت»: live search, "only changed" filter and the
 * submit step that packs just the edited rows into one JSON field (a form
 * with ~800 textareas would exceed PHP's max_input_vars).
 */
( function () {
	'use strict';

	var form = document.getElementById( 'ss-texts-form' );
	var search = document.getElementById( 'ss-texts-search' );
	var onlyChanged = document.getElementById( 'ss-texts-only-changed' );
	var count = document.getElementById( 'ss-texts-count' );
	var json = document.getElementById( 'ss-texts-json' );

	if ( ! form ) {
		return;
	}

	var rows = Array.prototype.slice.call( document.querySelectorAll( '.ss-row' ) );
	var groups = Array.prototype.slice.call( document.querySelectorAll( '.ss-group' ) );

	function toPersian( n ) {
		return String( n ).replace( /\d/g, function ( d ) {
			return '۰۱۲۳۴۵۶۷۸۹'.charAt( Number( d ) );
		} );
	}

	function norm( text ) {
		return String( text ).replace( /[يك]/g, function ( c ) {
			return 'ي' === c ? 'ی' : 'ک';
		} ).replace( /[‌‏]/g, ' ' ).toLowerCase();
	}

	function isChanged( row ) {
		var box = row.querySelector( 'textarea' );
		return box.value.trim() !== '' && box.value.trim() !== row.getAttribute( 'data-original' );
	}

	function apply() {
		var query = norm( search.value.trim() );
		var shown = 0;

		rows.forEach( function ( row ) {
			var box = row.querySelector( 'textarea' );
			var hay = norm( row.getAttribute( 'data-original' ) + ' ' + box.value );
			var match = ( '' === query || hay.indexOf( query ) !== -1 ) && ( ! onlyChanged.checked || isChanged( row ) );

			row.hidden = ! match;
			if ( match ) {
				shown++;
			}
		} );

		groups.forEach( function ( group ) {
			var any = group.querySelector( '.ss-row:not([hidden])' );
			group.hidden = ! any;

			if ( query || onlyChanged.checked ) {
				group.open = !! any;
			}
		} );

		count.textContent = toPersian( shown ) + ' متن';
	}

	rows.forEach( function ( row ) {
		row.querySelector( 'textarea' ).addEventListener( 'input', function () {
			row.classList.toggle( 'is-changed', isChanged( row ) );
		} );
	} );

	search.addEventListener( 'input', apply );
	onlyChanged.addEventListener( 'change', apply );

	form.addEventListener( 'submit', function () {
		var payload = {};

		rows.forEach( function ( row ) {
			var box = row.querySelector( 'textarea' );
			var saved = box.getAttribute( 'data-saved' ) || '';
			var value = box.value.trim();

			// Only rows that differ from what is stored — including cleared ones, which restore the default.
			if ( value !== saved ) {
				payload[ row.getAttribute( 'data-original' ) ] = value;
			}
		} );

		json.value = JSON.stringify( payload );
	} );

	apply();
} )();
