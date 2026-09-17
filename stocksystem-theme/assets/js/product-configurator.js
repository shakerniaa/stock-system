/**
 * RAM/storage configurator (15-A): syncs the visible pill-tiles to the
 * hidden native <select> elements that wc-add-to-cart-variation.js
 * already knows how to price server-authoritatively, then listens to
 * WooCommerce's own 'found_variation' / 'reset_data' events to fill in
 * the store-specific extra bits WooCommerce doesn't render on its own —
 * the workshop-upgrade stock/lead-time line and the add-on surcharge
 * total. Requires jQuery: WooCommerce's variation events are jQuery
 * custom events, not native DOM CustomEvents.
 */
( function ( $ ) {
	'use strict';

	if ( ! $ ) {
		return;
	}

	var PERSIAN_DIGITS = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];

	function toPersianDigits( value ) {
		return String( value ).replace( /[0-9]/g, function ( d ) {
			return PERSIAN_DIGITS[ d ];
		} );
	}

	function formatToman( amount ) {
		var grouped = Math.round( amount ).toLocaleString( 'en-US' ).replace( /,/g, '٬' );
		return toPersianDigits( grouped );
	}

	function initConfigurator( form ) {
		var $form = $( form );
		var $summary = $form.find( '.configurator__summary' );
		var basePrice = parseFloat( $summary.data( 'base-price' ) ) || 0;
		var isHardwareConfig = '1' === String( $summary.data( 'hardware-config' ) );

		// Tile ↔ hidden-select sync, both directions.
		$form.find( '.configurator__attribute' ).each( function () {
			var $attribute = $( this );
			var selectName = $attribute.data( 'attribute-select' );
			var $select = $attribute.find( 'select[name="' + selectName + '"]' );
			var $radios = $attribute.find( 'input[type="radio"]' );

			$radios.on( 'change', function () {
				$select.val( $( this ).val() ).trigger( 'change' );
			} );

			$select.on( 'change', function () {
				var value = $( this ).val();
				$radios.prop( 'checked', false );
				if ( value ) {
					$attribute.find( 'input[type="radio"][value="' + value + '"]' ).prop( 'checked', true );
				}
			} );
		} );

		$form.find( '.reset_variations' ).on( 'click', function () {
			$form.find( '.configurator__attribute input[type="radio"]' ).prop( 'checked', false );
		} );

		function currentAddonSurcharge() {
			var total = 0;
			$form.find( '.configurator__addon input:checked' ).each( function () {
				total += parseFloat( $( this ).data( 'price' ) ) || 0;
			} );
			return total;
		}

		function renderMeta( variation ) {
			if ( ! isHardwareConfig ) {
				return;
			}

			var $stockLine = $summary.find( '.configurator__stock-line' );
			var $leadLine = $summary.find( '.configurator__lead-line' );

			if ( ! variation ) {
				$stockLine.text( '' );
				$leadLine.text( '' );
				return;
			}

			var upgraded = basePrice > 0 && variation.display_price > basePrice;

			if ( ! variation.is_in_stock ) {
				$stockLine.text( '' );
				$leadLine.text( '' );
				return;
			}

			if ( upgraded ) {
				$stockLine.text( $stockLine.data( 'upgraded-text' ) );
				$leadLine.text( $leadLine.data( 'upgraded-text' ) );
			} else {
				var stockText = variation.max_qty ? variation.availability_html : '';
				$stockLine.text( stockText || '' );
				$leadLine.text( $leadLine.data( 'default-text' ) );
			}

			renderAddonTotal( variation );
		}

		function renderAddonTotal( variation ) {
			var $existing = $summary.find( '.configurator__addon-total' );
			var surcharge = currentAddonSurcharge();

			if ( ! surcharge || ! variation ) {
				$existing.remove();
				return;
			}

			var grandTotal = variation.display_price + surcharge;
			var text = 'قیمت با افزودنی‌های انتخابی: ' + formatToman( grandTotal ) + ' تومان';

			if ( $existing.length ) {
				$existing.text( text );
			} else {
				$( '<span class="configurator__addon-total"></span>' ).text( text ).insertAfter( $summary.find( '.woocommerce-variation-price' ) );
			}
		}

		var lastVariation = null;

		// Server-authoritative price/stock is an AJAX round-trip
		// (wc-add-to-cart-variation.js) — show a brief skeleton on the
		// price instead of a stale or blank number while it's in flight.
		$form.on( 'check_variations', function () {
			$summary.find( '.woocommerce-variation-price' ).addClass( 'skeleton' );
		} );

		$form.on( 'found_variation', function ( event, variation ) {
			lastVariation = variation;
			$summary.find( '.woocommerce-variation-price' ).removeClass( 'skeleton' );
			renderMeta( variation );
		} );

		$form.on( 'reset_data hide_variation', function () {
			lastVariation = null;
			$summary.find( '.woocommerce-variation-price' ).removeClass( 'skeleton' );
			renderMeta( null );
		} );

		$form.find( '.configurator__addon input' ).on( 'change', function () {
			renderAddonTotal( lastVariation );
		} );
	}

	$( function () {
		$( '.configurator.variations_form' ).each( function () {
			initConfigurator( this );
		} );
	} );
} )( window.jQuery );
