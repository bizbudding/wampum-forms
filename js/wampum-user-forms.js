;(function( $ ) {
    'use strict';

    // Show password strength meter when focusing on password field
	$('.wampum-form').on('focus', '.wampum_password', function(ev){
    	$(this).closest('form').find('.password-strength').slideDown('fast');
    });

    // Password strength meter
    $('.wampum-form').on('keyup', '.wampum_password', function(e){

    	var form = $(this).closest('form');

	    var strength = {
	        0: "Weak",
	        1: "Weak",
	        2: "Okay",
	        3: "Good",
	        4: "Great",
	    }

	    var meter = form.find('.password-strength-meter');
	    var text  = form.find('.password-strength-text');

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
    $( 'body' ).on( 'submit', '.wampum-user-login-form', function(e) {

    	console.log('Login form submitted');

        e.preventDefault();

        // Set the form as a variable
        var LoginForm = $(this);
        // Show the form as processing
        LoginForm.addClass('processing');
        // Set button as a variable
        var button = LoginForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
        hideNotices(LoginForm);

        // Setup our form data array
        var data = {
                user_login: LoginForm.find( '.wampum_user_login' ).val(),
                user_password: LoginForm.find( '.wampum_user_pass' ).val(),
                remember: LoginForm.find( '.wampum_rememberme' ).val(),
                say_what: LoginForm.find( '.wampum_say_what' ).val(), // honeypot
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
                	displayNotice( LoginForm, 'success', 'Success!' );

                	// Only redirect if we have a value
                    var redirect = LoginForm.find( '.wampum_redirect' ).val();
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
                    displayNotice( LoginForm, 'error', response.message );
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( LoginForm, 'error', response.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        LoginForm.removeClass('processing');
			// Re-enable the button
        	button.html(button_html).attr( 'disabled', false );
        });

    });


    // Password form submit
	$( 'body' ).on( 'submit', '.wampum-user-password-form', function(e) {

		console.log('Password form submitted');

        e.preventDefault();

        // Set the form as a variable
        var PasswordForm = $(this);
        // Show the form as processing
        PasswordForm.addClass('processing');
        // Set button as a variable
        var button = PasswordForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
        hideNotices(PasswordForm);

        // Setup our form data array
        var data = {
                password: PasswordForm.find( '.wampum_password' ).val(),
                password_confirm: PasswordForm.find( '.wampum_password_confirm' ).val(),
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
        	button.html(button_html).attr( 'disabled', false );
        });

    });

	// Membership verify submit
	$( 'body' ).on( 'submit', '.wampum-user-membership-form-verify', function(e) {

		e.preventDefault();

	    // Set the form as a variable
        var MembershipVerify = $(this);

        // Show the form as processing
        MembershipVerify.addClass('processing');
        // Set button as a variable
        var button = MembershipVerify.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
		hideNotices(MembershipVerify);

        // Setup our form data array
        var data = {
        		say_what: MembershipVerify.find( '[name="wampum_say_what"]' ).val(),
                user_email: MembershipVerify.find( '[name="wampum_membership_email"]' ).val(),
                username: MembershipVerify.find( '[name="wampum_membership_username"]' ).val(),
                current_url: wampum_user_forms.current_url,
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = MembershipVerify.find( '.wampum_ss_baseuri' ).val();
        var SharpSpringEndpoint = MembershipVerify.find( '.wampum_ss_endpoint' ).val();
        var urlParams = MembershipVerify.serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            MembershipVerify.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/membership-verify/',
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

                	var MembershipForm = MembershipVerify.siblings('.wampum-user-membership-form');
                	// Pass values and make fields read only
                	MembershipForm.find('.wampum_first_name').val(MembershipVerify.find('.wampum_first_name').val()).attr('readonly',true);
                	MembershipForm.find('.wampum_last_name').val(MembershipVerify.find('.wampum_last_name').val()).attr('readonly',true);
                    MembershipForm.find('.wampum_email').val(MembershipVerify.find('.wampum_email').val()).attr('readonly',true);
                	MembershipForm.find('.wampum_username').val(MembershipVerify.find('.wampum_username').val()).attr('readonly',true);
                	// Hide already filled out fields
                	MembershipForm.find('.membership-first-name').hide();
                	MembershipForm.find('.membership-last-name').hide();
                    MembershipForm.find('.membership-email').hide();
                	MembershipForm.find('.membership-username').hide();
                    // Add description to next form
                    displayNotice( MembershipForm, 'success', 'Almost there! This is the last step.' );
                    // Swap forms
                    MembershipVerify.hide();
                    MembershipForm.show();
                    // Focus on password field (should be the only one left?)
                    MembershipForm.find('.wampum_password').focus();
                } else {
                    // Display error message
                    displayNotice( MembershipVerify, 'error', response.message );

					// Show login form if clicking the "Log in?" link
	                MembershipVerify.on( 'click', '.login-link', function(e) {
	                	e.preventDefault();
	                	// Do the login stuff
						setTimeout(function() {
		                	swapLoginForm(MembershipVerify);
						}, 300 );
	                });

                }

            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                displayNotice( MembershipVerify, 'error', wampum_user_forms.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        MembershipVerify.removeClass('processing');
			// Re-enable the button
			button.html(button_html).attr( 'disabled', false );
        });

	});

	// Membership add submit
    $( 'body' ).on( 'submit', '.wampum-user-membership-form', function(e) {

    	console.log('Membership form submitted');

        e.preventDefault();

        // Set the form as a variable
        var MembershipForm = $(this);

        // Show the form as processing
        MembershipForm.addClass('processing');
        // Set button as a variable
        var button = MembershipForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( getLoadingHTML() );

        // Hide any notices
		hideNotices(MembershipForm);

        // Setup our form data array
        var data = {
                plan_id: MembershipForm.find( '.wampum_plan_id' ).val(),
                first_name: MembershipForm.find( '.wampum_first_name' ).val(),
                last_name: MembershipForm.find( '.wampum_last_name' ).val(),
                user_email: MembershipForm.find( '.wampum_email' ).val(),
                username: MembershipForm.find( '.wampum_username' ).val(),
                password: MembershipForm.find( '.wampum_password' ).val(),
                notifications: MembershipForm.find( '.wampum_notifications').val(),
                say_what: MembershipForm.find( '.wampum_say_what' ).val(), // honeypot
                current_url: wampum_user_forms.current_url,
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = MembershipForm.find( '.wampum_ss_baseuri' ).val();
        var SharpSpringEndpoint = MembershipForm.find( '.wampum_ss_endpoint' ).val();
        // var urlParams = MembershipForm.serialize();
        var urlParams = $('input[type!=password]', MembershipForm).serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            MembershipForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
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
                	displayNotice( MembershipForm, 'success', 'Success!' );

                	// Get the redirect value
                    var redirect = MembershipForm.find( '.wampum_redirect' ).val();

                	// Only redirect if we have a value
                    if ( redirect !== "" ) {
						setTimeout(function() {
	                        // Refresh/redirect
	                        window.location.replace( redirect );
						}, 300 );
                    } else {
						setTimeout(function() {
							// Fade the form out
	                    	MembershipForm.fadeOut('fast');
						}, 300 );
                    }

                } else {

                    // Display error message
					displayNotice( MembershipForm, 'error', response.message );

					// Show login form if clicking the "Log in?" link
	                MembershipForm.on( 'click', '.login-link', function(e) {
	                	e.preventDefault();
	                	// Do the login stuff
						setTimeout(function() {
		                	swapLoginForm(MembershipVerify);
						}, 300 );
	                });

                }
            },
            fail: function( response ) {
                // console.log(response);
                // Not sure when this would happen, but fallbacks!
                displayNotice( MembershipForm, 'error', wampum_user_forms.failure );
            }
        }).done( function( response )  {
        	// Remove form processing CSS
	        MembershipForm.removeClass('processing');
			// Re-enable the button
        	button.html(button_html).attr( 'disabled', false );
        });

    });

    // Swap a form for it's neighboring login form
	function swapLoginForm( form ) {

    	hideNotices(form);

    	var LoginForm = form.siblings('.wampum-user-login-form');

    	// Swap forms
    	form.hide();
    	LoginForm.show();

    	// Set submitted email value as the login field
    	LoginForm.find('.wampum_user_login').val(form.find('.wampum_email').val());

    	// If user goes to login, back to membership, then to login, we'd have duplicate back buttons
    	LoginForm.find('.wampum-back').remove();

    	// Add back button
    	LoginForm.find('.wampum_submit').after('<a class="wampum-back" href="#">&nbsp;&nbsp;Go back</a>');

		// On click of the back button
		LoginForm.on( 'click', '.wampum-back', function(e) {
			e.preventDefault();
        	// Swap forms
        	LoginForm.hide();
        	form.show();
        	// Clear the password field
        	LoginForm.find('.wampum_user_pass').val('');
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

})( jQuery );
