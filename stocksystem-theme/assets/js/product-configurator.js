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
		var grouped = Math.round( amount ).toLocaleString( 'en-US' );
		return toPersianDigits( grouped );
	}

	function initConfigurator( form ) {
		var $form = $( form );
		var $summary = $form.find( '.configurator__summary' );
		var basePrice = parseFloat( $summary.data( 'base-price' ) ) || 0;
		var isHardwareConfig = '1' === String( $summary.data( 'hardware-config' ) );
		var variations = $form.data( 'product_variations' ) || [];

		// WooCommerce narrows each select to values that fit the OTHER current
		// choices. A tile whose value was narrowed out (e.g. 16GB while 256GB
		// is chosen and no 16GB/256GB variation exists) would otherwise be a
		// dead click — so the clicked value wins and the other attributes move
		// to the cheapest in-stock combination that has it.
		function optionUsable( $select, value ) {
			var $option = $select.find( 'option' ).filter( function () {
				return this.value === value;
			} );
			return $option.length && ! $option.prop( 'disabled' );
		}

		function switchToCombination( selectName, value ) {
			var best = null;
			$.each( variations, function ( i, variation ) {
				var attrs = variation.attributes || {};
				if ( ! variation.is_in_stock || ( attrs[ selectName ] && attrs[ selectName ] !== value ) ) {
					return;
				}
				if ( ! best || variation.display_price < best.display_price ) {
					best = variation;
				}
			} );

			var $selects = $form.find( '.variations select' );
			// Clear everything first so WooCommerce re-lists every value...
			$selects.val( '' );
			$selects.first().trigger( 'change' );

			// ...then apply the whole combination and let it look the price up.
			$selects.each( function () {
				var name = $( this ).attr( 'name' );
				if ( name === selectName ) {
					$( this ).val( value );
				} else if ( best && best.attributes && best.attributes[ name ] ) {
					$( this ).val( best.attributes[ name ] );
				}
			} );
			$selects.filter( '[name="' + selectName + '"]' ).trigger( 'change' );
			$selects.not( '[name="' + selectName + '"]' ).each( function () {
				$( this ).triggerHandler( 'change' );
			} );
		}

		// Tile ↔ hidden-select sync, both directions.
		$form.find( '.configurator__attribute' ).each( function () {
			var $attribute = $( this );
			var selectName = $attribute.data( 'attribute-select' );
			var $select = $attribute.find( 'select[name="' + selectName + '"]' );
			var $radios = $attribute.find( 'input[type="radio"]' );

			$radios.on( 'change', function () {
				var value = $( this ).val();
				if ( ! optionUsable( $select, value ) ) {
					switchToCombination( selectName, value );
					return;
				}
				$select.val( value ).trigger( 'change' );
			} );

			$select.on( 'change', function () {
				var value = $( this ).val();
				$radios.prop( 'checked', false );
				if ( value ) {
					$attribute.find( 'input[type="radio"][value="' + value + '"]' ).prop( 'checked', true );
				}
			} );

			// Start in step with the select (pre-selected default, or a value
			// the browser restored on back/forward).
			if ( $select.val() ) {
				$radios.prop( 'checked', false );
				$radios.filter( function () {
					return this.value === $select.val();
				} ).prop( 'checked', true );
			}
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
			var text = window.stocksystemT( 'addons_total', 'قیمت با افزودنی‌های انتخابی: %s تومان' ).replace( '%s', formatToman( grandTotal ) );

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
