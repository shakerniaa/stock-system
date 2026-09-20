/**
 * Appearance → «صفحهٔ اصلی»: media-library picker for the hero images and the
 * "manual products" list that only shows for that source.
 */
( function ( $ ) {
	'use strict';

	$( '.ss-media' ).each( function () {
		var box = $( this );
		var input = box.find( 'input[type="hidden"]' );
		var preview = box.find( '.ss-media__preview' );
		var pick = box.find( '.ss-media__pick' );
		var clear = box.find( '.ss-media__clear' );
		var frame = null;

		pick.on( 'click', function ( event ) {
			event.preventDefault();

			if ( ! frame ) {
				frame = wp.media( {
					title: 'انتخاب تصویر',
					button: { text: 'استفاده از این تصویر' },
					library: { type: 'image' },
					multiple: false
				} );

				frame.on( 'select', function () {
					var file = frame.state().get( 'selection' ).first().toJSON();
					var url = file.sizes && file.sizes.thumbnail ? file.sizes.thumbnail.url : file.url;

					input.val( file.id );
					preview.empty().append( $( '<img>' ).attr( 'src', url ).attr( 'alt', '' ) );
					pick.text( 'تغییر تصویر' );
					clear.prop( 'hidden', false );
				} );
			}

			frame.open();
		} );

		clear.on( 'click', function () {
			input.val( '' );
			preview.empty();
			pick.text( box.data( 'empty-label' ) );
			clear.prop( 'hidden', true );
		} );
	} );

	var source = $( '.ss-source' );
	var manual = $( '.ss-manual' );

	function syncSource() {
		manual.prop( 'hidden', 'manual' !== source.val() );
	}

	source.on( 'change', syncSource );
	syncSource();
} )( jQuery );
