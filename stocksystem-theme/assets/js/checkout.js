/**
 * Checkout progressive reveal: two visual steps on one form/one page.
 * WooCommerce's own AJAX (update_checkout, payment method switching) is
 * bound to the whole form regardless of which step is hidden — elements
 * with the `hidden` attribute are skipped by native HTML validation too,
 * so reportValidity() while step 2 is hidden only checks step 1's
 * fields. Also handles the invoice-type (real/legal) field toggle.
 */
( function ( $ ) {
	'use strict';

	function initInvoiceTypeToggle() {
		var radios = document.querySelectorAll( 'input[name="billing_invoice_type"]' );
		if ( ! radios.length ) {
			return;
		}

		function sync() {
			var isCompany = document.querySelector( 'input[name="billing_invoice_type"]:checked' ).value === 'company';
			document.querySelectorAll( '.invoice-type-company-only' ).forEach( function ( field ) {
				field.style.display = isCompany ? '' : 'none';
			} );
		}

		radios.forEach( function ( radio ) {
			radio.addEventListener( 'change', sync );
		} );
		sync();
	}

	function initStepToggle() {
		var form = document.querySelector( 'form.woocommerce-checkout' );
		var stepInfo = document.getElementById( 'checkout-step-info' );
		var stepPayment = document.getElementById( 'checkout-step-payment' );
		var continueBtn = document.getElementById( 'checkout-continue-to-payment' );
		var backBtn = document.getElementById( 'checkout-back-to-info' );
		var stepper = document.querySelector( '.checkout-stepper' );

		if ( ! form || ! stepInfo || ! stepPayment || ! continueBtn ) {
			return;
		}

		function setStepperStage( stage ) {
			if ( ! stepper ) {
				return;
			}
			var stages = stepper.querySelectorAll( '.checkout-stepper__stage' );
			stages.forEach( function ( el, index ) {
				var number = index + 1;
				el.classList.remove( 'is-current', 'is-done' );
				if ( number < stage ) {
					el.classList.add( 'is-done' );
				} else if ( number === stage ) {
					el.classList.add( 'is-current' );
				}
			} );
		}

		// Phone-only fixed bar: total + the current step's primary action.
		var bar = document.querySelector( '[data-checkout-bar]' );
		var barCta = bar && bar.querySelector( '[data-checkout-bar-cta]' );
		var barTotal = bar && bar.querySelector( '[data-checkout-bar-total]' );

		function syncBar() {
			if ( ! bar ) {
				return;
			}
			var onPayment = ! stepPayment.hidden;
			barCta.textContent = onPayment ? barCta.getAttribute( 'data-label-pay' ) : barCta.getAttribute( 'data-label-continue' );

			// Once WooCommerce has priced shipping, prefer its total.
			var reviewTotal = onPayment && document.querySelector( '#order_review .checkout-summary__total-amount' );
			if ( reviewTotal && reviewTotal.innerHTML.trim() ) {
				barTotal.innerHTML = reviewTotal.innerHTML;
			}
		}

		if ( bar ) {
			barCta.addEventListener( 'click', function () {
				var placeOrder = document.getElementById( 'place_order' );
				if ( ! stepPayment.hidden && placeOrder ) {
					placeOrder.click();
				} else {
					continueBtn.click();
				}
			} );

			if ( $ ) {
				$( document.body ).on( 'updated_checkout', syncBar );
			}
		}

		continueBtn.addEventListener( 'click', function () {
			if ( ! form.reportValidity() ) {
				return;
			}

			stepInfo.hidden = true;
			stepPayment.hidden = false;
			setStepperStage( 3 );
			syncBar();
			stepPayment.scrollIntoView( { behavior: 'smooth', block: 'start' } );

			// Refresh shipping/totals now that the address is filled in.
			if ( $ ) {
				$( document.body ).trigger( 'update_checkout' );
			}
		} );

		if ( backBtn ) {
			backBtn.addEventListener( 'click', function () {
				stepPayment.hidden = true;
				stepInfo.hidden = false;
				setStepperStage( 2 );
				syncBar();
				stepInfo.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} );
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initInvoiceTypeToggle();
		initStepToggle();
	} );
} )( window.jQuery );
