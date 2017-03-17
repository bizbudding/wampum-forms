( function ( document, $, undefined ) {

    'use strict';

    var $forms = $('.wampum-form');

    // Show password strength meter when focusing on password field
	$forms.on( 'focus', '.wampum_user_password', function(e) {
    	$(this).closest('form').find('.password-strength').slideDown('fast');
    });

    // Password strength meter
    $forms.on( 'keyup', '.wampum_user_password', function(e) {

    	var $form = $(this).closest('form');

	    var strength = {
	        0: "Weak",
	        1: "Weak",
	        2: "Okay",
	        3: "Good",
	        4: "Great",
	    }

	    var meter = $form.find('.password-strength-meter');
	    var text  = $form.find('.password-strength-text');

        var val    = $(this).val();
        var result = zxcvbn(val);

        // Update the password strength meter
        meter.attr('data-strength', result.score);

        // Update the text indicator
        if ( val !== "" ) {
            text.html(strength[result.score]);
        } else {
            text.html("");
        }

    });

    // Login form submit
    $forms.on( 'submit', 'form[data-form="login"]', function(e) {

    	console.log('Login form submitted');

        e.preventDefault();

        // Set the form as a variable
        var $loginForm = $(this),
            $button    = $loginForm.find( '.wampum_submit' );

        // Get the button text/value so we can add it back later
        var buttonHTML = $button.html();

        // Show the form as processing
        $loginForm.addClass('processing');

        // Disable the $button
        $button.attr( 'disabled', true );

        // Set the $button text/value to loading icons
        $button.html( getLoadingHTML() );

        // Hide any notices
        hideNotices( $loginForm );

        // Setup our form data array
        var data = {
                user_login: $loginForm.find( '.wampum_username' ).val(),
                user_password: $loginForm.find( '.wampum_user_password' ).val(),
                remember: $loginForm.find( '.wampum_rememberme' ).val(),
                say_what: $loginForm.find( '.wampum_say_what' ).val(), // honeypot
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/login/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {
                if ( response.success == true ) {
                    // Display success message
                	displayNotice( $loginForm, 'success', 'Success!' );

                	// Only redirect if we have a value
                    var redirect = $loginForm.find( '.wampum_redirect' ).val();
                    if ( redirect !== "" ) {
                    	if ( 'membership_form' == redirect ) {
		                	// Refresh the page
		                	window.location.reload();
                    	} else {
	                        // Refresh/redirect
	                        window.location.replace( redirect );
                    	}
                    }
                } else {
                    // Display error message
                    displayNotice( $loginForm, 'error', response.message );
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( $loginForm, 'error', response.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        $loginForm.removeClass('processing');

                    // Re-enable the $butto// Get the button text/value so we can add it back latern
        	$button.html(buttonHTML).attr( 'disabled', false );
        });

    });

    // Register form submit
    $forms.on( 'submit', 'form[data-form="register"]', function(e) {

        console.log('Register form submitted');

        e.preventDefault();

        // Set the form as a variable
        var RegisterForm = $(this);
        // Show the form as processing
        RegisterForm.addClass('processing');
        // Set button as a variable
        var button = RegisterForm.find( '.wampum_submit' );

                // Get the but// Get the button text/value so we can add it back laterton text/value so we can add it back later
        var buttonHTML = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
        hideNotices(RegisterForm);

        // Setup our form data array
        var data = {
                user_email: RegisterForm.find( '.wampum_user_email' ).val(),
                username: RegisterForm.find( '.wampum_username' ).val(),
                first_name: RegisterForm.find( '.wampum_first_name' ).val(),
                last_name: RegisterForm.find( '.wampum_last_name' ).val(),
                password: RegisterForm.find( '.wampum_user_password' ).val(),
                log_in: RegisterForm.find( '.wampum_log_in' ).val(),
                list_id: RegisterForm.find( '.wampum_ac_list_ids' ).val(),
                say_what: RegisterForm.find( '.wampum_say_what' ).val(), // honeypot
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/register/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {
                if ( response.success == true ) {
                    // Display success message
                    displayNotice( RegisterForm, 'success', 'Success!' );

                    // Only redirect if we have a value
                    var redirect = RegisterForm.find( '.wampum_redirect' ).val();
                    if ( redirect !== "" ) {
                        // Refresh/redirect
                        window.location.replace( redirect );
                    }
                } else {
                    // Display error message
                    displayNotice( RegisterForm, 'error', response.message );
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( RegisterForm, 'error', response.failure );
            }
        }).done( function( response )  {
            // Remove form processing CSS
            RegisterForm.removeClass('processing');

            // Re-enable the button
            button.html(buttonHTML).attr( 'disabled', false );
        });

    });

    // Password form submit
    $forms.on( 'submit', 'form[data-form="password"]', function(e) {

		console.log('Password form submitted');

        e.preventDefault();

        // Set the form as a variable
        var PasswordForm = $(this);
        // Show the form as processing
        PasswordForm.addClass('processing');
        // Set button as a variable
        var button = PasswordForm.find( '.wampum_submit' );

                // Get the but// Get the button text/value so we can add it back laterton text/value so we can add it back later
        var buttonHTML = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
        hideNotices(PasswordForm);

        // Setup our form data array
        var data = {
                password: PasswordForm.find( '.wampum_user_password' ).val(),
                password_confirm: PasswordForm.find( '.wampum_user_password_confirm' ).val(),
                say_what: PasswordForm.find( '.wampum_say_what' ).val(), // honeypot
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/password/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {
                if ( response.success == true ) {
                	// Clear text field values
                	PasswordForm.find( 'input:password' ).val('');
                	// Display success notice
					displayNotice( PasswordForm, 'success', 'Success!' );
                	// Get redirect URL
                    var redirect = PasswordForm.find( '.wampum_redirect' ).val();
                    // Force refresh/redirect (trying to submit password form again was giving 403 forbidden, not worth dealing with)
                    window.location.replace( redirect );
                } else {
                    // Display error message
                    displayNotice( PasswordForm, 'error', response.message );
                }

            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( PasswordForm, 'error', wampum_user_forms.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        PasswordForm.removeClass('processing');
	        // Clear the password strength value
	        PasswordForm.find('.password-strength-meter').attr('data-strength', '');
	        // Clear the password strength text
	        PasswordForm.find('.password-strength-text').html('');

            // Re-enable the button
        	button.html(buttonHTML).attr( 'disabled', false );
        });

    });

	// Membership verify submit
    $forms.on( 'submit', 'form[data-form="user-available"]', function(e) {

        console.log( 'User available form submitted' );

		e.preventDefault();

	    // Set the form as a variable
        var $userAvailableForm  = $(this),
            $button             = $userAvailableForm.find( '.wampum_submit' );

        // Show the form as processing
        $userAvailableForm.addClass('processing');

        // Get the button text/value so we can add it back laterton text/value so we can add it back later
        var buttonHTML = $button.html();

        // Disable the button
        $button.attr( 'disabled', true );

        // Set the button text/value to loading icons
        $button.html( getLoadingHTML() );

        // Hide any notices
		hideNotices($userAvailableForm);

        // Setup our form data array
        var data = {
        		say_what: $userAvailableForm.find( '[name="wampum_say_what"]' ).val(),
                user_email: $userAvailableForm.find( '[name="wampum_user_email"]' ).val(),
                username: $userAvailableForm.find( '[name="wampum_username"]' ).val(),
                current_url: wampum_user_forms.current_url,
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = $userAvailableForm.find( '.wampum_ss_baseuri' ).val();
        var SharpSpringEndpoint = $userAvailableForm.find( '.wampum_ss_endpoint' ).val();
        var urlParams = $userAvailableForm.serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            $userAvailableForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/user-available/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {

                if ( response.success == true ) {

                    // If this is a SharpSpring form, send that data!
                    if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
                        // https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
                        $.ajax({
                            url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
                            contentType: "application/json",
                            dataType: 'jsonp',
                            success: function( response ) {
                                console.log('Data successfully sent to SharpSpring.');
                            }
                        });
                    }

                	var $membershipForm = $userAvailableForm.siblings( 'form[data-form="join-membership"]' );

                    // Pass values and make fields read only
                	$membershipForm.find( '.wampum_first_name' ).val( $userAvailableForm.find( '.wampum_first_name' ).val() ).attr( 'readonly', true );
                	$membershipForm.find( '.wampum_last_name' ).val( $userAvailableForm.find( '.wampum_last_name' ).val() ).attr( 'readonly', true );
                    $membershipForm.find( '.wampum_user_email' ).val( $userAvailableForm.find( '.wampum_user_email' ).val() ).attr( 'readonly', true );
                	$membershipForm.find( '.wampum_username' ).val( $userAvailableForm.find( '.wampum_username' ).val() ).attr( 'readonly', true );

                    // Hide already filled out fields
                	$membershipForm.find( '.wampum-first-name' ).hide();
                	$membershipForm.find( '.wampum-last-name' ).hide();
                    $membershipForm.find( '.wampum-email' ).hide();
                	$membershipForm.find( '.wampum-username' ).hide();

                    // Add description to next form
                    displayNotice( $membershipForm, 'success', 'Almost there! This is the last step.' );

                    // Swap forms
                    $userAvailableForm.hide();
                    $membershipForm.show();

                    // Focus on password field (should be the only one left?)
                    $membershipForm.find( '.wampum_user_password').focus();

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

            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( $userAvailableForm, 'error', wampum_user_forms.failure );
            }
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
            $button         = $membershipForm.find( '.wampum_submit' );

        // Show the form as processing
        $membershipForm.addClass('processing');

        // Get the button text/value so we can add it back laterton text/value so we can add it back later
        var buttonHTML = $button.html();

        // Disable the button
        $button.attr( 'disabled', true );

        // Set the button text/value to loading icons
        $button.html( getLoadingHTML() );

        // Hide any notices
		hideNotices( $membershipForm );

        // Setup our form data array
        var data = {
                plan_id: $membershipForm.find( '.wampum_plan_id' ).val(),
                first_name: $membershipForm.find( '.wampum_first_name' ).val(),
                last_name: $membershipForm.find( '.wampum_last_name' ).val(),
                user_email: $membershipForm.find( '.wampum_user_email' ).val(),
                username: $membershipForm.find( '.wampum_username' ).val(),
                password: $membershipForm.find( '.wampum_user_password' ).val(),
                notifications: $membershipForm.find( '.wampum_notifications').val(),
                say_what: $membershipForm.find( '.wampum_say_what' ).val(), // honeypot
                current_url: wampum_user_forms.current_url,
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = $membershipForm.find( '.wampum_ss_baseuri' ).val();
        var SharpSpringEndpoint = $membershipForm.find( '.wampum_ss_endpoint' ).val();
        // var urlParams = $membershipForm.serialize();
        var urlParams = $('input[type!=password]', $membershipForm).serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            $membershipForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/membership-add/',
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {

                if ( response.success == true ) {

                    // If this is a SharpSpring form, send that data!
                    if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
                        // https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
                        $.ajax({
                            url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
                            contentType: "application/json",
                            dataType: 'jsonp',
                            success: function( response ) {
                                console.log('Data successfully sent to SharpSpring.');
                            }
                        });
                    }

                    // Display success message
                	displayNotice( $membershipForm, 'success', 'Success!' );

                	// Get the redirect value
                    var redirect = $membershipForm.find( '.wampum_redirect' ).val();

                	// Only redirect if we have a value
                    if ( redirect !== "" ) {
						setTimeout(function() {
	                        // Refresh/redirect
	                        window.location.replace( redirect );
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
            },
            fail: function( response ) {
                // console.log(response);
                // Not sure when this would happen, but fallbacks!
                displayNotice( $membershipForm, 'error', wampum_user_forms.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        $membershipForm.removeClass('processing');

            // Re-enable the button
        	$button.html(buttonHTML).attr( 'disabled', false );
        });

    });

    // Swap a form for it's neighboring login form
	function swapLoginForm( $form ) {

    	hideNotices($form);

    	var $loginForm = $form.siblings('form[data-form="login"]');

    	/**
         * Swap forms
         * TODO: Make smoother?
         */
    	// $form.hide();
    	// $loginForm.show();
        $form.fadeOut( 300, function() {
            $loginForm.fadeIn( 600 );
        });

    	// Set submitted email value as the login field
    	$loginForm.find('.wampum_username').val( $form.find('.wampum_user_email').val() );

    	// If user goes to login, back to membership, then to login, we'd have duplicate back buttons
    	$loginForm.find('.wampum-back').remove();

    	// Add back button
    	$loginForm.find('.wampum_submit').after('<a class="wampum-back" href="#">&nbsp;&nbsp;Go back</a>');

		// On click of the back button
		$loginForm.on( 'click', '.wampum-back', function(e) {
			e.preventDefault();
        	// Swap forms
        	// $loginForm.hide();
        	// $form.show();
            $loginForm.fadeOut( 300, function() {
                $form.fadeIn( 600 );
            });

        	// Clear the password field
        	$loginForm.find('.wampum_user_password').val('');
		});

	}

	/**
	 * Display a notice in the form
	 *
	 * @param  object  form  The form variable
	 * @param  string  type  success|error
	 * @param  string  text  The notice text
	 *
	 * @return void
	 */
	function displayNotice( form, type, text ) {
		form.find('.wampum-notice').removeClass('success, error').addClass(type).html(text).fadeIn('fast');
	}

	function hideNotices( form ) {
		form.find('.wampum-notice').slideUp('fast' , function(){
            $(this).removeClass('success, error');
        });
	}

	function getLoadingHTML() {
		return '<div class="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>';
	}

})( document, jQuery );
