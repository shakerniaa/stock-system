/**
 * OTP login/registration form (woocommerce/myaccount/form-login.php).
 *
 * Phone -> 4-digit code. Errors are shown right under the field they belong
 * to (not at the bottom of the card), the field turns red and shakes, the
 * wrong code is cleared and re-focused; a full code auto-submits; Persian
 * digits are accepted; the resend button counts down from the server's
 * throttle. No name is asked for here — see inc/otp-auth.php.
 */
( function () {
	'use strict';

	var PERSIAN = '۰۱۲۳۴۵۶۷۸۹';
	var ARABIC = '٠١٢٣٤٥٦٧٨٩';

	function toLatin( value ) {
		return String( value ).replace( /[۰-۹٠-٩]/g, function ( ch ) {
			var i = PERSIAN.indexOf( ch );
			return String( i > -1 ? i : ARABIC.indexOf( ch ) );
		} );
	}

	function toPersian( value ) {
		return String( value ).replace( /[0-9]/g, function ( d ) {
			return PERSIAN.charAt( Number( d ) );
		} );
	}

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

	var NETWORK_ERROR = 'ارتباط با سرور برقرار نشد. اینترنت خود را بررسی کنید و دوباره تلاش کنید.';

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'otp-form' );
		if ( ! form || ! window.stocksystemAjax ) {
			return;
		}

		var nonce = form.getAttribute( 'data-nonce' );
		var phoneInput = document.getElementById( 'otp-phone' );
		var codeInput = document.getElementById( 'otp-code' );
		var stepPhone = document.getElementById( 'otp-step-phone' );
		var stepCode = document.getElementById( 'otp-step-code' );
		var sentTo = stepCode.querySelector( '.otp-form__sent-to' );
		var phoneError = document.getElementById( 'otp-phone-error' );
		var codeError = document.getElementById( 'otp-code-error' );
		var info = document.getElementById( 'otp-info' );
		var requestBtn = document.getElementById( 'otp-request-btn' );
		var verifyBtn = document.getElementById( 'otp-verify-btn' );
		var resendBtn = document.getElementById( 'otp-resend-btn' );
		var backBtn = document.getElementById( 'otp-back-btn' );
		var phone = '';
		var countdown = null;
		var busy = false;

		function setError( input, el, message ) {
			var field = input.closest( '.otp-form__field' );
			if ( message ) {
				el.textContent = message;
				el.hidden = false;
				input.setAttribute( 'aria-invalid', 'true' );
				field.classList.add( 'has-error' );
				// Restart the shake even if the class is already there.
				field.classList.remove( 'is-shaking' );
				void field.offsetWidth;
				field.classList.add( 'is-shaking' );
			} else {
				el.hidden = true;
				el.textContent = '';
				input.removeAttribute( 'aria-invalid' );
				field.classList.remove( 'has-error', 'is-shaking' );
			}
		}

		function setInfo( message ) {
			info.textContent = message || '';
			info.hidden = ! message;
		}

		function setBusy( button, isBusy, idleLabel, busyLabel ) {
			busy = isBusy;
			button.disabled = isBusy;
			button.classList.toggle( 'is-loading', isBusy );
			button.textContent = isBusy ? busyLabel : idleLabel;
		}

		function formatClock( seconds ) {
			var m = Math.floor( seconds / 60 );
			var s = seconds % 60;
			return toPersian( m + ':' + ( s < 10 ? '0' : '' ) + s );
		}

		function startCountdown( seconds ) {
			window.clearInterval( countdown );
			var left = Math.max( 0, Math.ceil( seconds ) );

			function tick() {
				if ( left <= 0 ) {
					window.clearInterval( countdown );
					resendBtn.disabled = false;
					resendBtn.textContent = 'ارسال مجدد کد';
					return;
				}
				resendBtn.disabled = true;
				resendBtn.textContent = 'ارسال مجدد کد (' + formatClock( left ) + ')';
				left--;
			}

			tick();
			countdown = window.setInterval( tick, 1000 );
		}

		function showCodeStep( data ) {
			stepPhone.hidden = true;
			stepCode.hidden = false;
			sentTo.innerHTML = '';
			sentTo.appendChild( document.createTextNode( 'کد ۴ رقمی به شمارهٔ ' ) );
			var num = document.createElement( 'strong' );
			num.className = 'ltr';
			num.textContent = toPersian( phone );
			sentTo.appendChild( num );
			sentTo.appendChild( document.createTextNode( ' پیامک شد.' ) );
			if ( data.debug_code ) {
				var dbg = document.createElement( 'span' );
				dbg.className = 'otp-form__debug';
				dbg.textContent = ' (WP_DEBUG: ' + data.debug_code + ')';
				sentTo.appendChild( dbg );
			}
			codeInput.value = '';
			setError( codeInput, codeError, '' );
			startCountdown( data.resend_in || 60 );
			codeInput.focus();
		}

		function requestCode( isResend ) {
			if ( busy ) {
				return;
			}

			var value = toLatin( phoneInput.value ).replace( /\D/g, '' );
			if ( 0 === value.indexOf( '98' ) && 12 === value.length ) {
				value = '0' + value.slice( 2 );
			}

			if ( ! /^09\d{9}$/.test( value ) ) {
				setError( phoneInput, phoneError, 'شمارهٔ موبایل معتبر نیست. آن را به شکل ۰۹۱۲۳۴۵۶۷۸۹ (۱۱ رقم) وارد کنید.' );
				phoneInput.focus();
				return;
			}

			setError( phoneInput, phoneError, '' );
			setInfo( '' );
			phone = value;

			var button = isResend ? resendBtn : requestBtn;
			var idle = button.textContent;
			setBusy( button, true, idle, 'در حال ارسال…' );

			post( 'stocksystem_request_otp', { nonce: nonce, phone: phone } )
				.then( function ( json ) {
					setBusy( button, false, isResend ? 'ارسال مجدد کد' : 'دریافت کد ورود', '' );

					if ( ! json.success ) {
						var d = json.data || {};
						if ( isResend ) {
							setError( codeInput, codeError, d.message || NETWORK_ERROR );
							if ( d.retry_after ) {
								startCountdown( d.retry_after );
							} else {
								resendBtn.disabled = false;
							}
						} else if ( 'throttled' === d.code ) {
							// A code was already sent moments ago — go on to entering it.
							showCodeStep( { resend_in: d.retry_after } );
							setInfo( d.message );
						} else {
							setError( phoneInput, phoneError, d.message || NETWORK_ERROR );
						}
						return;
					}

					showCodeStep( json.data );
					if ( isResend ) {
						setInfo( 'کد جدید ارسال شد. فقط کد آخر معتبر است.' );
					}
				} )
				.catch( function () {
					setBusy( button, false, isResend ? 'ارسال مجدد کد' : 'دریافت کد ورود', '' );
					if ( isResend ) {
						resendBtn.disabled = false;
						setError( codeInput, codeError, NETWORK_ERROR );
					} else {
						setError( phoneInput, phoneError, NETWORK_ERROR );
					}
				} );
		}

		function verifyCode() {
			if ( busy ) {
				return;
			}

			var code = toLatin( codeInput.value ).replace( /\D/g, '' );
			if ( code.length < 4 ) {
				setError( codeInput, codeError, 'کد ۴ رقمی را کامل وارد کنید.' );
				codeInput.focus();
				return;
			}

			setError( codeInput, codeError, '' );
			setInfo( '' );
			setBusy( verifyBtn, true, 'ورود', 'در حال بررسی…' );

			post( 'stocksystem_verify_otp', { nonce: nonce, phone: phone, code: code } )
				.then( function ( json ) {
					if ( json.success ) {
						verifyBtn.textContent = 'ورود انجام شد…';
						window.location.href = json.data.redirect;
						return;
					}

					setBusy( verifyBtn, false, 'ورود', '' );
					var d = json.data || {};
					setError( codeInput, codeError, d.message || NETWORK_ERROR );

					if ( 'wrong_code' === d.code || 'invalid_format' === d.code ) {
						// Keep it fast to retry: clear the wrong digits and refocus.
						codeInput.select();
					} else if ( 'expired' === d.code || 'too_many' === d.code ) {
						codeInput.value = '';
						if ( ! resendBtn.disabled ) {
							resendBtn.focus();
						}
					}
					if ( 'wrong_code' === d.code ) {
						codeInput.value = '';
						codeInput.focus();
					}
				} )
				.catch( function () {
					setBusy( verifyBtn, false, 'ورود', '' );
					setError( codeInput, codeError, NETWORK_ERROR );
				} );
		}

		phoneInput.addEventListener( 'input', function () {
			phoneInput.value = toLatin( phoneInput.value ).replace( /[^\d\s+-]/g, '' );
			setError( phoneInput, phoneError, '' );
		} );

		codeInput.addEventListener( 'input', function () {
			codeInput.value = toLatin( codeInput.value ).replace( /\D/g, '' ).slice( 0, 4 );
			setError( codeInput, codeError, '' );
			if ( 4 === codeInput.value.length ) {
				verifyCode();
			}
		} );

		phoneInput.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key ) {
				event.preventDefault();
				requestCode( false );
			}
		} );

		codeInput.addEventListener( 'keydown', function ( event ) {
			if ( 'Enter' === event.key ) {
				event.preventDefault();
				verifyCode();
			}
		} );

		form.addEventListener( 'submit', function ( event ) {
			event.preventDefault();
		} );

		requestBtn.addEventListener( 'click', function () {
			requestCode( false );
		} );
		verifyBtn.addEventListener( 'click', verifyCode );
		resendBtn.addEventListener( 'click', function () {
			requestCode( true );
		} );

		backBtn.addEventListener( 'click', function () {
			window.clearInterval( countdown );
			stepCode.hidden = true;
			stepPhone.hidden = false;
			setError( codeInput, codeError, '' );
			setInfo( '' );
			phoneInput.focus();
			phoneInput.select();
		} );

		var passwordToggle = document.getElementById( 'password-login-toggle' );
		var passwordForm = document.getElementById( 'password-login-form' );
		if ( passwordToggle && passwordForm ) {
			passwordToggle.addEventListener( 'click', function () {
				var isHidden = passwordForm.hidden;
				passwordForm.hidden = ! isHidden;
				passwordToggle.textContent = isHidden ? 'انصراف از ورود با رمز' : 'ورود با رمز عبور';
				if ( isHidden ) {
					var user = document.getElementById( 'username' );
					if ( user ) {
						user.focus();
					}
				}
			} );
		}
	} );
} )();
