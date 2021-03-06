( function ( document, $, undefined ) {

	'use strict';

	var $forms    = $('.wampum-form'),
		$strength = $('.password-strength-meter');

	// Password match
	$.each( $forms, function() {

		var $pwField   = $(this).find('input[name="password"]'),
			$pwConfirm = $(this).find('input[name="password_confirm"]');

		// If we have password and password confirm fields
		if ( ( $pwField.length && $pwConfirm.length ) > 0 ) {
			// When typing in confirm field
			$forms.on( 'keyup', $pwConfirm, function(e) {

				// Password fields values
				var pw  = $pwField.val(),
					pwc = $pwConfirm.val();

				// If both fields have a value
				if ( '' != ( pw && pwc ) ) {
					// If passwords match
					if ( $pwField.val() == $pwConfirm.val() ) {
						// Add green border to both fields if passwords match
						$pwField.add($pwConfirm).css({'border-color':'#25f500'});
					}
					// No match
					else {
						// Remove border
						$pwField.add($pwConfirm).css({'border-color':''});
					}
				}
			});
		}
	});

	// If we have a strength meter
	if ( $strength.length > 0 ) {

		// Show password strength meter when focusing on password field
		$forms.on( 'focus', 'input[name="password"]', function(e) {
			$(this).closest('form').find('.password-strength').slideDown('fast');
		});

		// Password strength meter
		$forms.on( 'keyup', 'input[name="password"]', function(e) {

			var $form = $(this).closest('form');

			var strength = {
				0: "Weak",
				1: "Weak",
				2: "Okay",
				3: "Good",
				4: "Great",
			}

			var $meter = $form.find('.password-strength-meter');
			var $text  = $form.find('.password-strength-text');

			var val    = $(this).val();
			var result = zxcvbn(val);

			// Update the password strength meter
			$meter.attr('data-strength', result.score);

			// Update the text indicator
			if ( val !== "" ) {
				$text.html(strength[result.score]);
			} else {
				$text.html("");
			}

		});

	}

	// Login form submit
	$forms.on( 'submit', 'form[data-form="login"]', function(e) {

		console.log('Login form submitted');

		e.preventDefault();

		// Set the form as a variable
		var $loginForm = $(this),
			$button    = $loginForm.find( 'button[name="submit"]' );

		// Get the button text/value so we can add it back later
		var buttonHTML = $button.html();

		// Show the form as processing
		$loginForm.addClass('processing');

		// Disable the $button
		$button.prop( 'disabled', true );

		// Set the $button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices( $loginForm );

		// Setup our form data array
		var data = {
				user_login:    $loginForm.find( 'input[name="username"]' ).val(),
				user_password: $loginForm.find( 'input[name="password"]' ).val(),
				remember:      $loginForm.find( 'input[name="rememberme"]' ).val(),
				notifications: $loginForm.find( 'input[name="notifications"]' ).val(),
				say_what:      $loginForm.find( 'input[name ="say_what"]' ).val(), // honeypot
			};

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/login/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {
				if ( true == response.success ) {
					// Display success message
					displayNotice( $loginForm, 'success', 'Success!' );

					// Get redirect URL
					var redirect = $loginForm.find( 'input[name="redirect"]' ).val();
					if ( '' != redirect ) {
						// If login form is part of a membership flow
						if ( 'membership_form' == redirect ) {
							// Refresh the page
							window.location.reload();
						} else {
							doRedirect( redirect );
						}
					}
				} else {
					// Display error message
					displayNotice( $loginForm, 'error', response.message );
				}
			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $loginForm, 'error', response.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$loginForm.removeClass('processing');
			// Re-enable the button
			$button.html(buttonHTML).prop( 'disabled', false );
		});

	});

	// Password form submit
	$forms.on( 'submit', 'form[data-form="password"]', function(e) {

		console.log('Password form submitted');

		e.preventDefault();

		// Set the form as a variable
		var $passwordForm = $(this),
			$button       = $passwordForm.find( 'button[name="submit"]' );

		// Get the button text/value so we can add it back later
		var buttonHTML = $button.html();

		// Show the form as processing
		$passwordForm.addClass('processing');

		// Disable the $button
		$button.prop( 'disabled', true );

		// Set the $button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices( $passwordForm );

		// Setup our form data array
		var data = {
				password:         $passwordForm.find( 'input[name="password"]' ).val(),
				password_confirm: $passwordForm.find( 'input[name="password_confirm"]' ).val(),
				notifications:    $passwordForm.find( 'input[name="notifications"]' ).val(),
				say_what:         $passwordForm.find( 'input[name="say_what"]' ).val(), // honeypot
			};

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/password/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {

				if ( true == response.success ) {

					// Display success notice.
					displayNotice( $passwordForm, 'success', 'Success!' );

					// Get redirect URL.
					var redirect = $passwordForm.find( 'input[name="redirect"]' ).val();

					/**
					 * Force refresh/redirect.
					 * Trying to submit password form again was giving 403 forbidden, not worth dealing with.
					 */
					doRedirect( redirect );
				} else {
					// Clear the password strength value
					$passwordForm.find('.password-strength-meter').attr('data-strength', '');
					// Clear the password strength text
					$passwordForm.find('.password-strength-text').html('');
					// Display error message
					displayNotice( $passwordForm, 'error', response.message );
				}

			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $passwordForm, 'error', wampumFormVars.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$passwordForm.removeClass('processing');
			// Re-enable the button
			$button.html(buttonHTML).prop( 'disabled', false );
		});

	});

	// Register form submit
	$forms.on( 'submit', 'form[data-form="register"]', function(e) {

		console.log('Register form submitted');

		e.preventDefault();

		// Set the form as a variable
		var $registerForm = $(this),
			$button       = $registerForm.find( 'button[name="submit"]' );

		// Get the button text/value so we can add it back later
		var buttonHTML = $button.html();

		// Disable the $button
		$button.prop( 'disabled', true );

		// Set the $button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices( $registerForm );

		// Setup our form data array
		var data = {
				email:            $registerForm.find( 'input[name="email"]' ).val(),
				username:         $registerForm.find( 'input[name="username"]' ).val(),
				first_name:       $registerForm.find( 'input[name="first_name"]' ).val(),
				last_name:        $registerForm.find( 'input[name="last_name"]' ).val(),
				password:         $registerForm.find( 'input[name="password"]' ).val(),
				password_confirm: $registerForm.find( 'input[name="password_confirm"]' ).val(),
				log_in:           $registerForm.find( 'input[name="log_in"]' ).val(),
				ac_list_ids:      $registerForm.find( 'input[name="ac_list_ids"]' ).val(),
				ac_tags:          $registerForm.find( 'input[name="ac_tags"]' ).val(),
				notifications:    $registerForm.find( 'input[name="notifications"]' ).val(),
				say_what:         $registerForm.find( 'input[name="say_what"]' ).val(), // honeypot
			};

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/register/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {
				if ( true == response.success ) {
					// Display success message
					displayNotice( $registerForm, 'success', 'Success!' );

					// Only redirect if we have a value
					var redirect = $registerForm.find( 'input[name="redirect"]' ).val();
					// Force refresh/redirect because we may be logged in
					doRedirect( redirect );
				} else {
					// Display error message
					displayNotice( $registerForm, 'error', response.message );
				}
			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $registerForm, 'error', response.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$registerForm.removeClass('processing');
			// Re-enable the button
			$button.html(buttonHTML).prop( 'disabled', false );
		});

	});

	// Subscribe form submit
	$forms.on( 'submit', 'form[data-form="subscribe"]', function(e) {

		console.log('Subscribe form submitted');

		e.preventDefault();

		// Set the form as a variable
		var $subscribeForm = $(this),
			$button        = $subscribeForm.find( 'button[name="submit"]' );

		// Get the button text/value so we can add it back later
		var buttonHTML = $button.html();

		// Disable the $button
		$button.prop( 'disabled', true );

		// Set the $button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices( $subscribeForm );

		// Setup our form data array
		var data = {
				email:         $subscribeForm.find( 'input[name="email"]' ).val(),
				first_name:    $subscribeForm.find( 'input[name="first_name"]' ).val(),
				last_name:     $subscribeForm.find( 'input[name="last_name"]' ).val(),
				ac_list_ids:   $subscribeForm.find( 'input[name="ac_list_ids"]' ).val(),
				ac_tags:       $subscribeForm.find( 'input[name="ac_tags"]' ).val(),
				notifications: $subscribeForm.find( 'input[name="notifications"]' ).val(),
				say_what:      $subscribeForm.find( 'input[name="say_what"]' ).val(), // honeypot
			};

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/subscribe/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {
				if ( true == response.success ) {
					// Display success message
					displayNotice( $subscribeForm, 'success', 'Success!' );

					// Only redirect if we have a value
					var redirect = $subscribeForm.find( 'input[name="redirect"]' ).val();
					// Force refresh/redirect because we may be logged in
					doRedirect( redirect );
				} else {
					// Display error message
					displayNotice( $subscribeForm, 'error', response.message );
				}
			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $subscribeForm, 'error', response.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$subscribeForm.removeClass('processing');
			// Re-enable the button
			$button.html(buttonHTML).prop( 'disabled', false );
		});

	});

	// Membership verify submit
	$forms.on( 'submit', 'form[data-form="user-available"]', function(e) {

		console.log( 'User available form submitted' );

		e.preventDefault();

		// Set the form as a variable
		var $userAvailableForm = $(this),
			$button            = $userAvailableForm.find( 'button[name="submit"]' );

		// Show the form as processing
		$userAvailableForm.addClass('processing');

		// Get the button text/value so we can add it back later on text/value so we can add it back later
		var buttonHTML = $button.html();

		// Disable the button
		$button.prop( 'disabled', true );

		// Set the button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices($userAvailableForm);

		// Setup our form data array
		var data = {
				email:       $userAvailableForm.find( 'input[name="email"]' ).val(),
				username:    $userAvailableForm.find( 'input[name="username"]' ).val(),
				say_what:    $userAvailableForm.find( 'input[name="say_what"]' ).val(),
				current_url: wampumFormVars.current_url,
			};

		// SharpSpring data, incase we need it later
		// var SharpSpringBaseURI  = $userAvailableForm.find( '.wampum_ss_baseuri' ).val();
		// var SharpSpringEndpoint = $userAvailableForm.find( '.wampum_ss_endpoint' ).val();
		// var urlParams = $userAvailableForm.serialize();

		// If we have SharpSpring data, add the main __ss_noform code right after the form
		// if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
		// 	// This fixes the error missing __ss_noform push
		// 	$userAvailableForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
		// }

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/user-available/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {

				if ( true == response.success ) {

					// // If this is a SharpSpring form, send that data!
					// if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
					// 	// https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
					// 	$.ajax({
					// 		url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
					// 		contentType: "application/json",
					// 		dataType: 'jsonp',
					// 		success: function( response ) {
					// 			console.log('Data successfully sent to SharpSpring.');
					// 		}
					// 	});
					// }

					var $membershipForm = $userAvailableForm.siblings( 'form[data-form="join-membership"]' );

					// Pass values and make fields read only
					$membershipForm.find( 'input[name="first_name"]' ).val( $userAvailableForm.find( 'input[name="first_name"]' ).val() ).prop( 'readonly', true );
					$membershipForm.find( 'input[name="last_name"]' ).val( $userAvailableForm.find( 'input[name="last_name"]' ).val() ).prop( 'readonly', true );
					$membershipForm.find( 'input[name="email"]' ).val( $userAvailableForm.find( 'input[name="email"]' ).val() ).prop( 'readonly', true );
					$membershipForm.find( 'input[name="username"]' ).val( $userAvailableForm.find( 'input[name="username"]' ).val() ).prop( 'readonly', true );

					// Hide already filled out fields
					$membershipForm.find( '.first-name' ).hide();
					$membershipForm.find( '.last-name' ).hide();
					$membershipForm.find( '.email' ).hide();
					$membershipForm.find( '.username' ).hide();

					// Add description to next form
					displayNotice( $membershipForm, 'success', 'Almost there! This is the last step.' );

					// Swap forms
					$userAvailableForm.fadeOut( 300, function() {
						$membershipForm.fadeIn( 600 );
						// Focus on password field (should be the only one left?)
						$membershipForm.find( 'input[name="password"]' ).focus();
					});

				} else {

					// Display error message
					displayNotice( $userAvailableForm, 'error', response.message );

					// Show login form if clicking the "Log in?" link
					$userAvailableForm.on( 'click', '.login-link', function(e) {
						e.preventDefault();
						// Do the login stuff
						setTimeout(function() {
							swapLoginForm( $userAvailableForm );
						}, 300 );
					});

				}

			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $userAvailableForm, 'error', wampumFormVars.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$userAvailableForm.removeClass('processing');
			// Re-enable the butto
			$button.html( buttonHTML ).attr( 'disabled', false );
		});

	});

	// Membership add submit
	$forms.on( 'submit', 'form[data-form="join-membership"]', function(e) {

		console.log('Membership form submitted');

		e.preventDefault();

		// Set the form as a variable
		var $membershipForm = $(this),
			$button         = $membershipForm.find( 'button[name="submit"]' );

		// Show the form as processing
		$membershipForm.addClass('processing');

		// Get the button text/value so we can add it back later on text/value so we can add it back later
		var buttonHTML = $button.html();

		// Disable the button
		$button.prop( 'disabled', true );

		// Set the button text/value to loading icons
		$button.html( getLoadingHTML() );

		// Hide any notices
		hideNotices( $membershipForm );

		// Setup our form data array
		var data = {
				plan_id:       $membershipForm.find( 'input[name="plan_id"]' ).val(),
				email:         $membershipForm.find( 'input[name="email"]' ).val(),
				first_name:    $membershipForm.find( 'input[name="first_name"]' ).val(),
				last_name:     $membershipForm.find( 'input[name="last_name"]' ).val(),
				username:      $membershipForm.find( 'input[name="username"]' ).val(),
				password:      $membershipForm.find( 'input[name="password"]' ).val(),
				log_in:        $membershipForm.find( 'input[name="log_in"]' ).val(),
				notifications: $membershipForm.find( 'input[name="notifications"]').val(),
				ac_list_ids:   $membershipForm.find( 'input[name="ac_list_ids"]' ).val(),
				ac_tags:       $membershipForm.find( 'input[name="ac_tags"]' ).val(),
				notifications: $membershipForm.find( 'input[name="notifications"]' ).val(),
				say_what:      $membershipForm.find( 'input[name="say_what"]' ).val(), // honeypot
				current_url:   wampumFormVars.current_url,
			};

		// SharpSpring data, incase we need it later
		// var SharpSpringBaseURI  = $membershipForm.find( '.wampum_ss_baseuri' ).val();
		// var SharpSpringEndpoint = $membershipForm.find( '.wampum_ss_endpoint' ).val();
		// // var urlParams = $membershipForm.serialize();
		// var urlParams = $('input[type!=password]', $membershipForm).serialize();

		// // If we have SharpSpring data, add the main __ss_noform code right after the form
		// if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
			// // This fixes the error missing __ss_noform push
			// $membershipForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
		// }

		$.ajax({
			method: 'POST',
			url: wampumFormVars.root + 'wampum/v1/membership-add/',
			data: data,
			beforeSend: function ( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', wampumFormVars.nonce );
			},
			success: function( response ) {

				if ( true == response.success ) {

					// If this is a SharpSpring form, send that data!
					// if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
					// 	// https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
					// 	$.ajax({
					// 		url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
					// 		contentType: "application/json",
					// 		dataType: 'jsonp',
					// 		success: function( response ) {
					// 			console.log('Data successfully sent to SharpSpring.');
					// 		}
					// 	});
					// }

					// Display success message
					displayNotice( $membershipForm, 'success', 'Success!' );

					// Get the redirect value
					var redirect = $membershipForm.find( 'input[name="redirect"]' ).val();

					// Only redirect if we have a value
					if ( '' != redirect ) {
						setTimeout(function() {
							// Refresh/redirect
							doRedirect( redirect );
						}, 300 );
					} else {
						setTimeout(function() {
							// Fade the form out
							$membershipForm.fadeOut('fast');
						}, 300 );
					}

				} else {

					// Display error message
					displayNotice( $membershipForm, 'error', response.message );

					// Show login form if clicking the "Log in?" link
					$membershipForm.on( 'click', '.login-link', function(e) {
						e.preventDefault();
						// Do the login stuff
						setTimeout(function() {
							swapLoginForm($userAvailableForm);
						}, 300 );
					});

				}
			}

		}).fail( function( response ) {
			// Not sure when this would happen, but fallbacks!
			displayNotice( $membershipForm, 'error', wampumFormVars.failure );
		}).done( function( response )  {
			// Remove form processing CSS
			$membershipForm.removeClass('processing');
			// Re-enable the button
			$button.html(buttonHTML).prop( 'disabled', false );
		});

	});

	// Swap a form for it's neighboring login form
	function swapLoginForm( $form ) {

		hideNotices($form);

		var $loginForm = $form.siblings('form[data-form="login"]');

		/**
		 * Swap forms
		 */
		$form.fadeOut( 300, function() {
			$loginForm.fadeIn( 600 );
		});

		// Set submitted email value as the login field
		$loginForm.find('input[name="username"]').val( $form.find('input[name="email"]').val() );
		$loginForm.find('input[name="password"]').focus();

		// If user goes to login, back to membership, then to login, we'd have duplicate back buttons
		$loginForm.find('.wampum-back').remove();

		// Add back button
		$loginForm.find('button[name="submit"]').after('<a class="wampum-back" href="#">&nbsp;&nbsp;Go back</a>');

		// On click of the back button
		$loginForm.on( 'click', '.wampum-back', function(e) {
			e.preventDefault();
			// Swap forms
			$loginForm.fadeOut( 300, function() {
				$form.fadeIn( 600 );
			});

			// Clear the password field
			$loginForm.find('input[name="password"]').val('');
		});

	}

	function doRedirect( redirect ) {
		// If the redirct has a hash.
		var hash = redirect.substring( redirect.indexOf('#') );
		if ( hash ) {
			// Get the current URL without has/params.
			var current_url = [location.protocol, '//', location.host, location.pathname].join('');
			// Make sure we're not redirecting to the same URL.
			if ( current_url == getPathFromUrl( redirect ) ) {
				window.location.href += "#" + hash;
				location.reload();
			}
		}
		// Redirect
		window.location.replace( redirect );
	}

	function getPathFromUrl( url ) {
		return url.split(/[?#]/)[0];
	}

	/**
	 * Display a notice in the form
	 *
	 * @param  object  $form  The form variable
	 * @param  string  type   success|error
	 * @param  string  text   The notice text
	 *
	 * @return void
	 */
	function displayNotice( $form, type, text ) {
		$form.find('.wampum-notice').removeClass('success error').addClass(type).html(text).fadeIn('fast');
	}

	function hideNotices( form ) {
		form.find('.wampum-notice').slideUp('fast' , function(){
			$(this).removeClass('success error');
		});
	}

	function getLoadingHTML() {
		return '<span class="wampum-loading"><span class="wampum-loading-circle wampum-loading-circle1">&#8226;</span><span class="wampum-loading-circle wampum-loading-circle2">&#8226;</span><span class="wampum-loading-circle wampum-loading-circle3">&#8226;</span></span>';
	}

})( document, jQuery );
