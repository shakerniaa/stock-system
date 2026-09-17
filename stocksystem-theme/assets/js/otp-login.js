/**
 * OTP login/registration form (woocommerce/myaccount/form-login.php).
 */
( function () {
	'use strict';

	function post( action, data ) {
		var body = new URLSearchParams( data );
		body.set( 'action', action );
		return fetch( window.stocksystemAjax.url, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: body.toString(),
		} ).then( function ( response ) {
			return response.json();
		} );
	}

	function showError( el, message ) {
		el.textContent = message;
		el.hidden = false;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'otp-form' );
		if ( ! form || ! window.stocksystemAjax ) {
			return;
		}

		var nonce = form.getAttribute( 'data-nonce' );
		var phoneInput = document.getElementById( 'otp-phone' );
		var nameInput = document.getElementById( 'otp-name' );
		var codeInput = document.getElementById( 'otp-code' );
		var stepPhone = document.getElementById( 'otp-step-phone' );
		var stepCode = document.getElementById( 'otp-step-code' );
		var sentTo = stepCode.querySelector( '.otp-form__sent-to' );
		var errorEl = document.getElementById( 'otp-error' );
		var requestBtn = document.getElementById( 'otp-request-btn' );
		var verifyBtn = document.getElementById( 'otp-verify-btn' );

		requestBtn.addEventListener( 'click', function () {
			errorEl.hidden = true;

			if ( ! phoneInput.checkValidity() ) {
				phoneInput.reportValidity();
				return;
			}

			requestBtn.disabled = true;
			requestBtn.textContent = '…';

			post( 'stocksystem_request_otp', { nonce: nonce, phone: phoneInput.value } )
				.then( function ( json ) {
					requestBtn.disabled = false;
					requestBtn.textContent = 'دریافت کد ورود';

					if ( ! json.success ) {
						showError( errorEl, json.data.message );
						return;
					}

					sentTo.textContent = 'کد ورود به ' + phoneInput.value + ' پیامک شد.';
					if ( json.data.debug_code ) {
						sentTo.textContent += ' (WP_DEBUG: ' + json.data.debug_code + ')';
					}

					stepPhone.hidden = true;
					stepCode.hidden = false;
					codeInput.focus();
				} );
		} );

		verifyBtn.addEventListener( 'click', function () {
			errorEl.hidden = true;

			if ( ! codeInput.checkValidity() ) {
				codeInput.reportValidity();
				return;
			}

			verifyBtn.disabled = true;

			post( 'stocksystem_verify_otp', {
				nonce: nonce,
				phone: phoneInput.value,
				code: codeInput.value,
				name: nameInput.value,
			} ).then( function ( json ) {
				verifyBtn.disabled = false;

				if ( ! json.success ) {
					showError( errorEl, json.data.message );
					return;
				}

				window.location.href = json.data.redirect;
			} );
		} );

		document.getElementById( 'otp-back-btn' ).addEventListener( 'click', function () {
			stepCode.hidden = true;
			stepPhone.hidden = false;
			errorEl.hidden = true;
		} );

		var passwordToggle = document.getElementById( 'password-login-toggle' );
		var passwordForm = document.getElementById( 'password-login-form' );
		if ( passwordToggle && passwordForm ) {
			passwordToggle.addEventListener( 'click', function () {
				var isHidden = passwordForm.hidden;
				passwordForm.hidden = ! isHidden;
				passwordToggle.textContent = isHidden ? 'انصراف از ورود با رمز' : 'ورود با رمز عبور';
			} );
		}
	} );
} )();
