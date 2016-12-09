;(function( $ ) {
    'use strict';

    // var LoginForm = $( '#wampum_user_login_form' );

    // if ( LoginForm.length ) {

	    // LoginForm.submit(function(e){
	    $( '.wampum-form' ).on( 'submit', '#wampum_user_login_form', function(e) {

	    	var LoginForm = $(this);

	    	console.log('Login form submitted');

	        e.preventDefault();

	        // Set button as a variable
	        var login_button = LoginForm.find( '#wampum_submit' );
	        // Disable the button
	        // NOTE: We can't do the fun loading icon because it's not a button, it's an input[type="submit"]
	        login_button.attr( 'disabled', true );

	        // Hide any notices
	        LoginForm.find('.wampum-notice').fadeOut('fast');

	        // Setup our form data array
	        var data = {
	                user_login: LoginForm.find( '#wampum_user_login' ).val(),
	                user_password: LoginForm.find( '#wampum_user_pass' ).val(),
	                remember: LoginForm.find( '#wampum_rememberme' ).val(),
	            };

	        // Display an error if username and password fields are emmpty. Why is those fields not required in WP core?
	        if ( ! ( data.user_login && data.user_password ) ) {
	            LoginForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.login.empty + '</div>').fadeIn('fast');
	            // Re-enable the button
	            // login_button.attr( 'disabled', false );
	            // Stop the submission!
	            return false;
	        }

	        $.ajax({
	            method: 'POST',
	            url: wampum_user_forms.root + 'wampum/v1/login/',
	            data: data,
	            beforeSend: function ( xhr ) {
	                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
	            },
	            success: function( response ) {
	                console.log(response);
	                if ( response.success == true ) {
	                    // Display success message
	                    LoginForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
	                        // Refresh/redirect
	                        window.location.replace( LoginForm.find( 'input[name="redirect_to"]' ).val() );
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
	                // login_button.html().attr( 'disabled', false );
	            }
	        });

	    });

	// }

})( jQuery );


;(function( $ ) {
    'use strict';

    var PasswordForm = $( '#wampum_user_password_form' );

    if ( PasswordForm.length ) {

	    console.log(PasswordForm);

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
    	$( '.wampum-form' ).on( 'submit', '#wampum_user_password_form', function(e) {

	    	var PasswordForm = $(this);

	        e.preventDefault();

	        // Set button as a variable
	        var button = PasswordForm.find( '#wampum_submit' );
	        // Get the button text/value so we can add it back later
	        var button_html = button.html();
	        // Disable the button
	        button.attr( 'disabled', true );
	        // Set the button text/value to loading icons
	        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

	        // Hide any notices
	        PasswordForm.find('.wampum-notice').fadeOut('fast');

	        var password = PasswordForm.find( '#wampum_user_password' ).val();
	        var confirm  = PasswordForm.find( '#wampum_user_password_confirm' ).val();

	        if ( password !== confirm ) {
	            PasswordForm.hide().prepend('<div class="wampum-notice error">' + wampum_user_forms.mismatch + '</div>').fadeIn('fast');
	            // Re-enable the button
	            button.html(button_html).attr( 'disabled', false );
	            // Stop the submission!
	            return false;
	        }

	        // Setup our form data array
	        var data = {
	                password: PasswordForm.find( '#wampum_user_password' ).val(),
	            };

	        $.ajax({
	            method: 'POST',
	            url: wampum_user_forms.root + 'wp/v2/users/' + wampum_user_forms.current_user_id,
	            data: data,
	            beforeSend: function ( xhr ) {
	                xhr.setRequestHeader( 'X-WP-Nonce', wampum_user_forms.nonce );
	            },
	            success: function( response ) {
	                // Display success message
	                PasswordForm.hide().prepend('<div class="wampum-notice success">Success!</div>').fadeIn('fast', function() {
	                    // Refresh/redirect
	                    window.location.replace( PasswordForm.find( 'input[name="redirect_to"]' ).val() );
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

})( jQuery );


;(function( $ ) {
    'use strict';

    // MembershipForm.submit(function(e){
    $( '.wampum-form' ).on( 'submit', '#wampum_user_membership_form', function(e) {

    	console.log('Membership Form Submitted!');

        e.preventDefault();

        var MembershipForm = $(this);

        // Set button as a variable
        var button = MembershipForm.find( '#wampum_submit' );
        // Get the button text/value so we can add it back later
        var button_html = button.html();
        // Disable the button
        button.attr( 'disabled', true );
        // Set the button text/value to loading icons
        button.html( '<div class ="wampum-loading"><div class="wampum-loading-circle wampum-loading-circle1">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle2">&#8226;</div><div class="wampum-loading-circle wampum-loading-circle3">&#8226;</div></div>' );

        // Hide any notices
        MembershipForm.find('.wampum-notice').fadeOut('fast');

        // Setup our form data array
        var data = {
                plan_id: MembershipForm.find( '#wampum_plan_id' ).val(),
                first_name: MembershipForm.find( '#wampum_membership_first_name' ).val(),
                last_name: MembershipForm.find( '#wampum_membership_last_name' ).val(),
                user_email: MembershipForm.find( '#wampum_membership_email' ).val(),
                username: MembershipForm.find( '#wampum_membership_username' ).val(),
                password: MembershipForm.find( '#wampum_membership_password' ).val(),
                say_what: MembershipForm.find( '#wampum_say_what' ).val(), // honeypot
                redirect: MembershipForm.find( 'input[name="redirect_to"]' ).val(),
            };

        // SharpSpring data, incase we need it later
        var SharpSpringBaseURI  = MembershipForm.find( '#wampum_ss_baseuri' ).val().trim();
        var SharpSpringEndpoint = MembershipForm.find( '#wampum_ss_endpoint' ).val().trim();
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
                        // Refresh/redirect
                        window.location.replace( response.redirect );
                    });

                } else {
                    // Display error message
                    MembershipForm.hide().prepend('<div class="wampum-notice error">' + response.message + '</div>').fadeIn('fast');
	                $(MembershipForm).on( 'click', '.login-link', function(click) {
	                	click.preventDefault();
						MembershipForm.parent('.wampum-form').html(wampum_user_forms.login.form);
						var LoginForm = $( '#wampum_user_login_form' ); // Listener? Not working?
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

})( jQuery );
