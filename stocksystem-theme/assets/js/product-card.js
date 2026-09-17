/**
 * Clearance countdown chips — ticks every second against the
 * data-countdown-to unix timestamp set in
 * template-parts/product/clearance-countdown.php.
 */
( function () {
	'use strict';

	var PERSIAN_DIGITS = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];

	function toPersianDigits( value ) {
		return String( value ).replace( /[0-9]/g, function ( d ) {
			return PERSIAN_DIGITS[ d ];
		} );
	}

	function pad( n ) {
		return n < 10 ? '0' + n : '' + n;
	}

	function render( el ) {
		var target = parseInt( el.getAttribute( 'data-countdown-to' ), 10 ) * 1000;
		var remaining = target - Date.now();

		if ( remaining <= 0 ) {
			el.textContent = toPersianDigits( '۰۰:۰۰:۰۰' );
			return false;
		}

		var totalSeconds = Math.floor( remaining / 1000 );
		var hours = Math.floor( totalSeconds / 3600 );
		var minutes = Math.floor( ( totalSeconds % 3600 ) / 60 );
		var seconds = totalSeconds % 60;

		el.textContent = toPersianDigits( pad( hours ) + ':' + pad( minutes ) + ':' + pad( seconds ) );
		return true;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var chips = document.querySelectorAll( '[data-countdown-to]' );
		if ( ! chips.length ) {
			return;
		}

		chips.forEach( render );

		window.setInterval( function () {
			chips.forEach( render );
		}, 1000 );
	} );
} )();
