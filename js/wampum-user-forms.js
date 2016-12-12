;(function( $ ) {
    'use strict';

    $( 'body' ).on( 'submit', '#wampum_user_login_form', function(e) {

    	console.log('Login form submitted');

        e.preventDefault();

        // Set the form as a variable
        var LoginForm = $(this);
        // Set button as a variable
        var button = LoginForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
        hideNotices(LoginForm);

        // Setup our form data array
        var data = {
                user_login: LoginForm.find( '#wampum_user_login' ).val(),
                user_password: LoginForm.find( '#wampum_user_pass' ).val(),
                remember: LoginForm.find( '#wampum_rememberme' ).val(),
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
                    LoginForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                    	// Only redirect if we have a value
                        var redirect = LoginForm.find( '.wampum_redirect' ).val();
                        if ( redirect ) {
			                console.log(redirect);
                        	if ( 'membership_form' == redirect ) {
			                	// Swap forms
			                	LoginForm.hide();
			                	$('#wampum_user_membership_form').show();
                        	} else {
		                        // Refresh/redirect
		                        window.location.replace( redirect );
                        	}
                        } else {
	                    	LoginForm.fadeOut('fast');
	                    }
                    });
                } else {
                    // Display error message
                    LoginForm.hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
                }
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                LoginForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
				// Re-enable the button
                button.html(button_html).attr( 'disabled', false );
            }
        });

    });

    var PasswordForm = $( '#wampum_user_password_form' );

    if ( PasswordForm.length ) {

	    var strength = {
	        0: "Weak",
	        1: "Weak",
	        2: "Okay",
	        3: "Good",
	        4: "Great",
	    }

	    var field = document.getElementById('wampum_user_password');
	    var meter = PasswordForm.find( '.password-strength-meter' );
	    var text  = PasswordForm.find( '.password-strength-text' );

	    field.addEventListener( 'input', function() {

	        var val    = field.value;
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

	}

    // PasswordForm.submit(function(e){
	$( 'body' ).on( 'submit', '#wampum_user_password_form', function(e) {

		console.log('Password form submitted');

        e.preventDefault();

        // Set the form as a variable
        var PasswordForm = $(this);
        // Set button as a variable
        var button = PasswordForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
        hideNotices(PasswordForm);

        var password = PasswordForm.find( '#wampum_user_password' ).val();
        var confirm  = PasswordForm.find( '#wampum_user_password_confirm' ).val();

        if ( password !== confirm ) {
            PasswordForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.mismatch + '</div>').fadeIn('fast');
            // Re-enable the button
				// Re-enable the button
            button.html(button_html).attr( 'disabled', false );
            // Stop the submission!
            return false;
        }

        var user_id = PasswordForm.find('.wampum_user_id').val();

        // Setup our form data array
        var data = {
                password: PasswordForm.find( '#wampum_user_password' ).val(),
            };

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wp/v2/users/' + user_id,
            data: data,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {
                // Display success message
                PasswordForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                	// Only redirect if we have a value
                    var redirect = PasswordForm.find( '.wampum_redirect' ).val();
                    if ( redirect ) {
                        // Refresh/redirect
                        window.location.replace( redirect );
                    } else {
                    	PasswordForm.fadeOut('fast');
                    }
                });
            },
            fail: function( response ) {
                // Not sure when this would happen, but fallbacks!
                PasswordForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
				// Re-enable the button
                button.html(button_html).attr( 'disabled', false );
            }
        });

    });

    $( 'body' ).on( 'submit', '#wampum_user_membership_form', function(e) {

    	console.log('Membership form submitted');

        e.preventDefault();

        // Set the form as a variable
        var MembershipForm = $(this);
        // Set button as a variable
        var button = MembershipForm.find( '.wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
		hideNotices(MembershipForm);

        // Setup our form data array
        var data = {
                plan_id: MembershipForm.find( '#wampum_plan_id' ).val(),
                first_name: MembershipForm.find( '#wampum_membership_first_name' ).val(),
                last_name: MembershipForm.find( '#wampum_membership_last_name' ).val(),
                user_email: MembershipForm.find( '#wampum_membership_email' ).val(),
                username: MembershipForm.find( '#wampum_membership_username' ).val(),
                password: MembershipForm.find( '#wampum_membership_password' ).val(),
                say_what: MembershipForm.find( '#wampum_say_what' ).val(), // honeypot
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = MembershipForm.find( '#wampum_ss_baseuri' ).val();
        var SharpSpringEndpoint = MembershipForm.find( '#wampum_ss_endpoint' ).val();
        var urlParams = MembershipForm.serialize();

        // If we have SharpSpring data, add the main __ss_noform code right after the form
        if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
            // This fixes the error missing __ss_noform push
            MembershipForm.after( '<script type="text/javascript">var __ss_noform = __ss_noform || [];</script>' );
        }

        $.ajax({
            method: 'POST',
            url: wampum_user_forms.root + 'wampum/v1/membership/',
            data: data,
            // async: false,
            beforeSend: function ( xhr ) {
                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
            },
            success: function( response ) {

            	console.log(response);

                if ( response.success == true ) {

                	var user_id = response.user;

                    // If this is a SharpSpring form, send that data!
                    if ( SharpSpringBaseURI && SharpSpringEndpoint ) {
                        // https://demodia.com/discovering-demand/how-to-create-multiple-forms-in-sharpspring
                        $.ajax({
                            url: SharpSpringBaseURI + SharpSpringEndpoint + '/jsonp/?' + urlParams,
                            contentType: "application/json",
                            dataType: 'jsonp',
                            success: function( response ) {
                                console.log(response);
                            }
                        });
                    }

                    // Display success message
                    MembershipForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
                    	// Get the redirect value
	                    var redirect = MembershipForm.find( '.wampum_redirect' ).val();
	                    // If a user was created during the process
						if ( user_id ) {
							var PasswordForm = $('#wampum_user_password_form');
							PasswordForm.find( '.wampum_user_id' ).val(user_id);
							PasswordForm.find( '.wampum_redirect' ).val(redirect);
		                	// Swap forms
		                	MembershipForm.hide();
		                	PasswordForm.show();
						} else {
		                	// Only redirect if we have a value
		                    if ( redirect ) {
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
		                }
					});

                } else {

                    // Display error message
                    MembershipForm.hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');

	                MembershipForm.on( 'click', '.login-link', function(e) {

	                	e.preventDefault();

	                	hideNotices(MembershipForm);

	                	var LoginForm = $('#wampum_user_login_form');

	                	// Swap forms
	                	MembershipForm.hide();
	                	LoginForm.show();

	                	// Set submitted email value as the login field
			        	LoginForm.find('#wampum_user_login').val(data.user_email);

			        	// TODO: HOW THE HECK DO WE GET BACK TO THE MEMBERSHIP FORM!!!!!

			        	// If user goes to login, back to membership, then to login, we'd have duplicate back buttons
			        	LoginForm.find('.wampum-back').remove();

			        	// Add back button
			        	LoginForm.find('.wampum_submit').after('<a class="wampum-back" href="#">&nbsp;&nbsp;Go back</a>');

						// On click of the back button
						LoginForm.on( 'click', '.wampum-back', function(e) {
							e.preventDefault();
		                	// Swap forms
		                	LoginForm.hide();
    	                	MembershipForm.show();
    	                	// Clear the password field
    	                	LoginForm.find('#wampum_user_pass').val('');
						});

					});
                }
            },
            fail: function( response ) {
                // console.log(response);
                // Not sure when this would happen, but fallbacks!
                MembershipForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.failure + '</div>').fadeIn('fast');
            },
            complete: function( response ) {
				// Re-enable the button
            	button.html(button_html).attr( 'disabled', false );
            }

        });

    });

	function hideNotices( Form ) {
		Form.find('.wampum-notice').fadeOut('fast');
	}

})( jQuery );
