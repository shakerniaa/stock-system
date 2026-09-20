/**
 * Admin edit pages: repeatable rows (add / remove / move), media-library image
 * pickers (also inside rows added later) and colour pickers.
 */
( function ( $ ) {
	'use strict';

	// ---- Repeaters ----
	var counter = Date.now();

	$( document ).on( 'click', '.ss-rep__add', function () {
		var rep = $( this ).closest( '.ss-rep' );
		var html = rep.find( '.ss-rep__tpl' ).first().html().replace( /__i__/g, String( counter++ ) );
		var row = $( $.parseHTML( html.trim() ) );
		rep.find( '.ss-rep__rows' ).first().append( row );
		initColors( row );
		row.find( 'input[type="text"], textarea' ).first().trigger( 'focus' );
	} );

	$( document ).on( 'click', '.ss-rep__remove', function () {
		$( this ).closest( '.ss-rep__row' ).remove();
	} );

	$( document ).on( 'click', '.ss-rep__up', function () {
		var row = $( this ).closest( '.ss-rep__row' );
		row.prev( '.ss-rep__row' ).before( row );
	} );

	$( document ).on( 'click', '.ss-rep__down', function () {
		var row = $( this ).closest( '.ss-rep__row' );
		row.next( '.ss-rep__row' ).after( row );
	} );

	// ---- Media pickers ----
	$( document ).on( 'click', '.ss-media__pick', function ( event ) {
		event.preventDefault();

		var box = $( this ).closest( '.ss-media' );
		var frame = wp.media( {
			title: 'انتخاب تصویر',
			button: { text: 'استفاده از این تصویر' },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var file = frame.state().get( 'selection' ).first().toJSON();
			var url = file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail.url : file.url;

			box.find( 'input[type="hidden"]' ).val( file.id );
			box.find( '.ss-media__preview' ).empty().append( $( '<img>' ).attr( 'src', url ).attr( 'alt', '' ) );
			box.find( '.ss-media__pick' ).text( 'تغییر تصویر' );
			box.find( '.ss-media__clear' ).prop( 'hidden', false );
		} );

		frame.open();
	} );

	$( document ).on( 'click', '.ss-media__clear', function () {
		var box = $( this ).closest( '.ss-media' );
		box.find( 'input[type="hidden"]' ).val( '' );
		box.find( '.ss-media__preview' ).empty();
		box.find( '.ss-media__pick' ).text( 'انتخاب تصویر' );
		$( this ).prop( 'hidden', true );
	} );

	// ---- Colour pickers ----
	function initColors( scope ) {
		if ( $.fn.wpColorPicker ) {
			( scope || $( document ) ).find( '.ss-color' ).not( '.wp-color-picker' ).wpColorPicker();
		}
	}

	$( function () {
		// Skip inputs inside the (inert) row templates.
		$( '.ss-color' ).filter( function () {
			return ! $( this ).closest( 'template' ).length;
		} ).wpColorPicker();
	} );
} )( jQuery );
